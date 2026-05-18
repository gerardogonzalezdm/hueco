<script setup>
import SuperadminLayout from '@/Layouts/SuperadminLayout.vue';
import { Head, Link } from '@inertiajs/vue3';

defineProps({
    stats: Object,
    top_companies: Array,
});
</script>

<template>
    <Head title="Super-admin · Inicio" />

    <SuperadminLayout>
        <template #header>
            <h2 class="text-xl font-semibold text-hueco-black">Visión general de Hueco</h2>
        </template>

        <div class="py-10">
            <div class="mx-auto max-w-7xl space-y-6 px-4 sm:px-6 lg:px-8">
                <div class="grid grid-cols-2 gap-4 lg:grid-cols-4">
                    <Link
                        :href="route('superadmin.companies.index')"
                        class="block rounded-2xl border-l-4 border-hueco-yellow bg-white p-5 shadow-sm transition hover:shadow-md"
                    >
                        <div class="text-xs font-bold uppercase tracking-wider text-gray-500">Empresas</div>
                        <div class="mt-2 text-3xl font-extrabold text-hueco-black">{{ stats.companies }}</div>
                    </Link>
                    <div class="rounded-2xl border-l-4 border-hueco-teal bg-white p-5 shadow-sm">
                        <div class="text-xs font-bold uppercase tracking-wider text-gray-500">Admins de empresa</div>
                        <div class="mt-2 text-3xl font-extrabold text-hueco-black">{{ stats.admins }}</div>
                    </div>
                    <div class="rounded-2xl border-l-4 border-hueco-green bg-white p-5 shadow-sm">
                        <div class="text-xs font-bold uppercase tracking-wider text-gray-500">Clientes</div>
                        <div class="mt-2 text-3xl font-extrabold text-hueco-black">{{ stats.customers }}</div>
                    </div>
                    <Link
                        :href="route('superadmin.admins.index')"
                        class="block rounded-2xl border-l-4 border-hueco-black bg-white p-5 shadow-sm transition hover:shadow-md"
                    >
                        <div class="text-xs font-bold uppercase tracking-wider text-gray-500">Super-admins</div>
                        <div class="mt-2 text-3xl font-extrabold text-hueco-black">{{ stats.superadmins }}</div>
                    </Link>
                </div>

                <div class="grid grid-cols-2 gap-4 lg:grid-cols-3">
                    <div class="rounded-2xl bg-white p-5 shadow-sm">
                        <div class="text-xs font-bold uppercase tracking-wider text-gray-500">Espacios totales</div>
                        <div class="mt-2 text-2xl font-bold text-hueco-black">{{ stats.spaces }}</div>
                    </div>
                    <div class="rounded-2xl bg-white p-5 shadow-sm">
                        <div class="text-xs font-bold uppercase tracking-wider text-gray-500">Reservas históricas</div>
                        <div class="mt-2 text-2xl font-bold text-hueco-black">{{ stats.bookings_total }}</div>
                    </div>
                    <div class="rounded-2xl bg-white p-5 shadow-sm">
                        <div class="text-xs font-bold uppercase tracking-wider text-gray-500">Reservas próximas</div>
                        <div class="mt-2 text-2xl font-bold text-hueco-teal">{{ stats.bookings_upcoming }}</div>
                    </div>
                </div>

                <div class="rounded-2xl bg-white p-6 shadow-sm">
                    <div class="mb-4 flex items-center justify-between">
                        <h3 class="font-semibold text-hueco-black">Top empresas por reservas</h3>
                        <Link
                            :href="route('superadmin.companies.create')"
                            class="rounded-full bg-hueco-yellow px-4 py-2 text-sm font-bold text-hueco-black shadow-sm hover:bg-yellow-300"
                        >
                            + Nueva empresa
                        </Link>
                    </div>
                    <ul v-if="top_companies.length" class="divide-y divide-hueco-cream">
                        <li
                            v-for="(c, i) in top_companies"
                            :key="c.id"
                            class="flex justify-between py-3"
                        >
                            <Link
                                :href="route('superadmin.companies.edit', c.id)"
                                class="flex items-center gap-3 hover:underline"
                            >
                                <span class="text-xs text-gray-400">#{{ i + 1 }}</span>
                                <span class="font-medium text-hueco-black">{{ c.name }}</span>
                                <span class="text-xs text-gray-500">/c/{{ c.slug }}</span>
                            </Link>
                            <span class="text-sm font-semibold text-hueco-teal">
                                {{ c.bookings_count }} reservas
                            </span>
                        </li>
                    </ul>
                    <p v-else class="text-sm text-gray-500">Aún no hay empresas creadas.</p>
                </div>
            </div>
        </div>
    </SuperadminLayout>
</template>
