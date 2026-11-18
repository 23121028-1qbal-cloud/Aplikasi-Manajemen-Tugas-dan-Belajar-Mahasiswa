<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreCourseRequest;
use App\Http\Requests\Admin\UpdateCourseRequest;
use App\Models\GuruDosen;
use App\Models\MataKuliah;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class CourseController extends Controller
{
    public function index(): View
    {
        return view('pages.admin.courses.index', [
            'courses' => MataKuliah::query()
                ->with('guruDosen')
                ->withCount('enrollments')
                ->orderBy('kode_mata_kuliah')
                ->paginate(10),
            'lecturers' => GuruDosen::query()->orderBy('nama_lengkap')->get(),
        ]);
    }

    public function store(StoreCourseRequest $request): RedirectResponse
    {
        MataKuliah::create($request->validated());

        return redirect()
            ->route('admin.courses.index')
            ->with('status', 'Mata kuliah berhasil dibuat.');
    }

    public function edit(MataKuliah $course): View
    {
        return view('pages.admin.courses.edit', [
            'course' => $course->load('guruDosen'),
            'lecturers' => GuruDosen::query()->orderBy('nama_lengkap')->get(),
        ]);
    }

    public function update(UpdateCourseRequest $request, MataKuliah $course): RedirectResponse
    {
        $course->update($request->validated());

        return redirect()
            ->route('admin.courses.index')
            ->with('status', 'Mata kuliah berhasil diperbarui.');
    }

    public function archive(MataKuliah $course): RedirectResponse
    {
        if ($course->is_archived) {
            return redirect()
                ->route('admin.courses.index')
                ->with('status', 'Mata kuliah sudah diarsipkan.');
        }

        $course->update([
            'is_archived' => true,
            'tanggal_arsip' => now(),
        ]);

        return redirect()
            ->route('admin.courses.index')
            ->with('status', 'Mata kuliah diarsipkan. Riwayat enrollment tetap tersimpan.');
    }
}
