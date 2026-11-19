<?php

namespace Tests\Feature\Admin;

use App\Enums\EnrollmentStatus;
use App\Enums\Role;
use App\Models\Enrollment;
use App\Models\GuruDosen;
use App\Models\Mahasiswa;
use App\Models\MataKuliah;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CourseManagementTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::factory()->role(Role::Admin)->create();
    }

    public function test_admin_can_create_course_with_assigned_lecturer(): void
    {
        $lecturer = GuruDosen::factory()->create();

        $this->actingAs($this->admin())->post('/admin/courses', [
            'kode_mata_kuliah' => 'IF101',
            'nama_mata_kuliah' => 'Algoritma',
            'guru_dosen_id' => $lecturer->id_guru_dosen,
        ])->assertRedirect(route('admin.courses.index'));

        $this->assertDatabaseHas('mata_kuliah', [
            'kode_mata_kuliah' => 'IF101',
            'nama_mata_kuliah' => 'Algoritma',
            'guru_dosen_id' => $lecturer->id_guru_dosen,
            'is_archived' => false,
        ]);
    }

    public function test_course_code_must_be_unique(): void
    {
        MataKuliah::factory()->create(['kode_mata_kuliah' => 'IF101']);
        $lecturer = GuruDosen::factory()->create();

        $this->actingAs($this->admin())->post('/admin/courses', [
            'kode_mata_kuliah' => 'IF101',
            'nama_mata_kuliah' => 'Algoritma',
            'guru_dosen_id' => $lecturer->id_guru_dosen,
        ])->assertSessionHasErrors('kode_mata_kuliah');

        $this->assertSame(1, MataKuliah::where('kode_mata_kuliah', 'IF101')->count());
    }

    public function test_archived_lecturer_cannot_be_assigned_new_course(): void
    {
        $lecturer = GuruDosen::factory()->create(['is_archived' => true, 'tanggal_arsip' => now()]);

        $this->actingAs($this->admin())->post('/admin/courses', [
            'kode_mata_kuliah' => 'IF102',
            'nama_mata_kuliah' => 'Struktur Data',
            'guru_dosen_id' => $lecturer->id_guru_dosen,
        ])->assertSessionHasErrors('guru_dosen_id');

        $this->assertDatabaseMissing('mata_kuliah', ['kode_mata_kuliah' => 'IF102']);
    }

    public function test_admin_can_open_course_edit_form(): void
    {
        $course = MataKuliah::factory()->create(['nama_mata_kuliah' => 'Nama Form']);

        $this->actingAs($this->admin())
            ->get(route('admin.courses.edit', $course))
            ->assertOk()
            ->assertSee('Nama Form');
    }

    public function test_admin_can_update_course_and_reassign_lecturer(): void
    {
        $course = MataKuliah::factory()->create();
        $newLecturer = GuruDosen::factory()->create();

        $this->actingAs($this->admin())->put(route('admin.courses.update', $course), [
            'kode_mata_kuliah' => $course->kode_mata_kuliah,
            'nama_mata_kuliah' => 'Nama Baru',
            'guru_dosen_id' => $newLecturer->id_guru_dosen,
            'row_version' => $course->row_version,
        ])->assertRedirect(route('admin.courses.index'));

        $course->refresh();

        $this->assertSame('Nama Baru', $course->nama_mata_kuliah);
        $this->assertSame($newLecturer->id_guru_dosen, $course->guru_dosen_id);
        $this->assertSame(2, $course->row_version);
    }

    public function test_admin_can_archive_course_without_deleting_history(): void
    {
        $course = MataKuliah::factory()->create();
        $enrollment = Enrollment::factory()->create(['id_mata_kuliah' => $course->id_mata_kuliah]);

        $this->actingAs($this->admin())
            ->patch(route('admin.courses.archive', $course))
            ->assertRedirect(route('admin.courses.index'));

        $this->assertTrue($course->fresh()->is_archived);
        $this->assertNotNull($course->fresh()->tanggal_arsip);
        $this->assertDatabaseHas('mahasiswa_mata_kuliah', [
            'id_mahasiswa_mata_kuliah' => $enrollment->id_mahasiswa_mata_kuliah,
            'status' => EnrollmentStatus::Terdaftar->value,
        ]);
    }

    public function test_admin_edit_with_stale_version_is_rejected(): void
    {
        $course = MataKuliah::factory()->create(['nama_mata_kuliah' => 'Nama Awal']);
        $lecturer = GuruDosen::factory()->create();
        $staleVersion = $course->row_version;

        $course->forceFill([
            'nama_mata_kuliah' => 'Diubah Sesi Lain',
            'row_version' => $staleVersion + 1,
        ])->save();

        $this->actingAs($this->admin())->put(route('admin.courses.update', $course), [
            'kode_mata_kuliah' => $course->kode_mata_kuliah,
            'nama_mata_kuliah' => 'Edit Saya',
            'guru_dosen_id' => $lecturer->id_guru_dosen,
            'row_version' => $staleVersion,
        ])->assertSessionHasErrors('row_version');

        $this->assertSame('Diubah Sesi Lain', $course->fresh()->nama_mata_kuliah);
    }

    public function test_lecturer_can_update_only_name_of_assigned_course(): void
    {
        $lecturer = GuruDosen::factory()->create();
        $course = MataKuliah::factory()->create([
            'guru_dosen_id' => $lecturer->id_guru_dosen,
            'nama_mata_kuliah' => 'Nama Awal',
            'kode_mata_kuliah' => 'IF101',
        ]);
        $otherLecturer = GuruDosen::factory()->create();

        $this->actingAs($lecturer->user)
            ->put(route('lecturer.courses.update', $course), [
                'nama_mata_kuliah' => 'Nama Baru',
                'kode_mata_kuliah' => 'DIUBAH',
                'guru_dosen_id' => $otherLecturer->id_guru_dosen,
                'row_version' => $course->row_version,
            ])
            ->assertRedirect(route('lecturer.courses.show', $course));

        $course->refresh();

        $this->assertSame('Nama Baru', $course->nama_mata_kuliah);
        $this->assertSame('IF101', $course->kode_mata_kuliah);
        $this->assertSame($lecturer->id_guru_dosen, $course->guru_dosen_id);
        $this->assertSame(2, $course->row_version);
    }

    public function test_lecturer_cannot_update_unassigned_course(): void
    {
        $lecturer = GuruDosen::factory()->create();
        $otherCourse = MataKuliah::factory()->create(['nama_mata_kuliah' => 'Bukan Miliknya']);

        $this->actingAs($lecturer->user)
            ->put(route('lecturer.courses.update', $otherCourse), [
                'nama_mata_kuliah' => 'Coba Ubah',
                'row_version' => $otherCourse->row_version,
            ])
            ->assertNotFound();

        $this->assertSame('Bukan Miliknya', $otherCourse->fresh()->nama_mata_kuliah);
    }

    public function test_non_admin_cannot_manage_courses(): void
    {
        $student = User::factory()->role(Role::Mahasiswa)->create();
        $course = MataKuliah::factory()->create();

        $this->actingAs($student)->get('/admin/courses')->assertForbidden();
        $this->actingAs($student)
            ->patch(route('admin.courses.archive', $course))
            ->assertForbidden();
    }

    public function test_lecturer_only_sees_assigned_courses(): void
    {
        $lecturer = GuruDosen::factory()->create();
        $other = GuruDosen::factory()->create();
        $ownCourse = MataKuliah::factory()->create([
            'guru_dosen_id' => $lecturer->id_guru_dosen,
            'nama_mata_kuliah' => 'Milik Sendiri',
        ]);
        $otherCourse = MataKuliah::factory()->create([
            'guru_dosen_id' => $other->id_guru_dosen,
            'nama_mata_kuliah' => 'Milik Orang Lain',
        ]);

        $this->actingAs($lecturer->user)
            ->get('/guru-dosen/courses')
            ->assertOk()
            ->assertSee('Milik Sendiri')
            ->assertDontSee('Milik Orang Lain');

        $this->actingAs($lecturer->user)
            ->get(route('lecturer.courses.show', $ownCourse))
            ->assertOk();

        $this->actingAs($lecturer->user)
            ->get(route('lecturer.courses.show', $otherCourse))
            ->assertNotFound();
    }

    public function test_student_only_sees_enrolled_courses(): void
    {
        $student = Mahasiswa::factory()->create();
        $enrolledCourse = MataKuliah::factory()->create(['nama_mata_kuliah' => 'Diikuti']);
        $otherCourse = MataKuliah::factory()->create(['nama_mata_kuliah' => 'Tidak Diikuti']);

        Enrollment::factory()->create([
            'id_mahasiswa' => $student->id_mahasiswa,
            'id_mata_kuliah' => $enrolledCourse->id_mata_kuliah,
        ]);

        $this->actingAs($student->user)
            ->get('/mahasiswa/courses')
            ->assertOk()
            ->assertSee('Diikuti')
            ->assertDontSee('Tidak Diikuti');

        $this->actingAs($student->user)
            ->get(route('mahasiswa.courses.show', $otherCourse))
            ->assertNotFound();

        $this->actingAs($student->user)
            ->get(route('mahasiswa.courses.show', $enrolledCourse))
            ->assertOk();
    }
}
