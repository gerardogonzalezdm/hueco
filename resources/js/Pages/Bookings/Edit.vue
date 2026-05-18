<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import BookingForm from '@/Components/BookingForm.vue';
import { Head } from '@inertiajs/vue3';

defineProps({
    booking: Object,
    spaces: Array,
});
</script>

<template>
    <Head :title="`Editar reserva — ${booking.client_name}`" />
    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold text-hueco-black dark:text-white">
                Editar reserva: {{ booking.client_name }}
            </h2>
        </template>

        <div class="py-10">
            <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
                <div class="rounded-2xl bg-white p-8 shadow-sm dark:bg-gray-800">
                    <BookingForm
                        :booking="booking"
                        :spaces="spaces"
                        :submit-url="route('bookings.update', booking.id)"
                        :cancel-url="route('bookings.index')"
                        method="put"
                        submit-label="Guardar cambios"
                    />
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
