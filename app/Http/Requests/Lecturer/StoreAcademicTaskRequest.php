<?php

namespace App\Http\Requests\Lecturer;

use Illuminate\Foundation\Http\FormRequest;

class StoreAcademicTaskRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasRole('guru_dosen') ?? false;
    }

    public function rules(): array
    {
        return [
            'id_mata_kuliah' => ['required', 'integer', 'exists:mata_kuliah,id_mata_kuliah'],
            'id_prioritas' => ['nullable', 'integer', 'exists:prioritas,id_prioritas'],
            'judul_tugas' => ['required', 'string', 'max:150'],
            'deskripsi' => ['nullable', 'string', 'max:2000'],
            'deadline' => ['required', 'date'],
        ];
    }
}
