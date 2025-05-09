<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Category;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Product>
 */
class ProductFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */


    public function definition(): array
    {
        $purchasePrice = $this->faker->randomFloat(2, 10, 100);
        $salePrice = $this->faker->randomFloat(2, $purchasePrice * 1.2, $purchasePrice * 2);

        return [
            'category_id' => function() {
                return Category::inRandomOrder()->first()->id 
                    ?? Category::factory(); // Fallback si no hay categorías
            },
            'name' => $this->faker->unique()->words(2, true),
            'sku' => Str::upper(Str::random(10)),
            'description' => $this->faker->paragraph(),
            'purchase_price' => $purchasePrice,
            'sale_price' => $salePrice,
            'stock' => $this->faker->numberBetween(0, 100),
            'featured_image' => $this->faker->imageUrl(800, 600, 'products', true, 'electronics', true),
            'status' => $this->faker->boolean(80),
        ];
    }
}
