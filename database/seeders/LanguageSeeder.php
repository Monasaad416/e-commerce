<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class LanguageSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        \App\Models\Language::create([
            'name' => 'English',
            'code' => 'en',
            'is_default' => true,
            'direction' => 'ltr',
        ]);

        \App\Models\Language::create([
            'name' => 'Arabic',
            'code' => 'ar',
            'is_default' => false,
            'direction' => 'rtl',
        ]); 
    }
}
