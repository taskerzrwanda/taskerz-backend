<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class InviteTaskerRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name'       => 'required|string|max:255',
            'email'      => 'required|email|unique:users,email',
            'phone'      => 'required|string|max:50',
            'profession' => 'required|string|max:191',
        ];
    }

    public function messages(): array
    {
        return [
            'email.unique' => 'A user with this email already exists.',
        ];
    }
}
