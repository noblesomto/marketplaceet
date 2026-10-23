<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class StoreCategoryRequest extends FormRequest
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
            'category'         => 'required',
            'seo_group'        => 'nullable|in:product,property,service',
            'meta_title'       => 'nullable|max:255',
            'meta_description' => 'nullable|max:500',
            'keywords'         => 'nullable|max:500',
        ];
    }
}
