<?php

namespace App\Services;

use App\Enums\NotificationType;
use App\Models\AktivitasBelajar;
use App\Models\Mahasiswa;
use App\Models\Notifikasi;
use App\Models\ProgresTugasAkademik;
use App\Models\Tugas;
use Carbon\CarbonImmutable;
use Carbon\CarbonInterface;
use Illuminate\Support\Facades\DB;

class NotificationService
{
    private const DISPLAY_TIMEZONE = 'Asia/Jakarta';

    /**
     * Notifikasi distribusi hanya untuk progres yang benar-benar dibuat.
     *
     * @param  array<int, int>  $studentIds
     */
    public function notifyAcademicTaskAssigned(Tugas $task, array $studentIds): int
    {
        if ($studentIds === []) {
            return 0;
        }

        $task->loadMissing('mataKuliah');

        $recipients = Mahasiswa::query()
            ->with('user')
            ->whereIn('id_mahasiswa', $studentIds)
            ->get()
            ->filter(fn (Mahasiswa $student) => $this->isActiveRecipient($student));

        if ($recipients->isEmpty()) {
            return 0;
        }

        $now = now();
        $course = $task->mataKuliah?->kode_mata_kuliah ?? 'mata kuliah';
        $deadline = $this->displayDate($task->deadline);

        $rows = $recipients->map(fn (Mahasiswa $student) => [
            'id_user' => $student->id_user,
            'tipe' => NotificationType::AcademicTaskAssigned->value,
            'judul' => 'Tugas akademik baru',
            'pesan' => "Tugas \"{$task->judul_tugas}\" untuk {$course} telah didistribusikan. Deadline {$deadline} WIB.",
            'resource_type' => 'tugas',
            'resource_id' => $task->id_tugas,
            'is_read' => false,
            'read_at' => null,
            'dedup_key' => "academic_task_assigned:{$task->id_tugas}:{$student->id_mahasiswa}",
            'tanggal_dibuat' => $now,
            'created_at' => $now,
            'updated_at' => $now,
        ])->all();

        return $this->insertRows($rows);
    }

    public function notifyAcademicTaskDeadlines(?CarbonInterface $now = null): int
    {
        $now = $now === null ? CarbonImmutable::now() : CarbonImmutable::instance($now);
        $jakartaNow = $now->timezone(self::DISPLAY_TIMEZONE);
        $start = $jakartaNow->addDay()->startOfDay()->utc();
        $end = $jakartaNow->addDay()->endOfDay()->utc();

        $inserted = 0;

        ProgresTugasAkademik::query()
            ->whereHas('tugas', fn ($query) => $query
                ->academic()
                ->where('is_closed', false)
                ->where('is_archived', false)
                ->whereBetween('deadline', [$start, $end]))
            ->whereHas('statusTugas', fn ($query) => $query->where('nama_status', '!=', 'Selesai'))
            ->with(['tugas.mataKuliah', 'mahasiswa.user'])
            ->orderBy('id_progress')
            ->chunkById(200, function ($progressRows) use (&$inserted) {
                $now = now();
                $rows = [];

                foreach ($progressRows as $progress) {
                    $task = $progress->tugas;
                    $student = $progress->mahasiswa;

                    if ($task === null || ! $this->isActiveRecipient($student)) {
                        continue;
                    }

                    $rows[] = [
                        'id_user' => $student->id_user,
                        'tipe' => NotificationType::AcademicTaskDeadline->value,
                        'judul' => 'Pengingat deadline tugas',
                        'pesan' => "Deadline tugas \"{$task->judul_tugas}\" untuk ".($task->mataKuliah?->kode_mata_kuliah ?? 'mata kuliah').' adalah '.$this->displayDate($task->deadline).' WIB. Progres Anda masih belum Selesai.',
                        'resource_type' => 'tugas',
                        'resource_id' => $task->id_tugas,
                        'is_read' => false,
                        'read_at' => null,
                        'dedup_key' => 'academic_task_deadline:'.$task->id_tugas.':'.$student->id_mahasiswa.':'.$task->deadline->copy()->timezone(self::DISPLAY_TIMEZONE)->toDateString(),
                        'tanggal_dibuat' => $now,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ];
                }

                $inserted += $this->insertRows($rows);
            });

        return $inserted;
    }

    public function notifyStudyActivityReminders(?CarbonInterface $now = null): int
    {
        $now = $now === null ? CarbonImmutable::now() : CarbonImmutable::instance($now);
        $windowEnd = $now->addHour();

        $inserted = 0;

        AktivitasBelajar::query()
            ->where('is_archived', false)
            ->whereBetween('waktu_mulai', [$now, $windowEnd])
            ->whereHas('statusBelajar', fn ($query) => $query->where('nama_status', 'Direncanakan'))
            ->with(['mahasiswa.user', 'mataKuliah'])
            ->orderBy('id_aktivitas')
            ->chunkById(200, function ($activities) use (&$inserted) {
                $now = now();
                $rows = [];

                foreach ($activities as $activity) {
                    $student = $activity->mahasiswa;

                    if (! $this->isActiveRecipient($student)) {
                        continue;
                    }

                    $course = $activity->mataKuliah?->kode_mata_kuliah;
                    $courseText = $course === null ? '' : " untuk {$course}";
                    $start = $this->displayDate($activity->waktu_mulai);

                    $rows[] = [
                        'id_user' => $student->id_user,
                        'tipe' => NotificationType::StudyActivityReminder->value,
                        'judul' => 'Pengingat aktivitas belajar',
                        'pesan' => "Aktivitas \"{$activity->judul_aktivitas}\"{$courseText} dimulai pukul {$start} WIB.",
                        'resource_type' => 'aktivitas_belajar',
                        'resource_id' => $activity->id_aktivitas,
                        'is_read' => false,
                        'read_at' => null,
                        'dedup_key' => 'study_activity_reminder:'.$activity->id_aktivitas.':'.$student->id_mahasiswa.':'.$activity->waktu_mulai->copy()->utc()->format('Y-m-d H:i:s'),
                        'tanggal_dibuat' => $now,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ];
                }

                $inserted += $this->insertRows($rows);
            });

        return $inserted;
    }

    public function cleanup(int $days = 90): int
    {
        return DB::transaction(fn () => Notifikasi::query()
            ->where('tanggal_dibuat', '<', now()->subDays($days))
            ->delete());
    }

    private function displayDate(?CarbonInterface $date): string
    {
        return $date?->copy()->timezone(self::DISPLAY_TIMEZONE)->format('d M Y H:i') ?? '—';
    }

    private function isActiveRecipient(?Mahasiswa $student): bool
    {
        return $student !== null
            && ! $student->is_archived
            && $student->user?->is_active === true;
    }

    /**
     * @param  array<int, array<string, mixed>>  $rows
     */
    private function insertRows(array $rows): int
    {
        if ($rows === []) {
            return 0;
        }

        return DB::transaction(fn () => Notifikasi::query()->insertOrIgnore($rows));
    }
}
