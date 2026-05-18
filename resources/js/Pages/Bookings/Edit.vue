<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/vue3';

defineProps({
    booking: Object,
    spaces: Array,
});
</script>

<template>
    <Head title="Editar reserva" />
    <AuthenticatedLayout>
        <div class="p-6">TODO: formulario de edición de reserva (Alex)</div>
    </AuthenticatedLayout>
</template>
