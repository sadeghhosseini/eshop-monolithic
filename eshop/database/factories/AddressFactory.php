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
            'province' => $faker->district,
            'city' => $faker->city,
            'rest_of_address' => $faker->address,
            'postal_code' => $faker->postcode,
        ];
    }
}
