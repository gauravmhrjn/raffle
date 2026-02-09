<?php

declare(strict_types=1);

namespace App\Listeners;

use App\Events\RaffleEntryCreated;
use App\Mail\RaffleEntryConfirmation;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Throwable;

final class SendRaffleEntryConfirmation implements ShouldQueue
{
    use InteractsWithQueue;

    public function handle(RaffleEntryCreated $event): void
    {
        $event->raffleEntry->load('user');

        Mail::to($event->raffleEntry->user->email)
            ->send(
                new RaffleEntryConfirmation($event->raffleEntry)
            );

        Log::info('SendRaffleEntryConfirmation executed', [
            'raffleEntryId' => $event->raffleEntry->id,
        ]);
    }

    public function failed(RaffleEntryCreated $event, Throwable $exception): void
    {
        Log::error('SendRaffleEntryConfirmation failed', [
            'raffleEntryId' => $event->raffleEntry->id,
            'jobFailedErrorMessage' => $exception->getMessage(),
        ]);
    }
}
