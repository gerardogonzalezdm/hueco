<script setup>
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { Link, useForm } from '@inertiajs/vue3';

const props = defineProps({
    space: {
        type: Object,
        default: () => null,
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

const form = useForm({
    name: props.space?.name ?? '',
    duration_minutes: props.space?.duration_minutes ?? 60,
    fixed_duration: props.space?.fixed_duration ?? true,
    price: props.space?.price ?? null,
    show_price: props.space?.show_price ?? true,
    show_duration: props.space?.show_duration ?? true,
});

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
            <InputLabel for="name" value="Nombre del espacio" />
            <TextInput
                id="name"
                v-model="form.name"
                type="text"
                class="mt-1 block w-full"
                placeholder="Ej: Sala de reuniones grande"
                required
                autofocus
            />
            <InputError class="mt-2" :message="form.errors.name" />
        </div>

        <div :class="form.fixed_duration ? 'grid gap-6 sm:grid-cols-2' : ''">
            <div v-if="form.fixed_duration">
                <InputLabel for="duration_minutes" value="Duración fija (minutos)" />
                <TextInput
                    id="duration_minutes"
                    v-model.number="form.duration_minutes"
                    type="number"
                    min="5"
                    max="1440"
                    step="5"
                    class="mt-1 block w-full"
                />
                <p class="mt-1 text-xs text-gray-500">
                    Entre 5 y 1440 minutos (24 h).
                </p>
                <InputError class="mt-2" :message="form.errors.duration_minutes" />
            </div>

            <div>
                <InputLabel for="price" value="Precio por reserva (€)" />
                <TextInput
                    id="price"
                    v-model.number="form.price"
                    type="number"
                    min="0"
                    step="0.01"
                    class="mt-1 block w-full"
                    placeholder="Ej: 15.00"
                />
                <p class="mt-1 text-xs text-gray-500">
                    Déjalo en blanco si no aplica.
                </p>
                <InputError class="mt-2" :message="form.errors.price" />
            </div>
        </div>

        <div class="space-y-3 rounded-2xl bg-hueco-cream/60 p-4">
            <label class="flex cursor-pointer items-center gap-3">
                <input
                    v-model="form.fixed_duration"
                    type="checkbox"
                    class="h-5 w-5 rounded border-gray-300 text-hueco-teal focus:ring-hueco-teal"
                />
                <div>
                    <div class="text-sm font-semibold text-hueco-black">Duración fija</div>
                    <div class="text-xs text-gray-600">
                        Si está activado, las reservas duran exactamente lo indicado arriba.
                        Si lo desactivas, las reservas se hacen seleccionando inicio y fin a medida (por ejemplo, arrastrando en el calendario).
                    </div>
                </div>
            </label>

            <label class="flex cursor-pointer items-center gap-3">
                <input
                    v-model="form.show_price"
                    type="checkbox"
                    class="h-5 w-5 rounded border-gray-300 text-hueco-teal focus:ring-hueco-teal"
                />
                <div>
                    <div class="text-sm font-semibold text-hueco-black">Mostrar precio al usuario</div>
                    <div class="text-xs text-gray-600">
                        Si está desactivado, el precio queda oculto en el portal del cliente.
                    </div>
                </div>
            </label>

            <label class="flex cursor-pointer items-center gap-3">
                <input
                    v-model="form.show_duration"
                    type="checkbox"
                    class="h-5 w-5 rounded border-gray-300 text-hueco-teal focus:ring-hueco-teal"
                />
                <div>
                    <div class="text-sm font-semibold text-hueco-black">Mostrar duración al usuario</div>
                    <div class="text-xs text-gray-600">
                        Si está desactivado, la duración queda oculta en el portal del cliente.
                    </div>
                </div>
            </label>
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
