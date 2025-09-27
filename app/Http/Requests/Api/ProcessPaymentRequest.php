<?php

namespace App\Http\Requests\Api;

use App\Enums\PaymentMethod;
use App\Rules\ValidCreditCard;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ProcessPaymentRequest extends FormRequest
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
        $rules = [
            'order_id' => [
                'required',
                'integer',
                'exists:orders,id',
            ],
            'method' => [
                'required',
                'string',
                Rule::in(PaymentMethod::values()),
            ],
            'payment_data' => [
                'required',
                'array',
                'max:20',
            ],
        ];

        $method = request('method');

        if ($method === PaymentMethod::CREDIT_CARD->value) {
            $rules['payment_data.card_number'] = [
                'required',
                'string',
                new ValidCreditCard,
            ];
            $rules['payment_data.card_exp_month'] = [
                'required',
                'integer',
                'between:1,12',
            ];
            $rules['payment_data.card_exp_year'] = [
                'required',
                'integer',
                'min:'.date('Y'),
                'max:'.(date('Y') + 10),
            ];
            $rules['payment_data.card_cvc'] = [
                'required',
                'string',
                'regex:/^[0-9]{3,4}$/',
            ];
        } elseif ($method === PaymentMethod::PAYPAL->value) {
            $rules['payment_data.paypal_email'] = [
                'required',
                'email:rfc,dns',
                'max:255',
            ];
            $rules['payment_data.paypal_password'] = [
                'sometimes',
                'string',
                'min:8',
                'max:255',
            ];
        } elseif ($method === PaymentMethod::STRIPE->value) {
            $rules['payment_data.card_number'] = [
                'required',
                'string',
                new ValidCreditCard,
            ];
            $rules['payment_data.card_exp_month'] = [
                'required',
                'integer',
                'between:1,12',
            ];
            $rules['payment_data.card_exp_year'] = [
                'required',
                'integer',
                'min:'.date('Y'),
                'max:'.(date('Y') + 10),
            ];
            $rules['payment_data.card_cvc'] = [
                'required',
                'string',
                'regex:/^[0-9]{3,4}$/',
            ];
        }

        return $rules;
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'order_id.exists' => 'The specified order does not exist.',
            'method.in' => 'Invalid payment method. Supported methods: '.implode(', ', PaymentMethod::values()),
            'payment_data.max' => 'Too many payment data fields provided.',

            'payment_data.card_number.required' => 'Card number is required for card payments.',
            'payment_data.card_number.regex' => 'Card number must be 13-19 digits.',
            'payment_data.card_exp_month.required' => 'Expiry month is required for card payments.',
            'payment_data.card_exp_month.between' => 'Expiry month must be between 1 and 12.',
            'payment_data.card_exp_year.required' => 'Expiry year is required for card payments.',
            'payment_data.card_exp_year.min' => 'Card has expired.',
            'payment_data.card_exp_year.max' => 'Expiry year is too far in the future.',
            'payment_data.card_cvc.required' => 'CVC is required for card payments.',
            'payment_data.card_cvc.regex' => 'CVC must be 3-4 digits.',

            'payment_data.paypal_email.required' => 'PayPal email is required for PayPal payments.',
            'payment_data.paypal_email.email' => 'Please provide a valid PayPal email address.',
            'payment_data.paypal_password.min' => 'PayPal password must be at least 8 characters.',
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
            'order_id' => 'order',
            'payment_data.card_number' => 'card number',
            'payment_data.card_exp_month' => 'expiry month',
            'payment_data.card_exp_year' => 'expiry year',
            'payment_data.card_cvc' => 'CVC',
            'payment_data.paypal_email' => 'PayPal email',
            'payment_data.paypal_password' => 'PayPal password',
        ];
    }
}
