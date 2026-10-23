<?php

namespace App\Http\Requests\User;

use Illuminate\Foundation\Http\FormRequest;

class UpdatePaymentInfoRequest extends FormRequest
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

        return [
            'bank_name'          => 'required',
            'bank_code'          => 'required',
            'account_number'     => 'required',
            'account_name'       => 'required',
        ];
    }
}
