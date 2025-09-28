<?php

namespace App\Enums;

enum PaymentStatus: string
{
    case PENDING = 'pending';
    case SUCCESSFUL = 'successful';
    case FAILED = 'failed';

     

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
