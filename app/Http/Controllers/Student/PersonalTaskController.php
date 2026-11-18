<?php

namespace App\Http\Controllers\Student;

use App\Enums\EnrollmentStatus;
use App\Enums\TaskType;
use App\Http\Controllers\Controller;
use App\Http\Requests\Student\StorePersonalTaskRequest;
use App\Http\Requests\Student\UpdatePersonalTaskRequest;
use App\Models\Prioritas;
use App\Models\StatusTugas;
use App\Models\Tugas;
use App\Services\PersonalTaskService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PersonalTaskController extends Controller
{
    public function index(Request $request): View
    {
        $student = $request->user()->mahasiswa;
        abort_if($student === null, 404);

        $tasks = $student->tugasPersonal()
            ->active()
            ->with(['prioritas', 'statusTugas', 'mataKuliah'])
            ->when($request->integer('id_status'), fn ($query, $status) => $query->where('id_status', $status))
            ->when($request->integer('id_prioritas'), fn ($query, $priority) => $query->where('id_prioritas', $priority))
            ->when($request->string('q')->trim()->value(), function ($query, $search) {
                $query->where('judul_tugas', 'like', "%{$search}%");
            })
            ->orderBy('deadline')
            ->paginate(10)
            ->withQueryString();

        return view('pages.mahasiswa.tasks.index', [
            'tasks' => $tasks,
            'priorities' => Prioritas::query()->orderBy('id_prioritas')->get(),
            'statuses' => StatusTugas::query()->orderBy('id_status')->get(),
            'filters' => $request->only(['id_status', 'id_prioritas', 'q']),
        ]);
    }

    public function create(Request $request): View
    {
        return view('pages.mahasiswa.tasks.create', $this->formData($request));
    }

    public function store(StorePersonalTaskRequest $request, PersonalTaskService $service): RedirectResponse
    {
        $task = $service->create($request->user()->mahasiswa, $request->validated());

        return redirect()
            ->route('mahasiswa.tasks.personal.show', $task)
            ->with('status', 'Tugas personal berhasil dibuat.');
    }

    public function show(Request $request, Tugas $task): View
    {
        return view('pages.mahasiswa.tasks.show', [
            'task' => $this->ownedTask($request, $task)->load(['prioritas', 'statusTugas', 'mataKuliah']),
        ]);
    }

    public function edit(Request $request, Tugas $task): View
    {
        $task = $this->ownedTask($request, $task);

        return view('pages.mahasiswa.tasks.edit', [
            ...$this->formData($request),
            'task' => $task,
        ]);
    }

    public function update(UpdatePersonalTaskRequest $request, Tugas $task, PersonalTaskService $service): RedirectResponse
    {
        $task = $this->ownedTask($request, $task);

        $service->update($task, $request->validated());

        return redirect()
            ->route('mahasiswa.tasks.personal.show', $task)
            ->with('status', 'Tugas personal berhasil diperbarui.');
    }

    public function archive(Request $request, Tugas $task, PersonalTaskService $service): RedirectResponse
    {
        $task = $this->ownedTask($request, $task);

        $service->archive($task);

        return redirect()
            ->route('mahasiswa.tasks.index')
            ->with('status', 'Tugas personal diarsipkan. Data tetap tersedia di riwayat.');
    }

    public function history(Request $request): View
    {
        $student = $request->user()->mahasiswa;
        abort_if($student === null, 404);

        $tasks = $student->tugasPersonal()
            ->with(['prioritas', 'statusTugas', 'mataKuliah'])
            ->where(function ($query) {
                $query->where('is_archived', true)
                    ->orWhereHas('statusTugas', fn ($status) => $status->where('nama_status', 'Selesai'));
            })
            ->orderByDesc('updated_at')
            ->paginate(10);

        return view('pages.mahasiswa.tasks.history', [
            'tasks' => $tasks,
        ]);
    }

    private function formData(Request $request): array
    {
        $student = $request->user()->mahasiswa;
        abort_if($student === null, 404);

        return [
            'priorities' => Prioritas::query()->orderBy('id_prioritas')->get(),
            'statuses' => StatusTugas::query()->orderBy('id_status')->get(),
            'courses' => $student->enrollments()
                ->with('mataKuliah')
                ->where('status', EnrollmentStatus::Terdaftar)
                ->get()
                ->pluck('mataKuliah')
                ->filter(),
        ];
    }

    private function ownedTask(Request $request, Tugas $task): Tugas
    {
        $student = $request->user()->mahasiswa;
        abort_if($student === null, 404);

        // Resource milik mahasiswa lain tidak didiskusikan keberadaannya (REC-12).
        return $student->tugasPersonal()
            ->whereKey($task->getKey())
            ->where('task_type', TaskType::Personal->value)
            ->firstOrFail();
    }
}
