<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import AttendeeForm from '@/components/events/AttendeeForm.vue';
import EventImageCarousel from '@/components/events/EventImageCarousel.vue';
import { Badge } from '@/components/ui/badge';
import { formatEventDateTime } from '@/lib/eventDate';
import type { EventVisual } from '@/types/events';

interface EventDetail {
    id: string;
    type: string;
    status: string;
    payload: Record<string, unknown>;
    attendees_count?: number;
}

defineProps<{
    event: EventDetail;
    display: EventVisual;
}>();
</script>

<template>
    <Head :title="display.title" />

    <div class="mx-auto flex max-w-4xl flex-col gap-6 p-4 md:p-6">
        <Link href="/events" class="text-sm text-primary hover:underline"
            >← Back to events</Link
        >

        <div class="overflow-hidden rounded-2xl border">
            <div class="aspect-[21/9]">
                <EventImageCarousel
                    :images="display.images"
                    :alt="display.title"
                />
            </div>
        </div>

        <div class="flex flex-wrap items-start justify-between gap-3">
            <div>
                <h1 class="text-3xl font-semibold">{{ display.title }}</h1>
                <p class="mt-2 text-muted-foreground">
                    {{ display.description }}
                </p>
            </div>
            <div class="flex gap-2">
                <Badge>{{ display.type }}</Badge>
                <Badge variant="outline">{{ event.status }}</Badge>
            </div>
        </div>

        <div class="grid gap-4 rounded-xl border bg-card p-4 sm:grid-cols-2">
            <div>
                <p class="text-xs text-muted-foreground uppercase">When</p>
                <p class="font-medium">
                    {{ formatEventDateTime(display.starts_at) }}
                </p>
                <p class="mt-1 text-xs text-muted-foreground">
                    Times shown in your local timezone
                </p>
            </div>
            <div>
                <p class="text-xs text-muted-foreground uppercase">Where</p>
                <p class="font-medium">{{ display.location.label }}</p>
            </div>
        </div>

        <p class="text-sm text-muted-foreground">
            {{ event.attendees_count ?? 0 }} people registered
        </p>

        <AttendeeForm :event-id="event.id" />
    </div>
</template>
