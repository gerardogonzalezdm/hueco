<?php

namespace App\Mail;

use App\Models\Booking;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class BookingConfirmedMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public Booking $booking)
    {
    }

    public function envelope(): Envelope
    {
        $space = $this->booking->space?->name ?? 'el espacio';

        return new Envelope(
            subject: "Reserva confirmada — {$space}",
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.booking-confirmed',
            with: [
                'booking' => $this->booking,
                'space' => $this->booking->space,
                'company' => $this->booking->company,
            ],
        );
    }
}
