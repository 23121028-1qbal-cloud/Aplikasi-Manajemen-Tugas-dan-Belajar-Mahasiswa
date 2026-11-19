<?php

namespace Tests\Feature;

use App\Enums\Role;
use App\Models\GuruDosen;
use App\Models\Mahasiswa;
use App\Models\MataKuliah;
use App\Models\Tugas;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class DashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_student_dashboard_shows_personal_summary(): void
    {
        $student = Mahasiswa::factory()->create(['nama_lengkap' => 'Mahasiswa Dashboard']);

        $this->actingAs($student->user)
            ->get('/mahasiswa/dashboard')
            ->assertOk()
            ->assertSee('Mahasiswa Dashboard')
            ->assertSee('Tugas Personal Aktif')
            ->assertSee('Aktivitas Belajar Terjadwal');
    }

    public function test_student_dashboard_does_not_query_the_course_once_per_task(): void
    {
        $student = Mahasiswa::factory()->create();
        $course = MataKuliah::factory()->create();

        $createTask = fn () => Tugas::factory()->create([
            'id_mahasiswa_pemilik' => $student->id_mahasiswa,
            'id_user' => $student->id_user,
            'id_mata_kuliah' => $course->id_mata_kuliah,
        ]);

        $createTask();
        $createTask();

        // Warm-up request so the auth user lookup is not counted in the baseline.
        $this->countDashboardQueries($student);
        $fewTasks = $this->countDashboardQueries($student);

        $createTask();
        $createTask();
        $createTask();
        $manyTasks = $this->countDashboardQueries($student);

        $this->assertSame(
            $fewTasks,
            $manyTasks,
            'Menambah tugas tanpa mata kuliah baru tidak boleh menambah query dashboard (N+1).'
        );
    }

    public function test_lecturer_dashboard_shows_course_summary(): void
    {
        $lecturer = GuruDosen::factory()->create(['nama_lengkap' => 'Dosen Dashboard']);

        $this->actingAs($lecturer->user)
            ->get('/guru-dosen/dashboard')
            ->assertOk()
            ->assertSee('Dosen Dashboard')
            ->assertSee('Mata Kuliah Diampu')
            ->assertSee('Tugas Akademik Terbuka');
    }

    public function test_admin_dashboard_shows_account_summary(): void
    {
        $admin = User::factory()->role(Role::Admin)->create();

        $this->actingAs($admin)
            ->get('/admin/dashboard')
            ->assertOk()
            ->assertSee('Mahasiswa Aktif')
            ->assertSee('Guru/Dosen Aktif')
            ->assertSee('Mata Kuliah Aktif')
            ->assertSee('Enrollment Terdaftar');
    }

    private function countDashboardQueries(Mahasiswa $student): int
    {
        DB::flushQueryLog();
        DB::enableQueryLog();

        $this->actingAs($student->user)->get('/mahasiswa/dashboard')->assertOk();

        $count = count(DB::getQueryLog());
        DB::disableQueryLog();

        return $count;
    }
}
