<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { ArrowRight, MapPin } from '@lucide/vue';
import EventImageCarousel from '@/components/events/EventImageCarousel.vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { formatEventDateTime, formatEventTime } from '@/lib/eventDate';
import type { EventVisual } from '@/types/events';

defineProps<{
    event: EventVisual;
}>();
</script>

<template>
    <article
        class="relative overflow-hidden rounded-xl border bg-card transition-colors hover:border-primary/40"
    >
        <div class="grid gap-4 p-4 pl-8 sm:grid-cols-[140px_minmax(0,1fr)]">
            <div
                class="absolute top-5 left-3 h-3 w-3 rounded-full bg-primary ring-4 ring-primary/20"
            />

            <div class="hidden h-28 overflow-hidden rounded-lg sm:block">
                <EventImageCarousel :images="event.images" :alt="event.title" />
            </div>

            <div class="min-w-0 space-y-3">
                <div class="flex flex-wrap items-start justify-between gap-2">
                    <div>
                        <p
                            class="text-xs tracking-wide text-muted-foreground uppercase"
                        >
                            {{ formatEventTime(event.starts_at) }}
                        </p>
                        <h3 class="text-lg font-semibold">{{ event.title }}</h3>
                    </div>
                    <Badge variant="outline">{{ event.type }}</Badge>
                </div>
                <p class="line-clamp-2 text-sm text-muted-foreground">
                    {{ event.description }}
                </p>
                <div
                    class="flex flex-wrap items-center gap-3 text-sm text-muted-foreground"
                >
                    <span>{{ formatEventDateTime(event.starts_at) }}</span>
                    <span class="inline-flex items-center gap-1">
                        <MapPin class="h-4 w-4" />
                        {{ event.location.city }}
                    </span>
                </div>
                <Button as-child size="sm" class="w-full sm:w-auto">
                    <Link :href="`/events/${event.id}`" class="gap-2">
                        View details
                        <ArrowRight class="h-4 w-4" />
                    </Link>
                </Button>
            </div>
        </div>
    </article>
</template>
