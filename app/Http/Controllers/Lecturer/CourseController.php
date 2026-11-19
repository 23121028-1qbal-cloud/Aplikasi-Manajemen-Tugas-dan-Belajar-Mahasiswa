<?php

namespace App\Http\Controllers\Lecturer;

use App\Http\Controllers\Controller;
use App\Http\Requests\Lecturer\UpdateAssignedCourseRequest;
use App\Models\MataKuliah;
use App\Models\Tugas;
use App\Services\CourseService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CourseController extends Controller
{
    public function index(Request $request): View
    {
        $lecturer = $request->user()->guruDosen;

        $courses = $lecturer === null
            ? MataKuliah::query()->whereKey(null)->paginate(10)
            : $lecturer->mataKuliah()
                ->withCount('enrollments')
                ->orderBy('kode_mata_kuliah')
                ->paginate(10);

        return view('pages.lecturer.courses.index', [
            'courses' => $courses,
        ]);
    }

    public function show(Request $request, MataKuliah $course): View
    {
        abort_unless($request->user()->can('view', $course), 404);

        return view('pages.lecturer.courses.show', [
            'course' => $course->load('guruDosen')->loadCount('enrollments'),
            'tasks' => Tugas::query()
                ->academic()
                ->where('id_mata_kuliah', $course->id_mata_kuliah)
                ->with('prioritas')
                ->withCount('progres')
                ->orderByDesc('created_at')
                ->get(),
        ]);
    }

    public function progress(Request $request, MataKuliah $course): View
    {
        abort_unless($request->user()->can('view', $course), 404);

        $tasks = Tugas::query()
            ->academic()
            ->where('id_mata_kuliah', $course->id_mata_kuliah)
            ->with('prioritas')
            ->withCount([
                'progres',
                'progres as selesai_count' => fn ($query) => $query->whereHas(
                    'statusTugas',
                    fn ($status) => $status->where('nama_status', 'Selesai')
                ),
            ])
            ->orderByDesc('created_at')
            ->paginate(10)
            ->withQueryString();

        return view('pages.lecturer.courses.progress', [
            'course' => $course->load('guruDosen'),
            'tasks' => $tasks,
        ]);
    }

    public function update(UpdateAssignedCourseRequest $request, MataKuliah $course, CourseService $service): RedirectResponse
    {
        abort_unless($request->user()->can('update', $course), 404);

        $service->update($course, $request->validated());

        return redirect()
            ->route('lecturer.courses.show', $course)
            ->with('status', 'Nama mata kuliah berhasil diperbarui.');
    }
}
