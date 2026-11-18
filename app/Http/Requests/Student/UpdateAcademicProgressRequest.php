<?php

namespace App\Http\Requests\Student;

use Illuminate\Foundation\Http\FormRequest;

class UpdateAcademicProgressRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasRole('mahasiswa') ?? false;
    }

    public function rules(): array
    {
        return [
            'id_status' => ['required', 'integer', 'exists:status_tugas,id_status'],
            'row_version' => ['required', 'integer', 'min:1'],
        ];
    }
}
