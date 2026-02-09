<?php

declare(strict_types=1);

namespace App\Enum;

enum OrderStatus: int
{
    case PENDING = 0;

    case COMPLETED = 1;

    public static function toString(int $statusId): string
    {
        return match ($statusId) {
            self::PENDING->value => 'pending',
            self::COMPLETED->value => 'completed',
            default => 'undefined',
        };
    }
}
