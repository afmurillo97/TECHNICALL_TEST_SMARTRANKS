<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        User::create([
            'name' => 'Felipe Murillo Admin',
            'email' => 'afmurillo97@gmail.com',
            'password' => Hash::make('smartranks'),
            'role' => 'admin'
        ]);
        
        $this->call([
            CategorySeeder::class,
        ]);
    }
}
