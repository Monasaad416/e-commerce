<?php

namespace Database\Seeders;

use App\Models\PageContent;
use Illuminate\Database\Seeder;

class ShopPageContentSeeder extends Seeder
{
    public function run(): void
    {
        PageContent::updateOrCreate(
            ['page' => 'shop'],
            [
                'content' => [
                    'hero_eyebrow' => [
                        'en' => 'Shop Collection',
                        'ar' => 'تسوق المجموعة',
                    ],
                    'hero_title' => [
                        'en' => 'Handmade leather essentials',
                        'ar' => 'منتجات جلدية يدوية',
                    ],
                    'hero_subtitle' => [
                        'en' => 'Browse wallets, belts, bags, and card holders crafted with premium leather.',
                        'ar' => 'تصفح المحافظ والأحزمة والحقائب وحافظات البطاقات المصنوعة من جلد فاخر.',
                    ],
                    'hero_image' => 'https://images.unsplash.com/photo-1473186578172-c141e6798cf4?auto=format&fit=crop&w=1800&h=600&q=80',
                ],
            ],
        );
    }
}
