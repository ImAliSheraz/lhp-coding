<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { computed, onBeforeUnmount, onMounted, ref } from 'vue';
import EventCard from '@/components/events/EventCard.vue';
import EventFilters from '@/components/events/EventFilters.vue';
import { Skeleton } from '@/components/ui/skeleton';
import { useEventVisuals } from '@/composables/useEventVisuals';
import type { CityOption, VisualFilters } from '@/types/events';

const props = defineProps<{
    cities: CityOption[];
    filters: VisualFilters;
}>();

const { filters, events, total, loading, hasLoadedOnce, error, loadMore, applyFilters } = useEventVisuals(props.filters, 24);

const sentinel = ref<HTMLElement | null>(null);
let observer: IntersectionObserver | null = null;

const empty = computed(() => hasLoadedOnce.value && events.value.length === 0);

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
    <Head title="Events Visual 1" />

    <div class="flex flex-col gap-6 p-4 md:p-6">
        <div>
            <h1 class="text-2xl font-semibold">Event Visual 1</h1>
            <p class="text-sm text-muted-foreground">Card grid browse — {{ total?.toLocaleString() ?? '…' }} published events</p>
        </div>

        <EventFilters v-model:filters="filters" :cities="cities" @apply="applyFilters" />

        <div v-if="error" class="rounded-xl border border-destructive/30 bg-destructive/5 p-4 text-sm text-destructive">
            {{ error }}
        </div>

        <div v-else-if="empty" class="rounded-xl border border-dashed p-10 text-center text-muted-foreground">
            No events match these filters.
        </div>

        <div v-else class="grid gap-5 sm:grid-cols-2 xl:grid-cols-3">
            <EventCard
                v-for="(event, index) in events"
                :key="event.id"
                :event="event"
                class="animate-in fade-in slide-in-from-bottom-2 duration-500"
                :style="{ animationDelay: `${Math.min(index, 8) * 40}ms` }"
            />
            <template v-if="loading">
                <Skeleton v-for="n in 3" :key="n" class="h-80 rounded-xl" />
            </template>
        </div>

        <div ref="sentinel" class="h-4" />
    </div>
</template>
