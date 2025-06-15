<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class BookingController extends Controller
{
    public function getAllBookingsForAdmin()
{
    try {
        $bookings = Booking::with('customer')->orderBy('date', 'desc')->get();

        return response()->json($bookings);
    } catch (\Exception $e) {
        Log::error('Error fetching bookings for admin: ' . $e->getMessage());
        return response()->json(['message' => 'Failed to fetch bookings'], 500);
    }
}
}
