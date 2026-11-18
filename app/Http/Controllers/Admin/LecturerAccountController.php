<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreLecturerRequest;
use App\Http\Requests\Admin\UpdateLecturerNameRequest;
use App\Models\GuruDosen;
use App\Services\LecturerAccountService;
use App\Services\TemporaryPasswordService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class LecturerAccountController extends Controller
{
    public function index(): View
    {
        return view('pages.admin.lecturers.index', [
            'lecturers' => GuruDosen::query()
                ->with('user')
                ->orderBy('nama_lengkap')
                ->paginate(10),
        ]);
    }

    public function show(GuruDosen $lecturer): View
    {
        return view('pages.admin.lecturers.show', [
            'lecturer' => $lecturer->load('user')->loadCount('mataKuliah'),
        ]);
    }

    public function update(UpdateLecturerNameRequest $request, GuruDosen $lecturer): RedirectResponse
    {
        $lecturer->update([
            'nama_lengkap' => $request->validated('nama_lengkap'),
        ]);

        return redirect()
            ->route('admin.lecturers.show', $lecturer)
            ->with('status', 'Nama guru/dosen diperbarui.');
    }

    public function store(StoreLecturerRequest $request, LecturerAccountService $service): RedirectResponse
    {
        $result = $service->create($request->validated());

        return redirect()
            ->route('admin.lecturers.index')
            ->with('status', 'Akun Guru/Dosen berhasil dibuat.')
            ->with('temporary_password', [
                'username' => $result['user']->username,
                'password' => $result['temporary_password'],
            ]);
    }

    public function resetTemporaryPassword(GuruDosen $lecturer, TemporaryPasswordService $temporaryPasswords): RedirectResponse
    {
        $temporaryPassword = $temporaryPasswords->issueFor($lecturer->user);

        return redirect()
            ->route('admin.lecturers.index')
            ->with('status', 'Temporary password baru diterbitkan.')
            ->with('temporary_password', [
                'username' => $lecturer->user->username,
                'password' => $temporaryPassword,
            ]);
    }
}
