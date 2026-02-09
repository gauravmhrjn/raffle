<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Actions\SelectWinnersAction;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;
use Throwable;

final class SelectWinnersJob implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public int $productId
    ) {}

    public function handle(SelectWinnersAction $selectWinnersAction): void
    {
        $selectWinnersAction->handle($this->productId);

        Log::info('SelectWinnersJob executed', [
            'productId' => $this->productId,
        ]);
    }

    public function failed(?Throwable $exception): void
    {
        Log::error('SelectWinnersJob failed', [
            'productId' => $this->productId,
            'jobFailedErrorMessage' => $exception->getMessage(),
        ]);
    }
}
