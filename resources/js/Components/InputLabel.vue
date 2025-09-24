<script setup>
defineProps(['value', 'custom_class']);
</script>

<template>
    <label 
        :class="custom_class"
        class="block font-medium text-sm text-gray-700">
        <span v-if="value">{{ value }}</span>
        <span v-else><slot /></span>
    </label>
</template>
