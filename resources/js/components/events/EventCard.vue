<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { ArrowRight } from '@lucide/vue';
import EventImageCarousel from '@/components/events/EventImageCarousel.vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import {
    Card,
    CardContent,
    CardFooter,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import { formatEventDateTime } from '@/lib/eventDate';
import type { EventVisual } from '@/types/events';

defineProps<{
    event: EventVisual;
}>();
</script>

<template>
    <Card
        class="flex flex-col overflow-hidden transition-all duration-300 hover:-translate-y-1 hover:shadow-lg"
    >
        <div class="aspect-[16/10]">
            <EventImageCarousel :images="event.images" :alt="event.title" />
        </div>
        <CardHeader class="gap-2">
            <div class="flex items-start justify-between gap-2">
                <CardTitle class="line-clamp-2 text-base">{{
                    event.title
                }}</CardTitle>
                <Badge variant="secondary">{{ event.type }}</Badge>
            </div>
            <p class="line-clamp-2 text-sm text-muted-foreground">
                {{ event.description }}
            </p>
        </CardHeader>
        <CardContent class="space-y-1 text-sm">
            <p>{{ formatEventDateTime(event.starts_at) }}</p>
            <p class="text-muted-foreground">{{ event.location.label }}</p>
        </CardContent>
        <CardFooter class="mt-auto flex-col gap-3 border-t bg-muted/30 pt-4">
            <span class="w-full text-xs text-muted-foreground"
                >{{ event.attendee_count ?? 0 }} attending</span
            >
            <Button as-child class="w-full">
                <Link :href="`/events/${event.id}`" class="gap-2">
                    View details
                    <ArrowRight class="h-4 w-4" />
                </Link>
            </Button>
        </CardFooter>
    </Card>
</template>
