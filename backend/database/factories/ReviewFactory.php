<?php

namespace Database\Factories;

use App\Models\Review;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class ReviewFactory extends Factory
{
    protected $model = Review::class;

    public function definition()
    {
        $rating = $this->faker->numberBetween(1, 5);
        
        $comments = [
            5 => ['Csodálatos hely, csak ajánlani tudom!', 'Minden tökéletes volt, a személyzet kedves.', 'Profi kiszolgálás és tiszta szobák.'],
            4 => ['Nagyon jó volt, de a reggeli lehetett volna változatosabb.', 'Szép kilátás, kényelmes ágyak.', 'Meg voltunk elégedve mindennel.'],
            3 => ['Átlagos szállás, az árnak megfelelő.', 'Kicsit zajos volt az utca, de a szoba tiszta.', 'Központi helyen van, de ráférne egy felújítás.'],
            2 => ['A képek szebbek voltak, mint a valóság.', 'A recepciós nem volt túl segítőkész.', 'Sajnos a klíma nem működött.'],
            1 => ['Soha többet! Senkinek nem ajánlom.', 'Kosz és hangzavar mindenhol.', 'Borzalmas élmény volt, azonnal eljöttünk.'],
        ];

        return [
            'user_id' => User::inRandomOrder()->first()->id ?? User::factory(),
            'rating' => $rating,
            'comment' => $this->faker->randomElement($comments[$rating]),
        ];
    }
}