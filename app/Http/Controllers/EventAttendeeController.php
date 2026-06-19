<?php

namespace App\Http\Controllers;

use App\Mail\AttendeeRegisteredMail;
use App\Models\Event;
use App\Models\EventAttendee;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Inertia\Inertia;

class EventAttendeeController extends Controller
{
    public function store(Request $request, Event $event): RedirectResponse
    {
        $validated = $request->validate([
            'email' => ['required', 'email', 'max:255'],
            'name' => ['nullable', 'string', 'max:255'],
        ]);

        $attendee = EventAttendee::firstOrCreate(
            ['event_id' => $event->id, 'email' => $validated['email']],
            ['name' => $validated['name'] ?? null],
        );

        if ($attendee->wasRecentlyCreated) {
            Mail::to($attendee->email)->send(new AttendeeRegisteredMail($event, $attendee));
            Inertia::flash('toast', ['type' => 'success', 'message' => 'You are on the attendee list. A confirmation email has been sent.']);
        } else {
            Inertia::flash('toast', ['type' => 'info', 'message' => 'You are already registered for this event.']);
        }

        return back();
    }
}
