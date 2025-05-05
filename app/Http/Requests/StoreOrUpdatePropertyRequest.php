<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreOrUpdatePropertyRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        return true; // You can add authorization logic here if necessary
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        return [
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'phone_number' => 'required|string|max:15',
            'email' => 'required|email|max:255',
            'property_type' => 'required|array',  // Validate that property_type is an array
            'property_type.*' => 'string', // Ensure each item in the array is a string
            'property_address' => 'required|string|max:500',
            'property_description' => 'nullable|string|max:1000',
            'income_goals' => 'nullable|string|max:500',
            'photos' => 'nullable|array',
            'photos.*' => 'image|mimes:jpeg,png,jpg,gif,svg',
            'is_managed_by_rejuvenest' => 'nullable|boolean',
        ];
    }

    /**
     * Get the validation error messages.
     *
     * @return array
     */
    public function messages(): array
    {
        return [
            'first_name.required' => 'First name is required.',
            'last_name.required' => 'Last name is required.',
            'phone_number.required' => 'Phone number is required.',
            'email.required' => 'Email is required.',
            'property_type.required' => 'Property type is required and should be an array.',
            'property_type.*.string' => 'Each property type should be a string.',
            'property_address.required' => 'Property address is required.',
            'photos.*.image' => 'Each photo must be an image.',
            'is_managed_by_rejuvenest.boolean' => 'The is_managed_by_rejuvenest field should be a boolean.',
        ];
    }
}
