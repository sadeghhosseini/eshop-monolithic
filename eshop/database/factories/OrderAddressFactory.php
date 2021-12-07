<?php

namespace Database\Factories;

use App\Models\Order;
use Illuminate\Database\Eloquent\Factories\Factory;

class OrderAddressFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'order_id' => Order::factory(),
            'province' => $this->faker->word(),
            'city' => $this->faker->city(),
            'rest_of_address' => $this->faker->address(),
            'postal_code' => $this->faker->postcode(),
        ];
    }
}
