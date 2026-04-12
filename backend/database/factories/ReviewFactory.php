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
        $rating = fake()->numberBetween(1, 5);

        $comments = [
            5 => [
                'Csodálatos hely, csak ajánlani tudom!',
                'Minden tökéletes volt, a személyzet kedves és segítőkész.',
                'Profi kiszolgálás és tiszta szobák, visszatérünk!',
                'Fantasztikus élmény volt, a reggeli is kiváló!',
                'Az egyik legjobb szállás ahol valaha voltunk!',
                'Gyönyörű kilátás, kényelmes szobák, szuper wellness.',
                'Tökéletes családi nyaralás volt, a gyerekek is imádták!',
            ],
            4 => [
                'Nagyon jó volt, de a reggeli lehetett volna változatosabb.',
                'Szép kilátás, kényelmes ágyak, ajánlom mindenkinek.',
                'Meg voltunk elégedve mindennel, legközelebb is ide jövünk.',
                'Kiváló ár-érték arány, a személyzet kedves volt.',
                'Szép környezet, jó parkolási lehetőség, picit zajos volt éjjel.',
                'A szoba tágas és tiszta, a fürdőszoba modern.',
            ],
            3 => [
                'Átlagos szállás, az árnak megfelelő.',
                'Kicsit zajos volt az utca, de a szoba tiszta.',
                'Központi helyen van, de ráférne egy felújítás.',
                'A wifi gyenge volt, de amúgy rendben volt minden.',
                'Nem rossz, de nem is kiemelkedő. Megfelel egy éjszakára.',
                'A szoba kicsi volt, de a személyzet kedves.',
            ],
            2 => [
                'A képek szebbek voltak, mint a valóság.',
                'A recepciós nem volt túl segítőkész.',
                'Sajnos a klíma nem működött rendesen.',
                'A szoba nem volt eléggé tiszta, csalódtunk.',
                'Az ár-érték arány nem stimmel, túl drága ezért.',
            ],
            1 => [
                'Soha többet! Senkinek nem ajánlom.',
                'Kosz és hangzavar mindenhol, borzalmas.',
                'Borzalmas élmény volt, azonnal eljöttünk.',
                'Félrevezető képek, a valóság sokkal rosszabb.',
                'Penészes fürdőszoba, udvariatlan személyzet.',
            ],
        ];

        return [
            'user_id' => User::inRandomOrder()->first()->id ?? User::factory(),
            'rating' => $rating,
            'comment' => fake()->randomElement($comments[$rating]),
        ];
    }
}
