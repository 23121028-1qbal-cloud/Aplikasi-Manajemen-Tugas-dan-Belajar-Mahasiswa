<?php

namespace App\Http\Controllers\Student;

use App\Enums\EnrollmentStatus;
use App\Http\Controllers\Controller;
use App\Models\Enrollment;
use App\Models\MataKuliah;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CourseController extends Controller
{
    public function index(Request $request): View
    {
        $student = $request->user()->mahasiswa;

        $enrollments = $student === null
            ? Enrollment::query()->whereKey(null)->paginate(10)
            : $student->enrollments()
                ->with(['mataKuliah.guruDosen'])
                ->where('status', EnrollmentStatus::Terdaftar)
                ->orderByDesc('created_at')
                ->paginate(10);

        return view('pages.mahasiswa.courses.index', [
            'enrollments' => $enrollments,
        ]);
    }

    public function show(Request $request, MataKuliah $course): View
    {
        $student = $request->user()->mahasiswa;

        $enrolled = $student !== null && $student->enrollments()
            ->where('id_mata_kuliah', $course->id_mata_kuliah)
            ->where('status', EnrollmentStatus::Terdaftar)
            ->exists();

        // Resource milik pihak lain tidak didiskusikan keberadaannya (REC-12).
        abort_unless($enrolled, 404);

        return view('pages.mahasiswa.courses.show', [
            'course' => $course->load('guruDosen'),
        ]);
    }
}
