<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;

class RegisterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Field mengikuti PRD §7.1: NPM, nama lengkap, username, password.
     * Role tidak pernah diterima dari input dan selalu dipaksa mahasiswa.
     */
    public function rules(): array
    {
        return [
            'npm' => ['required', 'string', 'max:20', 'unique:mahasiswa,npm'],
            'nama_lengkap' => ['required', 'string', 'max:100'],
            'username' => ['required', 'string', 'max:50', 'unique:users,username'],
            'password' => ['required', 'string', 'confirmed', Password::min(8)],
        ];
    }
}
