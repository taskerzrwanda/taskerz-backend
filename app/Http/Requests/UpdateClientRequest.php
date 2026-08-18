<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateClientRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Route is already gated by `admin` middleware.
        return true;
    }

    public function rules(): array
    {
        return [
            'name'        => ['sometimes', 'string', 'max:255'],
            'phone'       => ['sometimes', 'nullable', 'string', 'max:50'],
            'city'        => ['sometimes', 'nullable', 'string', 'max:120'],
            'district'    => ['sometimes', 'nullable', 'string', 'max:120'],
            'nationality' => ['sometimes', 'nullable', 'string', 'max:120'],
            'gender'      => ['sometimes', 'nullable', 'in:male,female,other'],
        ];
    }
}
