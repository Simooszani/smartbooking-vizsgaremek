<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Booking extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'room_id',
        'check_in',
        'check_out',
        'guests',
        'status'
    ];

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