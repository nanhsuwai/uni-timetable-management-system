<template>
  <svg
    :width="size"
    :height="size"
    viewBox="0 0 24 24"
    fill="currentColor"
    :class="iconClass"
  >
    <path :d="path" />
  </svg>
</template>

<script setup>
import { computed } from "vue";
const props = defineProps({
  path: {
    type: String,
    required: true,
  },
  size: {
    type: [String, Number],
    default: 24,
  },
  class: {
    type: String,
    default: '',
  },
});

const iconClass = computed(() => props.class);
</script>
