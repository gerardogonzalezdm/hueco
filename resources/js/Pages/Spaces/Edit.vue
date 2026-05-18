<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/vue3';

defineProps({
    space: Object,
});
</script>

<template>
    <Head title="Editar espacio" />
    <AuthenticatedLayout>
        <div class="p-6">TODO: formulario de edición de espacio (Alex)</div>
    </AuthenticatedLayout>
</template>
