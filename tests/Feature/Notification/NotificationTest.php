<?php

namespace Tests\Feature\Notification;

use App\Enums\EnrollmentStatus;
use App\Enums\NotificationType;
use App\Enums\Semester;
use App\Enums\TaskType;
use App\Models\AktivitasBelajar;
use App\Models\Enrollment;
use App\Models\GuruDosen;
use App\Models\Mahasiswa;
use App\Models\MataKuliah;
use App\Models\Notifikasi;
use App\Models\Prioritas;
use App\Models\ProgresTugasAkademik;
use App\Models\StatusBelajar;
use App\Models\StatusTugas;
use App\Models\Tugas;
use App\Models\User;
use App\Services\AcademicTaskService;
use App\Services\NotificationService;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NotificationTest extends TestCase
{
    use RefreshDatabase;

    public function test_academic_task_distribution_creates_one_notification_per_active_recipient_and_dedups(): void
    {
        $lecturer = GuruDosen::factory()->create();
        $course = MataKuliah::factory()->create(['guru_dosen_id' => $lecturer->id_guru_dosen]);
        $students = Mahasiswa::factory()->count(3)->create();
        $inactive = Mahasiswa::factory()->create();
        $inactive->user->update(['is_active' => false]);
        $archived = Mahasiswa::factory()->create(['is_archived' => true, 'tanggal_arsip' => now()]);

        foreach ([...$students, $inactive, $archived] as $student) {
            Enrollment::factory()->create([
                'id_mahasiswa' => $student->id_mahasiswa,
                'id_mata_kuliah' => $course->id_mata_kuliah,
                'tahun_akademik' => '2026/2027',
                'semester' => Semester::Ganjil,
                'status' => EnrollmentStatus::Terdaftar,
            ]);
        }

        $this->actingAs($lecturer->user)
            ->post(route('lecturer.academic-tasks.store'), [
                'id_mata_kuliah' => $course->id_mata_kuliah,
                'judul_tugas' => 'Tugas Notifikasi',
                'deskripsi' => 'Distribusi dengan dedup.',
                'deadline' => now()->addWeek()->format('Y-m-d H:i:s'),
            ])
            ->assertRedirect();

        $task = Tugas::query()->where('judul_tugas', 'Tugas Notifikasi')->firstOrFail();

        $this->assertSame(3, Notifikasi::query()->where('tipe', NotificationType::AcademicTaskAssigned->value)->count());
        $this->assertDatabaseMissing('notifikasi', ['id_user' => $inactive->id_user]);
        $this->assertDatabaseMissing('notifikasi', ['id_user' => $archived->id_user]);

        $service = app(AcademicTaskService::class);

        foreach ($students as $student) {
            $service->distributeForEnrollment($student->enrollments()->firstOrFail());
        }

        $this->assertSame(3, Notifikasi::query()->where('tipe', NotificationType::AcademicTaskAssigned->value)->count());
        $this->assertSame(4, ProgresTugasAkademik::query()->where('id_tugas', $task->id_tugas)->count());
    }

    public function test_late_enrollment_only_notifies_for_new_progress(): void
    {
        $lecturer = GuruDosen::factory()->create();
        $course = MataKuliah::factory()->create(['guru_dosen_id' => $lecturer->id_guru_dosen]);
        $first = Mahasiswa::factory()->create();
        $late = Mahasiswa::factory()->create();
        Enrollment::factory()->create([
            'id_mahasiswa' => $first->id_mahasiswa,
            'id_mata_kuliah' => $course->id_mata_kuliah,
            'status' => EnrollmentStatus::Terdaftar,
        ]);

        $task = $this->academicTask($course, $lecturer);
        app(AcademicTaskService::class)->distributeForEnrollment($first->enrollments()->firstOrFail());
        $this->assertSame(1, Notifikasi::query()->count());

        $enrollment = Enrollment::factory()->create([
            'id_mahasiswa' => $late->id_mahasiswa,
            'id_mata_kuliah' => $course->id_mata_kuliah,
            'status' => EnrollmentStatus::Terdaftar,
        ]);
        app(AcademicTaskService::class)->distributeForEnrollment($enrollment);

        $this->assertSame(2, Notifikasi::query()->count());
        $this->assertDatabaseHas('notifikasi', [
            'id_user' => $late->id_user,
            'resource_id' => $task->id_tugas,
            'tipe' => NotificationType::AcademicTaskAssigned->value,
        ]);
    }

    public function test_academic_deadline_h1_notifies_only_unfinished_open_non_archived_progress(): void
    {
        $this->travelTo(CarbonImmutable::parse('2026-11-19 03:00:00', 'UTC'));

        $lecturer = GuruDosen::factory()->create();
        $course = MataKuliah::factory()->create(['guru_dosen_id' => $lecturer->id_guru_dosen]);
        $students = Mahasiswa::factory()->count(4)->create();
        foreach ($students as $student) {
            Enrollment::factory()->create([
                'id_mahasiswa' => $student->id_mahasiswa,
                'id_mata_kuliah' => $course->id_mata_kuliah,
                'status' => EnrollmentStatus::Terdaftar,
            ]);
        }

        $deadline = CarbonImmutable::parse('2026-11-20 09:00:00', 'Asia/Jakarta')->utc();
        $service = app(AcademicTaskService::class);

        $open = $service->create($lecturer, [
            'id_mata_kuliah' => $course->id_mata_kuliah,
            'judul_tugas' => 'Tugas H-1',
            'deskripsi' => 'Harus diingatkan.',
            'deadline' => $deadline->format('Y-m-d H:i:s'),
        ])['task'];

        $progress = $open->progres()->orderBy('id_progress')->firstOrFail();
        $progress->forceFill([
            'id_status' => StatusTugas::query()->where('nama_status', 'Selesai')->value('id_status'),
        ])->save();

        $closed = $service->create($lecturer, [
            'id_mata_kuliah' => $course->id_mata_kuliah,
            'judul_tugas' => 'Tugas H-1 Ditutup',
            'deadline' => $deadline->format('Y-m-d H:i:s'),
        ])['task'];
        $service->close($closed);

        $archived = $service->create($lecturer, [
            'id_mata_kuliah' => $course->id_mata_kuliah,
            'judul_tugas' => 'Tugas H-1 Arsip',
            'deadline' => $deadline->format('Y-m-d H:i:s'),
        ])['task'];
        $archived->forceFill(['is_archived' => true, 'tanggal_arsip' => now()])->save();

        $inserted = app(NotificationService::class)->notifyAcademicTaskDeadlines();

        $this->assertSame(3, $inserted);
        $this->assertSame(3, Notifikasi::query()
            ->where('tipe', NotificationType::AcademicTaskDeadline->value)
            ->where('resource_id', $open->id_tugas)
            ->count());
        $this->assertDatabaseMissing('notifikasi', [
            'tipe' => NotificationType::AcademicTaskDeadline->value,
            'resource_id' => $closed->id_tugas,
        ]);
        $this->assertDatabaseMissing('notifikasi', [
            'tipe' => NotificationType::AcademicTaskDeadline->value,
            'resource_id' => $archived->id_tugas,
        ]);

        $this->assertSame(0, app(NotificationService::class)->notifyAcademicTaskDeadlines());
    }

    public function test_personal_task_deadline_does_not_create_notification(): void
    {
        $student = Mahasiswa::factory()->create();
        Tugas::factory()->create([
            'id_mahasiswa_pemilik' => $student->id_mahasiswa,
            'task_type' => TaskType::Personal,
            'deadline' => now()->addDay(),
        ]);

        $this->assertSame(0, app(NotificationService::class)->notifyAcademicTaskDeadlines());
        $this->assertDatabaseCount('notifikasi', 0);
    }

    public function test_study_activity_reminder_only_for_planned_activity_within_60_minutes_and_dedups(): void
    {
        $this->travelTo(CarbonImmutable::parse('2026-11-19 03:00:00', 'UTC'));

        $student = Mahasiswa::factory()->create();
        $planned = $this->activity($student, now()->addMinutes(30), 'Direncanakan');
        $tooFar = $this->activity($student, now()->addMinutes(90), 'Direncanakan');
        $finished = $this->activity($student, now()->addMinutes(30), 'Selesai');
        $archived = $this->activity($student, now()->addMinutes(30), 'Direncanakan', true);

        $service = app(NotificationService::class);
        $this->assertSame(1, $service->notifyStudyActivityReminders());
        $this->assertSame(1, Notifikasi::query()->where('tipe', NotificationType::StudyActivityReminder->value)->count());
        $this->assertDatabaseHas('notifikasi', ['resource_id' => $planned->id_aktivitas]);
        $this->assertDatabaseMissing('notifikasi', ['resource_id' => $tooFar->id_aktivitas]);
        $this->assertDatabaseMissing('notifikasi', ['resource_id' => $finished->id_aktivitas]);
        $this->assertDatabaseMissing('notifikasi', ['resource_id' => $archived->id_aktivitas]);

        $this->assertSame(0, $service->notifyStudyActivityReminders());
        $this->assertSame(1, Notifikasi::query()->where('tipe', NotificationType::StudyActivityReminder->value)->count());
    }

    public function test_notification_is_not_created_for_inactive_or_archived_student(): void
    {
        $lecturer = GuruDosen::factory()->create();
        $course = MataKuliah::factory()->create(['guru_dosen_id' => $lecturer->id_guru_dosen]);
        $inactive = Mahasiswa::factory()->create();
        $inactive->user->update(['is_active' => false]);
        $archived = Mahasiswa::factory()->create(['is_archived' => true, 'tanggal_arsip' => now()]);
        $task = $this->academicTask($course, $lecturer);
        $service = app(NotificationService::class);

        $this->assertSame(0, $service->notifyAcademicTaskAssigned($task, [
            $inactive->id_mahasiswa,
            $archived->id_mahasiswa,
        ]));
        $this->assertDatabaseCount('notifikasi', 0);
    }

    public function test_cleanup_removes_only_notifications_older_than_90_days(): void
    {
        $user = User::factory()->create();
        $old = $this->notification($user, 'old', now()->subDays(91));
        $fresh = $this->notification($user, 'fresh', now()->subDays(89));

        $this->assertSame(1, app(NotificationService::class)->cleanup());
        $this->assertDatabaseMissing('notifikasi', ['id_notifikasi' => $old->id_notifikasi]);
        $this->assertDatabaseHas('notifikasi', ['id_notifikasi' => $fresh->id_notifikasi]);
    }

    public function test_user_only_sees_and_marks_own_notifications(): void
    {
        $owner = User::factory()->create();
        $other = User::factory()->create();
        $own = $this->notification($owner, 'own', now());
        $otherNotification = $this->notification($other, 'other', now());

        $this->actingAs($owner)
            ->get(route('notifications.index'))
            ->assertOk()
            ->assertSee('Pesan own')
            ->assertDontSee('Pesan other')
            ->assertSee('1 belum dibaca');

        $this->actingAs($owner)
            ->patch(route('notifications.read', $otherNotification))
            ->assertNotFound();

        $this->actingAs($owner)
            ->patch(route('notifications.read', $own))
            ->assertRedirect();

        $this->assertTrue($own->fresh()->is_read);
        $this->assertNotNull($own->fresh()->read_at);
        $this->assertFalse($otherNotification->fresh()->is_read);

        $unread = $this->notification($owner, 'second', now());

        $this->actingAs($owner)
            ->patch(route('notifications.read-all'))
            ->assertRedirect();

        $this->assertTrue($unread->fresh()->is_read);
        $this->assertFalse($otherNotification->fresh()->is_read);
    }

    public function test_notification_commands_run_and_log_completion(): void
    {
        $this->artisan('notifications:dispatch')->assertExitCode(0);
        $this->artisan('notifications:cleanup')->assertExitCode(0);
    }

    private function academicTask(MataKuliah $course, GuruDosen $lecturer): Tugas
    {
        return Tugas::create([
            'id_mata_kuliah' => $course->id_mata_kuliah,
            'id_prioritas' => Prioritas::query()->where('nama_prioritas', 'Sedang')->value('id_prioritas'),
            'id_status' => null,
            'id_user' => $lecturer->id_user,
            'id_pembuat_guru_dosen' => $lecturer->id_guru_dosen,
            'id_mahasiswa_pemilik' => null,
            'task_type' => TaskType::Academic,
            'judul_tugas' => 'Tugas Akademik Notifikasi',
            'deskripsi' => 'Konteks notifikasi',
            'deadline' => now()->addWeek(),
            'is_closed' => false,
            'is_archived' => false,
        ]);
    }

    private function activity(Mahasiswa $student, $start, string $status, bool $archived = false): AktivitasBelajar
    {
        return AktivitasBelajar::factory()->create([
            'id_mahasiswa' => $student->id_mahasiswa,
            'waktu_mulai' => $start,
            'id_status_belajar' => StatusBelajar::query()->where('nama_status', $status)->value('id_status_belajar'),
            'is_archived' => $archived,
            'tanggal_arsip' => $archived ? now() : null,
        ]);
    }

    private function notification(User $user, string $key, $createdAt): Notifikasi
    {
        return Notifikasi::create([
            'id_user' => $user->id_user,
            'tipe' => NotificationType::AcademicTaskAssigned,
            'judul' => 'Judul '.$key,
            'pesan' => 'Pesan '.$key,
            'resource_type' => 'tugas',
            'resource_id' => null,
            'is_read' => false,
            'read_at' => null,
            'dedup_key' => 'test:'.$key,
            'tanggal_dibuat' => $createdAt,
        ]);
    }
}
