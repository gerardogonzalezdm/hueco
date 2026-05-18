<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { computed } from 'vue';

const props = defineProps({
    company: Object,
    stats: Object,
    upcoming: Array,
    top_spaces: Array,
    top_customers: Array,
    month_revenue: Number,
    bookings_by_day: Array,
});

const formatDateTime = (iso) => {
    if (!iso) return '';
    return new Date(iso).toLocaleString('es-ES', {
        day: '2-digit',
        month: '2-digit',
        hour: '2-digit',
        minute: '2-digit',
    });
};

const formatPrice = (price) =>
    new Intl.NumberFormat('es-ES', { style: 'currency', currency: 'EUR' }).format(price);

// Para escalar las barras: la barra más alta = 100%
const maxBookingsPerDay = computed(() => {
    const max = Math.max(...props.bookings_by_day.map((d) => d.count));
    return max > 0 ? max : 1;
});

const dayLabel = (date) => {
    return new Date(date).toLocaleDateString('es-ES', { day: '2-digit', month: 'short' });
};
</script>

<template>
    <Head title="Dashboard" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold text-hueco-black dark:text-white">
                {{ company?.name ?? 'Dashboard' }}
            </h2>
        </template>

        <div class="py-10">
            <div class="mx-auto max-w-7xl space-y-6 px-4 sm:px-6 lg:px-8">
                <!-- KPI cards -->
                <div class="grid grid-cols-2 gap-4 lg:grid-cols-4">
                    <Link
                        :href="route('spaces.index')"
                        class="block rounded-2xl border-l-4 border-hueco-teal bg-white p-5 shadow-sm transition hover:shadow-md dark:bg-gray-800"
                    >
                        <div class="text-xs font-medium uppercase tracking-wider text-gray-500">Espacios</div>
                        <div class="mt-2 text-3xl font-bold text-hueco-black dark:text-white">{{ stats.spaces }}</div>
                    </Link>

                    <Link
                        :href="route('bookings.index')"
                        class="block rounded-2xl border-l-4 border-hueco-green bg-white p-5 shadow-sm transition hover:shadow-md dark:bg-gray-800"
                    >
                        <div class="text-xs font-medium uppercase tracking-wider text-gray-500">Reservas totales</div>
                        <div class="mt-2 text-3xl font-bold text-hueco-black dark:text-white">{{ stats.bookings_total }}</div>
                    </Link>

                    <div class="rounded-2xl border-l-4 border-hueco-yellow bg-white p-5 shadow-sm dark:bg-gray-800">
                        <div class="text-xs font-medium uppercase tracking-wider text-gray-500">Próximas</div>
                        <div class="mt-2 text-3xl font-bold text-hueco-black dark:text-white">{{ stats.bookings_upcoming }}</div>
                    </div>

                    <Link
                        :href="route('customers.index')"
                        class="block rounded-2xl border-l-4 border-hueco-black bg-white p-5 shadow-sm transition hover:shadow-md dark:bg-gray-800"
                    >
                        <div class="text-xs font-medium uppercase tracking-wider text-gray-500">Clientes</div>
                        <div class="mt-2 text-3xl font-bold text-hueco-black dark:text-white">{{ stats.customers }}</div>
                    </Link>
                </div>

                <!-- Ingresos mes -->
                <div class="rounded-2xl bg-gradient-to-br from-hueco-teal to-hueco-green p-6 text-white shadow-sm">
                    <div class="text-xs font-bold uppercase tracking-wider opacity-90">Ingresos estimados este mes</div>
                    <div class="mt-2 text-4xl font-extrabold">{{ formatPrice(month_revenue) }}</div>
                    <p class="mt-1 text-xs opacity-80">
                        Calculado sumando el precio del espacio por cada reserva confirmada.
                    </p>
                </div>

                <!-- Gráfico de reservas por día -->
                <div class="rounded-2xl bg-white p-6 shadow-sm dark:bg-gray-800">
                    <h3 class="mb-4 font-semibold text-hueco-black dark:text-white">Reservas — últimos 14 días</h3>
                    <div class="flex h-40 items-end gap-1">
                        <div
                            v-for="day in bookings_by_day"
                            :key="day.date"
                            class="flex flex-1 flex-col items-center gap-1"
                        >
                            <div class="text-[10px] font-semibold text-gray-600">{{ day.count }}</div>
                            <div
                                :style="{ height: `${(day.count / maxBookingsPerDay) * 100}%` }"
                                class="w-full rounded-t bg-hueco-teal"
                            ></div>
                            <div class="rotate-45 text-[9px] text-gray-500">{{ dayLabel(day.date) }}</div>
                        </div>
                    </div>
                </div>

                <!-- Top espacios y top clientes -->
                <div class="grid gap-6 lg:grid-cols-2">
                    <div class="rounded-2xl bg-white p-6 shadow-sm dark:bg-gray-800">
                        <h3 class="mb-4 font-semibold text-hueco-black dark:text-white">Top espacios más reservados</h3>
                        <ul v-if="top_spaces.length" class="divide-y divide-hueco-cream">
                            <li v-for="(s, i) in top_spaces" :key="s.id" class="flex justify-between py-2">
                                <span class="font-medium text-hueco-black">
                                    <span class="mr-2 text-xs text-gray-400">#{{ i + 1 }}</span>
                                    {{ s.name }}
                                </span>
                                <span class="text-sm font-semibold text-hueco-teal">
                                    {{ s.bookings_count }} reservas
                                </span>
                            </li>
                        </ul>
                        <p v-else class="text-sm text-gray-500">Aún no hay datos.</p>
                    </div>

                    <div class="rounded-2xl bg-white p-6 shadow-sm dark:bg-gray-800">
                        <h3 class="mb-4 font-semibold text-hueco-black dark:text-white">Top clientes activos</h3>
                        <ul v-if="top_customers.length" class="divide-y divide-hueco-cream">
                            <li v-for="(c, i) in top_customers" :key="c.id" class="flex justify-between py-2">
                                <div>
                                    <div class="font-medium text-hueco-black">
                                        <span class="mr-2 text-xs text-gray-400">#{{ i + 1 }}</span>
                                        {{ c.name }}
                                    </div>
                                    <div class="text-xs text-gray-500">{{ c.email }}</div>
                                </div>
                                <span class="text-sm font-semibold text-hueco-green">
                                    {{ c.bookings_count }} reservas
                                </span>
                            </li>
                        </ul>
                        <p v-else class="text-sm text-gray-500">Aún no hay clientes registrados.</p>
                    </div>
                </div>

                <!-- Próximas reservas (lista corta) -->
                <div class="rounded-2xl bg-white shadow-sm dark:bg-gray-800">
                    <div class="border-b border-hueco-cream px-6 py-4">
                        <h3 class="font-semibold text-hueco-black dark:text-white">Próximas reservas</h3>
                    </div>
                    <div class="p-6">
                        <p v-if="upcoming.length === 0" class="text-sm text-gray-500">
                            No hay reservas próximas.
                            <Link
                                :href="route('bookings.create')"
                                class="font-semibold text-hueco-teal hover:underline"
                            >
                                Crear la primera
                            </Link>
                        </p>
                        <ul v-else class="divide-y divide-hueco-cream">
                            <li
                                v-for="b in upcoming"
                                :key="b.id"
                                class="flex justify-between py-3"
                            >
                                <div>
                                    <div class="font-medium text-hueco-black dark:text-gray-100">
                                        {{ b.client_name }}
                                    </div>
                                    <div class="text-sm text-gray-500">
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
