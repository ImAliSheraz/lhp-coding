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

const { filters, events, total, loading, hasLoadedOnce, error, loadMore, applyFilters } = useEventVisuals(props.filters, 40);

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

const cityCounts = computed(() => {
    const counts = new Map<string, number>();

    for (const event of events.value) {
        const city = event.location.city;
        counts.set(city, (counts.get(city) ?? 0) + 1);
    }

    return [...counts.entries()].sort((a, b) => b[1] - a[1]).slice(0, 8);
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
            <p class="text-sm text-muted-foreground">Timeline + location clusters — {{ total?.toLocaleString() ?? '…' }} published events</p>
        </div>

        <EventFilters v-model:filters="filters" :cities="cities" @apply="applyFilters" />

        <div v-if="error" class="rounded-xl border border-destructive/30 bg-destructive/5 p-4 text-sm text-destructive">
            {{ error }}
        </div>

        <div v-else-if="empty" class="rounded-xl border border-dashed p-10 text-center text-muted-foreground">
            No events match these filters.
        </div>

        <div v-else class="grid gap-6 lg:grid-cols-[minmax(0,1fr)_280px]">
            <div class="space-y-8">
                <section v-for="[day, dayEvents] in groupedEvents" :key="day" class="space-y-4">
                    <h2 class="sticky top-0 z-10 bg-background/90 py-2 text-sm font-semibold uppercase tracking-wide text-muted-foreground backdrop-blur">
                        {{ day }}
                    </h2>
                    <div class="space-y-4 border-l border-border pl-4">
                        <EventTimelineItem v-for="event in dayEvents" :key="event.id" :event="event" />
                    </div>
                </section>
                <Skeleton v-if="loading" class="h-28 rounded-xl" />
            </div>

            <aside class="h-fit rounded-xl border bg-card p-4 lg:sticky lg:top-4">
                <h2 class="text-sm font-semibold">Locations in view</h2>
                <p class="mt-1 text-xs text-muted-foreground">Top cities from loaded events</p>
                <ul class="mt-4 space-y-2">
                    <li
                        v-for="[city, count] in cityCounts"
                        :key="city"
                        class="flex items-center justify-between rounded-lg bg-muted/50 px-3 py-2 text-sm transition-colors hover:bg-muted"
                    >
                        <span>{{ city }}</span>
                        <span class="font-medium">{{ count }}</span>
                    </li>
                </ul>
            </aside>
        </div>

        <div ref="sentinel" class="h-4" />
    </div>
</template>
