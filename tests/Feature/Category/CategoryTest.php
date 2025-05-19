<?php

namespace Tests\Feature\Category;

use Tests\TestCase;
use App\Models\User;
use App\Models\Category;
use Illuminate\Foundation\Testing\RefreshDatabase;

class CategoryTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_list_categories()
    {
        Category::factory()->count(3)->create();

        $user = User::factory()->create([
            'name' => 'Test user',
            'email' => 'user@example.com',
            'password' => 'password123',
            'role' => 'user'
        ]);

        $this->actingAs($user);

        $response = $this->getJson('/api/v1/categories');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'message',
                'response' => [
                    'data' => [
                        '*' => [
                            'id',
                            'category_name',
                            'description_excerpt',
                            'created_at',
                            'url_image',
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

    public function test_user_can_view_category()
    {
        $category = Category::factory()->create();

        $user = User::factory()->create([
            'name' => 'Test user',
            'email' => 'user@example.com',
            'password' => 'password123',
            'role' => 'user'
        ]);

        $this->actingAs($user);

        $response = $this->getJson("/api/v1/categories/{$category->id}");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'message',
                'response' => [
                    'id',
                    'category_name',
                    'description_excerpt',
                    'created_at',
                    'url_image',
                ]
            ]);
    }

    public function test_admin_can_create_category()
    {
        $this->actingAsAdmin();

        $categoryData = [
            'name' => 'Test Category',
            'description' => 'Test Description',
            'featured_image' => null,
            'status' => true
        ];

        $response = $this->postJson('/api/v1/categories', $categoryData);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'message',
                'response' => [
                    'category_id',
                ]
            ]);

        $this->assertDatabaseHas('categories', [
            'name' => 'Test Category'
        ]);
    }

    public function test_admin_can_update_category()
    {
        $this->actingAsAdmin();
        $category = Category::factory()->create();

        $updateData = [
            'name' => 'Updated Category',
            'description' => 'Updated Description'
        ];

        $response = $this->patchJson("/api/v1/categories/{$category->id}", $updateData);

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Category updated successfully!!'
            ]);
    }

    public function test_admin_can_delete_category()
    {
        $this->actingAsAdmin();
        $category = Category::factory()->create();

        $response = $this->deleteJson("/api/v1/categories/{$category->id}");

        $response->assertStatus(204);
        $this->assertDatabaseMissing('categories', ['id' => $category->id]);
    }

    public function test_regular_user_cannot_create_category()
    {
        $this->actingAsUser();

        $categoryData = [
            'name' => 'Test Category',
            'description' => 'Test Description'
        ];

        $response = $this->postJson('/api/v1/categories', $categoryData);

        $response->assertStatus(403);
    }
} 