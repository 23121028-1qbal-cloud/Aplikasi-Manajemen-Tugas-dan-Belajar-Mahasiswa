<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateCourseRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasRole('admin') ?? false;
    }

    public function rules(): array
    {
        $course = $this->route('course');

        return [
            'kode_mata_kuliah' => [
                'required',
                'string',
                'max:20',
                Rule::unique('mata_kuliah', 'kode_mata_kuliah')->ignore($course?->id_mata_kuliah, 'id_mata_kuliah'),
            ],
            'nama_mata_kuliah' => ['required', 'string', 'max:100'],
            'guru_dosen_id' => [
                'required',
                'integer',
                Rule::exists('guru_dosen', 'id_guru_dosen')->where('is_archived', 0),
            ],
            'row_version' => ['required', 'integer', 'min:1'],
        ];
    }
}
