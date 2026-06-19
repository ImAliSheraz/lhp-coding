<?php

use App\Mail\AttendeeRegisteredMail;
use App\Models\Event;
use App\Models\EventAttendee;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;

uses(RefreshDatabase::class);

it('returns enriched visual event data with filters', function () {
    $user = User::factory()->create();
    Event::factory()->for($user)->create([
        'status' => 'published',
        'created_time' => strtotime('2026-07-01 18:00:00'),
        'latitude' => 40.7128,
        'longitude' => -74.0060,
        'payload' => [
            'name' => 'Harbor Jazz Night',
            'description' => 'An evening of live jazz.',
            'venue' => ['name' => 'Riverside Hall'],
            'schedule' => ['starts_at' => strtotime('2026-07-01 18:00:00'), 'ends_at' => strtotime('2026-07-01 21:00:00')],
        ],
    ]);

    Event::factory()->for($user)->create(['status' => 'draft']);

    $this->getJson(route('events.visual-data', [
        'from' => '2026-07-01',
        'to' => '2026-07-01',
        'city' => 'new-york',
    ]))
        ->assertOk()
        ->assertJsonPath('total', 1)
        ->assertJsonPath('data.0.title', 'Harbor Jazz Night')
        ->assertJsonPath('data.0.location.city', 'New York, US')
        ->assertJsonCount(2, 'data.0.images');
});

it('registers an attendee and queues a confirmation email', function () {
    Mail::fake();

    $user = User::factory()->create();
    $event = Event::factory()->for($user)->create(['status' => 'published']);

    $this->post(route('events.attendees.store', $event), [
        'email' => 'fan@example.test',
        'name' => 'Fan',
    ])->assertRedirect();

    expect(EventAttendee::count())->toBe(1);
    Mail::assertQueued(AttendeeRegisteredMail::class);
});

it('renders visual pages with shared props', function () {
    $this->get(route('events.visual1'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('Events/VisualOne')
            ->has('cities', 15)
        );

    $this->get(route('events.visual2'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('Events/VisualTwo')
            ->has('cities', 15)
        );
});
