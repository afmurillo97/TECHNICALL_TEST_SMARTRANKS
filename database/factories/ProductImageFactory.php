<?php

namespace Database\Factories;

use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ProductImage>
 */
class ProductImageFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'product_id' => function() {
                return Product::inRandomOrder()->first()->id 
                    ?? Product::factory();
            },
            'image_url' => $this->faker->imageUrl(800, 600, 'products'),
            'order' => $this->faker->numberBetween(0, 5),
            'is_featured' => $this->faker->boolean(20), // 20% chance of being featured
        ];
    }
}
