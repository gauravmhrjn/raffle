<?php

namespace App\Enum;

enum PaymentStatus: int
{
    case PENDING = 0;

    case SUCCESS = 1;

    case DECLINED = 2;

    public static function toString(int $statusId): string
    {
        return match ($statusId) {
            self::PENDING->value => 'pending',
            self::SUCCESS->value => 'success',
            self::DECLINED->value => 'declined',
            default => 'undefined',
        };
    }
}
