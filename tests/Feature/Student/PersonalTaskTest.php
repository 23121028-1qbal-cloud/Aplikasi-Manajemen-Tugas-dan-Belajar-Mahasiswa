<?php

namespace Tests\Feature\Student;

use App\Enums\Role;
use App\Enums\TaskType;
use App\Models\Mahasiswa;
use App\Models\Prioritas;
use App\Models\StatusTugas;
use App\Models\Tugas;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PersonalTaskTest extends TestCase
{
    use RefreshDatabase;

    private function payload(array $overrides = []): array
    {
        return array_merge([
            'judul_tugas' => 'Menyusun laporan praktikum',
            'deskripsi' => 'Bab 1 sampai 3',
            'deadline' => now()->addDays(3)->format('Y-m-d\TH:i'),
            'id_prioritas' => Prioritas::query()->where('nama_prioritas', 'Tinggi')->value('id_prioritas'),
            'id_status' => StatusTugas::query()->where('nama_status', 'Belum Dikerjakan')->value('id_status'),
            'id_mata_kuliah' => null,
        ], $overrides);
    }

    private function student(): Mahasiswa
    {
        return Mahasiswa::factory()->create();
    }

    public function test_student_can_create_personal_task(): void
    {
        $student = $this->student();

        $this->actingAs($student->user)
            ->post('/mahasiswa/tasks/personal', $this->payload())
            ->assertRedirect();

        $task = Tugas::firstOrFail();

        $this->assertSame(TaskType::Personal, $task->task_type);
        $this->assertSame($student->id_mahasiswa, $task->id_mahasiswa_pemilik);
        $this->assertSame($student->id_user, $task->id_user);
        $this->assertFalse($task->is_archived);
    }

    public function test_task_rejects_invalid_reference_values(): void
    {
        $student = $this->student();

        $this->actingAs($student->user)
            ->post('/mahasiswa/tasks/personal', $this->payload(['id_prioritas' => 9999]))
            ->assertSessionHasErrors('id_prioritas');

        $this->actingAs($student->user)
            ->post('/mahasiswa/tasks/personal', $this->payload(['id_status' => 9999]))
            ->assertSessionHasErrors('id_status');

        $this->actingAs($student->user)
            ->post('/mahasiswa/tasks/personal', $this->payload(['deadline' => '']))
            ->assertSessionHasErrors('deadline');

        $this->assertDatabaseCount('tugas', 0);
    }

    public function test_student_cannot_access_another_students_task(): void
    {
        $owner = $this->student();
        $other = $this->student();
        $task = Tugas::factory()->create(['id_mahasiswa_pemilik' => $owner->id_mahasiswa]);

        $this->actingAs($other->user)
            ->get(route('mahasiswa.tasks.personal.show', $task))
            ->assertNotFound();

        $this->actingAs($other->user)
            ->get(route('mahasiswa.tasks.personal.edit', $task))
            ->assertNotFound();

        $this->actingAs($other->user)
            ->put(route('mahasiswa.tasks.personal.update', $task), $this->payload(['row_version' => $task->row_version]))
            ->assertNotFound();

        $this->actingAs($other->user)
            ->patch(route('mahasiswa.tasks.personal.archive', $task))
            ->assertNotFound();

        $this->assertFalse($task->fresh()->is_archived);
    }

    public function test_task_edit_with_stale_version_is_rejected(): void
    {
        $student = $this->student();
        $task = Tugas::factory()->create([
            'id_mahasiswa_pemilik' => $student->id_mahasiswa,
            'judul_tugas' => 'Judul Awal',
        ]);
        $staleVersion = $task->row_version;

        $task->forceFill([
            'judul_tugas' => 'Diubah Sesi Lain',
            'row_version' => $staleVersion + 1,
        ])->save();

        $this->actingAs($student->user)
            ->put(route('mahasiswa.tasks.personal.update', $task), $this->payload([
                'judul_tugas' => 'Edit Saya',
                'row_version' => $staleVersion,
            ]))
            ->assertSessionHasErrors('row_version');

        $this->assertSame('Diubah Sesi Lain', $task->fresh()->judul_tugas);
    }

    public function test_successful_task_edit_increments_row_version(): void
    {
        $student = $this->student();
        $task = Tugas::factory()->create(['id_mahasiswa_pemilik' => $student->id_mahasiswa]);

        $this->actingAs($student->user)
            ->put(route('mahasiswa.tasks.personal.update', $task), $this->payload([
                'judul_tugas' => 'Judul Baru',
                'row_version' => $task->row_version,
            ]))
            ->assertRedirect(route('mahasiswa.tasks.personal.show', $task));

        $this->assertSame('Judul Baru', $task->fresh()->judul_tugas);
        $this->assertSame($task->row_version + 1, $task->fresh()->row_version);
    }

    public function test_archive_removes_task_from_active_list_and_keeps_history(): void
    {
        $student = $this->student();
        $task = Tugas::factory()->create([
            'id_mahasiswa_pemilik' => $student->id_mahasiswa,
            'judul_tugas' => 'Tugas Diarsipkan',
        ]);

        $this->actingAs($student->user)
            ->patch(route('mahasiswa.tasks.personal.archive', $task))
            ->assertRedirect(route('mahasiswa.tasks.index'));

        $this->assertTrue($task->fresh()->is_archived);
        $this->assertNotNull($task->fresh()->tanggal_arsip);

        $this->actingAs($student->user)
            ->get('/mahasiswa/tasks')
            ->assertDontSee('Tugas Diarsipkan');

        $this->actingAs($student->user)
            ->get('/mahasiswa/task-history')
            ->assertSee('Tugas Diarsipkan');
    }

    public function test_completed_task_appears_in_history(): void
    {
        $student = $this->student();
        $task = Tugas::factory()->completed()->create([
            'id_mahasiswa_pemilik' => $student->id_mahasiswa,
            'judul_tugas' => 'Tugas Selesai',
        ]);

        $this->actingAs($student->user)
            ->get('/mahasiswa/task-history')
            ->assertOk()
            ->assertSee('Tugas Selesai');

        $this->assertFalse($task->is_archived);
    }

    public function test_other_roles_cannot_access_student_task_routes(): void
    {
        $admin = User::factory()->role(Role::Admin)->create();
        $lecturer = User::factory()->role(Role::GuruDosen)->create();

        $this->actingAs($admin)->get('/mahasiswa/tasks')->assertForbidden();
        $this->actingAs($lecturer)->get('/mahasiswa/tasks')->assertForbidden();
    }
}
