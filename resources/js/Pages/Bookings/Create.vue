<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import BookingForm from '@/Components/BookingForm.vue';
import { Head, Link, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

defineProps({
    spaces: Array,
});

const isAdmin = computed(() => usePage().props.auth?.user?.role === 'admin');
</script>

<template>
    <Head title="Nueva reserva" />
    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold text-hueco-black dark:text-white">
                Nueva reserva
            </h2>
        </template>

        <div class="py-10">
            <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
                <div v-if="spaces.length === 0" class="rounded-2xl bg-white p-12 text-center shadow-sm">
                    <div class="text-5xl">⚠️</div>
                    <template v-if="isAdmin">
                        <h3 class="mt-4 text-lg font-semibold text-hueco-black">
                            Necesitas crear un espacio primero
                        </h3>
                        <p class="mt-1 text-sm text-gray-500">
                            Antes de aceptar reservas, define al menos un espacio reservable.
                        </p>
                        <Link
                            :href="route('spaces.create')"
                            class="mt-6 inline-block rounded-full bg-hueco-yellow px-5 py-2 text-sm font-bold text-hueco-black shadow-sm transition hover:bg-yellow-300"
                        >
                            Crear primer espacio
                        </Link>
                    </template>
                    <template v-else>
                        <h3 class="mt-4 text-lg font-semibold text-hueco-black">
                            Aún no hay espacios disponibles
                        </h3>
                        <p class="mt-1 text-sm text-gray-500">
                            Pídele al administrador de tu empresa que cree los espacios reservables.
                        </p>
                    </template>
                </div>

                <div v-else class="rounded-2xl bg-white p-8 shadow-sm dark:bg-gray-800">
                    <BookingForm
                        :spaces="spaces"
                        :submit-url="route('bookings.store')"
                        :cancel-url="route('bookings.index')"
                        method="post"
                        submit-label="Crear reserva"
                    />
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
