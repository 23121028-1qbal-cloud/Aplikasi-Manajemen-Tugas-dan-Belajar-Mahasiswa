<?php

namespace App\Http\Requests\Admin;

use App\Enums\Semester;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreEnrollmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasRole('admin') ?? false;
    }

    public function rules(): array
    {
        return [
            'id_mahasiswa' => ['required', 'integer', 'exists:mahasiswa,id_mahasiswa'],
            'id_mata_kuliah' => ['required', 'integer', 'exists:mata_kuliah,id_mata_kuliah'],
            'tahun_akademik' => ['required', 'string', 'regex:/^\d{4}\/\d{4}$/'],
            'semester' => ['required', Rule::enum(Semester::class)],
        ];
    }

    public function messages(): array
    {
        return [
            'tahun_akademik.regex' => 'Tahun akademik harus berformat TTTT/TTTT, contoh 2026/2027.',
        ];
    }
}
