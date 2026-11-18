<?php

namespace App\Http\Requests\Student;

class UpdateStudyActivityRequest extends StoreStudyActivityRequest
{
    public function rules(): array
    {
        return [
            ...parent::rules(),
            'row_version' => ['required', 'integer', 'min:1'],
        ];
    }
}
