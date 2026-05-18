<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import SpaceForm from '@/Components/SpaceForm.vue';
import { Head } from '@inertiajs/vue3';

defineProps({
    space: Object,
});
</script>

<template>
    <Head :title="`Editar — ${space.name}`" />
    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold text-hueco-black dark:text-white">
                Editar espacio: {{ space.name }}
            </h2>
        </template>

        <div class="py-10">
            <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
                <div class="rounded-2xl bg-white p-8 shadow-sm dark:bg-gray-800">
                    <SpaceForm
                        :space="space"
                        :submit-url="route('spaces.update', space.id)"
                        :cancel-url="route('spaces.index')"
                        method="put"
                        submit-label="Guardar cambios"
                    />
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
