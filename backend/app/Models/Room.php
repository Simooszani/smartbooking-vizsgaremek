<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Room extends Model {
    use HasFactory;
    protected $fillable = ['hotel_id', 'type', 'capacity', 'price_per_night'];
    protected $appends = ['room_number', 'has_duplicates'];

    public function hotel()
    {
        return $this->belongsTo(Hotel::class);
    }

    public function getRoomNumberAttribute()
    {
        return self::where('hotel_id', $this->hotel_id)
            ->where('type', $this->type)
            ->where('id', '<=', $this->id)
            ->count();
    }

    public function getHasDuplicatesAttribute()
    {
        return self::where('hotel_id', $this->hotel_id)
            ->where('type', $this->type)
            ->count() > 1;
    }
}