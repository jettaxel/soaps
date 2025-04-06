<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Faker\Factory as Faker;

class ProductsTableSeeder extends Seeder
{
    public function run()
    {

        $faker = Faker::create('en_US');
        // Create, for example, 10 products
        for ($i = 0; $i < 10; $i++) {
            DB::table('products')->insert([
             'name' => $faker->words(2, true),
                'description' => $faker->paragraph,
                'price'       => $faker->randomFloat(2, 10, 200), // price between 10 and 200
                'stock'       => $faker->numberBetween(10, 100),
                'image'       => $faker->imageUrl(640, 480, 'technics', true, 'soap'),
                'created_at'  => now(),
                'updated_at'  => now(),
                // randomly pick a category from 1, 2, 3
                'category_id' => $faker->randomElement([1, 2, 3]),
            ]);
        }
    }
}
