<script setup>
import ApplicationLogo from '@/Components/ApplicationLogo.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

const props = defineProps({
    company: Object,
});

const form = useForm({
    name: '',
    email: '',
    password: '',
    password_confirmation: '',
});

const submit = () => {
    form.post(route('public.register.store', props.company.slug), {
        onFinish: () => form.reset('password', 'password_confirmation'),
    });
};
</script>

<template>
    <Head :title="`Crear cuenta — ${company.name}`" />

    <div class="flex min-h-screen flex-col items-center bg-hueco-cream pt-6 sm:justify-center sm:pt-0">
        <div>
            <Link :href="route('public.company.show', company.slug)" class="flex flex-col items-center gap-2">
                <ApplicationLogo class="h-14 w-14" />
                <span class="text-xl font-bold tracking-tight text-hueco-black">
                    {{ company.name }}
                </span>
            </Link>
        </div>

        <div class="mt-6 w-full overflow-hidden bg-white px-6 py-6 shadow-sm sm:max-w-md sm:rounded-2xl">
            <h2 class="mb-6 text-lg font-bold text-hueco-black">Crear cuenta de cliente</h2>

            <form @submit.prevent="submit" class="space-y-4">
                <div>
                    <InputLabel for="name" value="Tu nombre" />
                    <TextInput
                        id="name"
                        v-model="form.name"
                        type="text"
                        class="mt-1 block w-full"
                        required
                        autofocus
                        autocomplete="name"
                    />
                    <InputError class="mt-2" :message="form.errors.name" />
                </div>

                <div>
                    <InputLabel for="email" value="Email" />
                    <TextInput
                        id="email"
                        v-model="form.email"
                        type="email"
                        class="mt-1 block w-full"
                        required
                        autocomplete="username"
                    />
                    <InputError class="mt-2" :message="form.errors.email" />
                </div>

                <div>
                    <InputLabel for="password" value="Contraseña" />
                    <TextInput
                        id="password"
                        v-model="form.password"
                        type="password"
                        class="mt-1 block w-full"
                        required
                        autocomplete="new-password"
                    />
                    <InputError class="mt-2" :message="form.errors.password" />
                </div>

                <div>
                    <InputLabel for="password_confirmation" value="Confirma la contraseña" />
                    <TextInput
                        id="password_confirmation"
                        v-model="form.password_confirmation"
                        type="password"
                        class="mt-1 block w-full"
                        required
                        autocomplete="new-password"
                    />
                    <InputError class="mt-2" :message="form.errors.password_confirmation" />
                </div>

                <div class="flex items-center justify-between pt-2">
                    <Link
                        :href="route('public.login', company.slug)"
                        class="text-sm font-semibold text-hueco-teal hover:underline"
                    >
                        Ya tengo cuenta
                    </Link>
                    <button
                        type="submit"
                        :disabled="form.processing"
                        :class="{ 'opacity-50': form.processing }"
                        class="rounded-full bg-hueco-yellow px-5 py-2 text-sm font-bold text-hueco-black shadow-sm transition hover:bg-yellow-300"
                    >
                        Crear cuenta
                    </button>
                </div>
            </form>
        </div>
    </div>
</template>
