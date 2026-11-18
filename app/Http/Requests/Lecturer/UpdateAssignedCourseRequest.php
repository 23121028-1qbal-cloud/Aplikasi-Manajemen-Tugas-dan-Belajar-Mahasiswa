<?php

namespace App\Http\Requests\Lecturer;

use Illuminate\Foundation\Http\FormRequest;

class UpdateAssignedCourseRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasRole('guru_dosen') ?? false;
    }

    /**
     * Keputusan 2026-11-18: dosen hanya boleh mengubah nama mata kuliah yang
     * diampu. Kode mata kuliah dan dosen pengampu tetap kewenangan Admin.
     */
    public function rules(): array
    {
        return [
            'nama_mata_kuliah' => ['required', 'string', 'max:100'],
            'row_version' => ['required', 'integer', 'min:1'],
        ];
    }
}
