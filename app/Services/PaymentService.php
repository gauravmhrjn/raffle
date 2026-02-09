<?php

declare(strict_types=1);

namespace App\Services;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

final readonly class PaymentService implements PaymentServiceInterface
{
    public function processMocked(): string
    {
        Log::info('PaymentService executed', [
            'mocked' => true,
        ]);

        // mocked payment transaction code
        return Str::uuid()->toString();
    }
}
