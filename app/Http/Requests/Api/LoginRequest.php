<?php

namespace App\Http\Requests\Api;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;

class LoginRequest extends FormRequest
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
            'email' => [
                'required',
                'string',
                'email:rfc',
                'max:255',
                'exists:users,email',
            ],
            'password' => [
                'required',
                'string',
                'min:1',
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
            'email.exists' => 'These credentials do not match our records.',
            'password.min' => 'Please provide your password.',
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
            'email' => 'email address',
            'password_confirmation' => 'password confirmation',
        ];
    }
}
