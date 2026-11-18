<?php

namespace App\Http\Controllers\Admin;

use App\Enums\EnrollmentStatus;
use App\Enums\Semester;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreEnrollmentRequest;
use App\Http\Requests\Admin\UpdateEnrollmentStatusRequest;
use App\Models\Enrollment;
use App\Models\Mahasiswa;
use App\Models\MataKuliah;
use App\Services\EnrollmentService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class EnrollmentController extends Controller
{
    public function index(): View
    {
        return view('pages.admin.enrollments.index', [
            'enrollments' => Enrollment::query()
                ->with(['mahasiswa', 'mataKuliah'])
                ->orderByDesc('created_at')
                ->paginate(10),
            'students' => Mahasiswa::query()->orderBy('nama_lengkap')->get(),
            'courses' => MataKuliah::query()
                ->with('guruDosen')
                ->where('is_archived', false)
                ->orderBy('kode_mata_kuliah')
                ->get(),
            'semesters' => Semester::cases(),
        ]);
    }

    public function store(StoreEnrollmentRequest $request, EnrollmentService $service): RedirectResponse
    {
        $result = $service->enroll($request->validated());

        $message = $result['reactivated']
            ? 'Enrollment periode yang sama diaktifkan kembali.'
            : 'Enrollment berhasil dibuat.';

        return redirect()
            ->route('admin.enrollments.index')
            ->with('status', $message);
    }

    public function update(UpdateEnrollmentStatusRequest $request, Enrollment $enrollment, EnrollmentService $service): RedirectResponse
    {
        $status = $request->enum('status', EnrollmentStatus::class);

        $service->changeStatus($enrollment, $status);

        return redirect()
            ->route('admin.enrollments.index')
            ->with('status', "Status enrollment diubah menjadi {$status->value}.");
    }
}
