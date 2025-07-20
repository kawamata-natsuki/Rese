<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class TestUserSeeder extends Seeder
{
    public function run(): void
    {
        User::create([
            'name' => 'test',
            'email' => 'test@example.com',
            'email_verified_at' => now(), 
            'password' => Hash::make('12345678'),
            'role' => 'user',
        ]);
    }
}
