<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Model>
 */
class HotelFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        return [
            'name' => $this->faker->company() . " Hotel",
            'address' => $this->faker->address(),
            'description' => $this->faker->paragraph(),
            'rating' => $this->faker->randomFloat(1, 1, 5),
        ];
    }
}
