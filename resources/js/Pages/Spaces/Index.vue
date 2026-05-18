<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

defineProps({
    spaces: Array,
});

const isAdmin = computed(() => usePage().props.auth?.user?.role === 'admin');

const confirmDelete = (space) => {
    if (confirm(`¿Eliminar el espacio "${space.name}"? Esta acción no se puede deshacer.`)) {
        router.delete(route('spaces.destroy', space.id), { preserveScroll: true });
    }
};

const formatPrice = (price) => {
    if (price === null || price === undefined || price === '') return '—';
    return new Intl.NumberFormat('es-ES', {
        style: 'currency',
        currency: 'EUR',
    }).format(price);
};

const formatDuration = (minutes) => {
    if (!minutes) return '—';
    if (minutes < 60) return `${minutes} min`;
    const h = Math.floor(minutes / 60);
    const m = minutes % 60;
    return m === 0 ? `${h} h` : `${h} h ${m} min`;
};
</script>

<template>
    <Head title="Espacios" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <h2 class="text-xl font-semibold text-hueco-black dark:text-white">Espacios</h2>
                <Link
                    v-if="isAdmin"
                    :href="route('spaces.create')"
                    class="rounded-full bg-hueco-yellow px-4 py-2 text-sm font-bold text-hueco-black shadow-sm transition hover:bg-yellow-300"
                >
                    + Nuevo espacio
                </Link>
            </div>
        </template>

        <div class="py-10">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div class="overflow-hidden rounded-2xl bg-white shadow-sm dark:bg-gray-800">
                    <div v-if="spaces.length === 0" class="p-12 text-center">
                        <div class="text-5xl">🏢</div>
                        <template v-if="isAdmin">
                            <h3 class="mt-4 text-lg font-semibold text-hueco-black dark:text-white">
                                Todavía no has creado ningún espacio
                            </h3>
                            <p class="mt-1 text-sm text-gray-500">
                                Empieza añadiendo tus salas, despachos o cabinas reservables.
                            </p>
                            <Link
                                :href="route('spaces.create')"
                                class="mt-6 inline-block rounded-full bg-hueco-yellow px-5 py-2 text-sm font-bold text-hueco-black shadow-sm transition hover:bg-yellow-300"
                            >
                                Crear mi primer espacio
                            </Link>
                        </template>
                        <template v-else>
                            <h3 class="mt-4 text-lg font-semibold text-hueco-black dark:text-white">
                                No hay espacios disponibles todavía
                            </h3>
                            <p class="mt-1 text-sm text-gray-500">
                                Pídele al administrador de tu empresa que cree espacios reservables.
                            </p>
                        </template>
                    </div>

                    <table v-else class="min-w-full divide-y divide-hueco-cream dark:divide-gray-700">
                        <thead class="bg-hueco-cream/40">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-bold uppercase tracking-wider text-gray-600">
                                    Nombre
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-bold uppercase tracking-wider text-gray-600">
                                    Duración
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-bold uppercase tracking-wider text-gray-600">
                                    Precio
                                </th>
                                <th v-if="isAdmin" class="px-6 py-3 text-right text-xs font-bold uppercase tracking-wider text-gray-600">
                                    Acciones
                                </th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-hueco-cream dark:divide-gray-700">
                            <tr
                                v-for="space in spaces"
                                :key="space.id"
                                class="transition hover:bg-hueco-cream/30"
                            >
                                <td class="px-6 py-4">
                                    <div class="font-semibold text-hueco-black dark:text-white">
                                        {{ space.name }}
                                    </div>
                                    <div class="text-xs text-gray-500">{{ space.slug }}</div>
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-700 dark:text-gray-300">
                                    {{ formatDuration(space.duration_minutes) }}
                                    <span
                                        v-if="space.duration_minutes && !space.fixed_duration"
                                        class="ml-1 text-xs text-gray-500"
                                    >
                                        (orientativa)
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-700 dark:text-gray-300">
                                    {{ formatPrice(space.price) }}
                                </td>
                                <td v-if="isAdmin" class="px-6 py-4 text-right">
                                    <Link
                                        :href="route('spaces.edit', space.id)"
                                        class="rounded px-3 py-1 text-sm font-semibold text-hueco-teal hover:underline"
                                    >
                                        Editar
                                    </Link>
                                    <button
                                        type="button"
                                        @click="confirmDelete(space)"
                                        class="rounded px-3 py-1 text-sm font-semibold text-red-600 hover:underline"
                                    >
                                        Eliminar
                                    </button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
