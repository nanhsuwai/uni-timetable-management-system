<script setup>
import { computed, ref } from "vue";
import { Head, usePage } from "@inertiajs/vue3";
import {
  mdiAccountGroup,
  mdiAccountArrowRight,
  mdiBookshelf,
  mdiClockTimeFour,
  mdiCalendarBadge,
  mdiFileDocument,
  mdiFileChart,
  mdiAccountSchool,
  mdiShape,
  mdiChartTimelineVariant
} from "@mdi/js";

import LayoutAuthenticated from "@/Layouts/LayoutAuthenticated.vue";
import SectionMain from "@/Components/SectionMain.vue";
import SectionTitleLineWithButton from "@/Components/SectionTitleLineWithButton.vue";
import DashboardStatsCard from "@/Components/DashboardStatsCard.vue";
import RecentActivities from "@/Components/RecentActivities.vue";
import QuickActions from "@/Components/QuickActions.vue";
import DashboardCharts from "@/Components/DashboardCharts.vue";
import SectionTimetables from "@/Components/SectionTimetables.vue";

const props = defineProps({
  stats: { type: Object, default: () => ({}) },
  recentActivities: { type: Array, default: () => [] },
  subjectDistribution: { type: Array, default: () => [] },
  teacherWorkload: { type: Array, default: () => [] },
  entriesByDay: { type: Array, default: () => [] },
  entriesByTimeSlot: { type: Array, default: () => [] },
  academicYears: { type: Array, default: () => [] },
  semesters: { type: Array, default: () => [] },
  academicPrograms: { type: Array, default: () => [] },
  academicLevels: { type: Array, default: () => [] },
  sections: { type: Array, default: () => [] },
  sectionTimetables: { type: Array, default: () => [] },
});

// User permissions
const userPermissions = computed(() => {
  return usePage().props.auth.user.permissions.reduce((acc, p) => {
    acc[p.code] = true;
    return acc;
  }, {});
});

// Stats cards
const statsCards = computed(() => [
  { title: "Academic Years", value: props.stats.total_academic_years || 0, icon: mdiCalendarBadge, color: "info" },
  { title: "Semesters", value: props.stats.total_semesters || 0, icon: mdiClockTimeFour, color: "success" },
  { title: "Academic Programs", value: props.stats.total_programs || 0, icon: mdiFileDocument, color: "warning" },
  { title: "Academic Levels", value: props.stats.total_levels || 0, icon: mdiFileChart, color: "danger" },
  { title: "Sections", value: props.stats.total_sections || 0, icon: mdiAccountSchool, color: "info" },
  { title: "Classrooms", value: props.stats.total_classrooms || 0, icon: mdiShape, color: "success" },
  { title: "Subjects", value: props.stats.total_subjects || 0, icon: mdiBookshelf, color: "warning" },
  { title: "Teachers", value: props.stats.total_teachers || 0, icon: mdiAccountArrowRight, color: "danger" },
  { title: "Timetable Entries", value: props.stats.total_timetable_entries || 0, icon: mdiCalendarBadge, color: "info" },
  { title: "Total Users", value: props.stats.total_users || 0, icon: mdiAccountGroup, color: "success" },
]);

// Filter variables
const selectedAcademicYear = ref(null);
const selectedSemester = ref(null);
const selectedProgram = ref(null);
const selectedLevel = ref(null);
const selectedSection = ref(null);

// Filtered options
const filteredSemesters = computed(() => {
  if (!selectedAcademicYear.value) return props.semesters;
  return props.semesters; // Assuming semesters are not tied to academic year in this context
});

const filteredPrograms = computed(() => {
  if (!selectedSemester.value) return props.academicPrograms;
  return props.academicPrograms; // Assuming programs are not tied to semester
});

const filteredSections = computed(() => {
  if (!selectedLevel.value) return props.sections;
  return props.sections; // Assuming sections are not tied to level in this context
});

// Filtered section timetables
const filteredSectionTimetables = computed(() => {
  if (!selectedSection.value) return props.sectionTimetables;
  return props.sectionTimetables.filter(st => st.id === selectedSection.value);
});

// Current academic info
const currentAcademicInfo = computed(() => [
  { title: "Active Academic Year", value: props.stats.active_academic_year || 'None', icon: mdiCalendarBadge, color: "info" },
  { title: "Active Semester", value: props.stats.active_semester || 'None', icon: mdiClockTimeFour, color: "success" },
]);
</script>

<template>
  <LayoutAuthenticated>
    <Head title="Dashboard" />

    <SectionMain>
      <SectionTitleLineWithButton :icon="mdiChartTimelineVariant" title="Dashboard" main />

      <!-- Current Academic Information -->
      <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
        <DashboardStatsCard
          v-for="info in currentAcademicInfo"
          :key="info.title"
          v-bind="info"
        />
      </div>

      <!-- Filters -->
      <!-- <div class="grid grid-cols-1 md:grid-cols-5 gap-6 mb-8">
        <div>
          <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Academic Year</label>
          <select v-model="selectedAcademicYear" class="w-full p-2 border border-gray-300 rounded-md dark:bg-slate-800 dark:border-slate-700">
            <option value="">All</option>
            <option v-for="year in academicYears" :key="year.id" :value="year.id">{{ year.name }}</option>
          </select>
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Semester</label>
          <select v-model="selectedSemester" class="w-full p-2 border border-gray-300 rounded-md dark:bg-slate-800 dark:border-slate-700">
            <option value="">All</option>
            <option v-for="semester in filteredSemesters" :key="semester.id" :value="semester.id">{{ semester.name }}</option>
          </select>
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Academic Program</label>
          <select v-model="selectedProgram" class="w-full p-2 border border-gray-300 rounded-md dark:bg-slate-800 dark:border-slate-700">
            <option value="">All</option>
            <option v-for="program in filteredPrograms" :key="program.id" :value="program.id">{{ program.name }}</option>
          </select>
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Academic Level</label>
          <select v-model="selectedLevel" class="w-full p-2 border border-gray-300 rounded-md dark:bg-slate-800 dark:border-slate-700">
            <option value="">All</option>
            <option v-for="level in academicLevels" :key="level.id" :value="level.id">{{ level.name }}</option>
          </select>
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Section</label>
          <select v-model="selectedSection" class="w-full p-2 border border-gray-300 rounded-md dark:bg-slate-800 dark:border-slate-700">
            <option value="">All</option>
            <option v-for="section in filteredSections" :key="section.id" :value="section.id">{{ section.name }}</option>
          </select>
        </div>
      </div> -->

      <!-- Statistics Cards Grid -->
      <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-5 gap-6 mb-8">
        <DashboardStatsCard
          v-for="card in statsCards"
          :key="card.title"
          v-bind="card"
        />
      </div>

      <!-- Main Dashboard Content -->
      <!-- <div class="grid grid-cols-1 xl:grid-cols-3 gap-6">
        <div class="xl:col-span-1 space-y-6">
          <RecentActivities :activities="recentActivities" />
          <QuickActions :permissions="userPermissions" />
        </div>
        <div class="xl:col-span-2">
          <DashboardCharts
            :subject-distribution="subjectDistribution"
            :teacher-workload="teacherWorkload"
            :entries-by-day="entriesByDay"
            :entries-by-time-slot="entriesByTimeSlot"
          />
        </div>
      </div> -->

      <!-- Section Timetables -->
      <!-- <div class="mt-8">
        <SectionTimetables :section-timetables="filteredSectionTimetables" />
      </div> -->
    </SectionMain>
  </LayoutAuthenticated>
</template>
