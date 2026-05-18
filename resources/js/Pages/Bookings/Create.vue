<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/vue3';

defineProps({
    spaces: Array,
});
</script>

<template>
    <Head title="Nueva reserva" />
    <AuthenticatedLayout>
        <div class="p-6">TODO: formulario de creación de reserva (Alex)</div>
    </AuthenticatedLayout>
</template>
