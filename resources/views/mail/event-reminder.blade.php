@if ($window === '3d')
# {{ $eventData['title'] }} is in 3 days

You're registered for **{{ $eventData['title'] }}**. It starts in about three days.
@else
# {{ $eventData['title'] }} is tomorrow

You're registered for **{{ $eventData['title'] }}**. It starts in about 24 hours.
@endif

**When:** {{ \Carbon\Carbon::createFromTimestamp($eventData['starts_at'])->utc()->format('D, M j, Y g:i A') }} UTC

**Where:** {{ $eventData['location']['label'] }}

@component('mail::button', ['url' => url('/events/'.$event->id)])
View event
@endcomponent

Thanks,<br>
{{ config('app.name') }}
