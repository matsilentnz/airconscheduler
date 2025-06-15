<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use App\Mail\BookingStatusChanged;
use Illuminate\Support\Facades\Mail;

class BookingController extends Controller
{
    /**
     * Create a new booking.
     */
    public function createBooking(Request $request)
{
    // Add detailed logging
    Log::info('Booking request received', $request->all());

    $validator = Validator::make($request->all(), [
        'date' => 'required|date|after_or_equal:today',
        'time' => 'required|in:10:00,12:00,14:00,16:00',
        'service_type' => 'required|string|max:255',
        'aircon_type' => ['required', 'string', function ($attribute, $value, $fail) {
    if (count(explode(',', $value)) > 5) {
        $fail('You can enter up to 5 air conditioner types.');
    }
}],

        'recurring_service' => 'required|in:Yes,No',
    ]);

    if ($validator->fails()) {
        Log::error('Validation failed', $validator->errors()->toArray());
        return response()->json([
            'message' => 'Validation failed',
            'errors' => $validator->errors()
        ], 422);
    }

    try {
        // Debug before checking availability
        Log::debug('Checking availability for:', [
            'date' => $request->date,
            'time' => $request->time
        ]);

        $existingBookingsCount = Booking::where('date', $request->date)
            ->where('time', $request->time)
            ->count();

        if ($existingBookingsCount >= 2) {
            Log::warning('Time slot full', [
                'date' => $request->date,
                'time' => $request->time,
                'count' => $existingBookingsCount
            ]);
            return response()->json([
                'message' => 'This time slot is already fully booked.'
            ], 422);
        }

        $customer = Auth::user();
        if (!$customer) {
            Log::error('No authenticated user');
            return response()->json([
                'message' => 'Authentication required'
            ], 401);
        }

        $booking = Booking::create([
            'customer_id' => $customer->id,
            'date' => $request->date,
            'time' => $request->time,
            'service_type' => $request->service_type,
            'aircon_type' => $request->aircon_type,
            'recurring_service' => $request->recurring_service,
            'status' => 'Booked'
        ]);

        Log::info('Booking created successfully', ['booking_id' => $booking->id]);

        return response()->json([
            'message' => 'Booking created successfully',
            'booking' => $booking
        ], 201);

    } catch (\Exception $e) {
        Log::error('Booking creation error: ' . $e->getMessage());
        Log::error('Stack trace: ' . $e->getTraceAsString());
        return response()->json([
            'message' => 'Server error occurred while creating booking'
        ], 500);
    }
}

    /**
     * Check booking availability for a date.
     */
    public function checkAvailability(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'date' => 'required|date'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $date = $request->date;
        $timeSlots = ['10:00', '12:00', '14:00', '16:00'];

        $availability = [];
        foreach ($timeSlots as $time) {
            $count = Booking::where('date', $date)
                ->where('time', $time)
                ->count();

            $availability[] = [
                'time' => $time,
                'count' => $count
            ];
        }

        return response()->json($availability);
    }

    /**
     * Get all bookings for the authenticated customer.
     */
    public function getBookings()
    {
        try {
            $customer = Auth::user();
            $bookings = Booking::where('customer_id', $customer->id)
            ->where('status', '!=', 'Completed')
                ->where('status', '!=', 'Verified')
                ->orderBy('date', 'asc')
                ->orderBy('time', 'asc')
                ->get();

            return response()->json($bookings);
        } catch (\Exception $e) {
            Log::error('Error fetching bookings: ' . $e->getMessage());
            return response()->json([
                'message' => 'Failed to fetch bookings. Please try again.'
            ], 500);
        }
    }

    /**
     * Get upcoming bookings for the authenticated customer.
     */
    public function getUpcomingBookings()
    {
        try {
            $customer = Auth::user();
            $bookings = Booking::where('customer_id', $customer->id)
                ->where('date', '>=', now()->toDateString())
                ->where('status', '!=', 'Completed')
                ->where('status', '!=', 'Cancelled')
                ->where('status', '!=', 'Verified')
                ->orderBy('date', 'asc')
                ->orderBy('time', 'asc')
                ->get();

            return response()->json($bookings);
        } catch (\Exception $e) {
            Log::error('Error fetching upcoming bookings: ' . $e->getMessage());
            return response()->json([
                'message' => 'Failed to fetch upcoming bookings. Please try again.'
            ], 500);
        }
    }

    /**
     * Cancel a booking.
     */
    public function cancelBooking(Request $request, $id)
    {
        try {
            $customer = Auth::user();
            $booking = Booking::where('id', $id)
                ->where('customer_id', $customer->id)
                ->first();

            if (!$booking) {
                return response()->json([
                    'message' => 'Booking not found or you do not have permission to cancel it.'
                ], 404);
            }

            // Check if the booking is within 24 hours
        $currentDateTime = now();
        $bookingDateTime = $booking->date . ' ' . $booking->time;

        if ($currentDateTime->diffInHours($bookingDateTime, false) < 24) {
            return response()->json([
                'message' => 'You cannot cancel a booking within 24 hours of the scheduled time.'
            ], 422);
        }

            // Delete the booking instead of changing status
            $booking->delete();

            return response()->json([
                'message' => 'Booking cancelled and removed successfully',
                'booking_id' => $id
            ]);
        } catch (\Exception $e) {
            Log::error('Error cancelling booking: ' . $e->getMessage());
            return response()->json([
                'message' => 'Failed to cancel booking. Please try again.'
            ], 500);
        }
    }

    public function getAllBookingsForAdmin()
{
    try {
        $bookings = Booking::with(['customer' => function($query) {
            $query->select('id', 'name', 'email', 'phone', 'address');
        }])
        ->where('status', '!=', 'Completed')
        ->where('status', '!=', 'Verified')
        ->orderBy('date', 'desc')
        ->get();

        return response()->json($bookings);
    } catch (\Exception $e) {
        Log::error('Error fetching bookings for admin: ' . $e->getMessage());
        return response()->json(['message' => 'Failed to fetch bookings'], 500);
    }
}
    /**
     * Update booking status
     */
    public function updateStatus(Request $request, $id)
{
    try {
        $booking = Booking::findOrFail($id);
        $oldStatus = $booking->status; // Store the old status before updating

        $validated = $request->validate([
            'status' => 'required|in:Booked,In Progress,Completed'
        ]);

        // Only allow adding completion details if status is being changed to Completed
        if ($validated['status'] === 'Completed') {
            $validated = array_merge($validated, $request->validate([
                'total_price' => 'required|numeric|min:0',
                'notes' => 'nullable|string',
            ]));

            // Create recurring booking if applicable
            if ($booking->recurring_service === 'Yes' && $booking->service_type === 'AC Maintenance') {
                $newBookingDate = \Carbon\Carbon::parse($booking->date)->addMonths(3);

                Booking::create([
                    'customer_id' => $booking->customer_id,
                    'date' => $newBookingDate->toDateString(),
                    'time' => $booking->time,
                    'service_type' => $booking->service_type,
                    'aircon_type' => $booking->aircon_type,
                    'recurring_service' => $booking->recurring_service,
                    'status' => 'Booked',
                ]);

                Log::info('Recurring booking created successfully', [
                    'customer_id' => $booking->customer_id,
                    'new_booking_date' => $newBookingDate->toDateString(),
                ]);
            }
        }

        $booking->update($validated);
        $booking->load('customer');

        // Send email notification if status changed
        if ($oldStatus !== $booking->status) {
            Mail::to($booking->customer->email)
                ->send(new BookingStatusChanged ($booking, $oldStatus));

            Log::info('Status change email sent', [
                'booking_id' => $booking->id,
                'old_status' => $oldStatus,
                'new_status' => $booking->status,
                'customer_email' => $booking->customer->email
            ]);
        }

        return response()->json([
            'message' => 'Booking status updated successfully',
            'booking' => $booking
        ]);
    } catch (\Exception $e) {
        Log::error('Error updating booking status: ' . $e->getMessage());
        return response()->json(['message' => 'Failed to update status'], 500);
    }
}
    /**
 * Get all completed bookings for admin
 */
public function getCompletedBookingsForAdmin(Request $request)
{
    try {
        $query = Booking::with(['customer' => function($query) {
            $query->select('id', 'name', 'email', 'phone', 'address');
        }])
        ->where('status','Completed')
        ->orWhere('status', 'Verified')
        ->orderBy('date', 'desc');

        // Add search functionality
        if ($request->has('search')) {
            $search = $request->input('search');
            $query->whereHas('customer', function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%");
            });
        }

        // Add pagination
        $perPage = $request->input('per_page', 10); // Default to 10 items per page
        $bookings = $query->paginate($perPage);

        return response()->json($bookings);
    } catch (\Exception $e) {
        Log::error('Error fetching completed bookings: ' . $e->getMessage());
        return response()->json(['message' => 'Failed to fetch completed bookings'], 500);
    }
}

public function updateCompletedBooking(Request $request, $id)
{
    try {
        $booking = Booking::findOrFail($id);

        // Only allow updates if booking is completed
        if ($booking->status !== 'Completed') {
            return response()->json([
                'message' => 'Only completed bookings can be updated'
            ], 422);
        }

        $validated = $request->validate([
            'total_price' => 'required|numeric|min:0',
            'notes' => 'nullable|string',
        ]);

        if ($booking->recurring_service === 'Yes' && $booking->service_type === 'AC Maintenance') {
            $newBookingDate = \Carbon\Carbon::parse($booking->date)->addMonths(3);

            Booking::create([
                'customer_id' => $booking->customer_id,
                'date' => $newBookingDate->toDateString(),
                'time' => $booking->time,
                'service_type' => $booking->service_type,
                'aircon_type' => $booking->aircon_type,
                'recurring_service' => $booking->recurring_service,
                'status' => 'Booked',
            ]);

            Log::info('Recurring booking created successfully', [
                'customer_id' => $booking->customer_id,
                'new_booking_date' => $newBookingDate->toDateString(),
            ]);
            $booking->status = 'Verified';
        }

        $booking->update($validated);

        return response()->json([
            'message' => 'Completed booking updated successfully',
            'booking' => $booking
        ]);
    } catch (\Exception $e) {
        Log::error('Error updating completed booking: ' . $e->getMessage());
        return response()->json(['message' => 'Failed to update booking'], 500);
    }
}

public function getCompletedBookings()
{
    try {
        $customer = Auth::user();

        if (!$customer) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        $completedBookings = Booking::where('customer_id', $customer->id)
            ->where('status', 'Completed')
            ->orWhere('status', 'Verified')
            ->orderBy('date', 'desc')
            ->get();

        return response()->json($completedBookings);
    } catch (\Exception $e) {
        Log::error('Error fetching completed bookings: ' . $e->getMessage());
        return response()->json(['message' => 'Failed to fetch completed bookings. Please try again.'], 500);
    }
}

public function getBookingTotals()
{
    try {
        $totalBooked = Booking::where('status', 'Booked')->count();
        $totalInProgress = Booking::where('status', 'In Progress')->count();

        return response()->json([
            'totalBooked' => $totalBooked,
            'totalInProgress' => $totalInProgress,
        ]);
    } catch (\Exception $e) {
        Log::error('Error fetching booking totals: ' . $e->getMessage());
        return response()->json(['message' => 'Failed to fetch booking totals'], 500);
    }
}

public function assignTechnician(Request $request, $id)
{
    try {
        Log::info('Assigning technician', ['booking_id' => $id, 'request' => $request->all()]);

        $booking = Booking::findOrFail($id);

        $validated = $request->validate([
            'technician_id' => 'nullable|exists:technicians,id'
        ]);

        $booking->update($validated);

        Log::info('Technician assigned successfully', [
            'booking_id' => $id,
            'technician_id' => $validated['technician_id']
        ]);

        return response()->json([
            'message' => 'Technician assigned successfully',
            'booking' => $booking->load(['customer', 'technician'])
        ]);
    } catch (\Exception $e) {
        Log::error('Error assigning technician: ' . $e->getMessage());
        return response()->json(['message' => 'Failed to assign technician'], 500);
    }
}

public function getTechnicianBookings(Request $request)
{
    try {
        $technician = $request->user();

        $bookings = Booking::with(['customer' => function($query) {
            $query->select('id', 'name', 'email', 'phone', 'address');
        }])
        ->where('technician_id', $technician->id)
        ->where('status', '!=', 'Completed')
        ->where('status', '!=', 'Verified')
        ->orderBy('date', 'asc')
        ->get();

        return response()->json($bookings);
    } catch (\Exception $e) {
        Log::error('Error fetching technician bookings: ' . $e->getMessage());
        return response()->json(['message' => 'Failed to fetch bookings'], 500);
    }
}

}
