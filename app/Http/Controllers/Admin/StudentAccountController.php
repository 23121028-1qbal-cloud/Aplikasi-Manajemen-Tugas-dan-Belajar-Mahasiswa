<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UpdateStudentNameRequest;
use App\Http\Requests\Admin\UpdateStudentStatusRequest;
use App\Models\Mahasiswa;
use App\Services\AccountArchiveService;
use App\Services\TemporaryPasswordService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class StudentAccountController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorize('manage', Mahasiswa::class);

        $search = trim((string) $request->query('q', ''));

        $students = Mahasiswa::query()
            ->with('user')
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($inner) use ($search) {
                    $inner->where('nama_lengkap', 'like', "%{$search}%")
                        ->orWhere('npm', 'like', "%{$search}%")
                        ->orWhereHas('user', fn ($user) => $user->where('username', 'like', "%{$search}%"));
                });
            })
            ->orderBy('nama_lengkap')
            ->paginate(10)
            ->withQueryString();

        return view('pages.admin.students.index', [
            'students' => $students,
            'search' => $search,
        ]);
    }

    public function show(Request $request, Mahasiswa $student): View
    {
        $this->authorize('view', $student);

        return view('pages.admin.students.show', [
            'student' => $student->load('user'),
        ]);
    }

    public function update(UpdateStudentNameRequest $request, Mahasiswa $student): RedirectResponse
    {
        $this->authorize('update', $student);

        $student->update([
            'nama_lengkap' => $request->validated('nama_lengkap'),
        ]);

        return redirect()
            ->route('admin.students.show', $student)
            ->with('status', 'Nama mahasiswa diperbarui.');
    }

    public function updateStatus(UpdateStudentStatusRequest $request, Mahasiswa $student): RedirectResponse
    {
        $this->authorize('update', $student);

        if ($student->is_archived && $request->boolean('is_active')) {
            return redirect()
                ->route('admin.students.show', $student)
                ->withErrors(['is_active' => 'Akun diarsipkan. Gunakan aksi Pulihkan untuk mengaktifkan kembali.']);
        }

        $student->user->update([
            'is_active' => $request->boolean('is_active'),
        ]);

        return redirect()
            ->route('admin.students.show', $student)
            ->with('status', 'Status akun mahasiswa diperbarui.');
    }

    public function temporaryPassword(Request $request, Mahasiswa $student, TemporaryPasswordService $temporaryPasswords): RedirectResponse
    {
        $this->authorize('update', $student);

        $temporaryPassword = $temporaryPasswords->issueFor($student->user);

        return redirect()
            ->route('admin.students.show', $student)
            ->with('status', 'Temporary password baru diterbitkan.')
            ->with('temporary_password', [
                'username' => $student->user->username,
                'password' => $temporaryPassword,
            ]);
    }

    public function archive(Request $request, Mahasiswa $student, AccountArchiveService $service): RedirectResponse
    {
        $this->authorize('archive', $student);

        $service->archiveStudent($student);

        return redirect()
            ->route('admin.students.show', $student)
            ->with('status', 'Akun mahasiswa diarsipkan. Riwayat tetap tersimpan.');
    }

    public function restore(Request $request, Mahasiswa $student, AccountArchiveService $service): RedirectResponse
    {
        $this->authorize('restore', $student);

        $service->restoreStudent($student);

        return redirect()
            ->route('admin.students.show', $student)
            ->with('status', 'Akun mahasiswa dipulihkan dan dapat login kembali.');
    }
}
