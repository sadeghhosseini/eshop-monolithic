<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\User;

class AddressFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'customer_id' => User::factory(),
            'province' => $this->faker->word(),
            'city' => $this->faker->city(),
            'rest_of_address' => $this->faker->address(),
            'postal_code' => $this->faker->postcode(),
        ];
    }
}
