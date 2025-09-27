<?php

namespace App\Enums;

enum OrderStatus: string
{
    case PENDING = 'pending';
    case CONFIRMED = 'confirmed';
    case CANCELLED = 'cancelled';

    /**
     * Get all status values.
     *
     * @return array<string>
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    /**
     * Get status labels for display.
     *
     * @return array<string, string>
     */
    public static function labels(): array
    {
        return [
            self::PENDING->value => 'Pending',
            self::CONFIRMED->value => 'Confirmed',
            self::CANCELLED->value => 'Cancelled',
        ];
    }
}
