<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateTaskerRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name'            => 'sometimes|required|string|max:255',
            'email'           => [
                'sometimes',
                'required',
                'email',
                Rule::unique('users', 'email')->ignore($this->route('id')),
            ],
            'phone'           => 'sometimes|nullable|string|max:50',
            'gender'          => 'sometimes|nullable|string|max:50',
            'nationality'     => 'sometimes|nullable|string|max:191',
            'education'       => 'sometimes|nullable|string|max:191',
            'profession'      => 'sometimes|nullable|string|max:191',
            'work_experience' => 'sometimes|nullable|string',
        ];
    }
}
