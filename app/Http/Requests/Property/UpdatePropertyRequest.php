<?php

namespace App\Http\Requests\Property;

use Illuminate\Foundation\Http\FormRequest;

class UpdatePropertyRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'title'            => 'sometimes|required|string|max:255',
            'description'      => 'sometimes|required|string',
            'price_per_night'  => 'sometimes|required|numeric|min:0',
            'location'         => 'sometimes|required|string|max:255',
            'amenities'        => 'nullable|array',
            'amenities.*'      => 'string',
            'images'           => 'nullable|array',
            'images.*'         => 'url',
        ];
    }
}
