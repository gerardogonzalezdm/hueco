<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';

defineProps({
    spaces: Array,
});
</script>

<template>
    <Head title="Espacios" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200">
                Espacios
            </h2>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg dark:bg-gray-800">
                    <div class="p-6 text-gray-900 dark:text-gray-100">
                        <Link
                            :href="route('spaces.create')"
                            class="rounded bg-indigo-600 px-4 py-2 text-white"
                        >
                            Nuevo espacio
                        </Link>

                        <ul class="mt-4 space-y-2">
                            <li
                                v-for="space in spaces"
                                :key="space.id"
                                class="flex justify-between border-b py-2"
                            >
                                <span>{{ space.name }}</span>
                                <Link
                                    :href="route('spaces.edit', space.id)"
                                    class="text-indigo-600 hover:underline"
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
