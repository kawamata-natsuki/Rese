<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\ShopOwner;
use Illuminate\Support\Facades\Hash;

class ShopOwnerSeeder extends Seeder
{
    public function run(): void
    {
        $owners = [
            ['name' => '佐藤 太郎', 'email' => 'owner1@example.com'],
            ['name' => '鈴木 次郎', 'email' => 'owner2@example.com'],
            ['name' => '高橋 花子', 'email' => 'owner3@example.com'],
            ['name' => '田中 一郎', 'email' => 'owner4@example.com'],
            ['name' => '山本 美咲', 'email' => 'owner5@example.com'],
        ];

        foreach ($owners as $owner) {
            ShopOwner::updateOrCreate(
                ['email' => $owner['email']],
                [
                    'name'     => $owner['name'],
                    'password' => Hash::make('12345678'),
                ]
            );
        }
    }
}
