<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import SpaceForm from '@/Components/SpaceForm.vue';
import { Head, Link } from '@inertiajs/vue3';
</script>

<template>
    <Head title="Nuevo espacio" />
    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold text-hueco-black dark:text-white">
                Nuevo espacio
            </h2>
        </template>

        <div class="py-10">
            <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
                <div class="rounded-2xl bg-white p-8 shadow-sm dark:bg-gray-800">
                    <p class="mb-6 text-sm text-gray-600">
                        Define un espacio reservable: nombre, duración por defecto y precio.
                        Podrás editarlo en cualquier momento.
                    </p>

                    <SpaceForm
                        :submit-url="route('spaces.store')"
                        :cancel-url="route('spaces.index')"
                        method="post"
                        submit-label="Crear espacio"
                    />
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
