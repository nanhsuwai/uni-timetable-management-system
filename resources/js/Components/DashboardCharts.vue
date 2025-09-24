<script setup>
import CardBoxWidget from "@/Components/CardBoxWidget.vue";
import { computed } from "vue";
import BaseIcon from "@/Components/BaseIcon.vue";
import CategoryPieChart from "@/Components/Charts/CategoryPieChart.vue";
import CourseBarChart from "@/Components/Charts/CourseBarChart.vue";
import { mdiChartBar, mdiChartPie, mdiTrendingUp } from "@mdi/js";

const props = defineProps({
  subjectDistribution: {
    type: Array,
    default: () => [],
  },
  teacherWorkload: {
    type: Array,
    default: () => [],
  },
  entriesByDay: {
    type: Array,
    default: () => [],
  },
  entriesByTimeSlot: {
    type: Array,
    default: () => [],
  },
});

// Prepare data for charts
const subjectChartData = computed(() => ({
  labels: props.subjectDistribution.map(item => item.name),
  datasets: [{
    data: props.subjectDistribution.map(item => item.count),
    backgroundColor: [
      '#3B82F6', '#EF4444', '#10B981', '#F59E0B', '#8B5CF6',
      '#06B6D4', '#84CC16', '#F97316', '#EC4899', '#6B7280'
    ],
  }]
}));

const teacherChartData = computed(() => ({
  labels: props.teacherWorkload.map(item => item.name),
  datasets: [{
    label: 'Subjects Count',
    data: props.teacherWorkload.map(item => item.subjects_count),
    backgroundColor: '#10B981',
    borderColor: '#10B981',
    borderWidth: 1,
  }]
}));

const dayChartData = computed(() => ({
  labels: props.entriesByDay.map(item => item.day),
  datasets: [{
    label: 'Timetable Entries',
    data: props.entriesByDay.map(item => item.count),
    backgroundColor: '#3B82F6',
    borderColor: '#3B82F6',
    borderWidth: 1,
  }]
}));

const timeSlotChartData = computed(() => ({
  labels: props.entriesByTimeSlot.map(item => item.time_slot),
  datasets: [{
    label: 'Timetable Entries',
    data: props.entriesByTimeSlot.map(item => item.count),
    backgroundColor: '#F59E0B',
    borderColor: '#F59E0B',
    borderWidth: 1,
  }]
}));

const chartOptions = {
  responsive: true,
  maintainAspectRatio: false,
  plugins: {
    legend: {
      display: true,
      position: 'bottom',
    },
  },
};
</script>

<template>
  <div class="space-y-6">
    <!-- Subject Distribution Chart -->
    <CardBoxWidget
      :class="[
        'bg-white dark:bg-slate-800 rounded-lg shadow-sm border border-gray-200 dark:border-slate-700'
      ]"
    >
      <div class="p-6">
        <div class="flex items-center justify-between mb-4">
          <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
            Subject Distribution
          </h3>
          <BaseIcon :path="mdiChartPie" size="20" class="text-gray-400" />
        </div>

        <div class="h-80">
          <CategoryPieChart
            v-if="subjectDistribution.length > 0"
            :data="subjectChartData"
            type="pie"
          />
          <div v-else class="flex items-center justify-center h-full">
            <p class="text-gray-500 dark:text-gray-400">No subject data available</p>
          </div>
        </div>
      </div>
    </CardBoxWidget>

    <!-- Teacher Workload Chart -->
    <CardBoxWidget
      :class="[
        'bg-white dark:bg-slate-800 rounded-lg shadow-sm border border-gray-200 dark:border-slate-700'
      ]"
    >
      <div class="p-6">
        <div class="flex items-center justify-between mb-4">
          <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
            Teacher Workload
          </h3>
          <BaseIcon :path="mdiChartBar" size="20" class="text-gray-400" />
        </div>

        <div class="h-80">
          <CourseBarChart
            v-if="teacherWorkload.length > 0"
            :data="teacherChartData"
            type="bar"
          />
          <div v-else class="flex items-center justify-center h-full">
            <p class="text-gray-500 dark:text-gray-400">No teacher workload data available</p>
          </div>
        </div>
      </div>
    </CardBoxWidget>

    <!-- Timetable Entries by Day -->
    <CardBoxWidget
      :class="[
        'bg-white dark:bg-slate-800 rounded-lg shadow-sm border border-gray-200 dark:border-slate-700'
      ]"
    >
      <div class="p-6">
        <div class="flex items-center justify-between mb-4">
          <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
            Timetable Entries by Day
          </h3>
          <BaseIcon :path="mdiTrendingUp" size="20" class="text-gray-400" />
        </div>

        <div class="h-80">
          <CourseBarChart
            v-if="entriesByDay.length > 0"
            :data="dayChartData"
            type="bar"
          />
          <div v-else class="flex items-center justify-center h-full">
            <p class="text-gray-500 dark:text-gray-400">No timetable entries data available</p>
          </div>
        </div>
      </div>
    </CardBoxWidget>

    <!-- Timetable Entries by Time Slot -->
    <CardBoxWidget
      :class="[
        'bg-white dark:bg-slate-800 rounded-lg shadow-sm border border-gray-200 dark:border-slate-700'
      ]"
    >
      <div class="p-6">
        <div class="flex items-center justify-between mb-4">
          <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
            Timetable Entries by Time Slot
          </h3>
          <BaseIcon :path="mdiChartBar" size="20" class="text-gray-400" />
        </div>

        <div class="h-80">
          <CourseBarChart
            v-if="entriesByTimeSlot.length > 0"
            :data="timeSlotChartData"
            type="bar"
          />
          <div v-else class="flex items-center justify-center h-full">
            <p class="text-gray-500 dark:text-gray-400">No timetable entries by time slot data available</p>
          </div>
        </div>
      </div>
    </CardBoxWidget>
  </div>
</template>
