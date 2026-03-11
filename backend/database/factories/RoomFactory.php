<?php

namespace Database\Factories;

use App\Models\Room;
use Illuminate\Database\Eloquent\Factories\Factory;

class RoomFactory extends Factory
{
    protected $model = Room::class;

    public function definition()
    {
        $roomConfigs = [
            ['type' => 'Standard Single', 'capacity' => 1, 'price' => [12000, 20000]],
            ['type' => 'Standard Double', 'capacity' => 2, 'price' => [18000, 30000]],
            ['type' => 'Superior Family', 'capacity' => 4, 'price' => [35000, 55000]],
            ['type' => 'VIP Suite', 'capacity' => 2, 'price' => [60000, 120000]],
            ['type' => 'Economy Triple', 'capacity' => 3, 'price' => [22000, 35000]],
        ];

        $config = $this->faker->randomElement($roomConfigs);

        return [
            'type' => $config['type'],
            'capacity' => $config['capacity'],
            'price_per_night' => $this->faker->numberBetween($config['price'][0], $config['price'][1]),
        ];
    }
}