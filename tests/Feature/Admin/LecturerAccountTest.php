<?php

namespace Tests\Feature\Admin;

use App\Enums\Role;
use App\Models\GuruDosen;
use App\Models\MataKuliah;
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

    public function test_admin_can_view_lecturer_detail_with_archive_status(): void
    {
        $lecturer = GuruDosen::factory()->create(['nama_lengkap' => 'Dr. Detail']);
        MataKuliah::factory()->create(['guru_dosen_id' => $lecturer->id_guru_dosen]);

        $this->actingAs($this->admin())
            ->get(route('admin.lecturers.show', $lecturer))
            ->assertOk()
            ->assertSee('Dr. Detail')
            ->assertSee('Status Arsip')
            ->assertSee('Masih ada 1 mata kuliah non-arsip.');
    }

    public function test_lecturer_with_active_course_cannot_be_archived(): void
    {
        $lecturer = GuruDosen::factory()->create();
        MataKuliah::factory()->create(['guru_dosen_id' => $lecturer->id_guru_dosen]);

        $this->actingAs($this->admin())
            ->patch(route('admin.lecturers.archive', $lecturer))
            ->assertSessionHasErrors('archive');

        $this->assertFalse($lecturer->fresh()->is_archived);
        $this->assertTrue($lecturer->user->fresh()->is_active);
    }

    public function test_lecturer_can_be_archived_after_courses_are_archived_and_restored(): void
    {
        $lecturer = GuruDosen::factory()->create();
        MataKuliah::factory()->archived()->create(['guru_dosen_id' => $lecturer->id_guru_dosen]);

        $this->actingAs($this->admin())
            ->patch(route('admin.lecturers.archive', $lecturer))
            ->assertRedirect(route('admin.lecturers.show', $lecturer));

        $lecturer->refresh();

        $this->assertTrue($lecturer->is_archived);
        $this->assertNotNull($lecturer->tanggal_arsip);
        $this->assertFalse($lecturer->user->fresh()->is_active);

        $this->actingAs($this->admin())
            ->patch(route('admin.lecturers.restore', $lecturer))
            ->assertRedirect(route('admin.lecturers.show', $lecturer));

        $lecturer->refresh();

        $this->assertFalse($lecturer->is_archived);
        $this->assertNull($lecturer->tanggal_arsip);
        $this->assertTrue($lecturer->user->fresh()->is_active);
    }

    public function test_non_admin_cannot_archive_lecturer(): void
    {
        $student = User::factory()->role(Role::Mahasiswa)->create();
        $lecturer = GuruDosen::factory()->create();

        $this->actingAs($student)
            ->patch(route('admin.lecturers.archive', $lecturer))
            ->assertForbidden();

        $this->assertFalse($lecturer->fresh()->is_archived);
    }
}
