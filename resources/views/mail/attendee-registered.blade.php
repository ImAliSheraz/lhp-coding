<x-mail::message>
# You're on the list!

Hi{{ $attendee->name ? ' '.$attendee->name : '' }},

Thanks for registering for **{{ $eventData['title'] }}**. You're confirmed on the attendee list and we'll send reminders as the event gets closer.

@include('mail.partials.event-details')

<x-mail::button :url="url('/events/'.$event->id)">
View event details
</x-mail::button>

Thanks,<br>
{{ config('mail.from.name', config('app.name')) }}
</x-mail::message>
