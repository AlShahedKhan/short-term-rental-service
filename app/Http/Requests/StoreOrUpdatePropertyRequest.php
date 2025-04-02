<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreOrUpdatePropertyRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function validationData(): array
    {
        $data = $this->all();

        if ($this->has('data')) {
            $json = json_decode($this->input('data'), true);
            if (json_last_error() === JSON_ERROR_NONE) {
                $data = array_merge($data, $json);
            }
        }

        return $data;
    }

    public function rules(): array
    {
        return [
            'zip_code' => 'required|string|max:10',
            'bedroom_count' => 'required|integer|min:0',
            'bathroom_count' => 'required|integer|min:0',
            'highlights' => 'nullable|string',
            'key_amenities' => 'nullable|array',
            'photos.*' => 'nullable|image|max:2048',
        ];
    }
}
