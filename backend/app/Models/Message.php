<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Message extends Model
{
    protected $fillable = ['hotel_id', 'user_id', 'sender_id', 'message', 'is_read'];

    public function hotel() { return $this->belongsTo(Hotel::class); }
    public function user() { return $this->belongsTo(User::class, 'user_id'); }
    public function sender() { return $this->belongsTo(User::class, 'sender_id'); }
}
