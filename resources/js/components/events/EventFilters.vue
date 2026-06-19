<script setup lang="ts">
import { Button } from '@/components/ui/button';
import type { CityOption, VisualFilters } from '@/types/events';

defineProps<{
    cities: CityOption[];
}>();

const filters = defineModel<VisualFilters>('filters', { required: true });

const emit = defineEmits<{
    apply: [];
}>();
</script>

<template>
    <form class="flex flex-wrap items-end gap-3 rounded-xl border bg-card p-4" @submit.prevent="emit('apply')">
        <div class="flex min-w-40 flex-col gap-1">
            <label class="text-xs text-muted-foreground" for="from">From</label>
            <input
                id="from"
                v-model="filters.from"
                type="date"
                class="h-9 rounded-md border border-input bg-background px-3 text-sm"
            />
        </div>
        <div class="flex min-w-40 flex-col gap-1">
            <label class="text-xs text-muted-foreground" for="to">To</label>
            <input
                id="to"
                v-model="filters.to"
                type="date"
                class="h-9 rounded-md border border-input bg-background px-3 text-sm"
            />
        </div>
        <div class="flex min-w-48 flex-col gap-1">
            <label class="text-xs text-muted-foreground" for="city">Location</label>
            <select
                id="city"
                v-model="filters.city"
                class="h-9 rounded-md border border-input bg-background px-3 text-sm"
            >
                <option value="">All cities</option>
                <option v-for="city in cities" :key="city.key" :value="city.key">{{ city.name }}</option>
            </select>
        </div>
        <Button type="submit">Apply filters</Button>
    </form>
</template>
