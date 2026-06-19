<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { computed, onBeforeUnmount, onMounted, ref } from 'vue';
import EventFilters from '@/components/events/EventFilters.vue';
import EventTimelineItem from '@/components/events/EventTimelineItem.vue';
import { Skeleton } from '@/components/ui/skeleton';
import { useEventVisuals } from '@/composables/useEventVisuals';
import { formatEventDay } from '@/lib/eventDate';
import type { CityOption, EventVisual, VisualFilters } from '@/types/events';

const props = defineProps<{
    cities: CityOption[];
    filters: VisualFilters;
}>();

const {
    filters,
    events,
    total,
    loading,
    hasLoadedOnce,
    error,
    loadMore,
    applyFilters,
} = useEventVisuals(props.filters, 40);

const sentinel = ref<HTMLElement | null>(null);
let observer: IntersectionObserver | null = null;

const empty = computed(() => hasLoadedOnce.value && events.value.length === 0);

const groupedEvents = computed(() => {
    const groups = new Map<string, EventVisual[]>();

    for (const event of events.value) {
        const key = formatEventDay(event.starts_at);
        const bucket = groups.get(key) ?? [];
        bucket.push(event);
        groups.set(key, bucket);
    }

    return [...groups.entries()];
});

onMounted(() => {
    observer = new IntersectionObserver(
        (entries) => {
            if (entries[0]?.isIntersecting) {
                loadMore();
            }
        },
        { rootMargin: '400px' },
    );

    if (sentinel.value) {
        observer.observe(sentinel.value);
    }

    loadMore();
});

onBeforeUnmount(() => observer?.disconnect());
</script>

<template>
    <Head title="Events Visual 2" />

    <div class="flex flex-col gap-6 p-4 md:p-6">
        <div>
            <h1 class="text-2xl font-semibold">Event Visual 2</h1>
            <p class="text-sm text-muted-foreground">
                Timeline browse — {{ total?.toLocaleString() ?? '…' }} matching
                events
            </p>
        </div>

        <EventFilters
            v-model:filters="filters"
            :cities="cities"
            @apply="applyFilters"
        />

        <div
            v-if="error"
            class="rounded-xl border border-destructive/30 bg-destructive/5 p-4 text-sm text-destructive"
        >
            {{ error }}
        </div>

        <div
            v-else-if="empty"
            class="rounded-xl border border-dashed p-10 text-center text-muted-foreground"
        >
            No events match these filters.
        </div>

        <div v-else class="space-y-8">
            <section
                v-for="[day, dayEvents] in groupedEvents"
                :key="day"
                class="space-y-4"
            >
                <h2
                    class="sticky top-0 z-10 bg-background/90 py-2 text-sm font-semibold tracking-wide text-muted-foreground uppercase backdrop-blur"
                >
                    {{ day }}
                </h2>
                <div class="space-y-4 border-l border-border pl-4">
                    <EventTimelineItem
                        v-for="event in dayEvents"
                        :key="event.id"
                        :event="event"
                    />
                </div>
            </section>
            <Skeleton v-if="loading" class="h-28 rounded-xl" />
        </div>

        <div ref="sentinel" class="h-4" />
    </div>
</template>
