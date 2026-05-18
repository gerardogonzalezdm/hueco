<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, useForm, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

const props = defineProps({
    company: Object,
    public_url: String,
});

const form = useForm({
    name: props.company.name ?? '',
    slug: props.company.slug ?? '',
    contact_email: props.company.contact_email ?? '',
    contact_phone: props.company.contact_phone ?? '',
    description: props.company.description ?? '',
});

const submit = () => {
    form.patch(route('settings.company.update'), {
        preserveScroll: true,
    });
};

const flash = computed(() => usePage().props.flash || {});
</script>

<template>
    <Head title="Ajustes — Empresa" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold text-hueco-black dark:text-white">
                Ajustes de la empresa
            </h2>
        </template>

        <div class="py-10">
            <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
                <div
                    v-if="flash.success"
                    class="mb-4 rounded-lg bg-hueco-green/20 px-4 py-3 text-sm font-semibold text-emerald-800"
                >
                    {{ flash.success }}
                </div>

                <div class="rounded-2xl bg-white p-8 shadow-sm dark:bg-gray-800">
                    <p class="mb-6 text-sm text-gray-600">
                        Estos datos aparecen en tu portal público
                        <a
                            :href="public_url"
                            target="_blank"
                            rel="noopener"
                            class="font-semibold text-hueco-teal hover:underline"
                        >
                            {{ public_url }} ↗
                        </a>
                    </p>

                    <form @submit.prevent="submit" class="space-y-6">
                        <div>
                            <InputLabel for="name" value="Nombre de la empresa" />
                            <TextInput
                                id="name"
                                v-model="form.name"
                                type="text"
                                class="mt-1 block w-full"
                                required
                            />
                            <InputError class="mt-2" :message="form.errors.name" />
                        </div>

                        <div>
                            <InputLabel for="slug" value="Identificador en la URL (slug)" />
                            <div class="mt-1 flex items-center rounded-md border border-gray-300 focus-within:border-hueco-teal focus-within:ring-1 focus-within:ring-hueco-teal">
                                <span class="border-r border-gray-300 bg-hueco-cream/60 px-3 py-2 text-sm text-gray-600">
                                    hueco.app/c/
                                </span>
                                <input
                                    id="slug"
                                    v-model="form.slug"
                                    type="text"
                                    class="block w-full border-0 px-3 py-2 focus:outline-none focus:ring-0"
                                    required
                                />
                            </div>
                            <p class="mt-1 text-xs text-gray-500">
                                Minúsculas, números y guiones. Tus clientes usan esta URL para reservar.
                            </p>
                            <InputError class="mt-2" :message="form.errors.slug" />
                        </div>

                        <div class="grid gap-6 sm:grid-cols-2">
                            <div>
                                <InputLabel for="contact_email" value="Email de contacto" />
                                <TextInput
                                    id="contact_email"
                                    v-model="form.contact_email"
                                    type="email"
                                    class="mt-1 block w-full"
                                />
                                <InputError class="mt-2" :message="form.errors.contact_email" />
                            </div>
                            <div>
                                <InputLabel for="contact_phone" value="Teléfono de contacto" />
                                <TextInput
                                    id="contact_phone"
                                    v-model="form.contact_phone"
                                    type="tel"
                                    class="mt-1 block w-full"
                                />
                                <InputError class="mt-2" :message="form.errors.contact_phone" />
                            </div>
                        </div>

                        <div>
                            <InputLabel for="description" value="Descripción (se muestra en el portal público)" />
                            <textarea
                                id="description"
                                v-model="form.description"
                                rows="4"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-hueco-teal focus:ring-hueco-teal"
                                placeholder="Una breve descripción de tu cowork, dirección, lo que ofrecéis..."
                            ></textarea>
                            <p class="mt-1 text-xs text-gray-500">
                                Máximo 1000 caracteres.
                            </p>
                            <InputError class="mt-2" :message="form.errors.description" />
                        </div>

                        <div class="flex items-center justify-end pt-2">
                            <PrimaryButton :disabled="form.processing" :class="{ 'opacity-50': form.processing }">
                                Guardar cambios
                            </PrimaryButton>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
