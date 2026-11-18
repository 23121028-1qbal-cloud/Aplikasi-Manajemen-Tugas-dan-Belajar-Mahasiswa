<?php

namespace App\Http\Controllers\Lecturer;

use App\Http\Controllers\Controller;
use App\Http\Requests\Lecturer\UpdateAssignedCourseRequest;
use App\Models\MataKuliah;
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
        $lecturer = $request->user()->guruDosen;

        abort_unless(
            $lecturer !== null && $course->guru_dosen_id === $lecturer->id_guru_dosen,
            404
        );

        return view('pages.lecturer.courses.show', [
            'course' => $course->load('guruDosen')->loadCount('enrollments'),
        ]);
    }

    public function update(UpdateAssignedCourseRequest $request, MataKuliah $course, CourseService $service): RedirectResponse
    {
        $lecturer = $request->user()->guruDosen;

        abort_unless(
            $lecturer !== null && $course->guru_dosen_id === $lecturer->id_guru_dosen,
            404
        );

        $service->update($course, $request->validated());

        return redirect()
            ->route('lecturer.courses.show', $course)
            ->with('status', 'Nama mata kuliah berhasil diperbarui.');
    }
}
