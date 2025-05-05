<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ContactFormRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }
    public function rules(): array
    {
        return [
            'first_name' => 'required|string|max:25',
            'last_name' => 'required|string|max:25',
            'phone' => [
                'required',
                'string',
                'regex:/^\+?[0-9]{1,4}?[0-9]{7,14}$/',
                'max:15'
            ],
            'email' => [
                'required',
                'email:rfc,dns',
                'max:50'
            ],
            'message' => 'required|string',
            'is_read' => [
                'sometimes',
                'boolean',
                Rule::in([0, 1])
            ],
        ];
    }
}
