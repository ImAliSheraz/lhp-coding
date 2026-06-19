<script setup lang="ts">
import { ChevronLeft, ChevronRight } from '@lucide/vue';
import { ref } from 'vue';
import { Button } from '@/components/ui/button';

const { images } = defineProps<{
    images: string[];
    alt: string;
}>();

const index = ref(0);

function next() {
    index.value = (index.value + 1) % images.length;
}

function prev() {
    index.value = (index.value - 1 + images.length) % images.length;
}
</script>

<template>
    <div class="group relative overflow-hidden bg-muted">
        <img
            :src="images[index]"
            :alt="alt"
            class="h-full w-full object-cover transition-transform duration-500 group-hover:scale-105"
        />
        <div
            v-if="images.length > 1"
            class="absolute inset-x-0 bottom-0 flex items-center justify-between bg-gradient-to-t from-black/50 to-transparent p-2 opacity-0 transition-opacity group-hover:opacity-100"
        >
            <Button
                size="icon"
                variant="secondary"
                class="h-7 w-7"
                @click.stop="prev"
            >
                <ChevronLeft class="h-4 w-4" />
            </Button>
            <span class="text-xs text-white"
                >{{ index + 1 }} / {{ images.length }}</span
            >
            <Button
                size="icon"
                variant="secondary"
                class="h-7 w-7"
                @click.stop="next"
            >
                <ChevronRight class="h-4 w-4" />
            </Button>
        </div>
    </div>
</template>
