<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Event extends Model
{
    use HasFactory, HasUuids;

    private const IMAGE_POOL = [
        '/images/events/event-1.svg',
        '/images/events/event-2.svg',
        '/images/events/event-3.svg',
        '/images/events/event-4.svg',
    ];

    protected $guarded = [];

    protected $casts = [
        'payload' => 'array',
        'latitude' => 'float',
        'longitude' => 'float',
    ];

    public function newUniqueId(): string
    {
        return (string) Str::uuid();
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function attendees(): HasMany
    {
        return $this->hasMany(EventAttendee::class);
    }

    public function title(): string
    {
        return (string) ($this->payload['name'] ?? 'Untitled event');
    }

    public function description(): string
    {
        return (string) ($this->payload['description'] ?? '');
    }

    public function venueName(): ?string
    {
        $venue = $this->payload['venue'] ?? null;

        return is_array($venue) ? ($venue['name'] ?? null) : null;
    }

    public function startsAt(): int
    {
        return (int) ($this->payload['schedule']['starts_at'] ?? $this->created_time ?? 0);
    }

    public function endsAt(): int
    {
        return (int) ($this->payload['schedule']['ends_at'] ?? ($this->startsAt() + 7200));
    }

    /** @return list<string> */
    public function imagePaths(): array
    {
        $pool = self::IMAGE_POOL;
        $hash = crc32($this->id);
        $first = $pool[$hash % count($pool)];
        $second = $pool[($hash >> 8) % count($pool)];

        if ($second === $first) {
            $second = $pool[((int) ($hash >> 8) + 1) % count($pool)];
        }

        return [$first, $second];
    }
}
