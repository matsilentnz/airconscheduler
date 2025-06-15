<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Sanctum\HasApiTokens;

class Technician extends Authenticatable
{
    use HasApiTokens;
    protected $fillable = ['name', 'email', 'password'];
    protected $hidden = ['password'];

    public function bookings()
{
    return $this->hasMany(Booking::class);
}
}
