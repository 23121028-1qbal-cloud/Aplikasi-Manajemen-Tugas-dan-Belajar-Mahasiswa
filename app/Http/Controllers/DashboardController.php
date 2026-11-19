<?php

namespace App\Http\Controllers;

use App\Enums\EnrollmentStatus;
use App\Models\Enrollment;
use App\Models\GuruDosen;
use App\Models\Mahasiswa;
use App\Models\MataKuliah;
use App\Models\ProgresTugasAkademik;
use App\Models\Tugas;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function show(Request $request): View
    {
        $user = $request->user()->load('mahasiswa', 'guruDosen');

        [$summary, $items, $itemsTitle] = match (true) {
            $user->hasRole('mahasiswa') => $this->studentDashboard($user),
            $user->hasRole('guru_dosen') => $this->lecturerDashboard($user),
            default => $this->adminDashboard(),
        };

        return view('pages.dashboard', [
            'user' => $user,
            'summary' => $summary,
            'items' => $items,
            'itemsTitle' => $itemsTitle,
        ]);
    }

    /**
     * @return array{0: array<int, array{label: string, value: int, hint: string}>, 1: \Illuminate\Support\Collection, 2: string}
     */
    private function studentDashboard(User $user): array
    {
        $student = $user->mahasiswa;

        if ($student === null) {
            return [[], collect(), 'Tugas Terdekat'];
        }

        $summary = [
            [
                'label' => 'Tugas Personal Aktif',
                'value' => $student->tugasPersonal()->active()->count(),
                'hint' => 'Belum diarsipkan',
            ],
            [
                'label' => 'Progres Akademik Belum Selesai',
                'value' => ProgresTugasAkademik::query()
                    ->where('id_mahasiswa', $student->id_mahasiswa)
                    ->whereHas('statusTugas', fn ($query) => $query->where('nama_status', '!=', 'Selesai'))
                    ->whereHas('tugas', fn ($query) => $query->academic()->where('is_archived', false))
                    ->count(),
                'hint' => 'Tugas akademik aktif',
            ],
            [
                'label' => 'Aktivitas Belajar Terjadwal',
                'value' => $student->aktivitasBelajar()
                    ->active()
                    ->where('waktu_mulai', '>=', now())
                    ->count(),
                'hint' => 'Akan datang',
            ],
        ];

        $items = $student->tugasPersonal()
            ->active()
            ->with(['prioritas', 'statusTugas', 'mataKuliah'])
            ->orderBy('deadline')
            ->limit(5)
            ->get();

        return [$summary, $items, 'Tugas Personal Terdekat'];
    }

    /**
     * @return array{0: array<int, array{label: string, value: int, hint: string}>, 1: \Illuminate\Support\Collection, 2: string}
     */
    private function lecturerDashboard(User $user): array
    {
        $lecturer = $user->guruDosen;

        if ($lecturer === null) {
            return [[], collect(), 'Tugas Akademik Terbaru'];
        }

        $courseIds = $lecturer->mataKuliah()
            ->where('is_archived', false)
            ->pluck('id_mata_kuliah');

        $summary = [
            [
                'label' => 'Mata Kuliah Diampu',
                'value' => $courseIds->count(),
                'hint' => 'Mata kuliah aktif',
            ],
            [
                'label' => 'Tugas Akademik Terbuka',
                'value' => Tugas::query()
                    ->academic()
                    ->whereIn('id_mata_kuliah', $courseIds)
                    ->where('is_closed', false)
                    ->where('is_archived', false)
                    ->count(),
                'hint' => 'Masih menerima progres',
            ],
            [
                'label' => 'Progres Belum Selesai',
                'value' => ProgresTugasAkademik::query()
                    ->whereHas('tugas', fn ($query) => $query->academic()->whereIn('id_mata_kuliah', $courseIds)->where('is_archived', false))
                    ->whereHas('statusTugas', fn ($query) => $query->where('nama_status', '!=', 'Selesai'))
                    ->count(),
                'hint' => 'Seluruh tugas di mata kuliah yang diampu',
            ],
        ];

        $items = Tugas::query()
            ->academic()
            ->whereIn('id_mata_kuliah', $courseIds)
            ->with(['mataKuliah', 'prioritas'])
            ->orderByDesc('created_at')
            ->limit(5)
            ->get();

        return [$summary, $items, 'Tugas Akademik Terbaru'];
    }

    /**
     * @return array{0: array<int, array{label: string, value: int, hint: string}>, 1: \Illuminate\Support\Collection, 2: string}
     */
    private function adminDashboard(): array
    {
        $summary = [
            [
                'label' => 'Mahasiswa Aktif',
                'value' => Mahasiswa::query()->where('is_archived', false)->count(),
                'hint' => 'Tidak diarsipkan',
            ],
            [
                'label' => 'Guru/Dosen Aktif',
                'value' => GuruDosen::query()->where('is_archived', false)->count(),
                'hint' => 'Tidak diarsipkan',
            ],
            [
                'label' => 'Mata Kuliah Aktif',
                'value' => MataKuliah::query()->where('is_archived', false)->count(),
                'hint' => 'Tidak diarsipkan',
            ],
            [
                'label' => 'Enrollment Terdaftar',
                'value' => Enrollment::query()->where('status', EnrollmentStatus::Terdaftar)->count(),
                'hint' => 'Seluruh periode',
            ],
        ];

        $items = Tugas::query()
            ->academic()
            ->where('is_closed', false)
            ->where('is_archived', false)
            ->with(['mataKuliah', 'prioritas'])
            ->orderByDesc('created_at')
            ->limit(5)
            ->get();

        return [$summary, $items, 'Tugas Akademik Terbuka Terbaru'];
    }
}
