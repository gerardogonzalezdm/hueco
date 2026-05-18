<script setup>
import GuestLayout from '@/Layouts/GuestLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { computed } from 'vue';

const props = defineProps({
    companies: Array,
    preselected_slug: String,
});

const preselectedCompanyId = computed(() => {
    if (!props.preselected_slug) return null;
    const match = props.companies.find((c) => c.slug === props.preselected_slug);
    return match?.id ?? null;
});

const form = useForm({
    company_id: preselectedCompanyId.value ?? props.companies[0]?.id ?? null,
    name: '',
    email: '',
    password: '',
    password_confirmation: '',
});

const submit = () => {
    form.post(route('customer.register.store'), {
        onFinish: () => form.reset('password', 'password_confirmation'),
    });
};
</script>

<template>
    <GuestLayout>
        <Head title="Crear cuenta de cliente" />

        <h2 class="mb-6 text-lg font-bold text-hueco-black">Crear cuenta de cliente</h2>

        <form @submit.prevent="submit" class="space-y-4">
            <div>
                <InputLabel for="company_id" value="¿En qué empresa quieres reservar?" />
                <select
                    id="company_id"
                    v-model="form.company_id"
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-hueco-teal focus:ring-hueco-teal"
                    required
                >
                    <option v-for="c in companies" :key="c.id" :value="c.id">
                        {{ c.name }}
                    </option>
                </select>
                <InputError class="mt-2" :message="form.errors.company_id" />
            </div>

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
                    :href="route('login')"
                    class="text-sm font-semibold text-hueco-teal hover:underline"
                >
                    Ya tengo cuenta
                </Link>
                <PrimaryButton
                    :disabled="form.processing"
                    :class="{ 'opacity-25': form.processing }"
                >
                    Crear cuenta
                </PrimaryButton>
            </div>
        </form>
    </GuestLayout>
</template>
