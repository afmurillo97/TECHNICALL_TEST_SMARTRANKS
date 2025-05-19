<?php

namespace Database\Factories;

use App\Models\Product;
use App\Models\Category;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Product>
 */
class ProductFactory extends Factory
{
    protected $model = Product::class;

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
            'category_id' => Category::factory(),
            'name' => $this->generateProductName(),
            'sku' => $this->faker->unique()->bothify('SKU-####-????'),
            'description' => $this->faker->paragraph(),
            'purchase_price' => $purchasePrice,
            'sale_price' => $salePrice,
            'stock' => $this->faker->numberBetween(0, 100),
            'featured_image' => $this->faker->imageUrl(),
            'status' => $this->faker->boolean(80)
        ];
    }

    /**
     * Populates Products names.
     *
     */
    private function generateProductName(): string
    {
        $productTypes = [
            'Laptop', 'Smartphone', 'Tablet', 'Monitor', 
            'Keyboard', 'Mouse', 'Printer', 'SSD', 
            'Router', 'Webcam'
        ];
        
        $brands = [
            'HP', 'Dell', 'Samsung', 'Apple', 
            'Logitech', 'Sony', 'Lenovo', 'Asus',
            'Acer', 'MSI'
        ];
        
        $features = [
            'Pro', 'Max', 'Elite', 'Gaming', 
            'Ultra', 'Plus', 'XT', 'Limited', 
            'Edition', 'Turbo'
        ];

        return sprintf(
            '%s %s %s %s',
            $this->faker->randomElement($brands),
            $this->faker->randomElement($productTypes),
            $this->faker->randomElement($features),
            $this->faker->numberBetween(1000, 9999)
        );
    }
}
