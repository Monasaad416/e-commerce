<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Setting;

class SettingsSeeder extends Seeder
{
    public function run(): void
    {
        $settings = [
            [
                'key' => 'light_logo',
                'value' => json_encode('/images/logo-light.png'),
            ],
            [
                'key' => 'dark_logo',
                'value' => json_encode('/images/logo-dark.png'),
            ],
            [
                'key' => 'favicon',
                'value' => json_encode('/images/favicon.png'),
            ],
            [
                'key' => 'tax_value',
                'value' => json_encode(5),
            ],
            [
                'key' => 'currency',
                'value' => json_encode('EGP'),
            ],
            [
                'key' => 'site_name',
                'value' => json_encode('Velora'),
            ],
            [
                'key' => 'contact_email',
                'value' => json_encode('support@velora.com'),
            ],
            [
                'key' => 'contact_phone',
                'value' => json_encode('+2123456789'),
            ],
            [
                'key' => 'shipping_cost',
                'value' => json_encode(20),
            ],
            [
                'key' => 'default_language',
                'value' => json_encode('ar'),
            ],
            [
                'key' => 'default_locale',
                'value' => json_encode('ar'),
            ],
            [
                'key' => 'maintenance_mode',
                'value' => json_encode(false),
            ],
        ];

        foreach ($settings as $setting) {
            Setting::updateOrCreate(['key' => $setting['key']], $setting);
        }
    }
}
