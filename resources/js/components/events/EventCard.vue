<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { Badge } from '@/components/ui/badge';
import { Card, CardContent, CardFooter, CardHeader, CardTitle } from '@/components/ui/card';
import EventImageCarousel from '@/components/events/EventImageCarousel.vue';
import { formatEventDateTime } from '@/lib/eventDate';
import type { EventVisual } from '@/types/events';

defineProps<{
    event: EventVisual;
}>();
</script>

<template>
    <Card class="overflow-hidden transition-all duration-300 hover:-translate-y-1 hover:shadow-lg">
        <div class="aspect-[16/10]">
            <EventImageCarousel :images="event.images" :alt="event.title" />
        </div>
        <CardHeader class="gap-2">
            <div class="flex items-start justify-between gap-2">
                <CardTitle class="line-clamp-2 text-base">{{ event.title }}</CardTitle>
                <Badge variant="secondary">{{ event.type }}</Badge>
            </div>
            <p class="line-clamp-2 text-sm text-muted-foreground">{{ event.description }}</p>
        </CardHeader>
        <CardContent class="space-y-1 text-sm">
            <p>{{ formatEventDateTime(event.starts_at) }}</p>
            <p class="text-muted-foreground">{{ event.location.label }}</p>
        </CardContent>
        <CardFooter class="justify-between text-xs text-muted-foreground">
            <span>{{ event.attendee_count ?? 0 }} attending</span>
            <Link :href="`/events/${event.id}`" class="text-primary hover:underline">Details</Link>
        </CardFooter>
    </Card>
</template>
