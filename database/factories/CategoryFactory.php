<?php

namespace Database\Factories;

use App\Models\Category;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Category>
 */
class CategoryFactory extends Factory
{
    protected $model = Category::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => $this->generateCategoryName(),
            'description' => $this->faker->sentence(),
            'featured_image' => $this->faker->imageUrl(),
            'status' => $this->faker->boolean(90)
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
