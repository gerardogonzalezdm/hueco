<script setup>
import ApplicationLogo from '@/Components/ApplicationLogo.vue';
import { Head, Link } from '@inertiajs/vue3';

defineProps({
    canLogin: { type: Boolean },
    canRegister: { type: Boolean },
    laravelVersion: { type: String, required: true },
    phpVersion: { type: String, required: true },
});
</script>

<template>
    <Head title="Hueco — Reservas de espacios sin fricción" />

    <div class="min-h-screen bg-hueco-cream text-hueco-black">
        <header class="mx-auto flex max-w-6xl items-center justify-between px-6 py-6">
            <Link href="/" class="flex items-center gap-2">
                <ApplicationLogo class="h-9 w-9" />
                <span class="text-xl font-bold tracking-tight">Hueco</span>
            </Link>

            <nav v-if="canLogin" class="flex items-center gap-3">
                <Link
                    :href="route('login')"
                    class="rounded-full px-4 py-2 text-sm font-semibold text-hueco-black hover:underline"
                >
                    Iniciar sesión
                </Link>
                <Link
                    v-if="canRegister"
                    :href="route('register')"
                    class="rounded-full bg-hueco-black px-5 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-gray-800"
                >
                    Crear cuenta
                </Link>
            </nav>
        </header>

        <main class="mx-auto max-w-6xl px-6 py-16 lg:py-24">
            <div class="grid items-center gap-12 lg:grid-cols-2">
                <div>
                    <span class="inline-block rounded-full bg-hueco-yellow px-3 py-1 text-xs font-bold uppercase tracking-wider">
                        SaaS de reservas
                    </span>
                    <h1 class="mt-6 text-4xl font-extrabold leading-tight sm:text-5xl lg:text-6xl">
                        Reserva tus espacios <span class="text-hueco-teal">sin fricción</span>
                    </h1>
                    <p class="mt-6 max-w-lg text-lg text-gray-700">
                        Hueco es la herramienta de reservas pensada para coworks, salas de reuniones,
                        salas de música y despachos por horas. Una cuenta, un panel, todas las reservas
                        bajo control.
                    </p>
                    <div class="mt-10 flex flex-col gap-3 sm:flex-row">
                        <Link
                            v-if="canRegister"
                            :href="route('register')"
                            class="rounded-full bg-hueco-yellow px-6 py-3 text-base font-bold text-hueco-black shadow-sm transition hover:bg-yellow-300"
                        >
                            Empezar gratis
                        </Link>
                        <Link
                            v-if="canLogin"
                            :href="route('login')"
                            class="rounded-full border-2 border-hueco-black bg-transparent px-6 py-3 text-base font-bold text-hueco-black transition hover:bg-hueco-black hover:text-white"
                        >
                            Ya tengo cuenta
                        </Link>
                    </div>
                </div>

                <div class="relative">
                    <div class="absolute -inset-4 -z-10 rounded-3xl bg-hueco-teal/20 blur-2xl"></div>
                    <div class="rounded-3xl bg-white p-8 shadow-xl">
                        <div class="grid grid-cols-3 gap-3 text-center text-xs font-semibold uppercase tracking-wider text-gray-500">
                            <div>Sala 1</div>
                            <div>Sala 2</div>
                            <div>Despacho</div>
                        </div>
                        <div class="mt-3 grid grid-cols-3 gap-3">
                            <div class="space-y-2">
                                <div class="h-10 rounded-lg bg-hueco-cream"></div>
                                <div class="h-10 rounded-lg bg-hueco-green/80"></div>
                                <div class="h-10 rounded-lg bg-hueco-green/80"></div>
                                <div class="h-10 rounded-lg bg-hueco-cream"></div>
                            </div>
                            <div class="space-y-2">
                                <div class="h-10 rounded-lg bg-hueco-teal/80"></div>
                                <div class="h-10 rounded-lg bg-hueco-teal/80"></div>
                                <div class="h-10 rounded-lg bg-hueco-cream"></div>
                                <div class="h-10 rounded-lg bg-hueco-cream"></div>
                            </div>
                            <div class="space-y-2">
                                <div class="h-10 rounded-lg bg-hueco-cream"></div>
                                <div class="h-10 rounded-lg bg-hueco-cream"></div>
                                <div class="h-10 rounded-lg bg-hueco-yellow"></div>
                                <div class="h-10 rounded-lg bg-hueco-yellow"></div>
                            </div>
                        </div>
                        <div class="mt-6 flex items-center justify-between text-sm text-gray-600">
                            <span>Hoy</span>
                            <span class="rounded-full bg-hueco-cream px-3 py-1 text-xs font-semibold">
                                3 reservas
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="mt-24 grid gap-6 sm:grid-cols-3">
                <div class="rounded-2xl bg-white p-6 shadow-sm">
                    <div class="text-2xl">🗓️</div>
                    <h3 class="mt-3 font-bold">Calendario diario</h3>
                    <p class="mt-1 text-sm text-gray-600">
                        Una columna por espacio. Reservas a un clic, sin conflictos horarios.
                    </p>
                </div>
                <div class="rounded-2xl bg-white p-6 shadow-sm">
                    <div class="text-2xl">📧</div>
                    <h3 class="mt-3 font-bold">Emails automáticos</h3>
                    <p class="mt-1 text-sm text-gray-600">
                        Confirmaciones y cancelaciones al cliente, sin que muevas un dedo.
                    </p>
                </div>
                <div class="rounded-2xl bg-white p-6 shadow-sm">
                    <div class="text-2xl">🏢</div>
                    <h3 class="mt-3 font-bold">Multi-empresa</h3>
                    <p class="mt-1 text-sm text-gray-600">
                        Cada empresa con su panel, sus espacios y sus clientes. Aislamiento total.
                    </p>
                </div>
            </div>
        </main>

        <footer class="mx-auto max-w-6xl px-6 py-10 text-center text-sm text-gray-500">
            Hueco · Hecho con Laravel {{ laravelVersion }} y PHP {{ phpVersion }}
        </footer>
    </div>
</template>
