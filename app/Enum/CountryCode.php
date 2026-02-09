<?php

declare(strict_types=1);

namespace App\Enum;

enum CountryCode: string
{
    case UK = 'uk';

    case US = 'us';

    case AU = 'au';

    case CA = 'ca';

    case FR = 'fr';

    public static function fullName(string $value): string
    {
        return match ($value) {
            self::UK->value => 'United Kingdom',
            self::US->value => 'United States',
            self::AU->value => 'Australia',
            self::CA->value => 'Canada',
            self::FR->value => 'France',
            default => 'undefined',
        };
    }
}
