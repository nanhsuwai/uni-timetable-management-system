<script setup>
import CardBoxWidget from "@/Components/CardBoxWidget.vue";
import BaseIcon from "@/Components/BaseIcon.vue";
import { mdiClockOutline, mdiMapMarker, mdiAccountGroup } from "@mdi/js";

const props = defineProps({
  activities: {
    type: Array,
    default: () => [],
  },
});

const formatTime = (time) => {
  if (!time) return 'N/A';
  return time.substring(0, 5); // Show only HH:MM
};

const getDayColor = (day) => {
  const colors = {
    'monday': 'bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-400',
    'tuesday': 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400',
    'wednesday': 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-400',
    'thursday': 'bg-purple-100 text-purple-800 dark:bg-purple-900/30 dark:text-purple-400',
    'friday': 'bg-pink-100 text-pink-800 dark:bg-pink-900/30 dark:text-pink-400',
    'saturday': 'bg-indigo-100 text-indigo-800 dark:bg-indigo-900/30 dark:text-indigo-400',
    'sunday': 'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-400',
  };
  return colors[day.toLowerCase()] || 'bg-gray-100 text-gray-800 dark:bg-gray-900/30 dark:text-gray-400';
};
</script>

<template>
  <CardBoxWidget
    :class="[
      'bg-white dark:bg-slate-800 rounded-lg shadow-sm border border-gray-200 dark:border-slate-700'
    ]"
  >
    <div class="p-6">
      <div class="flex items-center justify-between mb-4">
        <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
          Recent Activities
        </h3>
        <BaseIcon :path="mdiClockOutline" size="20" class="text-gray-400" />
      </div>

      <div class="space-y-4">
        <div
          v-for="activity in activities"
          :key="activity.id"
          class="flex items-start space-x-3 p-3 rounded-lg bg-gray-50 dark:bg-slate-700/50 hover:bg-gray-100 dark:hover:bg-slate-700 transition-colors"
        >
          <div class="flex-shrink-0">
            <div class="w-2 h-2 bg-blue-500 rounded-full mt-2"></div>
          </div>

          <div class="flex-1 min-w-0">
            <div class="flex items-center justify-between">
              <p class="text-sm font-medium text-gray-900 dark:text-white">
                {{ activity.subject }}
              </p>
              <span class="text-xs text-gray-500 dark:text-gray-400">
                {{ activity.created_at }}
              </span>
            </div>

            <div class="mt-1 flex items-center space-x-4 text-xs text-gray-600 dark:text-gray-400">
              <div class="flex items-center space-x-1">
                <BaseIcon :path="mdiMapMarker" size="14" />
                <span>{{ activity.classroom }}</span>
              </div>

              <div class="flex items-center space-x-1">
                <BaseIcon :path="mdiAccountGroup" size="14" />
                <span>{{ activity.section }}</span>
              </div>

              <span
                :class="[
                  'px-2 py-1 rounded-full font-medium',
                  getDayColor(activity.day_of_week)
                ]"
              >
                {{ activity.day_of_week }}
              </span>
            </div>

            <div class="mt-2 text-xs text-gray-500 dark:text-gray-400">
              <span class="font-medium">Time:</span> {{ formatTime(activity.start_time) }} - {{ formatTime(activity.end_time) }}
              <span v-if="activity.teachers" class="ml-2">
                <span class="font-medium">Teacher(s):</span> {{ activity.teachers }}
              </span>
            </div>
          </div>
        </div>

        <div v-if="activities.length === 0" class="text-center py-8">
          <p class="text-gray-500 dark:text-gray-400">
            No recent activities found
          </p>
        </div>
      </div>
    </div>
  </CardBoxWidget>
</template>
