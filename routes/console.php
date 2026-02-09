<?php

use App\Console\Commands\StartRaffleCommand;
use Illuminate\Support\Facades\Schedule;

Schedule::command(StartRaffleCommand::class)->hourly();
