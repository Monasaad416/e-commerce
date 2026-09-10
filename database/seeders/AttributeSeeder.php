<?php

namespace Database\Seeders;

use App\Models\Attribute;
use App\Models\AttributeValue;
use Illuminate\Database\Seeder;
use Illuminate\Support\Collection;

class AttributeSeeder extends Seeder
{
    public function run(): void
    {
        $this->seed();
    }

    /**
     * @return array<string, array{attribute: Attribute, values: Collection<int, AttributeValue>}>
     */
    public static function seed(): array
    {
        $map = [
            'Color' => [
                'ar' => 'اللون',
                'values' => [
                    ['en' => 'Black', 'ar' => 'أسود'],
                    ['en' => 'Dark Brown', 'ar' => 'بني غامق'],
                    ['en' => 'Tan', 'ar' => 'بني فاتح'],
                    ['en' => 'Burgundy', 'ar' => 'عنابي'],
                ],
            ],
            'Size' => [
                'ar' => 'المقاس',
                'values' => [
                    ['en' => 'Compact', 'ar' => 'صغير'],
                    ['en' => 'Standard', 'ar' => 'متوسط'],
                    ['en' => 'Large', 'ar' => 'كبير'],
                ],
            ],
            'Leather Type' => [
                'ar' => 'نوع الجلد',
                'values' => [
                    ['en' => 'Full Grain', 'ar' => 'كامل الحبيبات'],
                    ['en' => 'Top Grain', 'ar' => 'علوي الحبيبات'],
                    ['en' => 'Vegetable Tanned', 'ar' => 'مدبوغ نباتيا'],
                ],
            ],
            'Hardware Finish' => [
                'ar' => 'تشطيب الإكسسوار',
                'values' => [
                    ['en' => 'Antique Brass', 'ar' => 'نحاس عتيق'],
                    ['en' => 'Matte Nickel', 'ar' => 'نيكل مطفي'],
                    ['en' => 'Matte Black', 'ar' => 'أسود مطفي'],
                ],
            ],
        ];

        $result = [];
        foreach ($map as $enName => $config) {
            $attribute = Attribute::firstOrCreate(
                ['name' => ['en' => $enName, 'ar' => $config['ar']]],
                ['type' => 'select']
            );

            $values = collect($config['values'])->map(
                static fn (array $value) => AttributeValue::firstOrCreate([
                    'attribute_id' => $attribute->id,
                    'value' => $value,
                ])
            );

            $result[$enName] = ['attribute' => $attribute, 'values' => $values];
        }

        return $result;
    }
}
