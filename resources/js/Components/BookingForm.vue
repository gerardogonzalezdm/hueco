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
    prefill: {
        type: Object,
        default: () => ({}),
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

const toDateTimeLocal = (value) => {
    if (!value) return '';
    const d = new Date(value);
    if (Number.isNaN(d.getTime())) return '';
    const pad = (n) => String(n).padStart(2, '0');
    return `${d.getFullYear()}-${pad(d.getMonth() + 1)}-${pad(d.getDate())}T${pad(d.getHours())}:${pad(d.getMinutes())}`;
};

const initialDuration = () => {
    if (props.booking?.time_start && props.booking?.time_end) {
        const start = new Date(props.booking.time_start);
        const end = new Date(props.booking.time_end);
        return Math.round((end - start) / 60000);
    }
    // Si tenemos prefill con time_start y time_end, calcular la duración
    if (props.prefill?.time_start && props.prefill?.time_end) {
        const start = new Date(props.prefill.time_start);
        const end = new Date(props.prefill.time_end);
        return Math.round((end - start) / 60000);
    }
    return 60;
};

const initialTimeStart = () => {
    if (props.booking?.time_start) return toDateTimeLocal(props.booking.time_start);
    if (props.prefill?.time_start) {
        // El prefill viene en formato 'YYYY-MM-DDTHH:MM' — lo usamos tal cual
        return props.prefill.time_start;
    }
    return toDateTimeLocal(new Date());
};

const initialTimeEnd = () => {
    if (props.booking?.time_end) return toDateTimeLocal(props.booking.time_end);
    if (props.prefill?.time_end) return props.prefill.time_end;
    // Default: 1 hora después del time_start
    const baseStart = props.booking?.time_start ?? props.prefill?.time_start ?? new Date();
    const start = new Date(baseStart);
    const end = new Date(start.getTime() + 60 * 60000);
    return toDateTimeLocal(end);
};

const initialSpaceId = () => {
    if (props.booking?.space_id) return props.booking.space_id;
    if (props.prefill?.space_id) {
        const match = props.spaces.find((s) => s.id === Number(props.prefill.space_id));
        if (match) return match.id;
    }
    return props.spaces[0]?.id ?? null;
};

const form = useForm({
    space_id: initialSpaceId(),
    client_name: props.booking?.client_name ?? '',
    client_email: props.booking?.client_email ?? '',
    client_phone: props.booking?.client_phone ?? '',
    client_notes: props.booking?.client_notes ?? '',
    time_start: initialTimeStart(),
    duration_minutes: initialDuration(),
    time_end: initialTimeEnd(),
    create_account: false,
    account_password: '',
});

const selectedSpace = computed(() =>
    props.spaces.find((s) => s.id === form.space_id),
);

// Si en la inicialización el espacio es fixed_duration y no estamos editando ni el prefill
// trae time_end, usar la duración por defecto del espacio.
if (
    !props.booking?.time_start
    && !props.prefill?.time_end
    && selectedSpace.value?.fixed_duration
    && selectedSpace.value?.duration_minutes
) {
    form.duration_minutes = selectedSpace.value.duration_minutes;
}

const isFixedDuration = computed(() => selectedSpace.value?.fixed_duration ?? true);

// Si el espacio tiene duración fija, autorrellenar duration_minutes al cambiar de espacio
watch(
    () => form.space_id,
    () => {
        const space = selectedSpace.value;
        if (space?.fixed_duration && space?.duration_minutes) {
            form.duration_minutes = space.duration_minutes;
        }
    },
);

// Cuando cambia time_start, ajustar time_end manteniendo la duración (modo flex)
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
    // Enviar solo los campos relevantes según el modo del espacio
    const transform = (data) => {
        const payload = { ...data };
        if (isFixedDuration.value) {
            payload.time_end = null;
        } else {
            payload.duration_minutes = null;
        }
        if (!payload.create_account) {
            payload.account_password = '';
        }
        return payload;
    };

    if (props.method === 'put') {
        form.transform(transform).put(props.submitUrl);
    } else {
        form.transform(transform).post(props.submitUrl);
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
                    {{ space.name }} <span v-if="!space.fixed_duration">(duración libre)</span>
                </option>
            </select>
            <InputError class="mt-2" :message="form.errors.space_id" />
        </div>

        <!-- Modo duración fija: time_start + duration_minutes -->
        <div v-if="isFixedDuration" class="grid gap-6 sm:grid-cols-2">
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
                    v-if="selectedSpace?.duration_minutes"
                    class="mt-1 text-xs text-gray-500"
                >
                    Duración por defecto del espacio: {{ selectedSpace.duration_minutes }} min.
                </p>
                <InputError class="mt-2" :message="form.errors.duration_minutes" />
            </div>
        </div>

        <!-- Modo flex: time_start + time_end (selector de horario libre) -->
        <div v-else class="grid gap-6 sm:grid-cols-2">
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
                <InputLabel for="time_end" value="Fin" />
                <input
                    id="time_end"
                    v-model="form.time_end"
                    type="datetime-local"
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-hueco-teal focus:ring-hueco-teal"
                    required
                />
                <p class="mt-1 text-xs text-gray-500">
                    Espacio con duración libre — elige inicio y fin a tu medida.
                </p>
                <InputError class="mt-2" :message="form.errors.time_end" />
            </div>
        </div>

        <div class="rounded-2xl border border-hueco-cream bg-white p-5">
            <h4 class="mb-4 text-sm font-bold uppercase tracking-wider text-gray-500">
                Datos del cliente
            </h4>

            <div class="space-y-4">
                <div>
                    <InputLabel for="client_email" value="Email del cliente" />
                    <TextInput
                        id="client_email"
                        v-model="form.client_email"
                        type="email"
                        class="mt-1 block w-full"
                        placeholder="cliente@email.com"
                        required
                    />
                    <p class="mt-1 text-xs text-gray-500">
                        Si ya existe un cliente registrado con este email, la reserva se le asocia automáticamente.
                    </p>
                    <InputError class="mt-2" :message="form.errors.client_email" />
                </div>

                <div>
                    <InputLabel for="client_name" value="Nombre del cliente" />
                    <TextInput
                        id="client_name"
                        v-model="form.client_name"
                        type="text"
                        class="mt-1 block w-full"
                        placeholder="Nombre y apellidos"
                        required
                    />
                    <InputError class="mt-2" :message="form.errors.client_name" />
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
                        rows="2"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-hueco-teal focus:ring-hueco-teal"
                        placeholder="Necesidades especiales, equipo extra, etc."
                    ></textarea>
                    <InputError class="mt-2" :message="form.errors.client_notes" />
                </div>

                <!-- Crear cuenta de cliente al vuelo (solo en creación, no edición) -->
                <div v-if="method === 'post'" class="rounded-xl bg-hueco-cream/60 p-4">
                    <label class="flex cursor-pointer items-start gap-3">
                        <input
                            v-model="form.create_account"
                            type="checkbox"
                            class="mt-1 h-5 w-5 rounded border-gray-300 text-hueco-teal focus:ring-hueco-teal"
                        />
                        <div>
                            <div class="text-sm font-semibold text-hueco-black">
                                Crear cuenta para este cliente
                            </div>
                            <div class="text-xs text-gray-600">
                                Útil para clientes que vienen presencialmente y aún no tienen cuenta.
                                Podrá entrar luego con su email y la contraseña que pongas aquí.
                            </div>
                        </div>
                    </label>

                    <div v-if="form.create_account" class="mt-4">
                        <InputLabel for="account_password" value="Contraseña inicial" />
                        <TextInput
                            id="account_password"
                            v-model="form.account_password"
                            type="text"
                            class="mt-1 block w-full"
                            placeholder="Mínimo 8 caracteres"
                            required
                        />
                        <p class="mt-1 text-xs text-gray-500">
                            La mantenemos visible para que la copies y se la pases al cliente. Podrá cambiarla luego.
                        </p>
                        <InputError class="mt-2" :message="form.errors.account_password" />
                    </div>
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
