<?php

namespace App\Http\Requests\User;

use Illuminate\Foundation\Http\FormRequest;

class SubmitVerificationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'document_number' => 'required|string|max:255',
            'document_type'   => 'nullable|string|max:255',
            'document_file'   => 'required|file|mimes:jpeg,png,jpg,gif,webp,pdf|max:12048',
            'proof_address'   => 'nullable|file|mimes:jpeg,png,jpg,gif,webp,pdf|max:12048',
        ];
    }
}
