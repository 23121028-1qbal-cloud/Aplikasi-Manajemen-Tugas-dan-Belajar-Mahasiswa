<?php

namespace Tests\Feature\Admin;

use App\Enums\Role;
use App\Models\Mahasiswa;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class StudentAccountTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::factory()->role(Role::Admin)->create();
    }

    public function test_admin_can_list_and_search_students(): void
    {
        $student = Mahasiswa::factory()->create(['nama_lengkap' => 'Budi Santoso', 'npm' => '20260001']);

        $this->actingAs($this->admin())
            ->get('/admin/students')
            ->assertOk()
            ->assertSee('Budi Santoso')
            ->assertSee('20260001');

        $this->actingAs($this->admin())
            ->get('/admin/students?q=20260001')
            ->assertOk()
            ->assertSee('Budi Santoso');

        $this->actingAs($this->admin())
            ->get('/admin/students?q=tidak-ada')
            ->assertOk()
            ->assertSee('Tidak ada akun mahasiswa');
    }

    public function test_admin_can_view_student_detail(): void
    {
        $student = Mahasiswa::factory()->create();

        $this->actingAs($this->admin())
            ->get(route('admin.students.show', $student))
            ->assertOk()
            ->assertSee($student->npm)
            ->assertSee($student->user->username);
    }

    public function test_admin_can_update_only_student_name(): void
    {
        $student = Mahasiswa::factory()->create(['nama_lengkap' => 'Nama Lama']);
        $npm = $student->npm;
        $username = $student->user->username;

        $this->actingAs($this->admin())
            ->put(route('admin.students.update', $student), [
                'nama_lengkap' => 'Nama Baru',
                'username' => 'diubah',
                'npm' => '99999999',
                'role' => 'admin',
            ])
            ->assertRedirect(route('admin.students.show', $student));

        $student->refresh();

        $this->assertSame('Nama Baru', $student->nama_lengkap);
        $this->assertSame($npm, $student->npm);
        $this->assertSame($username, $student->user->username);
        $this->assertSame(Role::Mahasiswa, $student->user->role);
    }

    public function test_student_detail_exposes_accessible_confirmation_dialogs(): void
    {
        $student = Mahasiswa::factory()->create();

        $this->actingAs($this->admin())
            ->get(route('admin.students.show', $student))
            ->assertOk()
            ->assertSee('aria-labelledby="student-status-'.$student->id_mahasiswa.'-label"', false)
            ->assertSee('aria-label="Tutup dialog"', false);
    }

    public function test_admin_can_deactivate_and_activate_student(): void
    {
        $student = Mahasiswa::factory()->create();

        $this->actingAs($this->admin())
            ->patch(route('admin.students.status', $student), ['is_active' => '0'])
            ->assertRedirect(route('admin.students.show', $student));

        $this->assertFalse($student->user->fresh()->is_active);

        $this->actingAs($this->admin())
            ->patch(route('admin.students.status', $student), ['is_active' => '1'])
            ->assertRedirect(route('admin.students.show', $student));

        $this->assertTrue($student->user->fresh()->is_active);
    }

    public function test_admin_can_issue_student_temporary_password(): void
    {
        $student = Mahasiswa::factory()->create();

        $response = $this->actingAs($this->admin())
            ->post(route('admin.students.temporary-password', $student));

        $response->assertRedirect(route('admin.students.show', $student));
        $response->assertSessionHas('temporary_password');

        $temporaryPassword = session('temporary_password')['password'];

        $this->assertTrue(Hash::check($temporaryPassword, $student->user->fresh()->password));
    }

    public function test_non_admin_cannot_manage_student_accounts(): void
    {
        $student = Mahasiswa::factory()->create();
        $otherStudent = Mahasiswa::factory()->create();

        $this->actingAs($student->user)->get('/admin/students')->assertForbidden();
        $this->actingAs($student->user)->get(route('admin.students.show', $otherStudent))->assertForbidden();
        $this->actingAs($student->user)
            ->post(route('admin.students.temporary-password', $otherStudent))
            ->assertForbidden();
    }
}
