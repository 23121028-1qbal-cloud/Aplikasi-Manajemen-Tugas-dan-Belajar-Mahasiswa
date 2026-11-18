<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Http\Requests\Student\UpdateAcademicProgressRequest;
use App\Models\ProgresTugasAkademik;
use App\Models\StatusTugas;
use App\Models\Tugas;
use App\Services\AcademicTaskService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AcademicTaskController extends Controller
{
    public function show(Request $request, Tugas $task): View
    {
        $progress = $this->ownProgress($request, $task);

        return view('pages.mahasiswa.academic.show', [
            'task' => $task->load(['mataKuliah', 'prioritas']),
            'progress' => $progress->load(['statusTugas', 'diubahOleh']),
            'statuses' => StatusTugas::query()->orderBy('id_status')->get(),
        ]);
    }

    public function updateProgress(
        UpdateAcademicProgressRequest $request,
        Tugas $task,
        AcademicTaskService $service
    ): RedirectResponse {
        $progress = $this->ownProgress($request, $task);

        $service->updateProgress(
            $progress,
            $request->integer('id_status'),
            $request->user(),
            $request->integer('row_version')
        );

        return redirect()
            ->route('mahasiswa.academic-tasks.show', $task)
            ->with('status', 'Progres tugas akademik berhasil diperbarui.');
    }

    private function ownProgress(Request $request, Tugas $task): ProgresTugasAkademik
    {
        $student = $request->user()->mahasiswa;
        abort_if($student === null, 404);

        // Progres mahasiswa lain diperlakukan sebagai resource yang tidak ada.
        return ProgresTugasAkademik::query()
            ->where('id_tugas', $task->getKey())
            ->where('id_mahasiswa', $student->id_mahasiswa)
            ->firstOrFail();
    }
}
