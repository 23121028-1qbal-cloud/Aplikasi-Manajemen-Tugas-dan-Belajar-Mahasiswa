<?php

namespace Tests\Feature\Auth;

use App\Enums\EnrollmentStatus;
use App\Enums\NotificationType;
use App\Enums\Role;
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
use App\Models\StatusTugas;
use App\Models\Tugas;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Gate;
use Tests\TestCase;

class PolicyAuthorizationTest extends TestCase
{
    use RefreshDatabase;

    public function test_personal_task_policy_only_allows_the_owner(): void
    {
        $owner = Mahasiswa::factory()->create();
        $other = Mahasiswa::factory()->create();
        $task = Tugas::factory()->create([
            'id_mahasiswa_pemilik' => $owner->id_mahasiswa,
            'task_type' => TaskType::Personal,
        ]);
        $admin = User::factory()->role(Role::Admin)->create();

        $this->assertTrue(Gate::forUser($owner->user)->allows('viewPersonal', $task));
        $this->assertTrue(Gate::forUser($owner->user)->allows('updatePersonal', $task));
        $this->assertFalse(Gate::forUser($other->user)->allows('viewPersonal', $task));
        $this->assertFalse(Gate::forUser($admin)->allows('viewPersonal', $task));

        $task->forceFill(['is_archived' => true, 'tanggal_arsip' => now()])->save();

        $this->assertFalse(Gate::forUser($owner->user)->allows('updatePersonal', $task));
        $this->assertTrue(Gate::forUser($owner->user)->allows('archivePersonal', $task));
    }

    public function test_academic_task_policy_scopes_lecturer_student_and_admin(): void
    {
        [$lecturer, $otherLecturer, $course, $student, $otherStudent, $task] = $this->academicContext();
        $admin = User::factory()->role(Role::Admin)->create();

        $this->assertTrue(Gate::forUser($lecturer->user)->allows('viewAcademic', $task));
        $this->assertTrue(Gate::forUser($lecturer->user)->allows('manageAcademic', $task));
        $this->assertFalse(Gate::forUser($otherLecturer->user)->allows('viewAcademic', $task));
        $this->assertFalse(Gate::forUser($otherLecturer->user)->allows('manageAcademic', $task));

        $this->assertTrue(Gate::forUser($student->user)->allows('viewAcademic', $task));
        $this->assertFalse(Gate::forUser($otherStudent->user)->allows('viewAcademic', $task));

        $this->assertTrue(Gate::forUser($admin)->allows('viewAcademic', $task));
        $this->assertFalse(Gate::forUser($admin)->allows('manageAcademic', $task));
        $this->assertTrue(Gate::forUser($admin)->allows('monitor', Tugas::class));
        $this->assertFalse(Gate::forUser($lecturer->user)->allows('monitor', Tugas::class));

        $task->forceFill(['is_archived' => true, 'tanggal_arsip' => now()])->save();

        $this->assertFalse(Gate::forUser($lecturer->user)->allows('manageAcademic', $task));
        $this->assertTrue(Gate::forUser($admin)->allows('viewAcademic', $task));
    }

    public function test_progress_policy_locks_archived_and_closed_tasks_for_students(): void
    {
        [$lecturer, , , $student, , $task, $progress] = $this->academicContext();

        $this->assertTrue(Gate::forUser($student->user)->allows('update', $progress));
        $this->assertTrue(Gate::forUser($lecturer->user)->allows('correct', $progress));

        $task->forceFill(['is_closed' => true])->save();
        $progress->unsetRelation('tugas');

        $this->assertFalse(Gate::forUser($student->user)->allows('update', $progress));
        $this->assertTrue(Gate::forUser($lecturer->user)->allows('correct', $progress));

        $task->forceFill(['is_archived' => true, 'tanggal_arsip' => now()])->save();
        $progress->unsetRelation('tugas');

        $this->assertFalse(Gate::forUser($student->user)->allows('update', $progress));
        $this->assertFalse(Gate::forUser($lecturer->user)->allows('correct', $progress));
    }

    public function test_course_and_enrollment_policies_scope_by_assignment_and_enrollment(): void
    {
        [$lecturer, $otherLecturer, $course, $student, $otherStudent, $task, $progress, $enrollment] = $this->academicContext();
        $admin = User::factory()->role(Role::Admin)->create();

        $this->assertTrue(Gate::forUser($lecturer->user)->allows('view', $course));
        $this->assertFalse(Gate::forUser($otherLecturer->user)->allows('view', $course));
        $this->assertTrue(Gate::forUser($student->user)->allows('view', $course));
        $this->assertFalse(Gate::forUser($otherStudent->user)->allows('view', $course));
        $this->assertTrue(Gate::forUser($admin)->allows('view', $course));
        $this->assertTrue(Gate::forUser($admin)->allows('archive', $course));

        $this->assertTrue(Gate::forUser($student->user)->allows('view', $enrollment));
        $this->assertFalse(Gate::forUser($otherStudent->user)->allows('view', $enrollment));
        $this->assertTrue(Gate::forUser($lecturer->user)->allows('view', $enrollment));
        $this->assertFalse(Gate::forUser($otherLecturer->user)->allows('view', $enrollment));
        $this->assertTrue(Gate::forUser($admin)->allows('view', $progress));
    }

    public function test_study_activity_policy_only_allows_the_owner(): void
    {
        $owner = Mahasiswa::factory()->create();
        $other = Mahasiswa::factory()->create();
        $activity = AktivitasBelajar::factory()->create(['id_mahasiswa' => $owner->id_mahasiswa]);

        $this->assertTrue(Gate::forUser($owner->user)->allows('view', $activity));
        $this->assertTrue(Gate::forUser($owner->user)->allows('update', $activity));
        $this->assertFalse(Gate::forUser($other->user)->allows('view', $activity));

        $activity->forceFill(['is_archived' => true, 'tanggal_arsip' => now()])->save();

        $this->assertFalse(Gate::forUser($owner->user)->allows('update', $activity));
    }

    public function test_notification_policy_is_recipient_only(): void
    {
        $owner = User::factory()->create();
        $other = User::factory()->create();
        $admin = User::factory()->role(Role::Admin)->create();
        $notification = Notifikasi::create([
            'id_user' => $owner->id_user,
            'tipe' => NotificationType::AcademicTaskAssigned,
            'judul' => 'Notifikasi policy',
            'pesan' => 'Hanya untuk pemilik.',
            'resource_type' => 'tugas',
            'resource_id' => null,
            'is_read' => false,
            'dedup_key' => 'policy:notification:1',
            'tanggal_dibuat' => now(),
        ]);

        $this->assertTrue(Gate::forUser($owner)->allows('view', $notification));
        $this->assertTrue(Gate::forUser($owner)->allows('markRead', $notification));
        $this->assertFalse(Gate::forUser($other)->allows('view', $notification));
        $this->assertFalse(Gate::forUser($other)->allows('markRead', $notification));
        $this->assertFalse(Gate::forUser($admin)->allows('view', $notification));
    }

    public function test_account_archive_and_user_policies_are_admin_or_self_only(): void
    {
        $student = Mahasiswa::factory()->create();
        $otherStudent = Mahasiswa::factory()->create();
        $lecturer = GuruDosen::factory()->create();
        $admin = User::factory()->role(Role::Admin)->create();

        $this->assertTrue(Gate::forUser($admin)->allows('archive', $student));
        $this->assertTrue(Gate::forUser($admin)->allows('restore', $student));
        $this->assertFalse(Gate::forUser($student->user)->allows('archive', $student));
        $this->assertTrue(Gate::forUser($admin)->allows('archive', $lecturer));
        $this->assertFalse(Gate::forUser($lecturer->user)->allows('archive', $lecturer));

        $this->assertTrue(Gate::forUser($student->user)->allows('view', $student->user));
        $this->assertFalse(Gate::forUser($otherStudent->user)->allows('view', $student->user));
        $this->assertFalse(Gate::forUser($admin)->allows('view', $student->user));
        $this->assertTrue(Gate::forUser($student->user)->allows('updatePassword', $student->user));
    }

    /**
     * @return array{0: GuruDosen, 1: GuruDosen, 2: MataKuliah, 3: Mahasiswa, 4: Mahasiswa, 5: Tugas, 6: ProgresTugasAkademik, 7: Enrollment}
     */
    private function academicContext(): array
    {
        $lecturer = GuruDosen::factory()->create();
        $otherLecturer = GuruDosen::factory()->create();
        $course = MataKuliah::factory()->create(['guru_dosen_id' => $lecturer->id_guru_dosen]);
        $student = Mahasiswa::factory()->create();
        $otherStudent = Mahasiswa::factory()->create();

        $enrollment = Enrollment::factory()->create([
            'id_mahasiswa' => $student->id_mahasiswa,
            'id_mata_kuliah' => $course->id_mata_kuliah,
            'tahun_akademik' => '2026/2027',
            'semester' => Semester::Ganjil,
            'status' => EnrollmentStatus::Terdaftar,
        ]);

        $task = Tugas::create([
            'id_mata_kuliah' => $course->id_mata_kuliah,
            'id_prioritas' => Prioritas::query()->where('nama_prioritas', 'Sedang')->value('id_prioritas'),
            'id_status' => null,
            'id_user' => $lecturer->id_user,
            'id_pembuat_guru_dosen' => $lecturer->id_guru_dosen,
            'id_mahasiswa_pemilik' => null,
            'task_type' => TaskType::Academic,
            'judul_tugas' => 'Tugas Policy',
            'deskripsi' => 'Konteks test policy',
            'deadline' => now()->addWeek(),
            'is_closed' => false,
            'is_archived' => false,
        ]);

        $progress = ProgresTugasAkademik::create([
            'id_tugas' => $task->id_tugas,
            'id_mahasiswa' => $student->id_mahasiswa,
            'id_status' => StatusTugas::query()->where('nama_status', 'Belum Dikerjakan')->value('id_status'),
            'id_user_diubah_oleh' => $lecturer->id_user,
            'tanggal_ubah' => now(),
        ]);

        return [$lecturer, $otherLecturer, $course, $student, $otherStudent, $task, $progress, $enrollment];
    }
}
