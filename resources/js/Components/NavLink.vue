<script setup>
import { computed } from 'vue';
import { Link } from '@inertiajs/vue3';

const props = defineProps(['href', 'active']);

const classes = computed(() =>
    props.active
        ? 'inline-flex items-center px-2 rounded py-2 bg-gray-900 text-sm font-medium leading-5 text-white hover:bg-gray-500 focus:outline-none focus:border-indigo-700 transition duration-150 ease-in-out'
        : 'inline-flex items-center px-2 py-2 border-transparent text-sm font-medium leading-5 text-white hover:bg-gray-500 rounded hover:text-white hover:border-gray-300 focus:outline-none focus:text-gray-700 focus:border-gray-300 transition duration-150 ease-in-out'
);
</script>

<template>
    <Link :href="href" :class="classes">
        <slot />
    </Link>
</template>
