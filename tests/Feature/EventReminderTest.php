<?php

use App\Mail\EventReminderMail;
use App\Models\Event;
use App\Models\EventAttendee;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;

uses(RefreshDatabase::class);

it('queues reminder emails for events starting in three days and one day', function () {
    Mail::fake();

    $user = User::factory()->create();
    $startsInThreeDays = now()->addDays(3)->startOfDay()->addHours(10)->timestamp;
    $startsInOneDay = now()->addDay()->startOfDay()->addHours(14)->timestamp;

    $event3d = Event::factory()->for($user)->create([
        'status' => 'published',
        'created_time' => $startsInThreeDays,
        'payload' => [
            'name' => 'Three Day Jazz Night',
            'description' => 'Soon.',
            'schedule' => ['starts_at' => $startsInThreeDays, 'ends_at' => $startsInThreeDays + 7200],
        ],
    ]);

    $event24h = Event::factory()->for($user)->create([
        'status' => 'published',
        'created_time' => $startsInOneDay,
        'payload' => [
            'name' => 'Tomorrow Meetup',
            'description' => 'Very soon.',
            'schedule' => ['starts_at' => $startsInOneDay, 'ends_at' => $startsInOneDay + 7200],
        ],
    ]);

    $attendee3d = EventAttendee::create([
        'event_id' => $event3d->id,
        'email' => 'three-days@example.test',
        'name' => 'Three Day Fan',
    ]);

    $attendee24h = EventAttendee::create([
        'event_id' => $event24h->id,
        'email' => 'one-day@example.test',
        'name' => 'Tomorrow Fan',
    ]);

    $this->artisan('events:send-reminders')->assertSuccessful();

    Mail::assertQueued(EventReminderMail::class, function (EventReminderMail $mail) use ($attendee3d) {
        return $mail->hasTo($attendee3d->email) && $mail->window === '3d';
    });

    Mail::assertQueued(EventReminderMail::class, function (EventReminderMail $mail) use ($attendee24h) {
        return $mail->hasTo($attendee24h->email) && $mail->window === '24h';
    });

    expect($attendee3d->fresh()->reminded_3d_at)->not->toBeNull();
    expect($attendee24h->fresh()->reminded_24h_at)->not->toBeNull();
});

it('does not send duplicate reminders for the same attendee', function () {
    Mail::fake();

    $user = User::factory()->create();
    $startsInThreeDays = now()->addDays(3)->startOfDay()->addHours(10)->timestamp;

    $event = Event::factory()->for($user)->create([
        'status' => 'published',
        'created_time' => $startsInThreeDays,
        'payload' => [
            'name' => 'Repeat Reminder Test',
            'schedule' => ['starts_at' => $startsInThreeDays, 'ends_at' => $startsInThreeDays + 7200],
        ],
    ]);

    EventAttendee::create([
        'event_id' => $event->id,
        'email' => 'repeat@example.test',
    ]);

    $this->artisan('events:send-reminders')->assertSuccessful();
    $this->artisan('events:send-reminders')->assertSuccessful();

    Mail::assertQueued(EventReminderMail::class, 1);
});
