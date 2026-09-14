<?php

namespace Database\Seeders;

use App\Models\Review;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ReviewSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $reviews = [
            [
                'product_variant_id' => 1,
                // 'user_id' => 1,
                'rating' => 5,
                'comment' => 'Great product!',
                'is_approved' => true,
            ],
            [
                'product_variant_id' => 2,
                // 'user_id' => 2,
                'rating' => 4,
                'comment' => 'Good product!',
                'is_approved' => true,
            ],
            [
                'product_variant_id' => 4,
                // 'user_id' => 3,
                'rating' => 3,
                'comment' => 'Average product!',
                'is_approved' => true,
            ],
            [
                'product_variant_id' => 6,
                // 'user_id' => 4,
                'rating' => 2,
                'comment' => 'Bad product!',
                'is_approved' => true,
            ],
            [
                'product_variant_id' => 8,
                // 'user_id' => 5,
                'rating' => 1,
                'comment' => 'Worst product!',
                'is_approved' => true,
            ],
        ];
        foreach ($reviews as $review) {
            Review::create($review);
        }
    }
}
