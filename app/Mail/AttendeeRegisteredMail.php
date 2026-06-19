<?php

namespace App\Mail;

use App\Http\Resources\EventVisualResource;
use App\Models\Event;
use App\Models\EventAttendee;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class AttendeeRegisteredMail extends Mailable
{
    use Queueable, SerializesModels;

    /** @var array<string, mixed> */
    public array $eventData;

    public function __construct(public Event $event, public EventAttendee $attendee)
    {
        $this->eventData = (new EventVisualResource($event))->resolve();
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Confirmed: '.$this->event->title(),
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'mail.attendee-registered',
        );
    }
}
