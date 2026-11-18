<?php

namespace Tests\Feature\Academic;

use App\Enums\EnrollmentStatus;
use App\Enums\Role;
use App\Enums\Semester;
use App\Enums\TaskType;
use App\Models\Enrollment;
use App\Models\GuruDosen;
use App\Models\Mahasiswa;
use App\Models\MataKuliah;
use App\Models\Prioritas;
use App\Models\ProgresTugasAkademik;
use App\Models\StatusTugas;
use App\Models\Tugas;
use App\Models\User;
use App\Services\AcademicTaskService;
use App\Services\EnrollmentService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AcademicTaskTest extends TestCase
{
    use RefreshDatabase;

    private function courseFor(GuruDosen $lecturer): MataKuliah
    {
        return MataKuliah::factory()->create([
            'guru_dosen_id' => $lecturer->id_guru_dosen,
        ]);
    }

    private function enroll(Mahasiswa $student, MataKuliah $course, EnrollmentStatus $status = EnrollmentStatus::Terdaftar): Enrollment
    {
        return Enrollment::factory()->create([
            'id_mahasiswa' => $student->id_mahasiswa,
            'id_mata_kuliah' => $course->id_mata_kuliah,
            'status' => $status,
        ]);
    }

    private function academicTask(MataKuliah $course, GuruDosen $lecturer, array $attributes = []): Tugas
    {
        return Tugas::create([
            'id_mata_kuliah' => $course->id_mata_kuliah,
            'id_prioritas' => Prioritas::query()->where('nama_prioritas', 'Sedang')->value('id_prioritas'),
            'id_status' => null,
            'id_user' => $lecturer->id_user,
            'id_pembuat_guru_dosen' => $lecturer->id_guru_dosen,
            'id_mahasiswa_pemilik' => null,
            'task_type' => TaskType::Academic,
            'judul_tugas' => 'Tugas Akademik',
            'deskripsi' => 'Deskripsi akademik',
            'deadline' => now()->addWeek(),
            'is_closed' => false,
            'is_archived' => false,
            'row_version' => 1,
            ...$attributes,
        ]);
    }

    private function statusId(string $name): int
    {
        return (int) StatusTugas::query()->where('nama_status', $name)->value('id_status');
    }

    public function test_lecturer_create_distributes_initial_progress_only_to_registered_students(): void
    {
        $lecturer = GuruDosen::factory()->create();
        $course = $this->courseFor($lecturer);
        $first = Mahasiswa::factory()->create();
        $second = Mahasiswa::factory()->create();
        $cancelled = Mahasiswa::factory()->create();
        $this->enroll($first, $course);
        $this->enroll($second, $course);
        $this->enroll($cancelled, $course, EnrollmentStatus::Dibatalkan);

        $this->actingAs($lecturer->user)
            ->post(route('lecturer.academic-tasks.store'), [
                'id_mata_kuliah' => $course->id_mata_kuliah,
                'id_prioritas' => Prioritas::query()->where('nama_prioritas', 'Tinggi')->value('id_prioritas'),
                'judul_tugas' => 'Presentasi Kelompok',
                'deskripsi' => 'Buat presentasi.',
                'deadline' => now()->addDays(10)->format('Y-m-d H:i:s'),
            ])
            ->assertRedirect();

        $task = Tugas::query()->where('judul_tugas', 'Presentasi Kelompok')->firstOrFail();

        $this->assertSame(TaskType::Academic, $task->task_type);
        $this->assertNull($task->id_status);
        $this->assertNull($task->id_mahasiswa_pemilik);
        $this->assertFalse($task->is_closed);
        $this->assertSame(2, $task->progres()->count());

        foreach ([$first, $second] as $student) {
            $this->assertDatabaseHas('tugas_akademik_progress', [
                'id_tugas' => $task->id_tugas,
                'id_mahasiswa' => $student->id_mahasiswa,
                'id_status' => $this->statusId('Belum Dikerjakan'),
                'id_user_diubah_oleh' => $lecturer->id_user,
            ]);
        }

        $this->assertDatabaseMissing('tugas_akademik_progress', [
            'id_tugas' => $task->id_tugas,
            'id_mahasiswa' => $cancelled->id_mahasiswa,
        ]);
    }

    public function test_redistribution_is_idempotent_and_does_not_reset_progress(): void
    {
        $lecturer = GuruDosen::factory()->create();
        $course = $this->courseFor($lecturer);
        $student = Mahasiswa::factory()->create();
        $enrollment = $this->enroll($student, $course);
        $task = $this->academicTask($course, $lecturer);
        $service = app(AcademicTaskService::class);

        $service->distributeForEnrollment($enrollment);
        $progress = $task->progres()->firstOrFail();
        $progress->forceFill(['id_status' => $this->statusId('Selesai')])->save();

        $service->distributeForEnrollment($enrollment);
        $service->distributeForEnrollment($enrollment);

        $this->assertSame(1, $task->progres()->count());
        $this->assertSame($this->statusId('Selesai'), $progress->fresh()->id_status);
    }

    public function test_late_enrollment_receives_progress_for_open_task_only(): void
    {
        $lecturer = GuruDosen::factory()->create();
        $course = $this->courseFor($lecturer);
        $admin = User::factory()->role(Role::Admin)->create();
        $openStudent = Mahasiswa::factory()->create();
        $closedStudent = Mahasiswa::factory()->create();

        $open = $this->academicTask($course, $lecturer, ['judul_tugas' => 'Tugas Terbuka']);
        $closed = $this->academicTask($course, $lecturer, ['judul_tugas' => 'Tugas Ditutup']);
        app(AcademicTaskService::class)->close($closed);

        foreach ([$openStudent, $closedStudent] as $student) {
            $this->actingAs($admin)->post(route('admin.enrollments.store'), [
                'id_mahasiswa' => $student->id_mahasiswa,
                'id_mata_kuliah' => $course->id_mata_kuliah,
                'tahun_akademik' => '2026/2027',
                'semester' => Semester::Ganjil->value,
            ])->assertRedirect(route('admin.enrollments.index'));
        }

        $this->assertDatabaseHas('tugas_akademik_progress', [
            'id_tugas' => $open->id_tugas,
            'id_mahasiswa' => $openStudent->id_mahasiswa,
        ]);
        $this->assertDatabaseMissing('tugas_akademik_progress', [
            'id_tugas' => $closed->id_tugas,
            'id_mahasiswa' => $closedStudent->id_mahasiswa,
        ]);
    }

    public function test_reactivated_enrollment_receives_missing_progress(): void
    {
        $lecturer = GuruDosen::factory()->create();
        $course = $this->courseFor($lecturer);
        $student = Mahasiswa::factory()->create();
        $enrollment = $this->enroll($student, $course, EnrollmentStatus::Dibatalkan);
        $task = $this->academicTask($course, $lecturer);

        app(EnrollmentService::class)->enroll([
            'id_mahasiswa' => $student->id_mahasiswa,
            'id_mata_kuliah' => $course->id_mata_kuliah,
            'tahun_akademik' => $enrollment->tahun_akademik,
            'semester' => $enrollment->semester->value,
        ]);

        $this->assertSame(EnrollmentStatus::Terdaftar, $enrollment->fresh()->status);
        $this->assertDatabaseHas('tugas_akademik_progress', [
            'id_tugas' => $task->id_tugas,
            'id_mahasiswa' => $student->id_mahasiswa,
        ]);
    }

    public function test_student_updates_own_progress_and_actor_is_recorded(): void
    {
        $lecturer = GuruDosen::factory()->create();
        $course = $this->courseFor($lecturer);
        $student = Mahasiswa::factory()->create();
        $this->enroll($student, $course);
        $task = $this->academicTask($course, $lecturer);
        app(AcademicTaskService::class)->distributeForEnrollment($student->enrollments()->firstOrFail());

        $progress = $task->progres()->firstOrFail();

        $this->actingAs($student->user)
            ->patch(route('mahasiswa.academic-tasks.progress.update', $task), [
                'id_status' => $this->statusId('Sedang Dikerjakan'),
                'row_version' => $progress->row_version,
            ])
            ->assertRedirect(route('mahasiswa.academic-tasks.show', $task));

        $progress->refresh();

        $this->assertSame($this->statusId('Sedang Dikerjakan'), $progress->id_status);
        $this->assertSame($student->id_user, $progress->id_user_diubah_oleh);
        $this->assertSame(2, $progress->row_version);
    }

    public function test_student_cannot_update_another_students_progress(): void
    {
        $lecturer = GuruDosen::factory()->create();
        $course = $this->courseFor($lecturer);
        $owner = Mahasiswa::factory()->create();
        $other = Mahasiswa::factory()->create();
        $this->enroll($owner, $course);
        $this->enroll($other, $course);
        $task = $this->academicTask($course, $lecturer);
        app(AcademicTaskService::class)->distributeForEnrollment($owner->enrollments()->firstOrFail());

        $progress = $task->progres()->firstOrFail();

        $this->actingAs($other->user)
            ->patch(route('mahasiswa.academic-tasks.progress.update', $task), [
                'id_status' => $this->statusId('Selesai'),
                'row_version' => $progress->row_version,
            ])
            ->assertNotFound();

        $this->assertSame($this->statusId('Belum Dikerjakan'), $progress->fresh()->id_status);
    }

    public function test_stale_progress_update_is_rejected_and_preserves_newer_value(): void
    {
        $lecturer = GuruDosen::factory()->create();
        $course = $this->courseFor($lecturer);
        $student = Mahasiswa::factory()->create();
        $this->enroll($student, $course);
        $task = $this->academicTask($course, $lecturer);
        $service = app(AcademicTaskService::class);
        $service->distributeForEnrollment($student->enrollments()->firstOrFail());

        $progress = $task->progres()->firstOrFail();
        $service->updateProgress($progress, $this->statusId('Selesai'), $student->user, $progress->row_version);

        $this->actingAs($student->user)
            ->patch(route('mahasiswa.academic-tasks.progress.update', $task), [
                'id_status' => $this->statusId('Belum Dikerjakan'),
                'row_version' => 1,
            ])
            ->assertSessionHasErrors('row_version');

        $this->assertSame($this->statusId('Selesai'), $progress->fresh()->id_status);
    }

    public function test_lecturer_corrects_progress_only_within_assigned_course(): void
    {
        $lecturer = GuruDosen::factory()->create();
        $otherLecturer = GuruDosen::factory()->create();
        $course = $this->courseFor($lecturer);
        $student = Mahasiswa::factory()->create();
        $this->enroll($student, $course);
        $task = $this->academicTask($course, $lecturer);
        app(AcademicTaskService::class)->distributeForEnrollment($student->enrollments()->firstOrFail());
        $progress = $task->progres()->firstOrFail();

        $this->actingAs($otherLecturer->user)
            ->patch(route('lecturer.progress.update', $progress), [
                'id_status' => $this->statusId('Selesai'),
                'row_version' => $progress->row_version,
            ])
            ->assertNotFound();

        $this->actingAs($lecturer->user)
            ->patch(route('lecturer.progress.update', $progress), [
                'id_status' => $this->statusId('Selesai'),
                'row_version' => $progress->row_version,
            ])
            ->assertRedirect(route('lecturer.academic-tasks.progress', $task));

        $progress->refresh();

        $this->assertSame($this->statusId('Selesai'), $progress->id_status);
        $this->assertSame($lecturer->id_user, $progress->id_user_diubah_oleh);
    }

    public function test_lecturer_cannot_manage_another_lecturers_task(): void
    {
        $owner = GuruDosen::factory()->create();
        $other = GuruDosen::factory()->create();
        $course = $this->courseFor($owner);
        $task = $this->academicTask($course, $owner);

        $this->actingAs($other->user)
            ->get(route('lecturer.academic-tasks.show', $task))
            ->assertNotFound();
        $this->actingAs($other->user)
            ->get(route('lecturer.academic-tasks.progress', $task))
            ->assertNotFound();
        $this->actingAs($other->user)
            ->patch(route('lecturer.academic-tasks.close', $task))
            ->assertNotFound();

        $this->assertFalse($task->fresh()->is_closed);
    }

    public function test_academic_task_edit_uses_row_version_and_ownership(): void
    {
        $lecturer = GuruDosen::factory()->create();
        $course = $this->courseFor($lecturer);
        $task = $this->academicTask($course, $lecturer, ['judul_tugas' => 'Judul Awal']);
        $service = app(AcademicTaskService::class);

        $service->update($task, [
            'id_prioritas' => $task->id_prioritas,
            'judul_tugas' => 'Judul Baru',
            'deskripsi' => $task->deskripsi,
            'deadline' => $task->deadline->format('Y-m-d H:i:s'),
            'row_version' => 1,
        ]);

        $this->actingAs($lecturer->user)
            ->put(route('lecturer.academic-tasks.update', $task), [
                'id_prioritas' => $task->id_prioritas,
                'judul_tugas' => 'Timpa',
                'deskripsi' => $task->deskripsi,
                'deadline' => $task->deadline->format('Y-m-d H:i:s'),
                'row_version' => 1,
            ])
            ->assertSessionHasErrors('row_version');

        $this->assertSame('Judul Baru', $task->fresh()->judul_tugas);
    }

    public function test_lecturer_cannot_create_task_for_another_lecturers_course(): void
    {
        $owner = GuruDosen::factory()->create();
        $other = GuruDosen::factory()->create();
        $course = $this->courseFor($owner);

        $this->actingAs($other->user)
            ->post(route('lecturer.academic-tasks.store'), [
                'id_mata_kuliah' => $course->id_mata_kuliah,
                'id_prioritas' => Prioritas::query()->value('id_prioritas'),
                'judul_tugas' => 'Tugas Liar',
                'deadline' => now()->addWeek()->format('Y-m-d H:i:s'),
            ])
            ->assertNotFound();

        $this->assertDatabaseMissing('tugas', ['judul_tugas' => 'Tugas Liar']);
    }

    public function test_admin_monitoring_is_read_only_and_never_exposes_personal_tasks(): void
    {
        $lecturer = GuruDosen::factory()->create();
        $course = $this->courseFor($lecturer);
        $student = Mahasiswa::factory()->create();
        $this->enroll($student, $course);
        $academic = $this->academicTask($course, $lecturer, ['judul_tugas' => 'Tugas Monitoring']);
        app(AcademicTaskService::class)->distributeForEnrollment($student->enrollments()->firstOrFail());

        $personal = Tugas::create([
            'id_mata_kuliah' => null,
            'id_prioritas' => Prioritas::query()->value('id_prioritas'),
            'id_status' => $this->statusId('Belum Dikerjakan'),
            'id_user' => $student->id_user,
            'id_pembuat_guru_dosen' => null,
            'id_mahasiswa_pemilik' => $student->id_mahasiswa,
            'task_type' => TaskType::Personal,
            'judul_tugas' => 'Rahasia Personal',
            'deskripsi' => 'Catatan pribadi',
            'deadline' => now()->addWeek(),
        ]);

        $admin = User::factory()->role(Role::Admin)->create();

        $this->actingAs($admin)
            ->get(route('admin.academic-monitoring.index'))
            ->assertOk()
            ->assertSee('Tugas Monitoring')
            ->assertDontSee('Rahasia Personal');

        $this->actingAs($admin)
            ->get(route('admin.academic-monitoring.tasks.show', $academic))
            ->assertOk();

        $this->actingAs($admin)
            ->get(route('admin.academic-monitoring.tasks.show', $personal))
            ->assertNotFound();

        $progress = ProgresTugasAkademik::query()->where('id_tugas', $academic->id_tugas)->firstOrFail();

        $this->actingAs($admin)
            ->patch(route('lecturer.progress.update', $progress), [
                'id_status' => $this->statusId('Selesai'),
                'row_version' => $progress->row_version,
            ])
            ->assertForbidden();

        $this->actingAs($admin)
            ->post(route('lecturer.academic-tasks.store'), [
                'id_mata_kuliah' => $course->id_mata_kuliah,
                'id_prioritas' => $personal->id_prioritas,
                'judul_tugas' => 'Admin Tidak Boleh',
                'deadline' => now()->addWeek()->format('Y-m-d H:i:s'),
            ])
            ->assertForbidden();

        $this->assertSame($this->statusId('Belum Dikerjakan'), $progress->fresh()->id_status);
    }

    public function test_closing_task_stops_new_distribution_and_is_idempotent(): void
    {
        $lecturer = GuruDosen::factory()->create();
        $course = $this->courseFor($lecturer);
        $task = $this->academicTask($course, $lecturer);
        $service = app(AcademicTaskService::class);

        $this->actingAs($lecturer->user)
            ->patch(route('lecturer.academic-tasks.close', $task))
            ->assertRedirect(route('lecturer.academic-tasks.show', $task));

        $this->assertTrue($task->fresh()->is_closed);
        $this->assertSame(2, $task->fresh()->row_version);

        $service->close($task->fresh());
        $this->assertSame(2, $task->fresh()->row_version);

        $student = Mahasiswa::factory()->create();
        $this->enroll($student, $course);

        $this->assertSame(0, $task->progres()->count());
    }

    public function test_academic_pages_render_for_authorized_roles(): void
    {
        $lecturer = GuruDosen::factory()->create();
        $course = $this->courseFor($lecturer);
        $student = Mahasiswa::factory()->create();
        $this->enroll($student, $course);
        $task = $this->academicTask($course, $lecturer, ['judul_tugas' => 'Tugas Tampilan']);
        app(AcademicTaskService::class)->distributeForEnrollment($student->enrollments()->firstOrFail());
        $admin = User::factory()->role(Role::Admin)->create();

        $this->actingAs($lecturer->user)->get(route('lecturer.academic-tasks.index'))->assertOk()->assertSee('Tugas Tampilan');
        $this->actingAs($lecturer->user)->get(route('lecturer.academic-tasks.create'))->assertOk();
        $this->actingAs($lecturer->user)->get(route('lecturer.academic-tasks.show', $task))->assertOk();
        $this->actingAs($lecturer->user)->get(route('lecturer.academic-tasks.edit', $task))->assertOk();
        $this->actingAs($lecturer->user)->get(route('lecturer.academic-tasks.progress', $task))->assertOk();
        $this->actingAs($lecturer->user)->get(route('lecturer.courses.show', $course))->assertOk()->assertSee('Tugas Tampilan');

        $this->actingAs($student->user)->get(route('mahasiswa.tasks.index'))->assertOk()->assertSee('Tugas Tampilan');
        $this->actingAs($student->user)->get(route('mahasiswa.academic-tasks.show', $task))->assertOk();
        $this->actingAs($student->user)->get(route('mahasiswa.courses.show', $course))->assertOk()->assertSee('Tugas Tampilan');

        $this->actingAs($admin)->get(route('admin.academic-monitoring.index'))->assertOk()->assertSee('Tugas Tampilan');
        $this->actingAs($admin)->get(route('admin.academic-monitoring.tasks.show', $task))->assertOk();
    }

    public function test_student_cannot_access_academic_task_they_have_no_progress_for(): void
    {
        $lecturer = GuruDosen::factory()->create();
        $course = $this->courseFor($lecturer);
        $task = $this->academicTask($course, $lecturer);
        $student = Mahasiswa::factory()->create();

        $this->actingAs($student->user)
            ->get(route('mahasiswa.academic-tasks.show', $task))
            ->assertNotFound();
    }
}
