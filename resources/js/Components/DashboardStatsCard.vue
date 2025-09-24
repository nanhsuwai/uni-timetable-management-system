<script setup>
import CardBoxWidget from "@/Components/CardBoxWidget.vue";
import BaseIcon from "@/Components/BaseIcon.vue";

const props = defineProps({
  title: {
    type: String,
    required: true,
  },
  value: {
    type: [String, Number],
    required: true,
  },
  icon: {
    type: String,
    required: true,
  },
  color: {
    type: String,
    default: "info",
    validator: (value) => ["info", "success", "warning", "danger"].includes(value),
  },
  trend: {
    type: String,
    default: null,
  },
  trendValue: {
    type: String,
    default: null,
  },
});

const colorClasses = {
  info: "bg-blue-500",
  success: "bg-green-500",
  warning: "bg-yellow-500",
  danger: "bg-red-500",
};
</script>

<template>
  <CardBoxWidget
    :class="[
      'flex items-center justify-between p-6',
      'bg-white dark:bg-slate-800 rounded-lg shadow-sm border border-gray-200 dark:border-slate-700',
      'hover:shadow-md transition-shadow duration-200'
    ]"
  >
    <div class="flex items-center space-x-4">
      <div :class="['p-3 rounded-full', colorClasses[color]]">
        <BaseIcon :path="icon" size="24" class="text-white" />
      </div>
      <div>
        <h3 class="text-sm font-medium text-gray-600 dark:text-gray-400">
          {{ props.title }}
        </h3>
        <p class="text-2xl font-bold text-gray-900 dark:text-white">
          {{ value }}
        </p>
        <div v-if="trend" class="flex items-center mt-1">
          <span
            :class="[
              'text-xs font-medium px-2 py-1 rounded-full',
              trend === 'up' ? 'text-green-700 bg-green-100 dark:text-green-400 dark:bg-green-900/30' :
              trend === 'down' ? 'text-red-700 bg-red-100 dark:text-red-400 dark:bg-red-900/30' :
              'text-gray-700 bg-gray-100 dark:text-gray-400 dark:bg-gray-900/30'
            ]"
          >
            {{ trendValue }}
          </span>
        </div>
      </div>
    </div>
  </CardBoxWidget>
</template>
