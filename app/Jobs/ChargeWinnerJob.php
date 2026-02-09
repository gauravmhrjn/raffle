<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Actions\ChargeWinnerAction;
use Illuminate\Bus\Batchable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;
use Throwable;

final class ChargeWinnerJob implements ShouldQueue
{
    use Batchable, Queueable;

    public function __construct(
        public int $raffleEntryId
    ) {}

    public function handle(ChargeWinnerAction $chargeWinnerAction): void
    {
        $chargeWinnerAction->handle($this->raffleEntryId);

        Log::info('ChargeWinnerJob executed', [
            'raffleEntryId' => $this->raffleEntryId,
        ]);
    }

    public function failed(?Throwable $exception): void
    {
        Log::error('ChargeWinnerJob failed', [
            'raffleEntryId' => $this->raffleEntryId,
            'jobFailedErrorMessage' => $exception->getMessage(),
        ]);
    }
}
