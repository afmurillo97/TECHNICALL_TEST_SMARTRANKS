<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use App\Models\User;
use Laravel\Sanctum\Sanctum;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;

    protected function setUp(): void
    {
        parent::setUp();
    }

    protected function createUser($role = 'user')
    {
        return User::factory()->create([
            'role' => $role
        ]);
    }

    protected function createAdmin()
    {
        return $this->createUser('admin');
    }

    protected function actingAsUser($role = 'user')
    {
        $user = $this->createUser($role);
        Sanctum::actingAs($user);
        return $user;
    }

    protected function actingAsAdmin()
    {
        return $this->actingAsUser('admin');
    }
}
