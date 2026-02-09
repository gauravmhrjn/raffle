<?php

declare(strict_types=1);

namespace App\Exceptions;

use Exception;

final class AddressNotFoundException extends Exception
{
    public const string ERROR_MESSAGE = 'Address not found.';
}
