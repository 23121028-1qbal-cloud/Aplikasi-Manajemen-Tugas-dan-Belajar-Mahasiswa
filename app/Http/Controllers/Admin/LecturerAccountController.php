<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreLecturerRequest;
use App\Http\Requests\Admin\UpdateLecturerNameRequest;
use App\Models\GuruDosen;
use App\Services\AccountArchiveService;
use App\Services\LecturerAccountService;
use App\Services\TemporaryPasswordService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class LecturerAccountController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorize('manage', GuruDosen::class);

        return view('pages.admin.lecturers.index', [
            'lecturers' => GuruDosen::query()
                ->with('user')
                ->orderBy('nama_lengkap')
                ->paginate(10),
        ]);
    }

    public function show(Request $request, GuruDosen $lecturer): View
    {
        $this->authorize('view', $lecturer);

        return view('pages.admin.lecturers.show', [
            'lecturer' => $lecturer->load('user')->loadCount([
                'mataKuliah',
                'mataKuliah as active_courses_count' => fn ($query) => $query->where('is_archived', false),
            ]),
        ]);
    }

    public function update(UpdateLecturerNameRequest $request, GuruDosen $lecturer): RedirectResponse
    {
        $this->authorize('update', $lecturer);

        $lecturer->update([
            'nama_lengkap' => $request->validated('nama_lengkap'),
        ]);

        return redirect()
            ->route('admin.lecturers.show', $lecturer)
            ->with('status', 'Nama guru/dosen diperbarui.');
    }

    public function store(StoreLecturerRequest $request, LecturerAccountService $service): RedirectResponse
    {
        $this->authorize('manage', GuruDosen::class);

        $result = $service->create($request->validated());

        return redirect()
            ->route('admin.lecturers.index')
            ->with('status', 'Akun Guru/Dosen berhasil dibuat.')
            ->with('temporary_password', [
                'username' => $result['user']->username,
                'password' => $result['temporary_password'],
            ]);
    }

    public function resetTemporaryPassword(Request $request, GuruDosen $lecturer, TemporaryPasswordService $temporaryPasswords): RedirectResponse
    {
        $this->authorize('update', $lecturer);

        $temporaryPassword = $temporaryPasswords->issueFor($lecturer->user);

        return redirect()
            ->route('admin.lecturers.index')
            ->with('status', 'Temporary password baru diterbitkan.')
            ->with('temporary_password', [
                'username' => $lecturer->user->username,
                'password' => $temporaryPassword,
            ]);
    }

    public function archive(Request $request, GuruDosen $lecturer, AccountArchiveService $service): RedirectResponse
    {
        $this->authorize('archive', $lecturer);

        $service->archiveLecturer($lecturer);

        return redirect()
            ->route('admin.lecturers.show', $lecturer)
            ->with('status', 'Akun guru/dosen diarsipkan. Riwayat tetap tersimpan.');
    }

    public function restore(Request $request, GuruDosen $lecturer, AccountArchiveService $service): RedirectResponse
    {
        $this->authorize('restore', $lecturer);

        $service->restoreLecturer($lecturer);

        return redirect()
            ->route('admin.lecturers.show', $lecturer)
            ->with('status', 'Akun guru/dosen dipulihkan dan dapat login kembali.');
    }
}
