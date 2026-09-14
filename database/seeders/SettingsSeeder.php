<?php

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Seeder;

class SettingsSeeder extends Seeder
{
    public function run(): void
    {
        $settings = [
            ['key' => 'light_logo', 'value' => '/assets/imgs/logo/favicon.jpeg'],
            ['key' => 'dark_logo', 'value' => '/assets/imgs/logo/favicon.jpeg'],
            ['key' => 'favicon', 'value' => '/assets/imgs/logo/favicon.jpeg'],
            ['key' => 'tax_value', 'value' => 5],
            ['key' => 'currency', 'value' => 'EGP'],
            ['key' => 'site_name', 'value' => 'Velora'],
            ['key' => 'contact_email', 'value' => 'support@velora.com'],
            ['key' => 'contact_phone', 'value' => '+2123456789'],
            ['key' => 'shipping_cost', 'value' => 20],
            ['key' => 'default_language', 'value' => 'ar'],
            ['key' => 'default_locale', 'value' => 'ar'],
            ['key' => 'maintenance_mode', 'value' => false],
            ['key' => 'tax_rate', 'value' => 0.15],
        ];

        foreach ($settings as $setting) {
            Setting::updateOrCreate(
                ['key' => $setting['key']],
                ['value' => $setting['value']],
            );
        }
    }
}
