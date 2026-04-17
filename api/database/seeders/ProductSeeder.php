<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use App\Models\Tag;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        $categories = Category::all();
        $tags = Tag::all();

        Product::factory()->count(15)->create([
            'category_id' => fn () => $categories->random()->id,
        ])->each(function (Product $product) use ($tags) {
            $product->tags()->attach(
                $tags->random(rand(0, 3))->pluck('id')
            );
        });
    }
}
