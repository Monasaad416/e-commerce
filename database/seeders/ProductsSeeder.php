<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Models\Category;
use App\Models\ProductImage;
use App\Models\ProductVariant;
use App\Models\Attribute;
use App\Models\AttributeValue;
use App\Models\Tag;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class ProductsSeeder extends Seeder
{
    public function run()
    {
        // Create a category if it doesn't exist
        $category = Category::firstOrCreate(
            ['name->en' => 'Clothing', 'name->ar' => 'ملابس'],
            [
                'slug' => 'clothing',
                'description' => ['en' => 'Clothing items', 'ar' => 'ملابس'],
                'is_active' => true
            ]
        );


        // Create attribute values
        $colors = [
            ['en' => 'Red', 'ar' => 'أحمر'],
            ['en' => 'Blue', 'ar' => 'أزرق'],
            ['en' => 'Black', 'ar' => 'أسود'],
            ['en' => 'White', 'ar' => 'أبيض']
        ];

        $sizes = [
            ['en' => 'S', 'ar' => 'صغير'],
            ['en' => 'M', 'ar' => 'متوسط'],
            ['en' => 'L', 'ar' => 'كبير'],
            ['en' => 'XL', 'ar' => 'اكس ال']
        ];

      // In ProductsSeeder.php

// Update the attribute creation to remove the slug field
$colorAttribute = Attribute::firstOrCreate(
    ['name->en' => 'Color', 'name->ar' => 'اللون'],
    ['type' => 'select']
);

$sizeAttribute = Attribute::firstOrCreate(
    ['name->en' => 'Size', 'name->ar' => 'المقاس'],
    ['type' => 'select']
);

// Update the attribute value creation to handle the value as JSON
foreach ($colors as $color) {
    AttributeValue::firstOrCreate([
        'attribute_id' => $colorAttribute->id,
        'value' => $color,
        'slug' => Str::slug($color['en'])
    ]);
}

foreach ($sizes as $size) {
    AttributeValue::firstOrCreate([
        'attribute_id' => $sizeAttribute->id,
        'value' => $size,
        'slug' => Str::slug($size['en'])
    ]);
}

        // Create tags
        $tags = [
            ['en' => 'New', 'ar' => 'جديد'],
            ['en' => 'Sale', 'ar' => 'تخفيضات'],
            ['en' => 'Featured', 'ar' => 'مميز']
        ];

        $tagIds = [];
        foreach ($tags as $tag) {
            $tagModel = Tag::firstOrCreate(
                ['name->en' => $tag['en'], 'name->ar' => $tag['ar']],
                ['slug' => Str::slug($tag['en'])]
            );
            $tagIds[] = $tagModel->id;
        }

        // Create a simple product
        $simpleProduct = Product::create([
            'name' => [
                'en' => 'Basic T-Shirt',
                'ar' => 'تيشيرت أساسي'
            ],
            'description' => [
                'en' => 'A comfortable basic t-shirt for everyday wear.',
                'ar' => 'تيشيرت مريح للارتداء اليومي.'
            ],
            'short_description' => [
                'en' => 'Comfortable cotton t-shirt',
                'ar' => 'تيشيرت قطن مريح'
            ],
            'sku' => 'TSHIRT001',
            'category_id' => $category->id,
            'purchase_price' => 1500, // in cents
            'selling_price' => 2499,
            'discount_price' => 1999,
            'qty' => 100,
            'is_active' => true,
            'is_featured' => true,
            'meta_keywords' => ['en' => 'tshirt, cotton, basic', 'ar' => 'تيشيرت, قطن, أساسي'],
            'meta_description' => [
                'en' => 'High quality basic t-shirt',
                'ar' => 'تيشيرت أساسي عالي الجودة'
            ],
            'type' => 'simple'
        ]);

        // Attach tags to product
        $simpleProduct->tags()->sync($tagIds);

        // Add images to simple product
        $this->addProductImages($simpleProduct, 't-shirt', 3);

        // Create a variable product
        $variableProduct = Product::create([
            'name' => [
                'en' => 'Premium Jeans',
                'ar' => 'جينز مميز'
            ],
            'description' => [
                'en' => 'High-quality premium jeans with various fits and colors.',
                'ar' => 'جينز عالي الجودة بتصاميم وألوان متعددة.'
            ],
            'short_description' => [
                'en' => 'Premium quality denim jeans',
                'ar' => 'جينز دينيم عالي الجودة'
            ],
            'sku' => 'JEANS001',
            'category_id' => $category->id,
            'purchase_price' => 3000, // in cents
            'selling_price' => 5999,
            'discount_price' => 4999,
            'qty' => 50,
            'is_active' => true,
            'is_featured' => true,
            'meta_keywords' => ['en' => 'jeans, denim, premium', 'ar' => 'جينز, دينيم, مميز'],
            'meta_description' => [
                'en' => 'Premium quality denim jeans',
                'ar' => 'جينز دينيم عالي الجودة'
            ],
            'type' => 'variable'
        ]);

        // Add images to variable product
        $this->addProductImages($variableProduct, 'jeans', 4);

        // Get attribute values for variants
        $colorValues = AttributeValue::where('attribute_id', $colorAttribute->id)->get();
        $sizeValues = AttributeValue::where('attribute_id', $sizeAttribute->id)->get();

        // Create variants for the variable product
        foreach ($colorValues as $color) {
            foreach ($sizeValues as $size) {
                $variant = ProductVariant::create([
                    'product_id' => $variableProduct->id,
                    'sku' => 'JEANS' . strtoupper(substr($color->value['en'], 0, 1)) . $size->value['en'],
                    'purchase_price' => rand(3000, 3500),
                    'selling_price' => rand(5000, 5999),
                    'discount_price' => rand(4000, 4500),
                    'qty' => rand(5, 20),
                    'is_active' => true
                ]);

                // Attach attribute values to variant
                $variant->attributes()->attach([
                    $colorAttribute->id => ['attribute_value_id' => $color->id],
                    $sizeAttribute->id => ['attribute_value_id' => $size->id]
                ]);
            }
        }

        // Create some more sample products
        $this->createSampleProducts($category, $tagIds);
    }

    private function addProductImages($product, $prefix, $count)
    {
        for ($i = 1; $i <= $count; $i++) {
            ProductImage::create([
                'product_id' => $product->id,
                'image_path' => "products/{$prefix}-{$i}.jpg",
                'is_featured' => $i === 1
            ]);
        }
    }

    private function createSampleProducts($category, $tagIds)
    {
        $products = [
            [
                'name' => [
                    'en' => 'Classic Hat',
                    'ar' => 'قبعة كلاسيكية'
                ],
                'description' => [
                    'en' => 'A stylish classic hat for all occasions.',
                    'ar' => 'قبعة كلاسيكية أنيقة لجميع المناسبات.'
                ],
                'short_description' => [
                    'en' => 'Classic hat for men and women',
                    'ar' => 'قبعة كلاسيكية للرجال والنساء'
                ],
                'purchase_price' => 2000,
                'selling_price' => 3499,
                'qty' => 30,
                'type' => 'simple'
            ],
            [
                'name' => [
                    'en' => 'Running Shoes',
                    'ar' => 'أحذية رياضية'
                ],
                'description' => [
                    'en' => 'Comfortable running shoes for your daily exercise.',
                    'ar' => 'أحذية رياضية مريحة لتمارينك اليومية.'
                ],
                'short_description' => [
                    'en' => 'High-performance running shoes',
                    'ar' => 'أحذية رياضية عالية الأداء'
                ],
                'purchase_price' => 4000,
                'selling_price' => 8999,
                'qty' => 25,
                'type' => 'simple'
            ]
        ];

        foreach ($products as $productData) {
            $product = Product::create(array_merge($productData, [
                'category_id' => $category->id,
                'is_active' => true,
                'is_featured' => true,
                'discount_price' => $productData['selling_price'] * 0.9, // 10% off
                'sku' => 'PROD' . strtoupper(Str::random(6)),
                'meta_keywords' => [
                    'en' => $productData['name']['en'] . ', ' . $category->name,
                    'ar' => $productData['name']['ar'] . ', ' . $category->name
                ],
                'meta_description' => [
                    'en' => 'High quality ' . $productData['name']['en'],
                    'ar' => $productData['name']['ar'] . ' عالي الجودة'
                ]
            ]));

            // Attach tags
            $product->tags()->sync($tagIds);

            // Add images
            $this->addProductImages($product, str_replace(' ', '-', strtolower($productData['name']['en'])), 2);
        }
    }
}