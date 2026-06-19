<x-mail::message>
@if ($window === '3d')
# {{ $eventData['title'] }} is in 3 days
@else
# {{ $eventData['title'] }} is tomorrow
@endif

Hi{{ $attendee->name ? ' '.$attendee->name : '' }},

@if ($window === '3d')
This is a friendly reminder that you're registered for **{{ $eventData['title'] }}**, starting in about three days.
@else
This is a friendly reminder that **{{ $eventData['title'] }}** starts in about 24 hours.
@endif

@include('mail.partials.event-details')

<x-mail::button :url="url('/events/'.$event->id)">
View event details
</x-mail::button>

Thanks,<br>
{{ config('mail.from.name', config('app.name')) }}
</x-mail::message>
