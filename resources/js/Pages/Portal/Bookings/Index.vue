<script setup>
import CustomerLayout from '@/Layouts/CustomerLayout.vue';
import { Head, Link } from '@inertiajs/vue3';

defineProps({
    bookings: Array,
});

const formatDateTime = (iso) =>
    new Date(iso).toLocaleString('es-ES', {
        day: '2-digit',
        month: 'short',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
    });

const statusBadge = (status) => {
    const map = {
        confirmed: { label: 'Confirmada', class: 'bg-hueco-green/20 text-emerald-800' },
        pending: { label: 'Pendiente', class: 'bg-hueco-yellow/30 text-yellow-900' },
        cancelled: { label: 'Cancelada', class: 'bg-gray-200 text-gray-500 line-through' },
    };
    return map[status] || map.pending;
};
</script>

<template>
    <Head title="Mis reservas" />

    <CustomerLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <h2 class="text-xl font-semibold text-hueco-black">Mis reservas</h2>
                <Link
                    :href="route('portal.bookings.create')"
                    class="rounded-full bg-hueco-yellow px-4 py-2 text-sm font-bold text-hueco-black shadow-sm transition hover:bg-yellow-300"
                >
                    + Nueva reserva
                </Link>
            </div>
        </template>

        <div class="py-10">
            <div class="mx-auto max-w-5xl px-4 sm:px-6 lg:px-8">
                <div class="overflow-hidden rounded-2xl bg-white shadow-sm">
                    <div v-if="bookings.length === 0" class="p-12 text-center">
                        <div class="text-5xl">📅</div>
                        <h3 class="mt-4 text-lg font-semibold text-hueco-black">
                            Aún no tienes reservas
                        </h3>
                        <Link
                            :href="route('portal.bookings.create')"
                            class="mt-6 inline-block rounded-full bg-hueco-yellow px-5 py-2 text-sm font-bold text-hueco-black shadow-sm transition hover:bg-yellow-300"
                        >
                            Reservar la primera
                        </Link>
                    </div>

                    <ul v-else class="divide-y divide-hueco-cream">
                        <li v-for="b in bookings" :key="b.id">
                            <Link
                                :href="route('portal.bookings.show', b.id)"
                                class="flex items-center justify-between gap-4 px-6 py-4 transition hover:bg-hueco-cream/30"
                            >
                                <div>
                                    <div class="font-semibold text-hueco-black">{{ b.space?.name }}</div>
                                    <div class="text-sm text-gray-500">{{ formatDateTime(b.time_start) }}</div>
                                </div>
                                <span
                                    :class="['inline-flex rounded-full px-3 py-1 text-xs font-semibold', statusBadge(b.status).class]"
                                >
                                    {{ statusBadge(b.status).label }}
                                </span>
                            </Link>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </CustomerLayout>
</template>
