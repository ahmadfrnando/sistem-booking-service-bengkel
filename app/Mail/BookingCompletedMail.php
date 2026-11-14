<?php

namespace App\Mail;

use App\Models\Booking;
use App\Models\Payment;
use Illuminate\Mail\Attachment;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

class BookingCompletedMail extends Mailable
{
    public function __construct(
        public Booking $booking,
        public Payment $payment,
        protected string $invoicePdf,
        protected string $receiptPdf,
    ) {
    }

    public function envelope(): Envelope
    {
        return new Envelope(subject: 'Service Selesai & Lunas - '.$this->booking->code);
    }

    public function content(): Content
    {
        return new Content(markdown: 'emails.booking-completed');
    }

    public function attachments(): array
    {
        return [
            Attachment::fromData(fn () => $this->invoicePdf, 'Invoice-'.$this->booking->code.'.pdf')->withMime('application/pdf'),
            Attachment::fromData(fn () => $this->receiptPdf, 'Struk-'.$this->booking->code.'.pdf')->withMime('application/pdf'),
        ];
    }
}
