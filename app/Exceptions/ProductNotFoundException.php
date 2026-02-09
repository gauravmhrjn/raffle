<?php

declare(strict_types=1);

namespace App\Exceptions;

use Exception;

final class ProductNotFoundException extends Exception
{
    public const string ERROR_MESSAGE = 'Product not found.';
}
