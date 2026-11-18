<?php

namespace App\Http\Requests\Student;

use Illuminate\Foundation\Http\FormRequest;

class StorePersonalTaskRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasRole('mahasiswa') ?? false;
    }

    public function rules(): array
    {
        return [
            'judul_tugas' => ['required', 'string', 'max:150'],
            'deskripsi' => ['nullable', 'string', 'max:2000'],
            'deadline' => ['required', 'date'],
            'id_prioritas' => ['required', 'integer', 'exists:prioritas,id_prioritas'],
            'id_status' => ['required', 'integer', 'exists:status_tugas,id_status'],
            'id_mata_kuliah' => ['nullable', 'integer', 'exists:mata_kuliah,id_mata_kuliah'],
        ];
    }
}
