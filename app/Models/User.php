<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

#[Fillable(['gender_id', 'name', 'username', 'email', 'phone', 'password', 'photo', 'role', 'disabled'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasApiTokens, HasFactory, Notifiable;

    // Relationships gender to user
    public function gender()
    {
        return $this->belongsTo(Gender::class);
    }
    public function buildings()
    {
        return $this->hasMany(Building::class, 'created_by');
    }
    public function bookings()
    {
        return $this->hasMany(Booking::class, 'created_by');
    }

    public function tenantBookings()
    {
        return $this->hasMany(Booking::class, 'tenant_id');
    }

    public function payments()
    {
        return $this->hasMany(Payment::class, 'created_by');
    }

    public function bills()
    {
        return $this->hasMany(Bill::class, 'created_by');
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }
}
