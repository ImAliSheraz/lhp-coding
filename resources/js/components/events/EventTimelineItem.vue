<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { MapPin } from '@lucide/vue';
import { Badge } from '@/components/ui/badge';
import { formatEventDateTime, formatEventTime } from '@/lib/eventDate';
import type { EventVisual } from '@/types/events';

defineProps<{
    event: EventVisual;
}>();
</script>

<template>
    <article class="relative rounded-xl border bg-card p-4 pl-8 transition-colors hover:border-primary/40">
        <div class="absolute left-3 top-5 h-3 w-3 rounded-full bg-primary ring-4 ring-primary/20" />
        <div class="flex flex-wrap items-start justify-between gap-2">
            <div>
                <p class="text-xs uppercase tracking-wide text-muted-foreground">{{ formatEventTime(event.starts_at) }}</p>
                <h3 class="text-lg font-semibold">{{ event.title }}</h3>
            </div>
            <Badge variant="outline">{{ event.type }}</Badge>
        </div>
        <p class="mt-2 line-clamp-2 text-sm text-muted-foreground">{{ event.description }}</p>
        <div class="mt-3 flex flex-wrap items-center gap-3 text-sm text-muted-foreground">
            <span>{{ formatEventDateTime(event.starts_at) }}</span>
            <span class="inline-flex items-center gap-1">
                <MapPin class="h-4 w-4" />
                {{ event.location.city }}
            </span>
        </div>
        <Link :href="`/events/${event.id}`" class="mt-3 inline-block text-sm text-primary hover:underline">View details</Link>
    </article>
</template>
