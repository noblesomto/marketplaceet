<?php

namespace App\Http\Requests\User;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use App\Rules\EthiopianPhoneNumber;

class UpdateProfileRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        if ($this->isMethod('GET')) {
            return [];
        }

        $userId = $this->session()->get('user_id');

        return [
            'name'          => 'required|string|max:100',
            'phone'         => [
                'required',
                new EthiopianPhoneNumber(),
                Rule::unique('users', 'phone')->ignore($userId, 'user_id'),
            ],
            'profile_image' => 'sometimes|image|mimes:jpeg,png,jpg,gif,webp|max:12048',
        ];
    }
}
