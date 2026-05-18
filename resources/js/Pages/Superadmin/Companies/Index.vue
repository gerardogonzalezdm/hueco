<script setup>
import SuperadminLayout from '@/Layouts/SuperadminLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';

defineProps({
    companies: Array,
});

const confirmDelete = (company) => {
    if (
        confirm(
            `¿Eliminar "${company.name}"? Se borrarán también todos sus usuarios, espacios y reservas. Esta acción es irreversible.`,
        )
    ) {
        router.delete(route('superadmin.companies.destroy', company.id), {
            preserveScroll: true,
        });
    }
};

const accessAsAdmin = (company) => {
    if (
        confirm(
            `Vas a acceder al panel de "${company.name}" como su administrador. Podrás volver al panel super-admin desde el banner superior.`,
        )
    ) {
        router.post(route('superadmin.companies.access', company.id));
    }
};

const formatDate = (iso) =>
    new Date(iso).toLocaleDateString('es-ES', { day: '2-digit', month: 'short', year: 'numeric' });
</script>

<template>
    <Head title="Super-admin · Empresas" />

    <SuperadminLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <h2 class="text-xl font-semibold text-hueco-black">Empresas</h2>
                <Link
                    :href="route('superadmin.companies.create')"
                    class="rounded-full bg-hueco-yellow px-4 py-2 text-sm font-bold text-hueco-black shadow-sm hover:bg-yellow-300"
                >
                    + Nueva empresa
                </Link>
            </div>
        </template>

        <div class="py-10">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div class="overflow-hidden rounded-2xl bg-white shadow-sm">
                    <div v-if="companies.length === 0" class="p-12 text-center">
                        <div class="text-5xl">🏢</div>
                        <h3 class="mt-4 text-lg font-semibold text-hueco-black">
                            Todavía no hay empresas registradas
                        </h3>
                        <Link
                            :href="route('superadmin.companies.create')"
                            class="mt-6 inline-block rounded-full bg-hueco-yellow px-5 py-2 text-sm font-bold text-hueco-black hover:bg-yellow-300"
                        >
                            Crear primera empresa
                        </Link>
                    </div>

                    <table v-else class="min-w-full divide-y divide-hueco-cream">
                        <thead class="bg-hueco-cream/40">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-bold uppercase tracking-wider text-gray-600">Empresa</th>
                                <th class="px-6 py-3 text-left text-xs font-bold uppercase tracking-wider text-gray-600">Slug</th>
                                <th class="px-6 py-3 text-center text-xs font-bold uppercase tracking-wider text-gray-600">Usuarios</th>
                                <th class="px-6 py-3 text-center text-xs font-bold uppercase tracking-wider text-gray-600">Espacios</th>
                                <th class="px-6 py-3 text-center text-xs font-bold uppercase tracking-wider text-gray-600">Reservas</th>
                                <th class="px-6 py-3 text-left text-xs font-bold uppercase tracking-wider text-gray-600">Alta</th>
                                <th class="px-6 py-3 text-right text-xs font-bold uppercase tracking-wider text-gray-600">Acciones</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-hueco-cream">
                            <tr
                                v-for="c in companies"
                                :key="c.id"
                                class="transition hover:bg-hueco-cream/30"
                            >
                                <td class="px-6 py-4">
                                    <div class="font-semibold text-hueco-black">{{ c.name }}</div>
                                    <div class="text-xs text-gray-500">{{ c.contact_email }}</div>
                                </td>
                                <td class="px-6 py-4 font-mono text-xs text-gray-600">{{ c.slug }}</td>
                                <td class="px-6 py-4 text-center text-sm">{{ c.users_count }}</td>
                                <td class="px-6 py-4 text-center text-sm">{{ c.spaces_count }}</td>
                                <td class="px-6 py-4 text-center text-sm">{{ c.bookings_count }}</td>
                                <td class="px-6 py-4 text-sm text-gray-500">{{ formatDate(c.created_at) }}</td>
                                <td class="px-6 py-4 text-right">
                                    <button
                                        type="button"
                                        @click="accessAsAdmin(c)"
                                        :disabled="c.users_count === 0"
                                        :class="[
                                            'px-3 py-1 text-sm font-semibold',
                                            c.users_count > 0
                                                ? 'text-hueco-black hover:underline'
                                                : 'cursor-not-allowed text-gray-400',
                                        ]"
                                        :title="c.users_count === 0 ? 'Esta empresa no tiene admin' : 'Acceder como administrador'"
                                    >
                                        Acceder
                                    </button>
                                    <Link
                                        :href="route('superadmin.companies.edit', c.id)"
                                        class="px-3 py-1 text-sm font-semibold text-hueco-teal hover:underline"
                                    >
                                        Editar
                                    </Link>
                                    <button
                                        type="button"
                                        @click="confirmDelete(c)"
                                        class="px-3 py-1 text-sm font-semibold text-red-600 hover:underline"
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
    </SuperadminLayout>
</template>
