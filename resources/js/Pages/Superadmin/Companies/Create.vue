<script setup>
import SuperadminLayout from '@/Layouts/SuperadminLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

const form = useForm({
    company_name: '',
    slug: '',
    contact_email: '',
    contact_phone: '',
    admin_name: '',
    admin_email: '',
    admin_password: '',
    admin_password_confirmation: '',
});

const submit = () => {
    form.post(route('superadmin.companies.store'), {
        onFinish: () => form.reset('admin_password', 'admin_password_confirmation'),
    });
};
</script>

<template>
    <Head title="Super-admin · Nueva empresa" />

    <SuperadminLayout>
        <template #header>
            <h2 class="text-xl font-semibold text-hueco-black">Nueva empresa</h2>
        </template>

        <div class="py-10">
            <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
                <div class="rounded-2xl bg-white p-8 shadow-sm">
                    <p class="mb-6 text-sm text-gray-600">
                        Crea una nueva empresa cliente de Hueco y su administrador inicial.
                        Comparte las credenciales con el admin para que pueda entrar.
                    </p>

                    <form @submit.prevent="submit" class="space-y-8">
                        <fieldset class="space-y-4">
                            <legend class="text-sm font-bold uppercase tracking-wider text-gray-500">
                                Datos de la empresa
                            </legend>

                            <div>
                                <InputLabel for="company_name" value="Nombre" />
                                <TextInput
                                    id="company_name"
                                    v-model="form.company_name"
                                    type="text"
                                    class="mt-1 block w-full"
                                    placeholder="Cowork Hórreo"
                                    required
                                    autofocus
                                />
                                <InputError class="mt-2" :message="form.errors.company_name" />
                            </div>

                            <div>
                                <InputLabel for="slug" value="Slug (opcional)" />
                                <TextInput
                                    id="slug"
                                    v-model="form.slug"
                                    type="text"
                                    class="mt-1 block w-full"
                                    placeholder="cowork-horreo"
                                />
                                <p class="mt-1 text-xs text-gray-500">
                                    Se autogenera si lo dejas vacío. Solo minúsculas, números y guiones.
                                </p>
                                <InputError class="mt-2" :message="form.errors.slug" />
                            </div>

                            <div class="grid gap-4 sm:grid-cols-2">
                                <div>
                                    <InputLabel for="contact_email" value="Email contacto" />
                                    <TextInput
                                        id="contact_email"
                                        v-model="form.contact_email"
                                        type="email"
                                        class="mt-1 block w-full"
                                    />
                                    <InputError class="mt-2" :message="form.errors.contact_email" />
                                </div>
                                <div>
                                    <InputLabel for="contact_phone" value="Teléfono contacto" />
                                    <TextInput
                                        id="contact_phone"
                                        v-model="form.contact_phone"
                                        type="tel"
                                        class="mt-1 block w-full"
                                    />
                                    <InputError class="mt-2" :message="form.errors.contact_phone" />
                                </div>
                            </div>
                        </fieldset>

                        <fieldset class="space-y-4 rounded-2xl bg-hueco-cream/60 p-5">
                            <legend class="-mb-2 px-2 text-sm font-bold uppercase tracking-wider text-gray-500">
                                Administrador inicial
                            </legend>

                            <div>
                                <InputLabel for="admin_name" value="Nombre del admin" />
                                <TextInput
                                    id="admin_name"
                                    v-model="form.admin_name"
                                    type="text"
                                    class="mt-1 block w-full"
                                    required
                                />
                                <InputError class="mt-2" :message="form.errors.admin_name" />
                            </div>

                            <div>
                                <InputLabel for="admin_email" value="Email del admin" />
                                <TextInput
                                    id="admin_email"
                                    v-model="form.admin_email"
                                    type="email"
                                    class="mt-1 block w-full"
                                    required
                                />
                                <InputError class="mt-2" :message="form.errors.admin_email" />
                            </div>

                            <div class="grid gap-4 sm:grid-cols-2">
                                <div>
                                    <InputLabel for="admin_password" value="Contraseña inicial" />
                                    <TextInput
                                        id="admin_password"
                                        v-model="form.admin_password"
                                        type="text"
                                        class="mt-1 block w-full"
                                        required
                                    />
                                    <InputError class="mt-2" :message="form.errors.admin_password" />
                                </div>
                                <div>
                                    <InputLabel for="admin_password_confirmation" value="Repite la contraseña" />
                                    <TextInput
                                        id="admin_password_confirmation"
                                        v-model="form.admin_password_confirmation"
                                        type="text"
                                        class="mt-1 block w-full"
                                        required
                                    />
                                    <InputError class="mt-2" :message="form.errors.admin_password_confirmation" />
                                </div>
                            </div>

                            <p class="text-xs text-gray-600">
                                Mantenemos la contraseña visible para que la copies fácil. El admin podrá cambiarla en su perfil al entrar.
                            </p>
                        </fieldset>

                        <div class="flex items-center justify-end gap-3 pt-2">
                            <Link
                                :href="route('superadmin.companies.index')"
                                class="rounded-md px-4 py-2 text-sm font-medium text-gray-600 hover:text-hueco-black"
                            >
                                Cancelar
                            </Link>
                            <PrimaryButton :disabled="form.processing" :class="{ 'opacity-50': form.processing }">
                                Crear empresa
                            </PrimaryButton>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </SuperadminLayout>
</template>
