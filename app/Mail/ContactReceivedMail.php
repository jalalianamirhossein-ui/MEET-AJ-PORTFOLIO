<?php

namespace App\Mail;

use App\Models\Request as ContactRequest;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

class ContactReceivedMail extends Mailable
{
    public function __construct(public ContactRequest $contact) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'New contact request #'.$this->contact->id,
        );
    }

    public function content(): Content
    {
        return new Content(htmlString: $this->body());
    }

    private function body(): string
    {
        $contact = $this->contact;
        $contact->loadMissing('service');
        $serviceTitle = $contact->service?->title ?: '—';

        return '<p>A contact request was stored in the CMS.</p>'
            .'<p>ID: '.e((string) $contact->id).'<br>'
            .'Service: '.e($serviceTitle).'<br>'
            .'Subject: '.e((string) $contact->subject).'<br>'
            .'Name: '.e((string) $contact->name).'<br>'
            .'Email: '.e((string) $contact->email).'<br>'
            .'Phone: '.e((string) ($contact->phone ?: '—')).'</p>'
            .'<p>'.nl2br(e((string) $contact->message)).'</p>'
            .'<p>Open Filament → Requests to review it. Database storage is authoritative; this email is a notification only.</p>';
    }
}
