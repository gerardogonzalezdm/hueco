<script setup>
import SuperadminLayout from '@/Layouts/SuperadminLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

const form = useForm({
    name: '',
    email: '',
    password: '',
    password_confirmation: '',
});

const submit = () => {
    form.post(route('superadmin.admins.store'), {
        onFinish: () => form.reset('password', 'password_confirmation'),
    });
};
</script>

<template>
    <Head title="Super-admin · Nuevo super-admin" />

    <SuperadminLayout>
        <template #header>
            <h2 class="text-xl font-semibold text-hueco-black">Añadir super-admin</h2>
        </template>

        <div class="py-10">
            <div class="mx-auto max-w-2xl px-4 sm:px-6 lg:px-8">
                <div class="rounded-2xl bg-white p-8 shadow-sm">
                    <p class="mb-6 text-sm text-gray-600">
                        Los super-admins gestionan TODA la plataforma Hueco: crean empresas,
                        ven todas las estadísticas y pueden añadir/eliminar otros super-admins.
                        Comparte sus credenciales solo con personas de confianza.
                    </p>

                    <form @submit.prevent="submit" class="space-y-5">
                        <div>
                            <InputLabel for="name" value="Nombre" />
                            <TextInput id="name" v-model="form.name" type="text" class="mt-1 block w-full" required autofocus />
                            <InputError class="mt-2" :message="form.errors.name" />
                        </div>
                        <div>
                            <InputLabel for="email" value="Email" />
                            <TextInput id="email" v-model="form.email" type="email" class="mt-1 block w-full" required />
                            <InputError class="mt-2" :message="form.errors.email" />
                        </div>
                        <div class="grid gap-4 sm:grid-cols-2">
                            <div>
                                <InputLabel for="password" value="Contraseña" />
                                <TextInput id="password" v-model="form.password" type="text" class="mt-1 block w-full" required />
                                <InputError class="mt-2" :message="form.errors.password" />
                            </div>
                            <div>
                                <InputLabel for="password_confirmation" value="Repite la contraseña" />
                                <TextInput
                                    id="password_confirmation"
                                    v-model="form.password_confirmation"
                                    type="text"
                                    class="mt-1 block w-full"
                                    required
                                />
                                <InputError class="mt-2" :message="form.errors.password_confirmation" />
                            </div>
                        </div>
                        <div class="flex items-center justify-end gap-3 pt-2">
                            <Link
                                :href="route('superadmin.admins.index')"
                                class="rounded-md px-4 py-2 text-sm font-medium text-gray-600 hover:text-hueco-black"
                            >
                                Cancelar
                            </Link>
                            <PrimaryButton :disabled="form.processing" :class="{ 'opacity-50': form.processing }">
                                Crear super-admin
                            </PrimaryButton>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </SuperadminLayout>
</template>
