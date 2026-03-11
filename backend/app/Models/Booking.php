<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Booking extends Model
{
    protected $fillable = [
        'name',
        'email',
        'booking_date',
        'guests',
        'note'
    ];

    protected $appends = ['can_cancel'];

    public function getCanCancelAttribute()
    {
        $checkIn = \Carbon\Carbon::parse($this->check_in);
        return now()->diffInDays($checkIn, false) >= 7;
    }
}
