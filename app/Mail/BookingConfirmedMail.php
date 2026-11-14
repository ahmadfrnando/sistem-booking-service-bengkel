<?php

namespace App\Mail;

use App\Models\Booking;
use App\Models\Payment;
use Illuminate\Mail\Attachment;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

class BookingConfirmedMail extends Mailable
{
    public function __construct(
        public Booking $booking,
        public Payment $payment,
        protected string $invoicePdf,
    ) {
    }

    public function envelope(): Envelope
    {
        return new Envelope(subject: 'Booking Terkonfirmasi - '.$this->booking->code);
    }

    public function content(): Content
    {
        return new Content(markdown: 'emails.booking-confirmed');
    }

    public function attachments(): array
    {
        return [
            Attachment::fromData(fn () => $this->invoicePdf, 'Invoice-DP-'.$this->booking->code.'.pdf')->withMime('application/pdf'),
        ];
    }
}
