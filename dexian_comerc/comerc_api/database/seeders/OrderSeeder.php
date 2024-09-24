<?php

namespace Database\Seeders;

use App\Models\Client;
use Illuminate\Database\Seeder;
use App\Models\Order;
use App\Models\User;
use App\Models\Product;

class OrderSeeder extends Seeder
{
    public function run()
    {
        $client = Client::join('users', 'clients.user_id', '=', 'users.id')
            ->where('users.email', 'client@client.com')
            ->select('clients.*')
            ->first();

        $product = Product::where('name', 'Pastel de Frango')->first();

        Order::create([
            'client_id' => $client->id,
            'product_id' => $product->id,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
