<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';

defineProps({
    company: Object,
    stats: Object,
    upcoming: Array,
});

const formatDateTime = (iso) => {
    if (!iso) return '';
    const d = new Date(iso);
    return d.toLocaleString('es-ES', {
        day: '2-digit',
        month: '2-digit',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
    });
};
</script>

<template>
    <Head title="Dashboard" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200">
                {{ company?.name ?? 'Dashboard' }}
            </h2>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl space-y-6 sm:px-6 lg:px-8">
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
                    <Link
                        :href="route('spaces.index')"
                        class="group block rounded-2xl border-l-4 border-hueco-teal bg-white p-6 shadow-sm transition hover:shadow-md dark:bg-gray-800"
                    >
                        <div class="text-sm font-medium text-gray-500 dark:text-gray-400">Espacios</div>
                        <div class="mt-2 text-3xl font-bold text-hueco-black dark:text-white">
                            {{ stats.spaces }}
                        </div>
                    </Link>

                    <Link
                        :href="route('bookings.index')"
                        class="group block rounded-2xl border-l-4 border-hueco-green bg-white p-6 shadow-sm transition hover:shadow-md dark:bg-gray-800"
                    >
                        <div class="text-sm font-medium text-gray-500 dark:text-gray-400">Reservas totales</div>
                        <div class="mt-2 text-3xl font-bold text-hueco-black dark:text-white">
                            {{ stats.bookings_total }}
                        </div>
                    </Link>

                    <div class="rounded-2xl border-l-4 border-hueco-yellow bg-white p-6 shadow-sm dark:bg-gray-800">
                        <div class="text-sm font-medium text-gray-500 dark:text-gray-400">Próximas</div>
                        <div class="mt-2 text-3xl font-bold text-hueco-black dark:text-white">
                            {{ stats.bookings_upcoming }}
                        </div>
                    </div>
                </div>

                <div class="rounded-2xl bg-white shadow-sm dark:bg-gray-800">
                    <div class="border-b border-hueco-cream px-6 py-4 dark:border-gray-700">
                        <h3 class="font-semibold text-hueco-black dark:text-white">
                            Próximas reservas
                        </h3>
                    </div>
                    <div class="p-6">
                        <p
                            v-if="upcoming.length === 0"
                            class="text-sm text-gray-500 dark:text-gray-400"
                        >
                            No hay reservas próximas.
                            <Link
                                :href="route('bookings.create')"
                                class="font-semibold text-hueco-teal hover:underline"
                            >
                                Crear la primera
                            </Link>
                        </p>
                        <ul v-else class="divide-y divide-hueco-cream dark:divide-gray-700">
                            <li
                                v-for="b in upcoming"
                                :key="b.id"
                                class="flex justify-between py-3"
                            >
                                <div>
                                    <div class="font-medium text-hueco-black dark:text-gray-100">
                                        {{ b.client_name }}
                                    </div>
                                    <div class="text-sm text-gray-500 dark:text-gray-400">
                                        {{ b.space?.name }} · {{ formatDateTime(b.time_start) }}
                                    </div>
                                </div>
                                <Link
                                    :href="route('bookings.edit', b.id)"
                                    class="text-sm font-semibold text-hueco-teal hover:underline"
                                >
                                    Editar
                                </Link>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
