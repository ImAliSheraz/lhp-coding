<script setup lang="ts">
import { useForm } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';

const props = defineProps<{
    eventId: string;
}>();

const form = useForm({
    email: '',
    name: '',
});

function submit() {
    form.post(`/events/${props.eventId}/attendees`, {
        preserveScroll: true,
        onSuccess: () => form.reset('email', 'name'),
    });
}
</script>

<template>
    <form
        class="space-y-3 rounded-xl border bg-card p-4"
        @submit.prevent="submit"
    >
        <div>
            <h2 class="text-base font-semibold">Register interest</h2>
            <p class="text-sm text-muted-foreground">
                Join the attendee list and receive email updates.
            </p>
        </div>
        <div class="grid gap-3 sm:grid-cols-2">
            <div class="space-y-1">
                <Label for="name">Name (optional)</Label>
                <Input id="name" v-model="form.name" autocomplete="name" />
            </div>
            <div class="space-y-1">
                <Label for="email">Email</Label>
                <Input
                    id="email"
                    v-model="form.email"
                    type="email"
                    required
                    autocomplete="email"
                />
                <p v-if="form.errors.email" class="text-xs text-destructive">
                    {{ form.errors.email }}
                </p>
            </div>
        </div>
        <Button type="submit" :disabled="form.processing"
            >Join attendee list</Button
        >
    </form>
</template>
