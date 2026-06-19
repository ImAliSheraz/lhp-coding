# Event Visuals — Coding Test Submission

Implementation of the [Event Visuals coding test](CODING_TEST.md) for Rocky and team.

Two distinct event browsing pages, attendee registration, and email notifications — built on the existing Laravel + Inertia + Vue starter kit against the seeded events dataset (~1M+ rows).

For design rationale and trade-offs, see [DECISIONS.md](DECISIONS.md).

---

## What Was Built

### Event Visual 1 — Card Grid (`/events-visual-1`)
- Responsive card grid with image carousel (2+ local images per event)
- Filter bar: date range + city/location
- Infinite scroll pagination
- Prominent **View details** button on each card
- Subtle hover and fade-in animations

### Event Visual 2 — Timeline (`/events-visual-2`)
- Date-grouped vertical timeline layout (distinct from Visual 1)
- Same shared filters and data source
- Timeline items show image thumbnail, location, and **View details** button

### Event Detail Page (`/events/{id}`)
- Full event info: title, description, date/time, location, image carousel
- Attendee registration form (name + email)
- Attendee count

### Backend & Data
- **Local images** — 4 SVG placeholders in `public/images/events/`, assigned deterministically per event (no DB rows for images; keeps the large dataset lightweight)
- **Human-readable locations** — lat/lng resolved to nearest city + venue name via `LocationResolver`
- **Timezone display** — UTC timestamps shown in the visitor's local timezone (`Intl.DateTimeFormat`)
- **Attendee list** — `event_attendees` table with duplicate prevention per event/email
- **Confirmation email** — sent immediately on registration
- **Reminder emails** — queued 3 days before and 24 hours before the event; idempotent via `reminded_3d_at` / `reminded_24h_at` flags

### Performance (large dataset)
- Default **30-day upcoming window** so queries don't scan the full table
- Composite index on `(status, created_time)` for faster filtered listings
- Server-side pagination only; no full dataset loaded to the browser
- Existing `/events` admin listing left unchanged

---

## Setup

### Prerequisites
- PHP 8.3+
- Composer
- Node.js 18+
- MySQL (or SQLite for tests)
- Mailtrap account (or any SMTP) for email testing

### Install

```bash
composer install
cp .env.example .env   # if .env does not exist
php artisan key:generate
php artisan migrate
npm install
npm run build
```

### Environment (mail + queue)

I used Mailtrap for testing emails. For email testing with Mailtrap:

```env
MAIL_MAILER=smtp
MAIL_HOST=sandbox.smtp.mailtrap.io
MAIL_PORT=2525
MAIL_USERNAME=your_mailtrap_username
MAIL_PASSWORD=your_mailtrap_password
MAIL_FROM_ADDRESS="support@eventvisuals.com"
MAIL_FROM_NAME="Event Visuals"

QUEUE_CONNECTION=database
```

### Run the app

**Option A — all services (recommended for development):**

```bash
composer dev
```

Starts: web server, queue worker, logs, and Vite.

**Option B — production-style (nginx / `lhp-coding.test`):**

```bash
npm run build
php artisan serve          # or use your web server
php artisan queue:listen   # required for reminder emails
php artisan schedule:work  # optional; runs hourly reminder command
```

---

## Database Changes

### New table: `event_attendees`

| Column | Type | Notes |
|--------|------|-------|
| `id` | bigint | Primary key |
| `event_id` | uuid | FK → `events.id`, cascade delete |
| `email` | string | Unique per event |
| `name` | string | Nullable |
| `reminded_3d_at` | timestamp | Nullable; set after 3-day reminder sent |
| `reminded_24h_at` | timestamp | Nullable; set after 24-hour reminder sent |
| `created_at` / `updated_at` | timestamps | |

### New index on `events`

| Index | Columns | Purpose |
|-------|---------|---------|
| `events_status_created_time_index` | `(status, created_time)` | Faster filtered visual page queries |

### Migrations added

```
database/migrations/2026_06_19_000001_create_event_attendees_table.php
database/migrations/2026_06_19_000002_add_events_status_created_time_index.php
```

Run with:

```bash
php artisan migrate
```

---

## Routes & Pages

| URL | Description |
|-----|-------------|
| `/events-visual-1` | Card grid browse |
| `/events-visual-2` | Timeline browse |
| `/events/{id}` | Event detail + attendee registration |
| `/events/visual-data` | JSON API for visual pages (paginated, filtered) |
| `POST /events/{id}/attendees` | Register attendee |

Existing routes (`/events`, `/events/data`) are unchanged.

---

## How to Test

### 1. Visual pages

1. Open `/events-visual-1` — cards load with default 30-day window
2. Set **From / To** dates and **Location**, click **Apply filters**
3. Scroll down — more events load automatically
4. Click **View details** on any card
5. Open `/events-visual-2` — same filters, timeline layout (visually different)

### 2. Attendee registration + confirmation email

1. On an event detail page, enter name + email → **Join attendee list**
2. Toast confirms registration
3. **Confirmation email** sends immediately — check Mailtrap inbox (not real Gmail when using sandbox SMTP)
4. Registering the same email again shows “already registered” (no duplicate email)

### 3. Reminder emails

Reminders require the **queue worker** running:

```bash
php artisan queue:listen
```

**Manual test:**

```bash
php artisan events:send-reminders
```

This finds **published** events starting **exactly 3 days from today** or **tomorrow**, and queues reminder emails to registered attendees who haven't received that reminder yet.

Watch the queue terminal for:

```
App\Mail\EventReminderMail ... DONE
```

Check Mailtrap for subjects like:
- `Reminder (3 days): {Event Title}`
- `Reminder (24 hours): {Event Title}`

**Scheduled runs:** `events:send-reminders` is registered hourly in `bootstrap/app.php`. In production, add cron:

```bash
* * * * * cd /path-to-app && php artisan schedule:run >> /dev/null 2>&1
```

### 4. Automated tests

```bash
php artisan test
```

Feature tests cover:
- Visual data API + filters (`EventVisualTest`)
- Attendee registration + confirmation mail (`EventVisualTest`)
- Reminder command + idempotency (`EventReminderTest`)
- Existing event listing smoke tests (`EventListingTest`)

---

## Notes for Reviewers

- **Emails go to Mailtrap** when using sandbox SMTP — this is expected for local/dev testing.
- **Default date window** is today → +30 days. Widen the range in filters to see more events.
- **Confirmation emails** send synchronously; **reminder emails** are queued — keep `queue:listen` running.
- **Images** are deterministic placeholders (not stored per row) to avoid bloating the 1M+ event dataset.
- Design decisions and performance trade-offs are documented in [DECISIONS.md](DECISIONS.md).
