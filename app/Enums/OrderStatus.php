<?php

namespace App\Enums;

enum OrderStatus: string
{
    use EnumValues;
    case PENDING = 'pending';
    case CONFIRMED = 'confirmed';
    case CANCELLED = 'cancelled';



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
