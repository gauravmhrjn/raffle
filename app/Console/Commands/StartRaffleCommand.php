<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Actions\StartRaffleAction;
use Illuminate\Console\Command;

final class StartRaffleCommand extends Command
{
    protected $signature = 'app:start-raffle';

    protected $description = 'Command to start the raffle';

    public function handle(StartRaffleAction $startRaffleAction): void
    {
        $startRaffleAction->handle();
    }
}
