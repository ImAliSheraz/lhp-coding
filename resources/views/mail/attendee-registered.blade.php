# You're on the list for {{ $eventData['title'] }}

Hi{{ $attendee->name ? ' '.$attendee->name : '' }},

Thanks for registering your interest in **{{ $eventData['title'] }}**.

**When:** {{ \Carbon\Carbon::createFromTimestamp($eventData['starts_at'])->utc()->format('D, M j, Y g:i A') }} UTC

**Where:** {{ $eventData['location']['label'] }}

We'll send reminders as the event gets closer.

@component('mail::button', ['url' => url('/events/'.$event->id)])
View event
@endcomponent

Thanks,<br>
{{ config('app.name') }}
