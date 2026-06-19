<?php

namespace App\Mail;

use App\Http\Resources\EventVisualResource;
use App\Models\Event;
use App\Models\EventAttendee;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class EventReminderMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    /** @var array<string, mixed> */
    public array $eventData;

    public function __construct(
        public Event $event,
        public EventAttendee $attendee,
        public string $window,
    ) {
        $this->eventData = (new EventVisualResource($event))->resolve();
    }

    public function envelope(): Envelope
    {
        $label = $this->window === '3d' ? '3 days' : '24 hours';

        return new Envelope(
            subject: "Reminder ({$label}): ".$this->event->title(),
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'mail.event-reminder',
        );
    }
}
