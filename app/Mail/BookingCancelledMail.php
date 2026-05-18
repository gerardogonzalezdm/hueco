<?php

namespace App\Mail;

use App\Models\Booking;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class BookingCancelledMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public Booking $booking)
    {
    }

    public function envelope(): Envelope
    {
        $space = $this->booking->space?->name ?? 'el espacio';

        return new Envelope(
            subject: "Reserva cancelada — {$space}",
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.booking-cancelled',
            with: [
                'booking' => $this->booking,
                'space' => $this->booking->space,
                'company' => $this->booking->company,
            ],
        );
    }
}
