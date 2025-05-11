<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Category;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Category::factory()
            ->count(8)
            ->hasProducts(rand(1, 50))
            ->create();
        Category::factory()
            ->count(12)
            ->hasProducts(rand(1, 100))
            ->create();
        Category::factory()
            ->count(4)
            ->create();
    }
}
