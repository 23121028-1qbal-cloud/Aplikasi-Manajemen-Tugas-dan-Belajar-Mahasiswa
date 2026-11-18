<?php

namespace App\Http\Requests\Lecturer;

use Illuminate\Foundation\Http\FormRequest;

class CorrectAcademicProgressRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasRole('guru_dosen') ?? false;
    }

    public function rules(): array
    {
        return [
            'id_status' => ['required', 'integer', 'exists:status_tugas,id_status'],
            'row_version' => ['required', 'integer', 'min:1'],
        ];
    }
}
