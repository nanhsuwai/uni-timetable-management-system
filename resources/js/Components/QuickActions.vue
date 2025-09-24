<script setup>
import CardBoxWidget from "@/Components/CardBoxWidget.vue";
import BaseButton from "@/Components/BaseButton.vue";
import BaseIcon from "@/Components/BaseIcon.vue";
import { router } from "@inertiajs/vue3";
import {
  mdiPlus,
  mdiCalendarPlus,
  mdiAccountPlus,
  mdiBookOpenPageVariant,
  mdiMapMarker,
  mdiAccountSchool,
  mdiClipboardText,
  mdiGrid
} from "@mdi/js";

const props = defineProps({
  permissions: {
    type: Object,
    default: () => ({}),
  },
});

const quickActions = [
  {
    title: "Add Timetable Entry",
    description: "Create a new class schedule",
    icon: mdiPlus,
    route: "timetable_entry.create",
    permission: "manage_timetable_entry",
    color: "success",
  },
  {
    title: "View Timetable Grid",
    description: "See the complete timetable overview",
    icon: mdiGrid,
    route: "timetable_entry.grid",
    permission: "manage_timetable_entry",
    color: "info",
  },
  {
    title: "Add Teacher",
    description: "Register a new teacher",
    icon: mdiAccountPlus,
    route: "teacher.create",
    permission: "manage_teacher",
    color: "warning",
  },
  {
    title: "Add Subject",
    description: "Create a new subject",
    icon: mdiBookOpenPageVariant,
    route: "subject.create",
    permission: "manage_subject",
    color: "danger",
  },
  {
    title: "Add Classroom",
    description: "Register a new classroom",
    icon: mdiMapMarker,
    route: "classroom.create",
    permission: "manage_classroom",
    color: "info",
  },
  {
    title: "Add Section",
    description: "Create a new section",
    icon: mdiAccountSchool,
    route: "section.create",
    permission: "manage_section",
    color: "success",
  },
];

const handleAction = (action) => {
  if (action.route) {
    router.visit(route(action.route));
  }
};

const canAccess = (permission) => {
  // Check if user has the required permission
  return props.permissions[permission] === true;
};
</script>

<template>
  <CardBoxWidget
    :class="[
      'bg-white dark:bg-slate-800 rounded-lg shadow-sm border border-gray-200 dark:border-slate-700'
    ]"
  >
    <div class="p-6">
      <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">
        Quick Actions
      </h3>

      <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
        <div
          v-for="action in quickActions"
          :key="action.title"
          v-show="canAccess(action.permission)"
          @click="handleAction(action)"
          :class="[
            'p-4 rounded-lg border border-gray-200 dark:border-slate-700',
            'hover:shadow-md transition-all duration-200 cursor-pointer',
            'bg-gray-50 dark:bg-slate-700/50 hover:bg-gray-100 dark:hover:bg-slate-700'
          ]"
        >
          <div class="flex items-start space-x-3">
            <div :class="[
              'p-2 rounded-lg flex-shrink-0',
              action.color === 'success' ? 'bg-green-100 dark:bg-green-900/30' :
              action.color === 'info' ? 'bg-blue-100 dark:bg-blue-900/30' :
              action.color === 'warning' ? 'bg-yellow-100 dark:bg-yellow-900/30' :
              action.color === 'danger' ? 'bg-red-100 dark:bg-red-900/30' :
              'bg-gray-100 dark:bg-gray-900/30'
            ]">
              <BaseIcon
                :path="action.icon"
                size="20"
                :class="[
                  action.color === 'success' ? 'text-green-600 dark:text-green-400' :
                  action.color === 'info' ? 'text-blue-600 dark:text-blue-400' :
                  action.color === 'warning' ? 'text-yellow-600 dark:text-yellow-400' :
                  action.color === 'danger' ? 'text-red-600 dark:text-red-400' :
                  'text-gray-600 dark:text-gray-400'
                ]"
              />
            </div>

            <div class="flex-1 min-w-0">
              <h4 class="text-sm font-medium text-gray-900 dark:text-white">
                {{ action.title }}
              </h4>
              <p class="text-xs text-gray-600 dark:text-gray-400 mt-1">
                {{ action.description }}
              </p>
            </div>
          </div>
        </div>
      </div>

      <div v-if="quickActions.filter(action => canAccess(action.permission)).length === 0" class="text-center py-8">
        <p class="text-gray-500 dark:text-gray-400">
          No quick actions available for your permissions
        </p>
      </div>
    </div>
  </CardBoxWidget>
</template>
