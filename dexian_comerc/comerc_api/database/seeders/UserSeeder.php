<?php

namespace Database\Seeders;

use App\Models\Client;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class UserSeeder extends Seeder
{
    public function run()
    {
        User::create([
            'name' => 'Admin User',
            'email' => 'admin@admin.com',
            'password' => Hash::make('123456'),
            'type' => 1,
        ]);

        $clientUser = User::create([
            'name' => 'Client User',
            'email' => 'client@client.com',
            'password' => Hash::make('123456'),
            'type' => 2,
        ]);

        Client::create([
            'user_id' => $clientUser->id,
            'phone' => '1234567890',
            'birth_date' => '1990-01-01',
            'address' => '123 Client St',
            'complement' => 'Apt 101',
            'district' => 'Central',
            'zipcode' => '12345-678',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
