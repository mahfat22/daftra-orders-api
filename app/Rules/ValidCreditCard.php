<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class ValidCreditCard implements ValidationRule
{
    /**
     * Run the validation rule.
     *
     * @param  \Closure(string, ?string=): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $number = preg_replace('/[\s\-]/', '', $value);

        if (! ctype_digit($number) || strlen($number) < 13 || strlen($number) > 19) {
            $fail('The :attribute must be a valid credit card number.');

            return;
        }

        if (! $this->luhnCheck($number)) {
            $fail('The :attribute must be a valid credit card number.');

            return;
        }

        if (! $this->isValidCardType($number)) {
            $fail('The :attribute is not from a supported card provider.');
        }
    }

    /**
     * Validate using Luhn algorithm.
     */
    private function luhnCheck(string $cardNumber): bool
    {
        $sum = 0;
        $length = strlen($cardNumber);

        for ($i = 0; $i < $length; $i++) {
            $digit = (int) $cardNumber[$length - $i - 1];

            if ($i % 2 === 1) {
                $digit *= 2;
                if ($digit > 9) {
                    $digit -= 9;
                }
            }

            $sum += $digit;
        }

        return $sum % 10 === 0;
    }

    /**
     * Check if card type is supported.
     */
    private function isValidCardType(string $cardNumber): bool
    {
        $patterns = [
            'visa' => '/^4[0-9]{12}(?:[0-9]{3})?$/',
            'mastercard' => '/^5[1-5][0-9]{14}$|^2(?:2(?:2[1-9]|[3-9][0-9])|[3-6][0-9][0-9]|7(?:[01][0-9]|20))[0-9]{12}$/',
            'amex' => '/^3[47][0-9]{13}$/',
            'discover' => '/^6(?:011|5[0-9]{2})[0-9]{12}$/',
            'diners' => '/^3(?:0[0-5]|[68][0-9])[0-9]{11}$/',
            'jcb' => '/^(?:2131|1800|35\d{3})\d{11}$/',
        ];

        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $cardNumber)) {
                return true;
            }
        }

        return false;
    }
}
