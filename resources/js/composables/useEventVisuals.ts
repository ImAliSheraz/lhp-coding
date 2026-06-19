import { reactive, ref, computed } from 'vue';
import type { EventVisual, VisualFilters } from '@/types/events';

export function useEventVisuals(initialFilters: VisualFilters, perPage = 24) {
    const filters = reactive({ ...initialFilters });
    const events = ref<EventVisual[]>([]);
    const page = ref(0);
    const lastPage = ref<number | null>(null);
    const total = ref<number | null>(null);
    const loading = ref(false);
    const hasLoadedOnce = ref(false);
    const error = ref<string | null>(null);

    const hasMore = computed(() => lastPage.value === null || page.value < lastPage.value);

    async function loadMore() {
        if (loading.value || !hasMore.value) {
            return;
        }

        loading.value = true;
        error.value = null;

        const params = new URLSearchParams({
            page: String(page.value + 1),
            per_page: String(perPage),
        });

        if (filters.from) params.set('from', filters.from);
        if (filters.to) params.set('to', filters.to);
        if (filters.city) params.set('city', filters.city);

        try {
            const response = await fetch(`/events/visual-data?${params.toString()}`, {
                headers: { Accept: 'application/json' },
            });

            if (!response.ok) {
                throw new Error(`Could not load events (${response.status})`);
            }

            const payload = await response.json();

            events.value.push(...payload.data);
            page.value = payload.current_page;
            lastPage.value = payload.last_page;
            total.value = payload.total;
            hasLoadedOnce.value = true;
        } catch (e) {
            error.value = e instanceof Error ? e.message : 'Could not load events';
            hasLoadedOnce.value = true;
        } finally {
            loading.value = false;
        }
    }

    function applyFilters() {
        events.value = [];
        page.value = 0;
        lastPage.value = null;
        total.value = null;
        hasLoadedOnce.value = false;
        return loadMore();
    }

    return {
        filters,
        events,
        total,
        loading,
        hasLoadedOnce,
        error,
        hasMore,
        loadMore,
        applyFilters,
    };
}
