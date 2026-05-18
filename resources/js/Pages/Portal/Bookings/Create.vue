<script setup>
import CustomerLayout from '@/Layouts/CustomerLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { computed, watch } from 'vue';

const props = defineProps({
    spaces: Array,
});

const toDateTimeLocal = (date) => {
    const pad = (n) => String(n).padStart(2, '0');
    return `${date.getFullYear()}-${pad(date.getMonth() + 1)}-${pad(date.getDate())}T${pad(date.getHours())}:${pad(date.getMinutes())}`;
};

const defaultStart = () => {
    const now = new Date();
    now.setHours(now.getHours() + 1, 0, 0, 0);
    return toDateTimeLocal(now);
};

const defaultEnd = () => {
    const now = new Date();
    now.setHours(now.getHours() + 2, 0, 0, 0);
    return toDateTimeLocal(now);
};

const form = useForm({
    space_id: props.spaces[0]?.id ?? null,
    time_start: defaultStart(),
    duration_minutes: props.spaces[0]?.duration_minutes ?? 60,
    time_end: defaultEnd(),
    client_phone: '',
    client_notes: '',
});

const selectedSpace = computed(() =>
    props.spaces.find((s) => s.id === form.space_id),
);

const isFixedDuration = computed(() => selectedSpace.value?.fixed_duration ?? true);

watch(
    () => form.space_id,
    () => {
        const space = selectedSpace.value;
        if (space?.fixed_duration && space?.duration_minutes) {
            form.duration_minutes = space.duration_minutes;
        }
    },
);

// En modo flex, al cambiar time_start, mover time_end manteniendo la duración previa
watch(
    () => form.time_start,
    (newStart, oldStart) => {
        if (!isFixedDuration.value && newStart && oldStart) {
            const oldStartDate = new Date(oldStart);
            const oldEndDate = new Date(form.time_end);
            if (!Number.isNaN(oldStartDate.getTime()) && !Number.isNaN(oldEndDate.getTime())) {
                const duration = oldEndDate - oldStartDate;
                const newEnd = new Date(new Date(newStart).getTime() + duration);
                form.time_end = toDateTimeLocal(newEnd);
            }
        }
    },
);

const submit = () => {
    const transform = (data) => {
        const payload = { ...data };
        if (isFixedDuration.value) {
            payload.time_end = null;
        } else {
            payload.duration_minutes = null;
        }
        return payload;
    };

    form.transform(transform).post(route('portal.bookings.store'));
};
</script>

<template>
    <Head title="Nueva reserva" />

    <CustomerLayout>
        <template #header>
            <h2 class="text-xl font-semibold text-hueco-black">Nueva reserva</h2>
        </template>

        <div class="py-10">
            <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
                <div v-if="spaces.length === 0" class="rounded-2xl bg-white p-12 text-center shadow-sm">
                    <div class="text-5xl">⚠️</div>
                    <h3 class="mt-4 text-lg font-semibold text-hueco-black">
                        Aún no hay espacios disponibles
                    </h3>
                    <p class="mt-1 text-sm text-gray-500">
                        Vuelve en unos minutos.
                    </p>
                </div>

                <div v-else class="rounded-2xl bg-white p-8 shadow-sm">
                    <form @submit.prevent="submit" class="space-y-6">
                        <div>
                            <InputLabel for="space_id" value="¿Qué espacio quieres reservar?" />
                            <select
                                id="space_id"
                                v-model="form.space_id"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-hueco-teal focus:ring-hueco-teal"
                                required
                            >
                                <option v-for="space in spaces" :key="space.id" :value="space.id">
                                    {{ space.name }} <span v-if="!space.fixed_duration">(duración libre)</span>
                                </option>
                            </select>
                            <InputError class="mt-2" :message="form.errors.space_id" />
                        </div>

                        <!-- Modo duración fija -->
                        <div v-if="isFixedDuration" class="grid gap-6 sm:grid-cols-2">
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
                                    v-if="selectedSpace?.duration_minutes"
                                    class="mt-1 text-xs text-gray-500"
                                >
                                    Duración del espacio: {{ selectedSpace.duration_minutes }} min.
                                </p>
                                <InputError class="mt-2" :message="form.errors.duration_minutes" />
                            </div>
                        </div>

                        <!-- Modo flex -->
                        <div v-else class="grid gap-6 sm:grid-cols-2">
                            <div>
                                <InputLabel for="time_start" value="Desde" />
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
                                <InputLabel for="time_end" value="Hasta" />
                                <input
                                    id="time_end"
                                    v-model="form.time_end"
                                    type="datetime-local"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-hueco-teal focus:ring-hueco-teal"
                                    required
                                />
                                <p class="mt-1 text-xs text-gray-500">
                                    Este espacio acepta cualquier rango horario.
                                </p>
                                <InputError class="mt-2" :message="form.errors.time_end" />
                            </div>
                        </div>

                        <div>
                            <InputLabel for="client_phone" value="Tu teléfono (opcional)" />
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

                        <div class="flex items-center justify-end gap-3 pt-2">
                            <Link
                                :href="route('portal.bookings.index')"
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
            </div>
        </div>
    </CustomerLayout>
</template>
