<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\MataKuliah;
use App\Models\Tugas;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AcademicMonitoringController extends Controller
{
    public function index(Request $request): View
    {
        $tasks = Tugas::query()
            ->academic()
            ->with(['mataKuliah.guruDosen', 'prioritas'])
            ->withCount([
                'progres',
                'progres as selesai_count' => fn ($query) => $query->whereHas(
                    'statusTugas',
                    fn ($status) => $status->where('nama_status', 'Selesai')
                ),
            ])
            ->when($request->integer('id_mata_kuliah'), fn ($query, $course) => $query->where('id_mata_kuliah', $course))
            ->when($request->string('q')->trim()->value(), function ($query, $search) {
                $query->where('judul_tugas', 'like', "%{$search}%");
            })
            ->orderByDesc('created_at')
            ->paginate(10)
            ->withQueryString();

        return view('pages.admin.academic-monitoring.index', [
            'tasks' => $tasks,
            'courses' => MataKuliah::query()->orderBy('kode_mata_kuliah')->get(),
            'filters' => $request->only(['id_mata_kuliah', 'q']),
        ]);
    }

    public function show(Tugas $task): View
    {
        // Monitoring hanya membaca definisi tugas akademik dan progres.
        $task = Tugas::query()
            ->academic()
            ->whereKey($task->getKey())
            ->with(['mataKuliah.guruDosen', 'prioritas'])
            ->withCount([
                'progres',
                'progres as selesai_count' => fn ($query) => $query->whereHas(
                    'statusTugas',
                    fn ($status) => $status->where('nama_status', 'Selesai')
                ),
            ])
            ->firstOrFail();

        return view('pages.admin.academic-monitoring.show', [
            'task' => $task,
            'progress' => $task->progres()
                ->with(['mahasiswa', 'statusTugas', 'diubahOleh'])
                ->orderBy('id_progress')
                ->paginate(20),
        ]);
    }
}
