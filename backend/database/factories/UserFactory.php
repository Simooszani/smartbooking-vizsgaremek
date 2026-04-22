<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class UserFactory extends Factory
{
    protected static ?string $password;

    private static array $firstNamesMale = [
        'László', 'István', 'József', 'János', 'Zoltán', 'Sándor', 'Gábor', 'Ferenc',
        'Attila', 'Péter', 'Tamás', 'Zsolt', 'Tibor', 'András', 'Csaba', 'Imre',
        'Lajos', 'György', 'Balázs', 'Gyula', 'Mihály', 'Róbert', 'Béla', 'Dávid',
        'Dániel', 'Ádám', 'Krisztián', 'Miklós', 'Norbert', 'Bence', 'Máté', 'Levente',
    ];

    private static array $firstNamesFemale = [
        'Mária', 'Erzsébet', 'Katalin', 'Ilona', 'Éva', 'Anna', 'Zsuzsanna', 'Margit',
        'Judit', 'Ágnes', 'Andrea', 'Erika', 'Krisztina', 'Julianna', 'Irén', 'Eszter',
        'Mónika', 'Edit', 'Gabriella', 'Szilvia', 'Nikolett', 'Viktória', 'Petra', 'Vivien',
        'Boglárka', 'Réka', 'Nóra', 'Fruzsina', 'Lilla', 'Dóra', 'Fanni', 'Zsófia',
    ];

    private static array $lastNames = [
        'Nagy', 'Kovács', 'Tóth', 'Szabó', 'Horváth', 'Varga', 'Kiss', 'Molnár',
        'Németh', 'Farkas', 'Balogh', 'Papp', 'Takács', 'Juhász', 'Lakatos', 'Mészáros',
        'Oláh', 'Simon', 'Rácz', 'Fehér', 'Szilágyi', 'Török', 'Vincze', 'Balázs',
        'Hegedűs', 'Szűcs', 'Pintér', 'Fodor', 'Antal', 'Orosz',
    ];

    public function definition(): array
    {
        $isMale = fake()->boolean();
        $firstName = fake()->randomElement($isMale ? self::$firstNamesMale : self::$firstNamesFemale);
        $lastName = fake()->randomElement(self::$lastNames);
        $name = $lastName . ' ' . $firstName;

        return [
            'name' => $name,
            'email' => fake()->unique()->safeEmail(),
            'email_verified_at' => now(),
            'password' => static::$password ??= Hash::make('password'),
            'remember_token' => Str::random(10),
        ];
    }

    public function unverified(): static
    {
        return $this->state(fn (array $attributes) => [
            'email_verified_at' => null,
        ]);
    }
}
