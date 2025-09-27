<?php

namespace App\Enums;

enum PaymentMethod: string
{
    case CREDIT_CARD = 'credit_card';
    case PAYPAL = 'paypal';
    case STRIPE = 'stripe';

    /**
     * Get all method values.
     *
     * @return array<string>
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

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
