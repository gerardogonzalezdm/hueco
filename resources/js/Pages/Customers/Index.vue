<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/vue3';

defineProps({
    customers: Array,
});

const formatDate = (iso) =>
    new Date(iso).toLocaleDateString('es-ES', { day: '2-digit', month: 'short', year: 'numeric' });
</script>

<template>
    <Head title="Clientes" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold text-hueco-black dark:text-white">Clientes</h2>
        </template>

        <div class="py-10">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div class="overflow-hidden rounded-2xl bg-white shadow-sm dark:bg-gray-800">
                    <div v-if="customers.length === 0" class="p-12 text-center">
                        <div class="text-5xl">👥</div>
                        <h3 class="mt-4 text-lg font-semibold text-hueco-black dark:text-white">
                            Aún no tienes clientes registrados
                        </h3>
                        <p class="mt-1 text-sm text-gray-500">
                            Cuando alguien cree una cuenta desde tu portal público aparecerá aquí.
                        </p>
                    </div>

                    <table v-else class="min-w-full divide-y divide-hueco-cream dark:divide-gray-700">
                        <thead class="bg-hueco-cream/40">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-bold uppercase tracking-wider text-gray-600">
                                    Nombre
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-bold uppercase tracking-wider text-gray-600">
                                    Email
                                </th>
                                <th class="px-6 py-3 text-center text-xs font-bold uppercase tracking-wider text-gray-600">
                                    Reservas
                                </th>
                                <th class="px-6 py-3 text-center text-xs font-bold uppercase tracking-wider text-gray-600">
                                    Próximas
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-bold uppercase tracking-wider text-gray-600">
                                    Alta
                                </th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-hueco-cream dark:divide-gray-700">
                            <tr
                                v-for="c in customers"
                                :key="c.id"
                                class="transition hover:bg-hueco-cream/30"
                            >
                                <td class="px-6 py-4 font-semibold text-hueco-black dark:text-white">
                                    {{ c.name }}
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-700 dark:text-gray-300">
                                    {{ c.email }}
                                </td>
                                <td class="px-6 py-4 text-center text-sm text-gray-700 dark:text-gray-300">
                                    {{ c.bookings_count }}
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <span
                                        v-if="c.upcoming_bookings_count > 0"
                                        class="inline-flex rounded-full bg-hueco-green/20 px-2 py-0.5 text-xs font-semibold text-emerald-800"
                                    >
                                        {{ c.upcoming_bookings_count }}
                                    </span>
                                    <span v-else class="text-xs text-gray-400">—</span>
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-500">
                                    {{ formatDate(c.created_at) }}
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
