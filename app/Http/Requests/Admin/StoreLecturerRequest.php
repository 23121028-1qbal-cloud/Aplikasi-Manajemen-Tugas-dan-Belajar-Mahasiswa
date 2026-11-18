<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class StoreLecturerRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasRole('admin') ?? false;
    }

    public function rules(): array
    {
        return [
            'nama_lengkap' => ['required', 'string', 'max:100'],
            'nip_guru' => ['required', 'string', 'max:30', 'unique:guru_dosen,nip_guru'],
            'username' => ['required', 'string', 'max:50', 'unique:users,username'],
        ];
    }
}
