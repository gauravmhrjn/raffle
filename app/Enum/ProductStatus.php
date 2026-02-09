<?php

declare(strict_types=1);

namespace App\Enum;

enum ProductStatus: int
{
    case INACTIVE = 0;

    case ACTIVE = 1;

    case RAFFLED = 2;

    public static function toString(int $statusId): string
    {
        return match ($statusId) {
            self::INACTIVE->value => 'inactive',
            self::ACTIVE->value => 'active',
            self::RAFFLED->value => 'raffled',
            default => 'undefined',
        };
    }
}
