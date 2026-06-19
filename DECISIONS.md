# Decisions

## Layouts
- **Visual 1** uses a responsive card grid for discovery-style browsing.
- **Visual 2** uses a date-grouped timeline so the two pages feel clearly different while sharing the same data source.

## Shared code
- One JSON endpoint (`/events/visual-data`) and one API resource (`EventVisualResource`) power both pages.
- Shared Vue pieces: `useEventVisuals`, `EventFilters`, image carousel, and date helpers.

## Images
- Events do not store image rows (keeps the 1M+ seeded dataset lightweight).
- Two local SVG placeholders per event are assigned deterministically from the event UUID via `Event::imagePaths()`.

## Locations
- Lat/lng is mapped to the nearest known city using `LocationResolver` (no external geocoding API).
- Location filtering uses a simple bounding box around predefined city anchors.

## Date & time
- Timestamps are stored as UTC Unix times.
- The UI formats dates in the visitor's local timezone via `Intl.DateTimeFormat`.

## Performance
- Visual pages only load `published` events, paginated server-side.
- Default date window is the next 30 days so queries avoid scanning the full 1M+ row dataset.
- Composite index on `(status, created_time)` speeds up filtered listings.
- Existing `/events` listing endpoints were left unchanged.

## Attendees & email
- Registrations are stored in `event_attendees`.
- Confirmation emails send immediately; reminder emails are queued.
- Reminders run hourly via `events:send-reminders`, using day windows (3 days out / 1 day out) and idempotency flags.
