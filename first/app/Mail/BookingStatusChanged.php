<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class BookingStatusChanged extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     */
    public $booking;

    public function __construct($booking)
    {
        $this->booking = $booking;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Your Booking Status Has Changed',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        // Removed the build() method and moved its logic here
        // The view 'emails.booking_status_changed' is now correctly specified here.
        return new Content(
            view: 'emails.booking_status_changed',
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [];
    }

    // The build() method is removed as content() is now used for view definition.
    // public function build()
    // {
    //     return $this->subject('Your Booking Status Has Changed')
    //         ->view('emails.booking_status_changed');
    // }
}
