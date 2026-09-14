<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            ['en' => 'Wallets', 'ar' => 'محافظ', 'img' => 'https://images.unsplash.com/photo-1627123424574-724758594e93', 'thumb' => 'https://images.unsplash.com/photo-1594223274512-ad4803739b7c'],
            ['en' => 'Belts', 'ar' => 'أحزمة', 'img' => 'https://images.unsplash.com/photo-1542272604-787c3835535d', 'thumb' => 'https://images.unsplash.com/photo-1584917865442-de89df76afd3'],
            ['en' => 'Bags', 'ar' => 'حقائب', 'img' => 'https://images.unsplash.com/photo-1548036328-c9fa89d128fa', 'thumb' => 'https://images.unsplash.com/photo-1560343776-97e7d202ff0e'],
            ['en' => 'Card Holders', 'ar' => 'حافظات بطاقات', 'img' => 'https://images.unsplash.com/photo-1600857062241-98e5dba7f214', 'thumb' => 'https://images.unsplash.com/photo-1622560480605-d83c853bc5c3'],
        ];

        foreach ($categories as $item) {
            $slug = str($item['en'])->slug()->value();
            Category::updateOrCreate(
                ['slug' => $slug],
                [
                    'name' => $item,
                    'description' => [
                        'en' => "Handmade leather {$item['en']} crafted by artisans.",
                        'ar' => "منتجات {$item['ar']} جلدية يدوية مصنوعة بحرفية عالية.",
                    ],
                    'meta_keywords' => [
                        'en' => "leather, handmade, {$item['en']}",
                        'ar' => "جلد, يدوي, {$item['ar']}",
                    ],
                    'meta_description' => [
                        'en' => "Premium handcrafted leather {$item['en']}.",
                        'ar' => "{$item['ar']} جلدية يدوية عالية الجودة.",
                    ],
                    'img' => "{$item['img']}?auto=format&fit=crop&w=1400&h=900&q=80",
                    'thumbnail' => "{$item['thumb']}?auto=format&fit=crop&w=600&h=600&q=80",
                    'is_active' => true,
                ]
            );
        }
    }
}

