<?php

namespace App\Http\Controllers\Student;

use App\Enums\EnrollmentStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Student\StoreStudyActivityRequest;
use App\Http\Requests\Student\UpdateStudyActivityRequest;
use App\Models\AktivitasBelajar;
use App\Models\StatusBelajar;
use App\Services\StudyActivityService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class StudyActivityController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorize('viewAny', AktivitasBelajar::class);

        $student = $request->user()->mahasiswa;
        abort_if($student === null, 404);

        $activities = $student->aktivitasBelajar()
            ->active()
            ->with(['statusBelajar', 'mataKuliah'])
            ->when($request->integer('id_status_belajar'), fn ($query, $status) => $query->where('id_status_belajar', $status))
            ->when($request->string('q')->trim()->value(), function ($query, $search) {
                $query->where('judul_aktivitas', 'like', "%{$search}%");
            })
            ->orderBy('waktu_mulai')
            ->paginate(10)
            ->withQueryString();

        return view('pages.mahasiswa.study.index', [
            'activities' => $activities,
            'statuses' => StatusBelajar::query()->orderBy('id_status_belajar')->get(),
            'filters' => $request->only(['id_status_belajar', 'q']),
        ]);
    }

    public function create(Request $request): View
    {
        $this->authorize('create', AktivitasBelajar::class);

        return view('pages.mahasiswa.study.create', $this->formData($request));
    }

    public function store(StoreStudyActivityRequest $request, StudyActivityService $service): RedirectResponse
    {
        $this->authorize('create', AktivitasBelajar::class);

        $activity = $service->create($request->user()->mahasiswa, $request->validated());

        return redirect()
            ->route('mahasiswa.study-activities.show', $activity)
            ->with('status', 'Aktivitas belajar berhasil dibuat.');
    }

    public function show(Request $request, AktivitasBelajar $activity): View
    {
        $activity = $this->ownedActivity($request, $activity);
        abort_unless($request->user()->can('view', $activity), 404);

        return view('pages.mahasiswa.study.show', [
            'activity' => $activity->load(['statusBelajar', 'mataKuliah']),
        ]);
    }

    public function edit(Request $request, AktivitasBelajar $activity): View
    {
        $activity = $this->ownedActivity($request, $activity);
        abort_unless($request->user()->can('update', $activity), 404);

        return view('pages.mahasiswa.study.edit', [
            ...$this->formData($request),
            'activity' => $activity,
        ]);
    }

    public function update(UpdateStudyActivityRequest $request, AktivitasBelajar $activity, StudyActivityService $service): RedirectResponse
    {
        $activity = $this->ownedActivity($request, $activity);
        abort_unless($request->user()->can('update', $activity), 404);

        $service->update($activity, $request->validated());

        return redirect()
            ->route('mahasiswa.study-activities.show', $activity)
            ->with('status', 'Aktivitas belajar berhasil diperbarui.');
    }

    public function archive(Request $request, AktivitasBelajar $activity, StudyActivityService $service): RedirectResponse
    {
        $activity = $this->ownedActivity($request, $activity);
        abort_unless($request->user()->can('archive', $activity), 404);

        $service->archive($activity);

        return redirect()
            ->route('mahasiswa.study-activities.index')
            ->with('status', 'Aktivitas belajar diarsipkan. Data tetap tersedia di riwayat.');
    }

    public function history(Request $request): View
    {
        $this->authorize('viewAny', AktivitasBelajar::class);

        $student = $request->user()->mahasiswa;
        abort_if($student === null, 404);

        $activities = $student->aktivitasBelajar()
            ->with(['statusBelajar', 'mataKuliah'])
            ->where(function ($query) {
                $query->where('is_archived', true)
                    ->orWhereHas('statusBelajar', fn ($status) => $status->where('nama_status', 'Selesai'));
            })
            ->orderByDesc('updated_at')
            ->paginate(10);

        return view('pages.mahasiswa.study.history', [
            'activities' => $activities,
        ]);
    }

    private function formData(Request $request): array
    {
        $student = $request->user()->mahasiswa;
        abort_if($student === null, 404);

        return [
            'statuses' => StatusBelajar::query()->orderBy('id_status_belajar')->get(),
            'courses' => $student->enrollments()
                ->with('mataKuliah')
                ->where('status', EnrollmentStatus::Terdaftar)
                ->get()
                ->pluck('mataKuliah')
                ->filter(),
        ];
    }

    private function ownedActivity(Request $request, AktivitasBelajar $activity): AktivitasBelajar
    {
        $student = $request->user()->mahasiswa;
        abort_if($student === null, 404);

        return $student->aktivitasBelajar()
            ->whereKey($activity->getKey())
            ->firstOrFail();
    }
}
