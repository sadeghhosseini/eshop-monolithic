<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Comment;
use App\Models\Image;
use App\Models\Product;
use App\Models\Property;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Product::factory()->count(10)
            ->has(Property::factory()->count(5))
            ->has(Comment::factory(3))
            ->has(Image::factory())
            ->for(Category::factory()->create())
            ->create();
    }
}
