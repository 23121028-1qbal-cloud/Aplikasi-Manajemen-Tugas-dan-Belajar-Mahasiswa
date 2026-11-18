<?php

namespace Tests\Feature\Admin;

use App\Enums\EnrollmentStatus;
use App\Enums\Role;
use App\Enums\Semester;
use App\Models\Enrollment;
use App\Models\Mahasiswa;
use App\Models\MataKuliah;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EnrollmentTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::factory()->role(Role::Admin)->create();
    }

    private function payload(Mahasiswa $student, MataKuliah $course, array $overrides = []): array
    {
        return array_merge([
            'id_mahasiswa' => $student->id_mahasiswa,
            'id_mata_kuliah' => $course->id_mata_kuliah,
            'tahun_akademik' => '2026/2027',
            'semester' => Semester::Ganjil->value,
        ], $overrides);
    }

    public function test_admin_can_enroll_student_as_terdaftar(): void
    {
        $student = Mahasiswa::factory()->create();
        $course = MataKuliah::factory()->create();

        $this->actingAs($this->admin())
            ->post('/admin/enrollments', $this->payload($student, $course))
            ->assertRedirect(route('admin.enrollments.index'));

        $this->assertDatabaseHas('mahasiswa_mata_kuliah', [
            'id_mahasiswa' => $student->id_mahasiswa,
            'id_mata_kuliah' => $course->id_mata_kuliah,
            'tahun_akademik' => '2026/2027',
            'semester' => Semester::Ganjil->value,
            'status' => EnrollmentStatus::Terdaftar->value,
        ]);
    }

    public function test_duplicate_enrollment_in_same_period_is_rejected(): void
    {
        $student = Mahasiswa::factory()->create();
        $course = MataKuliah::factory()->create();
        Enrollment::factory()->create([
            'id_mahasiswa' => $student->id_mahasiswa,
            'id_mata_kuliah' => $course->id_mata_kuliah,
            'tahun_akademik' => '2026/2027',
            'semester' => Semester::Ganjil,
        ]);

        $this->actingAs($this->admin())
            ->post('/admin/enrollments', $this->payload($student, $course))
            ->assertSessionHasErrors('id_mata_kuliah');

        $this->assertSame(1, Enrollment::where('id_mahasiswa', $student->id_mahasiswa)->count());
    }

    public function test_reactivation_uses_same_row_and_preserves_first_registration_date(): void
    {
        $student = Mahasiswa::factory()->create();
        $course = MataKuliah::factory()->create();

        $enrollment = Enrollment::factory()->create([
            'id_mahasiswa' => $student->id_mahasiswa,
            'id_mata_kuliah' => $course->id_mata_kuliah,
            'tahun_akademik' => '2026/2027',
            'semester' => Semester::Ganjil,
            'tanggal_daftar' => '2026-08-01',
            'status' => EnrollmentStatus::Dibatalkan,
        ]);

        $this->actingAs($this->admin())
            ->post('/admin/enrollments', $this->payload($student, $course))
            ->assertRedirect(route('admin.enrollments.index'));

        $enrollment->refresh();

        $this->assertSame(EnrollmentStatus::Terdaftar, $enrollment->status);
        $this->assertSame('2026-08-01', $enrollment->tanggal_daftar->toDateString());
        $this->assertSame(1, Enrollment::where('id_mahasiswa', $student->id_mahasiswa)->count());
    }

    public function test_different_period_creates_new_row(): void
    {
        $student = Mahasiswa::factory()->create();
        $course = MataKuliah::factory()->create();

        Enrollment::factory()->create([
            'id_mahasiswa' => $student->id_mahasiswa,
            'id_mata_kuliah' => $course->id_mata_kuliah,
            'tahun_akademik' => '2025/2026',
            'semester' => Semester::Genap,
        ]);

        $this->actingAs($this->admin())
            ->post('/admin/enrollments', $this->payload($student, $course, [
                'tahun_akademik' => '2026/2027',
                'semester' => Semester::Ganjil->value,
            ]));

        $this->assertSame(2, Enrollment::where('id_mahasiswa', $student->id_mahasiswa)->count());
    }

    public function test_archived_course_rejects_new_enrollment(): void
    {
        $student = Mahasiswa::factory()->create();
        $course = MataKuliah::factory()->archived()->create();

        $this->actingAs($this->admin())
            ->post('/admin/enrollments', $this->payload($student, $course))
            ->assertSessionHasErrors('id_mata_kuliah');

        $this->assertDatabaseCount('mahasiswa_mata_kuliah', 0);
    }

    public function test_invalid_academic_year_format_is_rejected(): void
    {
        $student = Mahasiswa::factory()->create();
        $course = MataKuliah::factory()->create();

        $this->actingAs($this->admin())
            ->post('/admin/enrollments', $this->payload($student, $course, ['tahun_akademik' => '2026-2027']))
            ->assertSessionHasErrors('tahun_akademik');
    }

    public function test_admin_can_cancel_and_complete_active_enrollment(): void
    {
        $enrollment = Enrollment::factory()->create();

        $this->actingAs($this->admin())
            ->put(route('admin.enrollments.update', $enrollment), ['status' => 'Dibatalkan'])
            ->assertRedirect(route('admin.enrollments.index'));

        $this->assertSame(EnrollmentStatus::Dibatalkan, $enrollment->fresh()->status);

        $other = Enrollment::factory()->create();

        $this->actingAs($this->admin())
            ->put(route('admin.enrollments.update', $other), ['status' => 'Selesai']);

        $this->assertSame(EnrollmentStatus::Selesai, $other->fresh()->status);
    }

    public function test_invalid_enrollment_transition_is_rejected(): void
    {
        $enrollment = Enrollment::factory()->create([
            'status' => EnrollmentStatus::Selesai,
        ]);

        $this->actingAs($this->admin())
            ->put(route('admin.enrollments.update', $enrollment), ['status' => 'Terdaftar'])
            ->assertSessionHasErrors('status');

        $this->assertSame(EnrollmentStatus::Selesai, $enrollment->fresh()->status);
    }

    public function test_non_admin_cannot_create_enrollment(): void
    {
        $student = Mahasiswa::factory()->create();
        $course = MataKuliah::factory()->create();

        $this->actingAs($student->user)
            ->post('/admin/enrollments', $this->payload($student, $course))
            ->assertForbidden();

        $this->assertDatabaseCount('mahasiswa_mata_kuliah', 0);
    }
}
