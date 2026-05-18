<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';

defineProps({
    bookings: Array,
});
</script>

<template>
    <Head title="Reservas" />
    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200">
                Reservas
            </h2>
        </template>
        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg dark:bg-gray-800">
                    <div class="p-6 text-gray-900 dark:text-gray-100">
                        <Link
                            :href="route('bookings.create')"
                            class="rounded bg-indigo-600 px-4 py-2 text-white"
                        >
                            Nueva reserva
                        </Link>
                        <ul class="mt-4 space-y-2">
                            <li
                                v-for="b in bookings"
                                :key="b.id"
                                class="border-b py-2"
                            >
                                {{ b.time_start }} — {{ b.space?.name }} — {{ b.client_name }}
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
