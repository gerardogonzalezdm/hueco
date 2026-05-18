<script setup>
import ApplicationLogo from '@/Components/ApplicationLogo.vue';
import { Head, Link, router } from '@inertiajs/vue3';

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

const isCancelled = () => props.booking.status === 'cancelled';

const cancelBooking = () => {
    if (
        confirm(
            '¿Cancelar esta reserva? Se le enviará una notificación al email registrado.',
        )
    ) {
        router.post(route('public.bookings.cancel', props.booking.manage_token));
    }
};
</script>

<template>
    <Head title="Mi reserva" />

    <div class="min-h-screen bg-hueco-cream">
        <header class="mx-auto flex max-w-3xl items-center justify-between px-6 py-6">
            <Link
                :href="`/c/${booking.company.slug}`"
                class="flex items-center gap-2"
            >
                <ApplicationLogo class="h-8 w-8" />
                <span class="font-bold text-hueco-black">{{ booking.company.name }}</span>
            </Link>
        </header>

        <main class="mx-auto max-w-3xl px-6 pb-16">
            <div class="rounded-3xl bg-white p-8 shadow-sm sm:p-10">
                <span
                    v-if="isCancelled()"
                    class="inline-block rounded-full bg-gray-200 px-3 py-1 text-xs font-bold uppercase tracking-wider text-gray-600"
                >
                    Reserva cancelada
                </span>
                <span
                    v-else
                    class="inline-block rounded-full bg-hueco-green/20 px-3 py-1 text-xs font-bold uppercase tracking-wider text-emerald-800"
                >
                    Reserva confirmada
                </span>

                <h1 class="mt-4 text-3xl font-extrabold text-hueco-black">
                    Hola {{ booking.client_name }}
                </h1>

                <div class="mt-8 space-y-4 rounded-2xl bg-hueco-cream p-6">
                    <div>
                        <div class="text-xs uppercase tracking-wider text-gray-500">
                            Espacio
                        </div>
                        <div class="mt-1 text-lg font-semibold text-hueco-black">
                            {{ booking.space?.name }}
                        </div>
                    </div>

                    <div>
                        <div class="text-xs uppercase tracking-wider text-gray-500">
                            Cuándo
                        </div>
                        <div class="mt-1 text-lg font-semibold text-hueco-black">
                            {{ formatDateTime(booking.time_start) }}
                        </div>
                        <div class="text-sm text-gray-600">
                            Hasta {{ new Date(booking.time_end).toLocaleTimeString('es-ES', { hour: '2-digit', minute: '2-digit' }) }}
                        </div>
                    </div>

                    <div class="grid gap-4 sm:grid-cols-2">
                        <div>
                            <div class="text-xs uppercase tracking-wider text-gray-500">
                                Email
                            </div>
                            <div class="mt-1 font-medium">{{ booking.client_email }}</div>
                        </div>
                        <div v-if="booking.client_phone">
                            <div class="text-xs uppercase tracking-wider text-gray-500">
                                Teléfono
                            </div>
                            <div class="mt-1 font-medium">{{ booking.client_phone }}</div>
                        </div>
                    </div>
                </div>

                <div v-if="!isCancelled()" class="mt-8 flex justify-end">
                    <button
                        type="button"
                        @click="cancelBooking"
                        class="rounded-full border-2 border-red-500 px-5 py-2 text-sm font-bold text-red-600 transition hover:bg-red-500 hover:text-white"
                    >
                        Cancelar reserva
                    </button>
                </div>

                <div v-else class="mt-8 text-center text-sm text-gray-500">
                    Esta reserva ya está cancelada. Si necesitas hacer otra,
                    <Link
                        :href="route('public.bookings.create', booking.company.slug)"
                        class="font-semibold text-hueco-teal hover:underline"
                    >
                        crea una nueva
                    </Link>.
                </div>
            </div>
        </main>
    </div>
</template>
