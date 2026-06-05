<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class BulkMarketingMail extends Mailable
{
    use Queueable, SerializesModels;

    public User $user;
    public string $messageText;

    public function __construct(User $user, string $messageText)
    {
        $this->user = $user;
        $this->messageText = $messageText;
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: config('app.name') . ' - عرض خاص',
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.bulk-marketing',
        );
    }
}
