<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Product;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

use function Symfony\Component\Clock\now;

final class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        if (app()->isProduction()) {
            return;
        }

        Product::factory()
            ->active()
            ->count(2)
            ->create([
                'qty' => 2,
                'raffle_date' => Carbon::now()->subMinutes(5)
            ]);
    }
}
