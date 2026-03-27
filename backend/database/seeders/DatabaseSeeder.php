<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Hotel;
use App\Models\Room;
use App\Models\Review;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run()
    {
        User::factory(50)->create();

        User::create([
            'name' => 'Teszt Felhasználó',
            'email' => 'teszt@teszt.hu',
            'password' => Hash::make('password123'),
            'is_admin' => false,
        ]);

        User::create([
            'name' => 'Admin Felhasználó',
            'email' => 'admin@smartbooking.hu',
            'password' => Hash::make('admin123'),
            'is_admin' => true,
        ]);

        Hotel::factory(500)->create()->each(function ($hotel) {

            $roomCount = rand(5, 30);
            Room::factory($roomCount)->create([
                'hotel_id' => $hotel->id
            ]);

            Review::factory(rand(3, 12))->create([
                'hotel_id' => $hotel->id
            ]);

            $avgRating = Review::where('hotel_id', $hotel->id)->avg('rating');
            $hotel->update(['rating' => round($avgRating, 1)]);
        });
    }
}
