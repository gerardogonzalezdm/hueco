<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';

defineProps({
    bookings: Array,
});

const confirmDelete = (booking) => {
    if (confirm(`¿Eliminar la reserva de "${booking.client_name}"? Esta acción no se puede deshacer.`)) {
        router.delete(route('bookings.destroy', booking.id), { preserveScroll: true });
    }
};

const formatDateTime = (iso) => {
    if (!iso) return '';
    return new Date(iso).toLocaleString('es-ES', {
        day: '2-digit',
        month: 'short',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
    });
};

const statusBadge = (status) => {
    const map = {
        confirmed: { label: 'Confirmada', class: 'bg-hueco-green/20 text-emerald-800' },
        pending: { label: 'Pendiente', class: 'bg-hueco-yellow/30 text-yellow-900' },
        cancelled: { label: 'Cancelada', class: 'bg-gray-200 text-gray-600 line-through' },
    };
    return map[status] || map.pending;
};
</script>

<template>
    <Head title="Reservas" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <h2 class="text-xl font-semibold text-hueco-black dark:text-white">Reservas</h2>
                <Link
                    :href="route('bookings.create')"
                    class="rounded-full bg-hueco-yellow px-4 py-2 text-sm font-bold text-hueco-black shadow-sm transition hover:bg-yellow-300"
                >
                    + Nueva reserva
                </Link>
            </div>
        </template>

        <div class="py-10">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div class="overflow-hidden rounded-2xl bg-white shadow-sm dark:bg-gray-800">
                    <div v-if="bookings.length === 0" class="p-12 text-center">
                        <div class="text-5xl">📅</div>
                        <h3 class="mt-4 text-lg font-semibold text-hueco-black dark:text-white">
                            Aún no hay reservas
                        </h3>
                        <p class="mt-1 text-sm text-gray-500">
                            Crea la primera reserva para tu primer cliente.
                        </p>
                        <Link
                            :href="route('bookings.create')"
                            class="mt-6 inline-block rounded-full bg-hueco-yellow px-5 py-2 text-sm font-bold text-hueco-black shadow-sm transition hover:bg-yellow-300"
                        >
                            Crear primera reserva
                        </Link>
                    </div>

                    <table v-else class="min-w-full divide-y divide-hueco-cream dark:divide-gray-700">
                        <thead class="bg-hueco-cream/40">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-bold uppercase tracking-wider text-gray-600">
                                    Cliente
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-bold uppercase tracking-wider text-gray-600">
                                    Espacio
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-bold uppercase tracking-wider text-gray-600">
                                    Cuándo
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-bold uppercase tracking-wider text-gray-600">
                                    Estado
                                </th>
                                <th class="px-6 py-3 text-right text-xs font-bold uppercase tracking-wider text-gray-600">
                                    Acciones
                                </th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-hueco-cream dark:divide-gray-700">
                            <tr
                                v-for="b in bookings"
                                :key="b.id"
                                class="transition hover:bg-hueco-cream/30"
                            >
                                <td class="px-6 py-4">
                                    <div class="font-semibold text-hueco-black dark:text-white">
                                        {{ b.client_name }}
                                    </div>
                                    <div class="text-xs text-gray-500">{{ b.client_email }}</div>
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-700 dark:text-gray-300">
                                    {{ b.space?.name ?? '—' }}
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-700 dark:text-gray-300">
                                    {{ formatDateTime(b.time_start) }}
                                </td>
                                <td class="px-6 py-4">
                                    <span
                                        :class="['inline-flex rounded-full px-3 py-1 text-xs font-semibold', statusBadge(b.status).class]"
                                    >
                                        {{ statusBadge(b.status).label }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <Link
                                        :href="route('bookings.edit', b.id)"
                                        class="rounded px-3 py-1 text-sm font-semibold text-hueco-teal hover:underline"
                                    >
                                        Editar
                                    </Link>
                                    <button
                                        type="button"
                                        @click="confirmDelete(b)"
                                        class="rounded px-3 py-1 text-sm font-semibold text-red-600 hover:underline"
                                    >
                                        Eliminar
                                    </button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
