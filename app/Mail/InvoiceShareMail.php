<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Queue\SerializesModels;

class InvoiceShareMail extends Mailable
{
    use Queueable, SerializesModels;

    public $invoice;
    public $customMessage;
    public $pdfData;
    public $pdfFilename;
    public $emailSubject;

    /**
     * Create a new message instance.
     */
    public function __construct($invoice, $customMessage, $pdfData, $pdfFilename, $emailSubject = null)
    {
        $this->invoice = $invoice;
        $this->customMessage = $customMessage;
        $this->pdfData = $pdfData;
        $this->pdfFilename = $pdfFilename;
        $this->emailSubject = $emailSubject ?: ('Tax Invoice ' . ($invoice->invoice_no ?: ('INV-' . $invoice->id)));
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: $this->emailSubject,
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.invoice_share',
            with: [
                'invoice' => $this->invoice,
                'customMessage' => $this->customMessage,
                'pdfFilename' => $this->pdfFilename,
                'invoiceNo' => $this->invoice->invoice_no ?: ('INV-' . $this->invoice->id),
                'customerName' => $this->invoice->customer_name ?: 'Valued Customer',
                'totalAmount' => number_format($this->invoice->grand_total, 2),
                'invoiceDate' => $this->invoice->invoice_date ?: date('Y-m-d'),
            ],
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [
            Attachment::fromData(fn () => $this->pdfData, $this->pdfFilename)
                ->withMime('application/pdf'),
        ];
    }
}
