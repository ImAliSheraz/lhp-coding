<?php

namespace App\Console\Commands;

use App\Mail\EventReminderMail;
use App\Models\Event;
use App\Models\EventAttendee;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Mail;

class SendEventReminders extends Command
{
    protected $signature = 'events:send-reminders';

    protected $description = 'Send 3-day and 24-hour event reminder emails';

    public function handle(): int
    {
        $this->sendWindow('3d', now()->addDays(3), 'reminded_3d_at');
        $this->sendWindow('24h', now()->addDay(), 'reminded_24h_at');

        return self::SUCCESS;
    }

    private function sendWindow(string $window, Carbon $day, string $flag): void
    {
        $events = Event::query()
            ->where('status', 'published')
            ->whereBetween('created_time', [$day->copy()->startOfDay()->timestamp, $day->copy()->endOfDay()->timestamp])
            ->get();

        foreach ($events as $event) {
            EventAttendee::query()
                ->where('event_id', $event->id)
                ->whereNull($flag)
                ->each(function (EventAttendee $attendee) use ($event, $window, $flag): void {
                    Mail::to($attendee->email)->queue(new EventReminderMail($event, $attendee, $window));
                    $attendee->update([$flag => now()]);
                });
        }
    }
}
