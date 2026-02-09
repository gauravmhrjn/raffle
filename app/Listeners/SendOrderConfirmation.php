<?php

declare(strict_types=1);

namespace App\Listeners;

use App\Events\OrderCreated;
use App\Mail\OrderConfirmation;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Throwable;

final class SendOrderConfirmation implements ShouldQueue
{
    use InteractsWithQueue;

    public function handle(OrderCreated $event): void
    {
        $event->order->load('user');

        Mail::to($event->order->user->email)
            ->send(
                new OrderConfirmation($event->order)
            );

        Log::info('SendOrderConfirmation executed', [
            'orderId' => $event->order->id,
        ]);
    }

    public function failed(OrderCreated $event, Throwable $exception): void
    {
        Log::error('SendOrderConfirmation failed', [
            'orderId' => $event->order->id,
            'jobFailedErrorMessage' => $exception->getMessage(),
        ]);
    }
}
