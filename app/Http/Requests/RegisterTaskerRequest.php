<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;

class RegisterTaskerRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name'            => 'required|string|max:255',
            'email'           => 'required|email|unique:users,email',
            'password'        => ['required', 'confirmed', Password::min(8)->letters()],
            'phone'           => 'required|string|max:50',
            'profession'      => 'required|string|max:191',
            'nationality'     => 'nullable|string|max:191',
            'gender'          => 'nullable|string|max:50',
            'education'       => 'nullable|string|max:191',
            'work_experience' => 'nullable|string',
            'city'            => 'nullable|string|max:191',
            'district'        => 'nullable|string|max:191',
            'latitude'        => 'nullable|numeric|between:-90,90',
            'longitude'       => 'nullable|numeric|between:-180,180',
            'skills'          => 'nullable|array',
            'skills.*'        => 'string',
        ];
    }
}
