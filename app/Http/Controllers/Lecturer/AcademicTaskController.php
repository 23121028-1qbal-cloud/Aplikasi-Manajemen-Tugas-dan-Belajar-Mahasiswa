<?php

namespace App\Http\Controllers\Lecturer;

use App\Http\Controllers\Controller;
use App\Http\Requests\Lecturer\CorrectAcademicProgressRequest;
use App\Http\Requests\Lecturer\StoreAcademicTaskRequest;
use App\Http\Requests\Lecturer\UpdateAcademicTaskRequest;
use App\Models\MataKuliah;
use App\Models\Prioritas;
use App\Models\ProgresTugasAkademik;
use App\Models\StatusTugas;
use App\Models\Tugas;
use App\Services\AcademicTaskService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AcademicTaskController extends Controller
{
    public function index(Request $request): View
    {
        $lecturer = $request->user()->guruDosen;
        abort_if($lecturer === null, 404);

        $tasks = Tugas::query()
            ->academic()
            ->whereHas('mataKuliah', fn ($query) => $query->where('guru_dosen_id', $lecturer->id_guru_dosen))
            ->with(['mataKuliah', 'prioritas'])
            ->withCount('progres')
            ->when($request->integer('id_mata_kuliah'), fn ($query, $course) => $query->where('id_mata_kuliah', $course))
            ->when($request->string('status')->value() === 'open', fn ($query) => $query->where('is_closed', false))
            ->when($request->string('status')->value() === 'closed', fn ($query) => $query->where('is_closed', true))
            ->when($request->string('q')->trim()->value(), function ($query, $search) {
                $query->where('judul_tugas', 'like', "%{$search}%");
            })
            ->orderByDesc('created_at')
            ->paginate(10)
            ->withQueryString();

        return view('pages.lecturer.academic-tasks.index', [
            'tasks' => $tasks,
            'courses' => $lecturer->mataKuliah()->orderBy('kode_mata_kuliah')->get(),
            'filters' => $request->only(['id_mata_kuliah', 'status', 'q']),
        ]);
    }

    public function create(Request $request): View
    {
        $lecturer = $request->user()->guruDosen;
        abort_if($lecturer === null, 404);

        return view('pages.lecturer.academic-tasks.create', [
            'courses' => $lecturer->mataKuliah()
                ->where('is_archived', false)
                ->orderBy('kode_mata_kuliah')
                ->get(),
            'priorities' => Prioritas::query()->orderBy('id_prioritas')->get(),
        ]);
    }

    public function store(StoreAcademicTaskRequest $request, AcademicTaskService $service): RedirectResponse
    {
        $lecturer = $request->user()->guruDosen;
        abort_if($lecturer === null, 404);

        $course = $this->assignedCourse($lecturer->id_guru_dosen, $request->integer('id_mata_kuliah'));
        $result = $service->create($lecturer, [
            ...$request->validated(),
            'id_mata_kuliah' => $course->id_mata_kuliah,
        ]);

        return redirect()
            ->route('lecturer.academic-tasks.show', $result['task'])
            ->with('status', "Tugas akademik dibuat. {$result['distributed']} progres mahasiswa didistribusikan.");
    }

    public function show(Request $request, Tugas $task): View
    {
        $task = $this->assignedTask($request, $task)
            ->load(['mataKuliah', 'prioritas'])
            ->loadCount([
                'progres',
                'progres as selesai_count' => fn ($query) => $query->whereHas(
                    'statusTugas',
                    fn ($status) => $status->where('nama_status', 'Selesai')
                ),
            ]);

        return view('pages.lecturer.academic-tasks.show', [
            'task' => $task,
        ]);
    }

    public function edit(Request $request, Tugas $task): View
    {
        $task = $this->assignedTask($request, $task)->load('mataKuliah');

        return view('pages.lecturer.academic-tasks.edit', [
            'task' => $task,
            'priorities' => Prioritas::query()->orderBy('id_prioritas')->get(),
        ]);
    }

    public function update(UpdateAcademicTaskRequest $request, Tugas $task, AcademicTaskService $service): RedirectResponse
    {
        $task = $this->assignedTask($request, $task);

        $service->update($task, $request->validated());

        return redirect()
            ->route('lecturer.academic-tasks.show', $task)
            ->with('status', 'Definisi tugas akademik berhasil diperbarui.');
    }

    public function close(Request $request, Tugas $task, AcademicTaskService $service): RedirectResponse
    {
        $task = $this->assignedTask($request, $task);

        $service->close($task);

        return redirect()
            ->route('lecturer.academic-tasks.show', $task)
            ->with('status', 'Tugas akademik ditutup. Distribusi progres baru dihentikan.');
    }

    public function progress(Request $request, Tugas $task): View
    {
        $task = $this->assignedTask($request, $task)->load(['mataKuliah', 'prioritas']);

        $progress = $task->progres()
            ->with(['mahasiswa', 'statusTugas', 'diubahOleh'])
            ->orderBy('id_progress')
            ->paginate(20)
            ->withQueryString();

        return view('pages.lecturer.academic-tasks.progress', [
            'task' => $task,
            'progress' => $progress,
            'statuses' => StatusTugas::query()->orderBy('id_status')->get(),
        ]);
    }

    public function updateProgress(
        CorrectAcademicProgressRequest $request,
        ProgresTugasAkademik $progress,
        AcademicTaskService $service
    ): RedirectResponse {
        $task = $this->assignedTask($request, $progress->tugas);

        abort_unless($progress->id_tugas === $task->id_tugas, 404);

        $service->updateProgress(
            $progress,
            $request->integer('id_status'),
            $request->user(),
            $request->integer('row_version')
        );

        return redirect()
            ->route('lecturer.academic-tasks.progress', $task)
            ->with('status', 'Progres mahasiswa berhasil dikoreksi.');
    }

    private function assignedTask(Request $request, Tugas $task): Tugas
    {
        $lecturer = $request->user()->guruDosen;
        abort_if($lecturer === null, 404);

        return Tugas::query()
            ->academic()
            ->whereKey($task->getKey())
            ->whereHas('mataKuliah', fn ($query) => $query->where('guru_dosen_id', $lecturer->id_guru_dosen))
            ->firstOrFail();
    }

    private function assignedCourse(int $lecturerId, int $courseId): MataKuliah
    {
        return MataKuliah::query()
            ->whereKey($courseId)
            ->where('guru_dosen_id', $lecturerId)
            ->firstOrFail();
    }
}
