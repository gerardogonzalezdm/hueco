<script setup>
import ApplicationLogo from '@/Components/ApplicationLogo.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { computed, watch } from 'vue';

const props = defineProps({
    company: Object,
    spaces: Array,
});

const toDateTimeLocal = (date) => {
    const pad = (n) => String(n).padStart(2, '0');
    return `${date.getFullYear()}-${pad(date.getMonth() + 1)}-${pad(date.getDate())}T${pad(date.getHours())}:${pad(date.getMinutes())}`;
};

// Hora por defecto: próximo "round hour" + 1 hora.
const defaultStart = () => {
    const now = new Date();
    now.setHours(now.getHours() + 1, 0, 0, 0);
    return toDateTimeLocal(now);
};

const form = useForm({
    space_id: props.spaces[0]?.id ?? null,
    client_name: '',
    client_email: '',
    client_phone: '',
    client_notes: '',
    time_start: defaultStart(),
    duration_minutes: props.spaces[0]?.duration_minutes ?? 60,
});

const selectedSpace = computed(() =>
    props.spaces.find((s) => s.id === form.space_id),
);

watch(
    () => form.space_id,
    () => {
        const space = selectedSpace.value;
        if (space?.fixed_duration && space?.duration_minutes) {
            form.duration_minutes = space.duration_minutes;
        }
    },
);

const submit = () => {
    form.post(route('public.bookings.store', props.company.slug));
};
</script>

<template>
    <Head :title="`Reservar — ${company.name}`" />

    <div class="min-h-screen bg-hueco-cream">
        <header class="mx-auto flex max-w-3xl items-center justify-between px-6 py-6">
            <Link :href="`/c/${company.slug}`" class="flex items-center gap-2">
                <ApplicationLogo class="h-8 w-8" />
                <span class="font-bold text-hueco-black">{{ company.name }}</span>
            </Link>
        </header>

        <main class="mx-auto max-w-3xl px-6 pb-16">
            <div class="rounded-3xl bg-white p-8 shadow-sm sm:p-10">
                <h1 class="text-2xl font-bold text-hueco-black">Nueva reserva</h1>
                <p class="mt-1 text-sm text-gray-600">
                    Completa los datos y te enviaremos la confirmación por email.
                </p>

                <form @submit.prevent="submit" class="mt-8 space-y-6">
                    <div>
                        <InputLabel for="space_id" value="¿Qué espacio quieres reservar?" />
                        <select
                            id="space_id"
                            v-model="form.space_id"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-hueco-teal focus:ring-hueco-teal"
                            required
                        >
                            <option v-for="space in spaces" :key="space.id" :value="space.id">
                                {{ space.name }}
                            </option>
                        </select>
                        <InputError class="mt-2" :message="form.errors.space_id" />
                    </div>

                    <div class="grid gap-6 sm:grid-cols-2">
                        <div>
                            <InputLabel for="time_start" value="Cuándo" />
                            <input
                                id="time_start"
                                v-model="form.time_start"
                                type="datetime-local"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-hueco-teal focus:ring-hueco-teal"
                                required
                            />
                            <InputError class="mt-2" :message="form.errors.time_start" />
                        </div>

                        <div>
                            <InputLabel for="duration_minutes" value="Duración (minutos)" />
                            <TextInput
                                id="duration_minutes"
                                v-model.number="form.duration_minutes"
                                type="number"
                                min="5"
                                max="1440"
                                step="5"
                                class="mt-1 block w-full"
                                required
                            />
                            <p
                                v-if="selectedSpace?.fixed_duration"
                                class="mt-1 text-xs text-gray-500"
                            >
                                Duración del espacio: {{ selectedSpace.duration_minutes }} min.
                            </p>
                            <InputError class="mt-2" :message="form.errors.duration_minutes" />
                        </div>
                    </div>

                    <div class="space-y-4 rounded-2xl border border-hueco-cream p-5">
                        <h3 class="text-sm font-bold uppercase tracking-wider text-gray-500">
                            Tus datos
                        </h3>

                        <div class="grid gap-4 sm:grid-cols-2">
                            <div>
                                <InputLabel for="client_name" value="Tu nombre" />
                                <TextInput
                                    id="client_name"
                                    v-model="form.client_name"
                                    type="text"
                                    class="mt-1 block w-full"
                                    required
                                />
                                <InputError class="mt-2" :message="form.errors.client_name" />
                            </div>

                            <div>
                                <InputLabel for="client_email" value="Email" />
                                <TextInput
                                    id="client_email"
                                    v-model="form.client_email"
                                    type="email"
                                    class="mt-1 block w-full"
                                    required
                                />
                                <InputError class="mt-2" :message="form.errors.client_email" />
                            </div>
                        </div>

                        <div>
                            <InputLabel for="client_phone" value="Teléfono (opcional)" />
                            <TextInput
                                id="client_phone"
                                v-model="form.client_phone"
                                type="tel"
                                class="mt-1 block w-full"
                            />
                            <InputError class="mt-2" :message="form.errors.client_phone" />
                        </div>

                        <div>
                            <InputLabel for="client_notes" value="Notas (opcional)" />
                            <textarea
                                id="client_notes"
                                v-model="form.client_notes"
                                rows="3"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-hueco-teal focus:ring-hueco-teal"
                            ></textarea>
                            <InputError class="mt-2" :message="form.errors.client_notes" />
                        </div>
                    </div>

                    <div class="flex items-center justify-end gap-3 pt-2">
                        <Link
                            :href="route('public.company.show', company.slug)"
                            class="rounded-md px-4 py-2 text-sm font-medium text-gray-600 hover:text-hueco-black"
                        >
                            Cancelar
                        </Link>
                        <button
                            type="submit"
                            :disabled="form.processing"
                            :class="{ 'opacity-50': form.processing }"
                            class="rounded-full bg-hueco-yellow px-6 py-3 text-base font-bold text-hueco-black shadow-sm transition hover:bg-yellow-300"
                        >
                            Confirmar reserva
                        </button>
                    </div>
                </form>
            </div>
        </main>
    </div>
</template>
