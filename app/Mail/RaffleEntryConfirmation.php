<?php

declare(strict_types=1);

namespace App\Mail;

use App\Models\RaffleEntry;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

final class RaffleEntryConfirmation extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(
        public RaffleEntry $raffleEntry
    ) {
        $raffleEntry->load('product');
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            from: new Address(config('mail.from.address'), config('mail.from.name')),
            subject: 'Raffle Entry Confirmation',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'mail.raffle_entry_confirmation',
        );
    }
}
