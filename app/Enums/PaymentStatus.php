<?php

namespace App\Enums;

enum PaymentStatus: string
{
    case PENDING = 'pending';
    case SUCCESSFUL = 'successful';
    case FAILED = 'failed';

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
            self::SUCCESSFUL->value => 'Successful',
            self::FAILED->value => 'Failed',
        ];
    }
}
