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
            'role' => 'user',
        ]);

        User::create([
            'name' => 'Főadmin',
            'email' => 'admin@smartbooking.hu',
            'password' => Hash::make('admin123'),
            'role' => 'super_admin',
        ]);

        User::create([
            'name' => 'Admin Felhasználó',
            'email' => 'admin2@smartbooking.hu',
            'password' => Hash::make('admin123'),
            'role' => 'admin',
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

        // Első hotel admin hozzárendelése demóhoz
        $firstHotel = Hotel::first();
        if ($firstHotel) {
            User::create([
                'name' => 'Hotel Manager',
                'email' => 'manager@smartbooking.hu',
                'password' => Hash::make('manager123'),
                'role' => 'hotel_admin',
                'managed_hotel_id' => $firstHotel->id,
            ]);
        }
    }
}
