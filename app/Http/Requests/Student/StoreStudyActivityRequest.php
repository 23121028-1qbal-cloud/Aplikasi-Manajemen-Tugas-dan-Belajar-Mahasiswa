<?php

namespace App\Http\Requests\Student;

use Illuminate\Foundation\Http\FormRequest;

class StoreStudyActivityRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasRole('mahasiswa') ?? false;
    }

    public function rules(): array
    {
        return [
            'judul_aktivitas' => ['required', 'string', 'max:150'],
            'materi_belajar' => ['nullable', 'string', 'max:2000'],
            'waktu_mulai' => ['required', 'date'],
            'durasi_menit' => ['required', 'integer', 'min:1', 'max:65535'],
            'id_status_belajar' => ['required', 'integer', 'exists:status_belajar,id_status_belajar'],
            'id_mata_kuliah' => ['nullable', 'integer', 'exists:mata_kuliah,id_mata_kuliah'],
        ];
    }

    public function messages(): array
    {
        return [
            'durasi_menit.min' => 'Durasi harus lebih besar dari nol menit.',
        ];
    }
}
