<?php

namespace App\Http\Requests\Api;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;

class ConfirmOrderRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return auth('api')->check();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'confirmation_notes' => [
                'sometimes',
                'nullable',
                'string',
                'max:1000',
            ],
            'estimated_delivery_date' => [
                'sometimes',
                'nullable',
                'date',
                'after:today',
                'before:'.now()->addMonths(6)->format('Y-m-d'),
            ],
            'tracking_number' => [
                'sometimes',
                'nullable',
                'string',
                'max:255',
                'regex:/^[A-Za-z0-9\-_]+$/',
            ],
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'confirmation_notes.max' => 'Confirmation notes cannot exceed 1000 characters.',
            'estimated_delivery_date.after' => 'Estimated delivery date must be in the future.',
            'estimated_delivery_date.before' => 'Estimated delivery date cannot be more than 6 months from now.',
            'tracking_number.max' => 'Tracking number cannot exceed 255 characters.',
            'tracking_number.regex' => 'Tracking number contains invalid characters.',
        ];
    }

    /**
     * Get custom attribute names for validator errors.
     *
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'confirmation_notes' => 'confirmation notes',
            'estimated_delivery_date' => 'estimated delivery date',
            'tracking_number' => 'tracking number',
        ];
    }
}
