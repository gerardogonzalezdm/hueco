<script setup>
import SuperadminLayout from '@/Layouts/SuperadminLayout.vue';
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

defineProps({
    admins: Array,
});

const currentUserId = computed(() => usePage().props.auth?.user?.id);

const confirmDelete = (admin) => {
    if (confirm(`¿Eliminar al super-admin "${admin.name}"?`)) {
        router.delete(route('superadmin.admins.destroy', admin.id), { preserveScroll: true });
    }
};

const formatDate = (iso) =>
    new Date(iso).toLocaleDateString('es-ES', { day: '2-digit', month: 'short', year: 'numeric' });
</script>

<template>
    <Head title="Super-admin · Super-admins" />

    <SuperadminLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <h2 class="text-xl font-semibold text-hueco-black">Super-admins</h2>
                <Link
                    :href="route('superadmin.admins.create')"
                    class="rounded-full bg-hueco-yellow px-4 py-2 text-sm font-bold text-hueco-black shadow-sm hover:bg-yellow-300"
                >
                    + Añadir super-admin
                </Link>
            </div>
        </template>

        <div class="py-10">
            <div class="mx-auto max-w-5xl px-4 sm:px-6 lg:px-8">
                <div class="rounded-2xl bg-white shadow-sm">
                    <table class="min-w-full divide-y divide-hueco-cream">
                        <thead class="bg-hueco-cream/40">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-bold uppercase tracking-wider text-gray-600">Nombre</th>
                                <th class="px-6 py-3 text-left text-xs font-bold uppercase tracking-wider text-gray-600">Email</th>
                                <th class="px-6 py-3 text-left text-xs font-bold uppercase tracking-wider text-gray-600">Alta</th>
                                <th class="px-6 py-3 text-right text-xs font-bold uppercase tracking-wider text-gray-600">Acciones</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-hueco-cream">
                            <tr v-for="a in admins" :key="a.id" class="transition hover:bg-hueco-cream/30">
                                <td class="px-6 py-4 font-semibold text-hueco-black">
                                    {{ a.name }}
                                    <span
                                        v-if="a.id === currentUserId"
                                        class="ml-2 rounded-full bg-hueco-yellow px-2 py-0.5 text-[10px] font-black uppercase text-hueco-black"
                                    >
                                        Tú
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-700">{{ a.email }}</td>
                                <td class="px-6 py-4 text-sm text-gray-500">{{ formatDate(a.created_at) }}</td>
                                <td class="px-6 py-4 text-right">
                                    <button
                                        v-if="a.id !== currentUserId"
                                        type="button"
                                        @click="confirmDelete(a)"
                                        class="px-3 py-1 text-sm font-semibold text-red-600 hover:underline"
                                    >
                                        Eliminar
                                    </button>
                                    <span v-else class="text-xs text-gray-400">No puedes eliminarte</span>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </SuperadminLayout>
</template>
