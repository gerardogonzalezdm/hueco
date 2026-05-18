<script setup>
import CustomerLayout from '@/Layouts/CustomerLayout.vue';
import { Head, Link } from '@inertiajs/vue3';

defineProps({
    company: Object,
    stats: Object,
    upcoming: Array,
});

const formatDateTime = (iso) => {
    if (!iso) return '';
    return new Date(iso).toLocaleString('es-ES', {
        weekday: 'long',
        day: 'numeric',
        month: 'long',
        hour: '2-digit',
        minute: '2-digit',
    });
};
</script>

<template>
    <Head title="Mi área" />

    <CustomerLayout>
        <template #header>
            <h2 class="text-xl font-semibold text-hueco-black">
                Hola, {{ $page.props.auth.user.name }} 👋
            </h2>
        </template>

        <div class="py-10">
            <div class="mx-auto max-w-5xl space-y-6 px-4 sm:px-6 lg:px-8">
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <div class="rounded-2xl border-l-4 border-hueco-teal bg-white p-6 shadow-sm">
                        <div class="text-sm font-medium text-gray-500">Próximas reservas</div>
                        <div class="mt-2 text-3xl font-bold text-hueco-black">{{ stats.upcoming }}</div>
                    </div>
                    <div class="rounded-2xl border-l-4 border-hueco-green bg-white p-6 shadow-sm">
                        <div class="text-sm font-medium text-gray-500">Total reservas hechas</div>
                        <div class="mt-2 text-3xl font-bold text-hueco-black">{{ stats.total }}</div>
                    </div>
                </div>

                <div class="rounded-2xl bg-hueco-yellow p-6 text-hueco-black shadow-sm">
                    <h3 class="text-lg font-bold">¿Necesitas una sala?</h3>
                    <p class="mt-1 text-sm">
                        Reserva tu próximo espacio en {{ company?.name }}.
                    </p>
                    <Link
                        :href="route('portal.bookings.create')"
                        class="mt-4 inline-block rounded-full bg-hueco-black px-5 py-2 text-sm font-bold text-white transition hover:bg-gray-800"
                    >
                        + Nueva reserva
                    </Link>
                </div>

                <div class="rounded-2xl bg-white shadow-sm">
                    <div class="border-b border-hueco-cream px-6 py-4">
                        <h3 class="font-semibold text-hueco-black">Tus próximas reservas</h3>
                    </div>
                    <div class="p-6">
                        <p v-if="upcoming.length === 0" class="text-sm text-gray-500">
                            No tienes reservas próximas.
                            <Link
                                :href="route('portal.bookings.create')"
                                class="font-semibold text-hueco-teal hover:underline"
                            >
                                Crea la primera
                            </Link>
                        </p>
                        <ul v-else class="divide-y divide-hueco-cream">
                            <li
                                v-for="b in upcoming"
                                :key="b.id"
                                class="flex justify-between py-3"
                            >
                                <div>
                                    <div class="font-medium text-hueco-black">
                                        {{ b.space?.name }}
                                    </div>
                                    <div class="text-sm text-gray-500">
                                        {{ formatDateTime(b.time_start) }}
                                    </div>
                                </div>
                                <Link
                                    :href="route('portal.bookings.show', b.id)"
                                    class="text-sm font-semibold text-hueco-teal hover:underline"
                                >
                                    Ver detalle
                                </Link>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </CustomerLayout>
</template>
