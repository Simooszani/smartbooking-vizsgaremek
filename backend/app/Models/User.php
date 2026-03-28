<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'managed_hotel_id',
        'suspended_until',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $appends = ['is_admin'];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'suspended_until' => 'datetime',
        ];
    }

    public function getIsAdminAttribute(): bool
    {
        return in_array($this->role, ['admin', 'super_admin']);
    }

    public function isSuperAdmin(): bool
    {
        return $this->role === 'super_admin';
    }

    public function isAdmin(): bool
    {
        return in_array($this->role, ['admin', 'super_admin']);
    }

    public function isHotelAdmin(): bool
    {
        return $this->role === 'hotel_admin';
    }

    public function managedHotel()
    {
        return $this->belongsTo(Hotel::class, 'managed_hotel_id');
    }

    public function warnings()
    {
        return $this->hasMany(Warning::class);
    }

    public function isSuspended(): bool
    {
        return $this->suspended_until && $this->suspended_until->isFuture();
    }
}
