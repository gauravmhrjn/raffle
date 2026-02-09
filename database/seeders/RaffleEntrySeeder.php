<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\RaffleEntry;
use Illuminate\Database\Seeder;

final class RaffleEntrySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        if (app()->isProduction()) {
            return;
        }

        RaffleEntry::factory()
            ->pending()
            ->count(12)
            ->create();
    }
}
