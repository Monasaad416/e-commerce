<?php

namespace Database\Seeders;

use App\Models\AttributeValue;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\ProductVariant;
use App\Models\Tag;
use App\Models\VariantAttributeValue;
use App\Models\VariantImage;
use Illuminate\Database\Seeder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ProductsSeeder extends Seeder
{
    private const PRODUCTS_COUNT = 100;
    private const SIMPLE_PRODUCTS_COUNT = 50;
    private const VARIABLE_PRODUCTS_COUNT = 50;

    public function run(): void
    {
        DB::transaction(function (): void {
            $categories = Category::query()
                ->whereIn('slug', ['wallets', 'belts', 'bags', 'card-holders'])
                ->get();

            if ($categories->isEmpty()) {
                throw new \RuntimeException('Required categories are missing. Run CategorySeeder before ProductsSeeder.');
            }

            $this->cleanupOldProducts();

            $tags = $this->seedTags();
            $attributes = AttributeSeeder::seed();

            $this->seedProducts($categories, $tags, $attributes);
        });
    }

    private function cleanupOldProducts(): void
    {
        $productIds = Product::query()
            ->withTrashed()
            ->where('sku', 'like', 'LTHR-%')
            ->pluck('id');

        if ($productIds->isEmpty()) {
            return;
        }

        DB::table('product_tags')->whereIn('product_id', $productIds)->delete();
        Product::query()->withTrashed()->whereIn('id', $productIds)->forceDelete();
    }

    private function seedTags(): Collection
    {
        $tags = [
            ['en' => 'Hand-stitched', 'ar' => 'خياطة يدوية'],
            ['en' => 'Full-grain leather', 'ar' => 'جلد طبيعي كامل'],
            ['en' => 'Gift-ready', 'ar' => 'جاهز للإهداء'],
            ['en' => 'Bestseller', 'ar' => 'الأكثر مبيعا'],
            ['en' => 'Personalizable', 'ar' => 'قابل للتخصيص'],
            ['en' => 'Limited edition', 'ar' => 'إصدار محدود'],
            ['en' => 'Daily use', 'ar' => 'استخدام يومي'],
        ];

        return collect($tags)->map(static fn (array $tag) => Tag::firstOrCreate(['name' => $tag]));
    }

    private function seedProducts(Collection $categories, Collection $tags, array $attributes): void
    {
        /** @var \App\Models\Attribute $colorAttribute */
        $colorAttribute = $attributes['Color']['attribute'];
        /** @var Collection $colorValues */
        $colorValues = $attributes['Color']['values'];
        /** @var \App\Models\Attribute $sizeAttribute */
        $sizeAttribute = $attributes['Size']['attribute'];
        /** @var Collection $sizeValues */
        $sizeValues = $attributes['Size']['values'];
        /** @var \App\Models\Attribute $leatherTypeAttribute */
        $leatherTypeAttribute = $attributes['Leather Type']['attribute'];
        /** @var Collection $leatherTypeValues */
        $leatherTypeValues = $attributes['Leather Type']['values'];
        /** @var \App\Models\Attribute $hardwareFinishAttribute */
        $hardwareFinishAttribute = $attributes['Hardware Finish']['attribute'];
        /** @var Collection $hardwareFinishValues */
        $hardwareFinishValues = $attributes['Hardware Finish']['values'];

        $namesByCategory = [
            'wallets' => [
                ['en' => 'Executive Wallet', 'ar' => 'محفظة تنفيذية'],
                ['en' => 'Bifold Wallet', 'ar' => 'محفظة ثنائية'],
                ['en' => 'Long Wallet', 'ar' => 'محفظة طويلة'],
            ],
            'belts' => [
                ['en' => 'Classic Belt', 'ar' => 'حزام كلاسيكي'],
                ['en' => 'Reversible Belt', 'ar' => 'حزام مزدوج'],
                ['en' => 'Formal Leather Belt', 'ar' => 'حزام جلد رسمي'],
            ],
            'bags' => [
                ['en' => 'Crossbody Bag', 'ar' => 'حقيبة كروس'],
                ['en' => 'Messenger Bag', 'ar' => 'حقيبة كتف'],
                ['en' => 'Tote Leather Bag', 'ar' => 'حقيبة جلد حمل'],
            ],
            'card-holders' => [
                ['en' => 'Slim Card Holder', 'ar' => 'حافظة بطاقات نحيفة'],
                ['en' => 'Minimal Card Holder', 'ar' => 'حافظة بطاقات بسيطة'],
                ['en' => 'Snap Card Holder', 'ar' => 'حافظة بطاقات زر'],
            ],
        ];

        for ($i = 1; $i <= self::PRODUCTS_COUNT; $i++) {
            $category = $categories->random();
            $slug = $category->slug;
            $categoryNames = $namesByCategory[$slug] ?? $namesByCategory['wallets'];
            $name = $categoryNames[array_rand($categoryNames)];
            $sku = sprintf('LTHR-%04d', $i);
            $isSimple = $i <= self::SIMPLE_PRODUCTS_COUNT;

            $purchase = fake()->randomFloat(2, 20, 140);
            $selling = round($purchase * fake()->randomFloat(2, 1.35, 2.0), 2);
            $discount = fake()->boolean(35) ? round($selling * fake()->randomFloat(2, 0.82, 0.95), 2) : null;

            $product = Product::create([
                'name' => ['en' => "{$name['en']} {$i}", 'ar' => "{$name['ar']} {$i}"],
                'description' => [
                    'en' => "Handmade {$name['en']} from premium leather with artisan finishing.",
                    'ar' => "منتج {$name['ar']} مصنوع يدويا من جلد طبيعي فاخر بتشطيب حرفي.",
                ],
                'short_description' => [
                    'en' => "Configurable handmade {$name['en']}.",
                    'ar' => "{$name['ar']} يدوي بخيارات متعددة.",
                ],
                'sku' => $sku,
                'slug' => Str::slug("{$name['en']}-{$sku}"),
                'type' => $isSimple ? 'simple' : 'variable',
                'category_id' => $category->id,
                'purchase_price' => $purchase,
                'selling_price' => $selling,
                'discount_price' => $discount,
                'qty' => $isSimple ? fake()->numberBetween(5, 80) : 0,
                'thumbnail' => $this->leatherImageUrl($category->slug, $name['en'], 'brown', 720, 720, "{$sku}-thumb"),
                'is_active' => true,
                'is_featured' => fake()->boolean(20),
                'meta_keywords' => [
                    'en' => "handmade leather, {$name['en']}, artisan",
                    'ar' => "جلد يدوي, {$name['ar']}, حرفي",
                ],
                'meta_description' => [
                    'en' => "Premium handmade leather {$name['en']}.",
                    'ar' => "{$name['ar']} جلدية يدوية عالية الجودة.",
                ],
            ]);

            $product->tags()->sync($tags->random(fake()->numberBetween(2, 4))->pluck('id')->all());
            $productColors = $selectedColors = $colorValues->random(2)->values();
            $this->seedProductImages($product, $category->slug, $sku, $name['en'], $productColors);

            if ($isSimple) {
                continue;
            }

            $totalQty = 0;
            $selectedSizes = $sizeValues->random(2)->values();
            $selectedLeatherType = $leatherTypeValues->random();
            $selectedHardwareFinish = $hardwareFinishValues->random();

            $variantIndex = 1;
            foreach ($selectedColors as $colorValue) {
                foreach ($selectedSizes as $sizeValue) {
                    $variantSku = "{$sku}-{$variantIndex}";
                    $variantPurchase = round($purchase * fake()->randomFloat(2, 0.9, 1.15), 2);
                    $variantSelling = round($selling * fake()->randomFloat(2, 0.95, 1.12), 2);
                    $variantDiscount = fake()->boolean(35)
                        ? round($variantSelling * fake()->randomFloat(2, 0.83, 0.95), 2)
                        : null;
                    $variantQty = fake()->numberBetween(2, 30);

                    $variant = ProductVariant::create([
                        'product_id' => $product->id,
                        'sku' => $variantSku,
                        'purchase_price' => $variantPurchase,
                        'selling_price' => $variantSelling,
                        'discount_price' => $variantDiscount,
                        'qty' => $variantQty,
                        'is_active' => true,
                        'is_featured' => $variantIndex === 1,
                    ]);

                    VariantAttributeValue::create([
                        'product_variant_id' => $variant->id,
                        'attribute_id' => $colorAttribute->id,
                        'attribute_value_id' => $colorValue->id,
                    ]);
                    VariantAttributeValue::create([
                        'product_variant_id' => $variant->id,
                        'attribute_id' => $sizeAttribute->id,
                        'attribute_value_id' => $sizeValue->id,
                    ]);
                    VariantAttributeValue::create([
                        'product_variant_id' => $variant->id,
                        'attribute_id' => $leatherTypeAttribute->id,
                        'attribute_value_id' => $selectedLeatherType->id,
                    ]);
                    VariantAttributeValue::create([
                        'product_variant_id' => $variant->id,
                        'attribute_id' => $hardwareFinishAttribute->id,
                        'attribute_value_id' => $selectedHardwareFinish->id,
                    ]);

                    $variantColor = $this->translatedValue($colorValue, 'en');
                    $this->seedVariantImages($variant, $category->slug, $variantSku, $name['en'], $variantColor);
                    $totalQty += $variantQty;
                    $variantIndex++;
                }
            }

            $product->update(['qty' => $totalQty]);
        }
    }

    private function seedProductImages(Product $product, string $categorySlug, string $seed, string $productName, Collection $colors): void
    {
        foreach ($colors->values() as $index => $colorValue) {
            $colorName = $this->translatedValue($colorValue, 'en');
            $sequence = $index + 1;
            ProductImage::create([
                'product_id' => $product->id,
                'image_path' => $this->leatherImageUrl($categorySlug, $productName, $colorName, 960, 960, "{$seed}-p{$sequence}"),
                'is_featured' => $sequence === 1,
            ]);
        }
    }

    private function seedVariantImages(ProductVariant $variant, string $categorySlug, string $seed, string $productName, string $colorName): void
    {
        for ($i = 1; $i <= 2; $i++) {
            VariantImage::create([
                'product_variant_id' => $variant->id,
                'image_path' => $this->leatherImageUrl($categorySlug, $productName, $colorName, 960, 960, "{$seed}-v{$i}"),
                'is_featured' => $i === 1,
            ]);
        }
    }

    private function leatherImageUrl(string $categorySlug, string $productName, string $colorName, int $width, int $height, string $seed): string
    {
        $pool = match ($categorySlug) {
            'wallets' => [
                'https://images.unsplash.com/photo-1627123424574-724758594e93',
                'https://images.unsplash.com/photo-1594223274512-ad4803739b7c',
                'https://images.unsplash.com/photo-1511556532299-8f662fc26c06',
            ],
            'belts' => [
                'https://images.unsplash.com/photo-1542272604-787c3835535d',
                'https://images.unsplash.com/photo-1584917865442-de89df76afd3',
                'https://images.unsplash.com/photo-1553062407-98eeb64c6a62',
            ],
            'bags' => [
                'https://images.unsplash.com/photo-1548036328-c9fa89d128fa',
                'https://images.unsplash.com/photo-1560343776-97e7d202ff0e',
                'https://images.unsplash.com/photo-1542291026-7eec264c27ff',
            ],
            'card-holders' => [
                'https://images.unsplash.com/photo-1600185365483-26d7a4cc7519',
                'https://images.unsplash.com/photo-1622560480605-d83c853bc5c3',
                'https://images.unsplash.com/photo-1600857062241-98e5dba7f214',
            ],
            default => [
                'https://images.unsplash.com/photo-1627123424574-724758594e93',
                'https://images.unsplash.com/photo-1548036328-c9fa89d128fa',
                'https://images.unsplash.com/photo-1594223274512-ad4803739b7c',
            ],
        };

        $index = abs(crc32($seed . $productName . $colorName)) % count($pool);
        $base = $pool[$index];
        return "{$base}?auto=format&fit=crop&w={$width}&h={$height}&q=80";
    }

    private function translatedValue(AttributeValue $attributeValue, string $locale): string
    {
        $value = $attributeValue->value;
        if (is_array($value)) {
            return (string) ($value[$locale] ?? $value['en'] ?? reset($value));
        }

        return (string) $value;
    }
}