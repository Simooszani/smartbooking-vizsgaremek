<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Str;

class Booking extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'room_id',
        'check_in',
        'check_out',
        'guests',
        'status',
        'booking_code'
    ];

    protected static function booted()
    {
        static::creating(function ($booking) {
            if (!$booking->booking_code) {
                $booking->booking_code = self::generateBookingCode();
            }
        });
    }

    public static function generateBookingCode(): string
    {
        do {
            $code = strtoupper(Str::random(2)) . rand(100, 999) . strtoupper(Str::random(2));
        } while (self::where('booking_code', $code)->exists());

        return $code;
    }

    public function room()
    {
        return $this->belongsTo(Room::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    protected $appends = ['can_cancel'];

    public function getCanCancelAttribute()
    {
        if (!$this->check_in) return false;
        $checkIn = \Carbon\Carbon::parse($this->check_in);
        return now()->diffInDays($checkIn, false) >= 7;
    }
}