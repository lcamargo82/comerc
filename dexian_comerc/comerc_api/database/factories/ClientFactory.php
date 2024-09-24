<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Client>
 */
class ClientFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'phone' => $this->faker->phoneNumber,
            'birth_date' => $this->faker->date(),
            'address' => $this->faker->address,
            'complement' => $this->faker->optional()->sentence,
            'district' => $this->faker->city,
            'zipcode' => $this->faker->postcode,
        ];
    }
}
