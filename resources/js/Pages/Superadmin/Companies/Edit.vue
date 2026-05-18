<script setup>
import SuperadminLayout from '@/Layouts/SuperadminLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

const props = defineProps({
    company: Object,
});

const form = useForm({
    name: props.company.name,
    slug: props.company.slug,
    contact_email: props.company.contact_email ?? '',
    contact_phone: props.company.contact_phone ?? '',
    description: props.company.description ?? '',
});

const submit = () => {
    form.patch(route('superadmin.companies.update', props.company.id));
};
</script>

<template>
    <Head :title="`Super-admin · Editar ${company.name}`" />

    <SuperadminLayout>
        <template #header>
            <h2 class="text-xl font-semibold text-hueco-black">
                Editar empresa: {{ company.name }}
            </h2>
        </template>

        <div class="py-10">
            <div class="mx-auto max-w-2xl px-4 sm:px-6 lg:px-8">
                <div class="rounded-2xl bg-white p-8 shadow-sm">
                    <form @submit.prevent="submit" class="space-y-5">
                        <div>
                            <InputLabel for="name" value="Nombre" />
                            <TextInput id="name" v-model="form.name" type="text" class="mt-1 block w-full" required />
                            <InputError class="mt-2" :message="form.errors.name" />
                        </div>
                        <div>
                            <InputLabel for="slug" value="Slug" />
                            <TextInput id="slug" v-model="form.slug" type="text" class="mt-1 block w-full" required />
                            <InputError class="mt-2" :message="form.errors.slug" />
                        </div>
                        <div class="grid gap-4 sm:grid-cols-2">
                            <div>
                                <InputLabel for="contact_email" value="Email contacto" />
                                <TextInput id="contact_email" v-model="form.contact_email" type="email" class="mt-1 block w-full" />
                                <InputError class="mt-2" :message="form.errors.contact_email" />
                            </div>
                            <div>
                                <InputLabel for="contact_phone" value="Teléfono contacto" />
                                <TextInput id="contact_phone" v-model="form.contact_phone" type="tel" class="mt-1 block w-full" />
                                <InputError class="mt-2" :message="form.errors.contact_phone" />
                            </div>
                        </div>
                        <div>
                            <InputLabel for="description" value="Descripción" />
                            <textarea
                                id="description"
                                v-model="form.description"
                                rows="3"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-hueco-teal focus:ring-hueco-teal"
                            ></textarea>
                            <InputError class="mt-2" :message="form.errors.description" />
                        </div>
                        <div class="flex items-center justify-end gap-3 pt-2">
                            <Link
                                :href="route('superadmin.companies.index')"
                                class="rounded-md px-4 py-2 text-sm font-medium text-gray-600 hover:text-hueco-black"
                            >
                                Cancelar
                            </Link>
                            <PrimaryButton :disabled="form.processing" :class="{ 'opacity-50': form.processing }">
                                Guardar cambios
                            </PrimaryButton>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </SuperadminLayout>
</template>
