<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed base application data (safe to re-run; does not touch products).
     *
     * Products: run separately when needed:
     *   php artisan db:seed --class=ProductsSeeder
     */
    public function run(): void
    {
        $this->call([
            LanguageSeeder::class,
            CategorySeeder::class,
            AttributeSeeder::class,
            SettingsSeeder::class,
            TaxSeeder::class,
            UserSeeder::class,
            HomePageContentSeeder::class,
            ShopPageContentSeeder::class,
            ProductsSeeder::class,
        ]);
    }
}
