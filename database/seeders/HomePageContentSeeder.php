<?php

namespace Database\Seeders;

use App\Models\PageContent;
use Illuminate\Database\Seeder;

class HomePageContentSeeder extends Seeder
{
    public function run(): void
    {
        $content = [
            'main_banner_eyebrow' => [
                'en' => 'Leather Handmade Collection',
                'ar' => 'مجموعة الجلد اليدوية',
            ],
            'main_banner_title' => [
                'en' => 'Crafted leather essentials built to last.',
                'ar' => 'منتجات جلدية مصنوعة يدويًا بجودة تدوم.',
            ],
            'main_banner_subtitle' => [
                'en' => 'Discover handmade wallets, bags, and accessories designed with premium materials, clean stitching, and timeless style.',
                'ar' => 'اكتشف المحافظ والحقائب والإكسسوارات الجلدية المصممة بخامات فاخرة وتشطيب يدوي أنيق.',
            ],
            'main_banner_image' => 'https://images.unsplash.com/photo-1473186578172-c141e6798cf4?auto=format&fit=crop&w=1800&h=1100&q=80',
            'main_banner_button_link' => 'shop',
            'main_banner_button_text' => [
                'en' => 'Shop Collection',
                'ar' => 'تسوق المجموعة',
            ],
            'main_banner_trust_points' => [
                ['text' => ['en' => 'Full-grain leather', 'ar' => 'جلد طبيعي فاخر']],
                ['text' => ['en' => 'Hand-stitched details', 'ar' => 'تفاصيل خياطة يدوية']],
                ['text' => ['en' => 'Built for daily use', 'ar' => 'مصمم للاستخدام اليومي']],
            ],
            'main_banner_stats' => [
                [
                    'value' => '100%',
                    'label' => ['en' => 'Handmade finish', 'ar' => 'تشطيب يدوي'],
                ],
                [
                    'value' => '48H',
                    'label' => ['en' => 'Fast processing', 'ar' => 'تجهيز سريع'],
                ],
                [
                    'value' => '4.9/5',
                    'label' => ['en' => 'Customer rating', 'ar' => 'تقييم العملاء'],
                ],
            ],

            'sec_banner_first_half_part1_title' => [
                'en' => 'Handmade Wallets',
                'ar' => 'محافظ مصنوعة يدويًا',
            ],
            'sec_banner_first_half_part1_subtitle' => [
                'en' => 'Slim profiles, premium leather, and lasting character for daily carry.',
                'ar' => 'تصاميم عملية بخامات جلدية فاخرة وتفاصيل تدوم للاستخدام اليومي.',
            ],
            'sec_banner_first_half_part1_image' => 'https://images.unsplash.com/photo-1627123424574-724758594e93?auto=format&fit=crop&w=900&h=900&q=80',

            'sec_banner_first_half_part2_title' => [
                'en' => 'Leather Card Holders',
                'ar' => 'حافظات بطاقات جلدية',
            ],
            'sec_banner_first_half_part2_subtitle' => [
                'en' => 'Minimal form with handcrafted edges and elegant finish.',
                'ar' => 'تصميم بسيط بحواف مشغولة يدويًا وتشطيب أنيق.',
            ],
            'sec_banner_first_half_part2_image' => 'https://images.unsplash.com/photo-1600857062241-98e5dba7f214?auto=format&fit=crop&w=900&h=900&q=80',

            'sec_banner_first_half_part3_title' => [
                'en' => 'Signature Keychains',
                'ar' => 'ميداليات جلدية مميزة',
            ],
            'sec_banner_first_half_part3_subtitle' => [
                'en' => 'Small leather details that complete the handmade experience.',
                'ar' => 'تفاصيل جلدية صغيرة تضيف لمسة يدوية مميزة.',
            ],
            'sec_banner_first_half_part3_image' => 'https://images.unsplash.com/photo-1522312346375-d1a52e2b99b3?auto=format&fit=crop&w=900&h=900&q=80',

            'sec_banner_sec_half_title' => [
                'en' => 'Weekend Bags & Everyday Carry',
                'ar' => 'حقائب نهاية الأسبوع والاستخدام اليومي',
            ],
            'sec_banner_sec_half_subtitle' => [
                'en' => 'Explore durable leather bags made for travel, work, and timeless style.',
                'ar' => 'اكتشف حقائب جلدية متينة مناسبة للسفر والعمل بإطلالة كلاسيكية.',
            ],
            'sec_banner_sec_half_image' => 'https://images.unsplash.com/photo-1548036328-c9fa89d128fa?auto=format&fit=crop&w=1200&h=1400&q=80',
            'sec_banner_sec_half_button_link' => 'shop',
            'sec_banner_sec_half_button_text' => [
                'en' => 'Explore Bags',
                'ar' => 'اكتشف الحقائب',
            ],

            'craft_process_eyebrow' => [
                'en' => 'Handcrafted Standard',
                'ar' => 'معيار الصناعة اليدوية',
            ],
            'craft_process_title' => [
                'en' => 'From premium leather to a finished piece made to last.',
                'ar' => 'من الجلد الفاخر إلى قطعة نهائية مصنوعة لتدوم.',
            ],
            'craft_process_subtitle' => [
                'en' => 'Every product goes through careful material selection, hand finishing, and a final quality check before it reaches you.',
                'ar' => 'كل منتج يمر باختيار دقيق للخامة، ولمسات يدوية، وفحص نهائي للجودة قبل أن يصل إليك.',
            ],
            'craft_process_badge_text' => [
                'en' => 'Luxury made by hand',
                'ar' => 'صناعة بلمسة فاخرة',
            ],
            'craft_process_items' => [
                [
                    'title' => [
                        'en' => 'Select Leather',
                        'ar' => 'اختيار الجلد',
                    ],
                    'text' => [
                        'en' => 'We choose rich, durable leather with character, texture, and long-term wear in mind.',
                        'ar' => 'نختار الجلود الغنية بالملمس والطابع مع التركيز على الجودة والتحمل على المدى الطويل.',
                    ],
                ],
                [
                    'title' => [
                        'en' => 'Hand Stitching',
                        'ar' => 'الخياطة اليدوية',
                    ],
                    'text' => [
                        'en' => 'Edges, seams, and details are refined by hand for a cleaner and more premium finish.',
                        'ar' => 'يتم تشطيب الحواف والتفاصيل يدويًا للحصول على مظهر أنيق وإحساس أكثر فخامة.',
                    ],
                ],
                [
                    'title' => [
                        'en' => 'Final Inspection',
                        'ar' => 'الفحص النهائي',
                    ],
                    'text' => [
                        'en' => 'Each piece is reviewed for balance, durability, and presentation before shipping.',
                        'ar' => 'نراجع كل قطعة من حيث التوازن والمتانة وطريقة العرض قبل الشحن.',
                    ],
                ],
            ],
        ];

        PageContent::updateOrCreate(
            ['page' => 'home'],
            ['content' => $content],
        );
    }
}
