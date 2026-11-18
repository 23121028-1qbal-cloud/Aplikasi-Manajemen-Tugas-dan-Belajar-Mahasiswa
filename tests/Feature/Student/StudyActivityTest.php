<?php

namespace Tests\Feature\Student;

use App\Enums\Role;
use App\Models\AktivitasBelajar;
use App\Models\Mahasiswa;
use App\Models\StatusBelajar;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StudyActivityTest extends TestCase
{
    use RefreshDatabase;

    private function payload(array $overrides = []): array
    {
        return array_merge([
            'judul_aktivitas' => 'Belajar basis data',
            'materi_belajar' => 'Normalisasi dan indeks',
            'waktu_mulai' => now()->addDay()->format('Y-m-d\TH:i'),
            'durasi_menit' => 90,
            'id_status_belajar' => StatusBelajar::query()->where('nama_status', 'Direncanakan')->value('id_status_belajar'),
            'id_mata_kuliah' => null,
        ], $overrides);
    }

    private function student(): Mahasiswa
    {
        return Mahasiswa::factory()->create();
    }

    public function test_student_can_create_study_activity(): void
    {
        $student = $this->student();

        $this->actingAs($student->user)
            ->post('/mahasiswa/study-activities', $this->payload())
            ->assertRedirect();

        $activity = AktivitasBelajar::firstOrFail();

        $this->assertSame($student->id_mahasiswa, $activity->id_mahasiswa);
        $this->assertSame(90, $activity->durasi_menit);
        $this->assertSame(now()->toDateString(), $activity->tanggal_dibuat->toDateString());
    }

    public function test_duration_must_be_positive(): void
    {
        $student = $this->student();

        $this->actingAs($student->user)
            ->post('/mahasiswa/study-activities', $this->payload(['durasi_menit' => 0]))
            ->assertSessionHasErrors('durasi_menit');

        $this->actingAs($student->user)
            ->post('/mahasiswa/study-activities', $this->payload(['durasi_menit' => -5]))
            ->assertSessionHasErrors('durasi_menit');

        $this->assertDatabaseCount('aktivitas_belajar', 0);
    }

    public function test_student_cannot_access_another_students_activity(): void
    {
        $owner = $this->student();
        $other = $this->student();
        $activity = AktivitasBelajar::factory()->create(['id_mahasiswa' => $owner->id_mahasiswa]);

        $this->actingAs($other->user)
            ->get(route('mahasiswa.study-activities.show', $activity))
            ->assertNotFound();

        $this->actingAs($other->user)
            ->get(route('mahasiswa.study-activities.edit', $activity))
            ->assertNotFound();

        $this->actingAs($other->user)
            ->put(route('mahasiswa.study-activities.update', $activity), $this->payload(['row_version' => $activity->row_version]))
            ->assertNotFound();

        $this->actingAs($other->user)
            ->patch(route('mahasiswa.study-activities.archive', $activity))
            ->assertNotFound();
    }

    public function test_study_activity_edit_with_stale_version_is_rejected(): void
    {
        $student = $this->student();
        $activity = AktivitasBelajar::factory()->create([
            'id_mahasiswa' => $student->id_mahasiswa,
            'judul_aktivitas' => 'Judul Awal',
        ]);
        $staleVersion = $activity->row_version;

        $activity->forceFill([
            'judul_aktivitas' => 'Diubah Sesi Lain',
            'row_version' => $staleVersion + 1,
        ])->save();

        $this->actingAs($student->user)
            ->put(route('mahasiswa.study-activities.update', $activity), $this->payload([
                'judul_aktivitas' => 'Edit Saya',
                'row_version' => $staleVersion,
            ]))
            ->assertSessionHasErrors('row_version');

        $this->assertSame('Diubah Sesi Lain', $activity->fresh()->judul_aktivitas);
    }

    public function test_archived_activity_moves_to_history(): void
    {
        $student = $this->student();
        $activity = AktivitasBelajar::factory()->create([
            'id_mahasiswa' => $student->id_mahasiswa,
            'judul_aktivitas' => 'Belajar Diarsipkan',
        ]);

        $this->actingAs($student->user)
            ->patch(route('mahasiswa.study-activities.archive', $activity))
            ->assertRedirect(route('mahasiswa.study-activities.index'));

        $this->assertTrue($activity->fresh()->is_archived);

        $this->actingAs($student->user)
            ->get('/mahasiswa/study-activities')
            ->assertDontSee('Belajar Diarsipkan');

        $this->actingAs($student->user)
            ->get('/mahasiswa/study-history')
            ->assertSee('Belajar Diarsipkan');
    }

    public function test_completed_activity_appears_in_history(): void
    {
        $student = $this->student();
        $completed = StatusBelajar::query()->where('nama_status', 'Selesai')->value('id_status_belajar');

        AktivitasBelajar::factory()->create([
            'id_mahasiswa' => $student->id_mahasiswa,
            'id_status_belajar' => $completed,
            'judul_aktivitas' => 'Belajar Selesai',
        ]);

        $this->actingAs($student->user)
            ->get('/mahasiswa/study-history')
            ->assertOk()
            ->assertSee('Belajar Selesai');
    }

    public function test_other_roles_cannot_access_study_activity_routes(): void
    {
        $admin = User::factory()->role(Role::Admin)->create();
        $lecturer = User::factory()->role(Role::GuruDosen)->create();

        $this->actingAs($admin)->get('/mahasiswa/study-activities')->assertForbidden();
        $this->actingAs($lecturer)->get('/mahasiswa/study-activities')->assertForbidden();
    }
}
