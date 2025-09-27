<?php

namespace App\Http\Requests\Api;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;

class RegisterRequest extends FormRequest
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
            'name' => [
                'required',
                'string',
                'min:2',
                'max:255',
            ],
            'email' => [
                'required',
                'string',
                'email',
                'max:255',
                'unique:users,email',
            ],
            'password' => [
                'required',
                'string',
                'confirmed',
                Password::min(8)
                    ->letters()
                    ->mixedCase()
                    ->numbers()
                    ->symbols(),
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
            'required' => 'The :attribute field is required.',
            'string' => 'The :attribute must be a string.',
            'email' => 'The :attribute must be a valid email address.',
            'unique' => 'The :attribute has already been taken.',
            'confirmed' => 'The :attribute confirmation does not match.',
            'min' => 'The :attribute must be at least :min characters.',
            'max' => 'The :attribute may not be greater than :max characters.',
            'integer' => 'The :attribute must be an integer.',
            'numeric' => 'The :attribute must be a number.',
            'array' => 'The :attribute must be an array.',
            'exists' => 'The selected :attribute is invalid.',
            'in' => 'The selected :attribute is invalid.',
            'date' => 'The :attribute is not a valid date.',
            'after' => 'The :attribute must be a date after :date.',
            'before' => 'The :attribute must be a date before :date.',
            'name.min' => 'The name must be at least 2 characters long.',
            'name.regex' => 'The name may only contain letters, spaces, apostrophes, hyphens, and dots.',
            'email.email' => 'Please provide a valid email address.',
            'email.unique' => 'This email address is already registered.',
            'password.confirmed' => 'Password confirmation does not match.',
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
            'name' => 'full name',
            'email' => 'email address',
            'password' => 'password',
            'password_confirmation' => 'password confirmation',
            'unit_price' => 'unit price',
            'product_name' => 'product name',
            'payment_data' => 'payment data',
            'order_id' => 'order ID',
        ];
    }
}
