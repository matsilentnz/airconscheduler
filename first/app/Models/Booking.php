<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Booking extends Model
{
    use HasFactory;

    protected $fillable = [
        'customer_id',
        'technician_id',
        'date',
        'time',
        'service_type',
        'aircon_type',
        'recurring_service',
        'status',
        'total_price',
        'notes'
    ];

    /**
     * Get the customer that owns the booking.
     */
    public function customer()
{
    return $this->belongsTo(Customer::class)->select(['id', 'name', 'email', 'phone', 'address']);
}
    /**
     * Get the technician assigned to the booking.
     */
    public function technician()
{
    return $this->belongsTo(Technician::class)->select(['id', 'name']);
}
}
