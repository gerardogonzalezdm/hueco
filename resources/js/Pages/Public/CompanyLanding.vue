<script setup>
import ApplicationLogo from '@/Components/ApplicationLogo.vue';
import { Head, Link } from '@inertiajs/vue3';

defineProps({
    company: Object,
    spaces: Array,
});

const formatPrice = (price) => {
    if (price === null || price === undefined || price === '') return null;
    return new Intl.NumberFormat('es-ES', { style: 'currency', currency: 'EUR' }).format(price);
};

const formatDuration = (minutes) => {
    if (!minutes) return null;
    if (minutes < 60) return `${minutes} min`;
    const h = Math.floor(minutes / 60);
    const m = minutes % 60;
    return m === 0 ? `${h} h` : `${h} h ${m} min`;
};
</script>

<template>
    <Head :title="`Reservar en ${company.name}`" />

    <div class="min-h-screen bg-hueco-cream">
        <header class="mx-auto flex max-w-5xl items-center justify-between px-6 py-6">
            <Link :href="`/c/${company.slug}`" class="flex items-center gap-2">
                <ApplicationLogo class="h-8 w-8" />
                <span class="font-bold text-hueco-black">{{ company.name }}</span>
            </Link>
            <a
                href="https://hueco.app"
                target="_blank"
                rel="noopener"
                class="text-xs text-gray-500 hover:underline"
            >
                Reservas con Hueco
            </a>
        </header>

        <main class="mx-auto max-w-5xl px-6 pb-16">
            <section class="rounded-3xl bg-white p-10 shadow-sm sm:p-14">
                <span class="inline-block rounded-full bg-hueco-yellow px-3 py-1 text-xs font-bold uppercase tracking-wider">
                    Reserva online
                </span>
                <h1 class="mt-6 text-4xl font-extrabold tracking-tight text-hueco-black sm:text-5xl">
                    Reserva en <span class="text-hueco-teal">{{ company.name }}</span>
                </h1>
                <p
                    v-if="company.description"
                    class="mt-4 max-w-2xl whitespace-pre-line text-lg text-gray-700"
                >
                    {{ company.description }}
                </p>
                <p v-else class="mt-4 max-w-2xl text-lg text-gray-700">
                    Elige tu espacio, fecha y hora. Te enviamos un email con la confirmación
                    y un link para gestionar tu reserva.
                </p>
                <div class="mt-6 flex flex-wrap items-center gap-4 text-sm text-gray-600">
                    <a
                        v-if="company.contact_email"
                        :href="`mailto:${company.contact_email}`"
                        class="hover:text-hueco-black"
                    >
                        ✉ {{ company.contact_email }}
                    </a>
                    <a
                        v-if="company.contact_phone"
                        :href="`tel:${company.contact_phone}`"
                        class="hover:text-hueco-black"
                    >
                        ☎ {{ company.contact_phone }}
                    </a>
                </div>
                <div class="mt-8 flex flex-wrap gap-3">
                    <Link
                        :href="route('login')"
                        class="rounded-full bg-hueco-yellow px-6 py-3 text-base font-bold text-hueco-black shadow-sm transition hover:bg-yellow-300"
                    >
                        Iniciar sesión
                    </Link>
                    <Link
                        :href="route('customer.register', { company: company.slug })"
                        class="rounded-full border-2 border-hueco-black bg-transparent px-6 py-3 text-base font-bold text-hueco-black transition hover:bg-hueco-black hover:text-white"
                    >
                        Crear cuenta
                    </Link>
                </div>
            </section>

            <section v-if="spaces.length > 0" class="mt-12">
                <h2 class="mb-6 text-2xl font-bold text-hueco-black">Nuestros espacios</h2>
                <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                    <div
                        v-for="space in spaces"
                        :key="space.id"
                        class="rounded-2xl bg-white p-6 shadow-sm transition hover:shadow-md"
                    >
                        <h3 class="text-lg font-bold text-hueco-black">{{ space.name }}</h3>
                        <div class="mt-3 space-y-1 text-sm text-gray-600">
                            <div v-if="space.show_duration && formatDuration(space.duration_minutes)">
                                ⏱️ {{ formatDuration(space.duration_minutes) }}
                                <span v-if="!space.fixed_duration" class="text-xs">(orientativa)</span>
                            </div>
                            <div v-if="space.show_price && formatPrice(space.price)">
                                💰 {{ formatPrice(space.price) }}
                            </div>
                        </div>
                    </div>
                </div>
            </section>
        </main>
    </div>
</template>
