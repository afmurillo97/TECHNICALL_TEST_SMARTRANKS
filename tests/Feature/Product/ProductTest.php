<?php

namespace Tests\Feature\Product;

use Tests\TestCase;
use App\Models\User;
use App\Models\Product;
use App\Models\Category;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ProductTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->category = Category::factory()->create();
    }

    public function test_user_can_list_products()
    {
        Product::factory()->count(3)->create([
            'category_id' => $this->category->id
        ]);

        $user = User::factory()->create([
            'name' => 'Test user',
            'email' => 'user@example.com',
            'password' => 'password123',
            'role' => 'user'
        ]);

        $this->actingAs($user);

        $response = $this->getJson('/api/v1/products');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'message',
                'response' => [
                    'data' => [
                        '*' => [
                            'id',
                            'name',
                            'category_id',
                            'category',
                            'sku',
                            'description',
                            'price',
                            'stock',
                            'created_at',
                            'product_images' => [
                                'featured_image'
                            ]
                        ]
                    ],
                    'meta' => [
                        'organization',
                        'final_tester',
                        'authors'
                    ]
                ]
            ]);
    }

    public function test_user_can_view_product()
    {
        $product = Product::factory()->create([
            'category_id' => $this->category->id
        ]);

        $user = User::factory()->create([
            'name' => 'Test user',
            'email' => 'user@example.com',
            'password' => 'password123',
            'role' => 'user'
        ]);

        $this->actingAs($user);

        $response = $this->getJson("/api/v1/products/{$product->id}");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'message',
                'response' => [
                    'id',
                    'name',
                    'category_id',
                    'category',
                    'sku',
                    'description',
                    'price',
                    'stock',
                    'created_at',
                    'product_images' => [
                        'featured_image'
                    ]
                ]
            ]);
    }

    public function test_admin_can_create_product()
    {
        $this->actingAsAdmin();

        $productData = [
            'name' => 'Test Product',
            'description' => 'Test Description',
            'purchase_price' => 10.00,
            'sale_price' => 15.00,
            'stock' => 100,
            'category_id' => $this->category->id,
            'status' => true
        ];

        $response = $this->postJson('/api/v1/products', $productData);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'message',
                'response' => [
                    'product_id',
                ]
            ]);

        $this->assertDatabaseHas('products', [
            'sale_price' => '15.00'
        ]);
    }

    public function test_admin_can_update_product()
    {
        $this->actingAsAdmin();
        $product = Product::factory()->create([
            'category_id' => $this->category->id,
            'purchase_price' => 10.00
        ]);

        $updateData = [
            'name' => 'Updated Product',
            'sale_price' => 40.00
        ];

        $response = $this->patchJson("/api/v1/products/{$product->id}", $updateData);

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Product updated successfully!!'
            ]);
    }

    public function test_admin_can_delete_product()
    {
        $this->actingAsAdmin();
        $product = Product::factory()->create([
            'category_id' => $this->category->id
        ]);

        $response = $this->deleteJson("/api/v1/products/{$product->id}");

        $response->assertStatus(204);
        $this->assertDatabaseMissing('products', ['id' => $product->id]);
    }

    public function test_regular_user_cannot_create_product()
    {
        $this->actingAsUser();

        $productData = [
            'name' => 'Test Product',
            'sku' => 'TEST123',
            'category_id' => $this->category->id
        ];

        $response = $this->postJson('/api/v1/products', $productData);

        $response->assertStatus(403);
    }
} 