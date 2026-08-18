<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateMyProfileRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = auth('api')->user();
        return $user !== null && $user->isCustomer();
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
