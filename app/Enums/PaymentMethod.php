<?php

namespace App\Enums;

enum PaymentMethod: string
{
    use EnumValues;
    case CREDIT_CARD = 'credit_card';
    case PAYPAL = 'paypal';
    case STRIPE = 'stripe';


    /**
     * Get method labels for display.
     *
     * @return array<string, string>
     */
    public static function labels(): array
    {
        return [
            self::CREDIT_CARD->value => 'Credit Card',
            self::PAYPAL->value => 'PayPal',
            self::STRIPE->value => 'Stripe',
        ];
    }
}
