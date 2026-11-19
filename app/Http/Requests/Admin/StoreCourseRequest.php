<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreCourseRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasRole('admin') ?? false;
    }

    public function rules(): array
    {
        return [
            'kode_mata_kuliah' => ['required', 'string', 'max:20', 'unique:mata_kuliah,kode_mata_kuliah'],
            'nama_mata_kuliah' => ['required', 'string', 'max:100'],
            'guru_dosen_id' => [
                'required',
                'integer',
                Rule::exists('guru_dosen', 'id_guru_dosen')->where('is_archived', 0),
            ],
        ];
    }
}
