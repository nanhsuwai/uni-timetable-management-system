<template>
  <CardBoxWidget
    :class="[
      'bg-white dark:bg-slate-800 rounded-lg shadow-sm border border-gray-200 dark:border-slate-700'
    ]"
  >
    <div class="p-6">
      <div class="flex items-center justify-between mb-4">
        <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
          Section Timetables
        </h3>
        <BaseIcon :path="mdiCalendarBadge" size="20" class="text-gray-400" />
      </div>

      <div v-if="sectionTimetables.length > 0" class="space-y-6">
        <div v-for="section in sectionTimetables" :key="section.id" class="border border-gray-200 dark:border-slate-700 rounded-lg p-4">
          <h4 class="text-md font-semibold text-gray-900 dark:text-white mb-3">{{ section.name }}</h4>
          <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-slate-700">
              <thead class="bg-gray-50 dark:bg-slate-700">
                <tr>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Day</th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Time</th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Subject</th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Teachers</th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Classroom</th>
                </tr>
              </thead>
              <tbody class="bg-white dark:bg-slate-800 divide-y divide-gray-200 dark:divide-slate-700">
                <tr v-for="entry in section.timetable" :key="`${section.id}-${entry.day}-${entry.start_time}`">
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">{{ entry.day }}</td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">{{ entry.start_time }} - {{ entry.end_time }}</td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">{{ entry.subject }}</td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">{{ entry.teachers }}</td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">{{ entry.classroom }}</td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>
      <div v-else class="flex items-center justify-center h-32">
        <p class="text-gray-500 dark:text-gray-400">No section timetables available</p>
      </div>
    </div>
  </CardBoxWidget>
</template>

<script setup>
import CardBoxWidget from "@/Components/CardBoxWidget.vue";
import BaseIcon from "@/Components/BaseIcon.vue";
import { mdiCalendarBadge } from "@mdi/js";

const props = defineProps({
  sectionTimetables: {
    type: Array,
    default: () => [],
  },
});
</script>
