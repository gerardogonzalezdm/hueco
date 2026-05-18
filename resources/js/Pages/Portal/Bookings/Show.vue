<script setup>
import CustomerLayout from '@/Layouts/CustomerLayout.vue';
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

const props = defineProps({
    booking: Object,
});

const formatDateTime = (iso) =>
    new Date(iso).toLocaleString('es-ES', {
        weekday: 'long',
        day: 'numeric',
        month: 'long',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
    });

const isCancelled = computed(() => props.booking.status === 'cancelled');
const flash = computed(() => usePage().props.flash || {});

const cancelBooking = () => {
    if (confirm('¿Cancelar esta reserva? Recibirás un email confirmando la cancelación.')) {
        router.post(route('portal.bookings.cancel', props.booking.id));
    }
};
</script>

<template>
    <Head title="Detalle reserva" />

    <CustomerLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <h2 class="text-xl font-semibold text-hueco-black">Detalle de tu reserva</h2>
                <Link
                    :href="route('portal.bookings.index')"
                    class="text-sm font-semibold text-hueco-teal hover:underline"
                >
                    ← Volver a mis reservas
                </Link>
            </div>
        </template>

        <div class="py-10">
            <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
                <div
                    v-if="flash.success"
                    class="mb-4 rounded-lg bg-hueco-green/20 px-4 py-3 text-sm font-semibold text-emerald-800"
                >
                    {{ flash.success }}
                </div>

                <div class="rounded-2xl bg-white p-8 shadow-sm">
                    <span
                        v-if="isCancelled"
                        class="inline-block rounded-full bg-gray-200 px-3 py-1 text-xs font-bold uppercase tracking-wider text-gray-600"
                    >
                        Cancelada
                    </span>
                    <span
                        v-else
                        class="inline-block rounded-full bg-hueco-green/20 px-3 py-1 text-xs font-bold uppercase tracking-wider text-emerald-800"
                    >
                        Confirmada
                    </span>

                    <div class="mt-6 space-y-4 rounded-2xl bg-hueco-cream p-6">
                        <div>
                            <div class="text-xs uppercase tracking-wider text-gray-500">Espacio</div>
                            <div class="mt-1 text-lg font-semibold text-hueco-black">
                                {{ booking.space?.name }}
                            </div>
                        </div>

                        <div>
                            <div class="text-xs uppercase tracking-wider text-gray-500">Cuándo</div>
                            <div class="mt-1 text-lg font-semibold text-hueco-black">
                                {{ formatDateTime(booking.time_start) }}
                            </div>
                            <div class="text-sm text-gray-600">
                                Hasta {{ new Date(booking.time_end).toLocaleTimeString('es-ES', { hour: '2-digit', minute: '2-digit' }) }}
                            </div>
                        </div>

                        <div v-if="booking.client_notes">
                            <div class="text-xs uppercase tracking-wider text-gray-500">Notas</div>
                            <div class="mt-1 whitespace-pre-line text-gray-700">
                                {{ booking.client_notes }}
                            </div>
                        </div>
                    </div>

                    <div v-if="!isCancelled" class="mt-8 flex justify-end">
                        <button
                            type="button"
                            @click="cancelBooking"
                            class="rounded-full border-2 border-red-500 px-5 py-2 text-sm font-bold text-red-600 transition hover:bg-red-500 hover:text-white"
                        >
                            Cancelar reserva
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </CustomerLayout>
</template>
