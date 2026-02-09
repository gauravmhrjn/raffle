<?php

declare(strict_types=1);

namespace App\Services;

interface PaymentServiceInterface
{
    public function processMocked(): string|bool;
}
