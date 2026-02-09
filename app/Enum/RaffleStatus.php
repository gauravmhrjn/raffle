<?php

declare(strict_types=1);

namespace App\Enum;

enum RaffleStatus: int
{
    case PENDING = 0;

    case WINNER = 1;

    case LOSER = 2;

    public static function toString(int $statusId): string
    {
        return match ($statusId) {
            self::PENDING->value => 'pending',
            self::WINNER->value => 'winner',
            self::LOSER->value => 'loser',
            default => 'undefined',
        };
    }
}
