<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Category>
 */
class CategoryFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => $this->generateCategoryName(),
            'description' => $this->faker->paragraph(),
            'featured_image' => $this->faker->imageUrl(800, 600, 'categories', true, 'electronics', true),
            'status' => $this->faker->boolean(90),
        ];
    }

    /**
     * Populates Categories names.
     *
     */
    private function generateCategoryName(): string
    {
        $mainCategories = [
            'Electronics', 'Clothing', 'Home & Garden', 
            'Sports', 'Beauty', 'Toys', 
            'Books', 'Automotive', 'Health', 
            'Office'
        ];

        $subcategories = [
            'Accessories', 'Gadgets', 'Premium', 
            'Budget', 'Vintage', 'Modern',
            'Professional', 'DIY', 'Luxury'
        ];

        return sprintf(
            '%s %s',
            $this->faker->randomElement($mainCategories),
            $this->faker->randomElement($subcategories)
        );
    }
}
