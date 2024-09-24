<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Product;

class ProductSeeder extends Seeder
{
    public function run()
    {
        $products = [
            ['name' => 'Pastel de Carne', 'price' => 5.50, 'photo' => 'products/CpHm5cVN3qJablxXyoZyUE7aBNs7E4eIFt7T2XVe.jpg'],
            ['name' => 'Pastel de Queijo', 'price' => 4.50, 'photo' => 'products/CpHm5cVN3qJablxXyoZyUE7aBNs7E4eIFt7T2XVe.jpg'],
            ['name' => 'Pastel de Frango', 'price' => 6.00, 'photo' => 'products/CpHm5cVN3qJablxXyoZyUE7aBNs7E4eIFt7T2XVe.jpg'],
            ['name' => 'Pastel de Pizza', 'price' => 6.50, 'photo' => 'products/CpHm5cVN3qJablxXyoZyUE7aBNs7E4eIFt7T2XVe.jpg'],
            ['name' => 'Pastel de Chocolate', 'price' => 7.00, 'photo' => 'products/CpHm5cVN3qJablxXyoZyUE7aBNs7E4eIFt7T2XVe.jpg'],
        ];

        foreach ($products as $product) {
            Product::create($product);
        }
    }
}
