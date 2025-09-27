<?php

namespace App\Http\Requests\Api;

use App\Enums\OrderStatus;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateOrderRequest extends FormRequest
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
            'status' => [
                'sometimes',
                'required',
                Rule::in(OrderStatus::values()),
            ],
            'items' => [
                'sometimes',
                'array',
                'min:1',
                'max:100',
            ],
            'items.*.product_name' => [
                'required_with:items',
                'string',
                'min:1',
                'max:255',
                'regex:/^[a-zA-Z0-9\s\-\_\.\,\(\)]+$/',
            ],
            'items.*.quantity' => [
                'required_with:items',
                'integer',
                'min:1',
                'max:1000',
            ],
            'items.*.unit_price' => [
                'required_with:items',
                'numeric',
                'min:0.01',
                'max:999999.99',
                'regex:/^\d+(\.\d{1,2})?$/',
            ],
            'meta' => [
                'sometimes',
                'array',
                'max:50',
            ],
            'meta.*' => [
                'nullable',
                'string',
                'max:1000',
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
            'status.in' => 'Status must be one of: pending, confirmed, cancelled.',
            'items.min' => 'At least one item is required when updating items.',
            'items.max' => 'Orders cannot have more than 100 items.',
            'items.*.product_name.min' => 'Product name cannot be empty.',
            'items.*.product_name.regex' => 'Product name contains invalid characters.',
            'items.*.quantity.max' => 'Quantity cannot exceed 1000 per item.',
            'items.*.unit_price.max' => 'Unit price cannot exceed $999,999.99.',
            'items.*.unit_price.regex' => 'Unit price must be a valid amount (e.g., 10.50).',
            'meta.max' => 'Too many meta fields provided (maximum 50).',
            'meta.*.max' => 'Meta field value is too long (maximum 1000 characters).',
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
            'items.*.product_name' => 'product name',
            'items.*.quantity' => 'quantity',
            'items.*.unit_price' => 'unit price',
        ];
    }
}
