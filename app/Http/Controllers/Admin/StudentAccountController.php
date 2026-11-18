<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UpdateStudentNameRequest;
use App\Http\Requests\Admin\UpdateStudentStatusRequest;
use App\Models\Mahasiswa;
use App\Services\TemporaryPasswordService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class StudentAccountController extends Controller
{
    /**
     * Field profil yang boleh diubah Admin belum ditetapkan (PRD-TBD-023),
     * sehingga halaman ini hanya menampilkan data dan mengelola status akun.
     */
    public function index(Request $request): View
    {
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

    public function show(Mahasiswa $student): View
    {
        return view('pages.admin.students.show', [
            'student' => $student->load('user'),
        ]);
    }

    public function update(UpdateStudentNameRequest $request, Mahasiswa $student): RedirectResponse
    {
        $student->update([
            'nama_lengkap' => $request->validated('nama_lengkap'),
        ]);

        return redirect()
            ->route('admin.students.show', $student)
            ->with('status', 'Nama mahasiswa diperbarui.');
    }

    public function updateStatus(UpdateStudentStatusRequest $request, Mahasiswa $student): RedirectResponse
    {
        $student->user->update([
            'is_active' => $request->boolean('is_active'),
        ]);

        return redirect()
            ->route('admin.students.show', $student)
            ->with('status', 'Status akun mahasiswa diperbarui.');
    }

    public function temporaryPassword(Mahasiswa $student, TemporaryPasswordService $temporaryPasswords): RedirectResponse
    {
        $temporaryPassword = $temporaryPasswords->issueFor($student->user);

        return redirect()
            ->route('admin.students.show', $student)
            ->with('status', 'Temporary password baru diterbitkan.')
            ->with('temporary_password', [
                'username' => $student->user->username,
                'password' => $temporaryPassword,
            ]);
    }
}
