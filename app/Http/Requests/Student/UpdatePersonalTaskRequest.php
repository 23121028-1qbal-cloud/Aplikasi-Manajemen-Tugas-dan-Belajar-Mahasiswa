<?php

namespace App\Http\Requests\Student;

class UpdatePersonalTaskRequest extends StorePersonalTaskRequest
{
    public function rules(): array
    {
        return [
            ...parent::rules(),
            'row_version' => ['required', 'integer', 'min:1'],
        ];
    }
}
