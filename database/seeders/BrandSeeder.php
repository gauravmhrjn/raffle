<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Brand;
use Illuminate\Database\Seeder;

final class BrandSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        if (app()->isProduction() || Brand::exists()) {
            return;
        }

        $brands = ['Adidas', 'Nike', 'New Balance'];

        foreach ($brands as $brand) {
            Brand::factory()->create([
                'name' => $brand,
            ]);
        }
    }
}
