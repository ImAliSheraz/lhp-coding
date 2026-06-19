@php
    $when = \Carbon\Carbon::createFromTimestamp($eventData['starts_at'])
        ->utc()
        ->format('l, F j, Y \a\t g:i A') . ' UTC';
@endphp

<x-mail::panel>
**When:** {{ $when }}

**Where:** {{ $eventData['location']['label'] }}
</x-mail::panel>
