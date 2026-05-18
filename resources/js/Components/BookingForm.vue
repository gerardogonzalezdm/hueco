<script setup>
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { Link, useForm } from '@inertiajs/vue3';
import { computed, watch } from 'vue';

const props = defineProps({
    booking: {
        type: Object,
        default: () => null,
    },
    spaces: {
        type: Array,
        required: true,
    },
    submitUrl: {
        type: String,
        required: true,
    },
    method: {
        type: String,
        default: 'post',
    },
    submitLabel: {
        type: String,
        default: 'Guardar',
    },
    cancelUrl: {
        type: String,
        required: true,
    },
});

// Convierte cualquier fecha (ISO o string MySQL) al formato YYYY-MM-DDTHH:MM
// que espera el input datetime-local.
const toDateTimeLocal = (value) => {
    if (!value) return '';
    const d = new Date(value);
    if (Number.isNaN(d.getTime())) return '';
    const pad = (n) => String(n).padStart(2, '0');
    return `${d.getFullYear()}-${pad(d.getMonth() + 1)}-${pad(d.getDate())}T${pad(d.getHours())}:${pad(d.getMinutes())}`;
};

const initialDuration = () => {
    if (!props.booking?.time_start || !props.booking?.time_end) return 60;
    const start = new Date(props.booking.time_start);
    const end = new Date(props.booking.time_end);
    return Math.round((end - start) / 60000);
};

const form = useForm({
    space_id: props.booking?.space_id ?? props.spaces[0]?.id ?? null,
    client_name: props.booking?.client_name ?? '',
    client_email: props.booking?.client_email ?? '',
    client_phone: props.booking?.client_phone ?? '',
    client_notes: props.booking?.client_notes ?? '',
    time_start: toDateTimeLocal(props.booking?.time_start) || toDateTimeLocal(new Date()),
    duration_minutes: initialDuration(),
});

const selectedSpace = computed(() =>
    props.spaces.find((s) => s.id === form.space_id),
);

// Si el espacio tiene duración fija, autorrellenar duración al cambiar de espacio.
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
    if (props.method === 'put') {
        form.put(props.submitUrl);
    } else {
        form.post(props.submitUrl);
    }
};
</script>

<template>
    <form @submit.prevent="submit" class="space-y-6">
        <div>
            <InputLabel for="space_id" value="Espacio" />
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
                <InputLabel for="time_start" value="Inicio" />
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
                    Duración por defecto del espacio: {{ selectedSpace.duration_minutes }} min.
                </p>
                <InputError class="mt-2" :message="form.errors.duration_minutes" />
            </div>
        </div>

        <div class="rounded-2xl border border-hueco-cream bg-white p-5">
            <h4 class="mb-4 text-sm font-bold uppercase tracking-wider text-gray-500">
                Datos del cliente
            </h4>

            <div class="space-y-4">
                <div class="grid gap-4 sm:grid-cols-2">
                    <div>
                        <InputLabel for="client_name" value="Nombre" />
                        <TextInput
                            id="client_name"
                            v-model="form.client_name"
                            type="text"
                            class="mt-1 block w-full"
                            placeholder="Nombre del cliente"
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
                            placeholder="cliente@email.com"
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
                        placeholder="+34 600 000 000"
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
                        placeholder="Necesidades especiales, equipo extra, etc."
                    ></textarea>
                    <InputError class="mt-2" :message="form.errors.client_notes" />
                </div>
            </div>
        </div>

        <div class="flex items-center justify-end gap-3 pt-2">
            <Link
                :href="cancelUrl"
                class="rounded-md px-4 py-2 text-sm font-medium text-gray-600 hover:text-hueco-black"
            >
                Cancelar
            </Link>
            <PrimaryButton :disabled="form.processing" :class="{ 'opacity-50': form.processing }">
                {{ submitLabel }}
            </PrimaryButton>
        </div>
    </form>
</template>
