<?php

namespace Database\Factories;

use App\Models\Category;
use Illuminate\Database\Eloquent\Factories\Factory;

class ProductFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'category_id' => Category::factory(),
            'title' => $this->faker->name(),
            'description' => $this->faker->text(),
            'quantity' => $this->faker->randomNumber(),
            'price' => $this->faker->numberBetween(1000, 100000000),
        ];
    }
}
