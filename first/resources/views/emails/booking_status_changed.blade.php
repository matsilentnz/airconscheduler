<!-- filepath: resources/views/emails/booking_status_changed.blade.php -->
<p>Dear {{ $booking->customer->name }},</p>
<p>Your booking status has been updated to: <strong>{{ $booking->status }}</strong>.</p>
<p>Thank you for using our service!</p>
