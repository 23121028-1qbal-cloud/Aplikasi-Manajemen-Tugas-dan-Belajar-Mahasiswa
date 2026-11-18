<?php

namespace Tests\Feature\Admin;

use App\Enums\Role;
use App\Models\GuruDosen;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class LecturerAccountTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::factory()->role(Role::Admin)->create();
    }

    public function test_admin_can_create_lecturer_with_temporary_password(): void
    {
        $response = $this->actingAs($this->admin())->post('/admin/lecturers', [
            'nama_lengkap' => 'Dr. Sari',
            'nip_guru' => '198001012005011001',
            'username' => 'sari',
        ]);

        $response->assertRedirect(route('admin.lecturers.index'));
        $response->assertSessionHas('temporary_password');

        $user = User::where('username', 'sari')->firstOrFail();
        $temporaryPassword = session('temporary_password')['password'];

        $this->assertSame(Role::GuruDosen, $user->role);
        $this->assertTrue($user->is_active);
        $this->assertTrue(Hash::check($temporaryPassword, $user->password));
        $this->assertNotSame($temporaryPassword, $user->password);
        $this->assertTrue($user->must_change_password);
        $this->assertTrue($user->temporary_password_expires_at->isFuture());
        $this->assertDatabaseHas('guru_dosen', [
            'id_user' => $user->id_user,
            'nip_guru' => '198001012005011001',
            'nama_lengkap' => 'Dr. Sari',
        ]);
    }

    public function test_non_admin_cannot_create_lecturer(): void
    {
        $student = User::factory()->role(Role::Mahasiswa)->create();

        $this->actingAs($student)->post('/admin/lecturers', [
            'nama_lengkap' => 'Dr. Sari',
            'nip_guru' => '198001012005011001',
            'username' => 'sari',
        ])->assertForbidden();

        $this->assertDatabaseMissing('users', ['username' => 'sari']);
    }

    public function test_admin_can_reset_lecturer_temporary_password(): void
    {
        $lecturer = GuruDosen::factory()->create();

        $response = $this->actingAs($this->admin())
            ->post(route('admin.lecturers.temporary-password', $lecturer));

        $response->assertRedirect(route('admin.lecturers.index'));

        $temporaryPassword = session('temporary_password')['password'];

        $this->assertTrue(Hash::check($temporaryPassword, $lecturer->user->fresh()->password));
    }

    public function test_admin_can_update_lecturer_name_only(): void
    {
        $lecturer = GuruDosen::factory()->create(['nama_lengkap' => 'Nama Lama']);
        $nip = $lecturer->nip_guru;

        $this->actingAs($this->admin())
            ->put(route('admin.lecturers.update', $lecturer), [
                'nama_lengkap' => 'Nama Baru',
                'nip_guru' => '000',
                'username' => 'diubah',
            ])
            ->assertRedirect(route('admin.lecturers.show', $lecturer));

        $lecturer->refresh();

        $this->assertSame('Nama Baru', $lecturer->nama_lengkap);
        $this->assertSame($nip, $lecturer->nip_guru);
        $this->assertNotSame('diubah', $lecturer->user->username);
    }

    public function test_duplicate_username_or_nip_is_rejected(): void
    {
        GuruDosen::factory()->create(['nip_guru' => '198001012005011001']);

        $this->actingAs($this->admin())->post('/admin/lecturers', [
            'nama_lengkap' => 'Dr. Sari',
            'nip_guru' => '198001012005011001',
            'username' => 'sari-baru',
        ])->assertSessionHasErrors('nip_guru');
    }
}
