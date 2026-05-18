<script setup>
import { computed } from 'vue';
import { Link } from '@inertiajs/vue3';

const props = defineProps({
    href: {
        type: String,
        required: true,
    },
    active: {
        type: Boolean,
    },
});

const classes = computed(() =>
    props.active
        ? 'block w-full ps-3 pe-4 py-2 border-l-4 border-hueco-yellow text-start text-base font-semibold text-hueco-black dark:text-white bg-hueco-cream dark:bg-gray-700 focus:outline-none focus:bg-hueco-cream dark:focus:bg-gray-600 transition duration-150 ease-in-out'
        : 'block w-full ps-3 pe-4 py-2 border-l-4 border-transparent text-start text-base font-medium text-gray-600 dark:text-gray-400 hover:text-hueco-black dark:hover:text-gray-200 hover:bg-hueco-cream dark:hover:bg-gray-700 hover:border-hueco-cream focus:outline-none transition duration-150 ease-in-out',
);
</script>

<template>
    <Link :href="href" :class="classes">
        <slot />
    </Link>
</template>
