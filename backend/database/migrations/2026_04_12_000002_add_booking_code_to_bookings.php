<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->string('booking_code', 10)->nullable()->unique()->after('id');
        });

        // Generate codes for existing bookings
        $bookings = \App\Models\Booking::whereNull('booking_code')->get();
        foreach ($bookings as $booking) {
            $booking->booking_code = $this->generateCode();
            $booking->save();
        }
    }

    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->dropColumn('booking_code');
        });
    }

    private function generateCode(): string
    {
        do {
            $code = strtoupper(Str::random(2)) . $this->randomDigits(3) . strtoupper(Str::random(2));
        } while (\App\Models\Booking::where('booking_code', $code)->exists());

        return $code;
    }

    private function randomDigits(int $length): string
    {
        $digits = '';
        for ($i = 0; $i < $length; $i++) {
            $digits .= rand(0, 9);
        }
        return $digits;
    }
};
