<script setup>
import { ref, computed, watch } from "vue";
import { useForm, router } from "@inertiajs/vue3";
import toast from "@/Stores/toast";
import checkPermissionComposable from "@/Composables/Permission/checkPermission";

// Components
import LayoutAuthenticated from "@/Layouts/LayoutAuthenticated.vue";
import SectionTitleLineWithButton from "@/Components/SectionTitleLineWithButton.vue";
import CardBox from "@/Components/CardBox.vue";
import TextInput from "@/Components/TextInput.vue";
import InputLabel from "@/Components/InputLabel.vue";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import SecondaryButton from "@/Components/SecondaryButton.vue";
import BaseButton from "@/Components/BaseButton.vue";
import { mdiTable, mdiGrid, mdiShapePlus, mdiHeart, mdiHeartOutline } from "@mdi/js";

// Props from Laravel
const props = defineProps({
  entries: Array,
  academicYears: Array,
  programs: Array,
  levels: Array,
  sections: Array,
  subjects: Array,
  teachers: Array,
  semesters: Array,
  timeSlots: Array,
  filters: Object,
});

let hasPermission = ref(checkPermissionComposable("timetable_entry_manage"));

// Filters
const filterYear = ref(props.filters.filterYear || "");
const filterSemester = ref(props.filters.filterSemester || "");
const filterProgram = ref(props.filters.filterProgram || "");
const filterLevel = ref(props.filters.filterLevel || "");
const filterSection = ref(props.filters.filterSection || "");

// Update when filters change
watch(
  [filterYear, filterSemester, filterProgram, filterLevel, filterSection],
  ([newYear, newSemester, newProgram, newLevel, newSection]) => {
    router.get(
      route("timetable_entry:grid"),
      {
        filterYear: newYear,
        filterSemester: newSemester,
        filterProgram: newProgram,
        filterLevel: newLevel,
        filterSection: newSection,
      },
      { preserveState: true, replace: true }
    );
  }
);

// Computed for dependent selects
const filteredPrograms = computed(() => {
  if (!filterYear.value) return props.programs;
  return props.programs.filter(p => p.academic_year_id == filterYear.value);
});

const filteredSemesters = computed(() => {
  if (!filterYear.value) return props.semesters;
  return props.semesters.filter(s => s.academic_year_id == filterYear.value);
});

const filteredLevels = computed(() => {
  if (!filterProgram.value) return props.levels;
  return props.levels.filter(l => l.program_id == filterProgram.value);
});

const filteredSections = computed(() => {
  if (!filterLevel.value) return props.sections;
  return props.sections.filter(s => s.level_id == filterLevel.value);
});

// Check if all required selections are complete
const allSelectionsComplete = computed(() => {
  return filterYear.value &&
    filterSemester.value &&
    filterProgram.value &&
    filterLevel.value &&
    filterSection.value;
});

// Computed for selected items to display in header
const selectedYear = computed(() => props.academicYears.find(y => y.id == filterYear.value)); // ADDED: Selected Academic Year
const selectedSemester = computed(() => props.semesters.find(s => s.id == filterSemester.value));
const selectedProgram = computed(() => props.programs.find(p => p.id == filterProgram.value));
const selectedLevel = computed(() => props.levels.find(l => l.id == filterLevel.value));
const selectedSection = computed(() => props.sections.find(s => s.id == filterSection.value));

// Time slots for the grid - using dynamic data from backend and filtering by academic year
const timeSlots = computed(() => {
  let slots = props.timeSlots;

  // Filter by academic year if selected
  if (filterYear.value) {
    slots = slots.filter(slot => slot.academic_year_id == filterYear.value);
  }
  // console.log("Filtered Time Slots:", slots); // Removed console.log for clean code
  return slots.map(slot => ({
    start: slot.start_time,
    end: slot.end_time,
    label: `${slot.start_time} - ${slot.end_time}`,
    code: slot.code,
    name: slot.name,
    day_of_week: slot.day_of_week,
    academic_year_id: slot.academic_year_id,
    is_lunch_period: slot.is_lunch_period,
  }));
});

// Days of the week
const days = [
  { key: "monday", label: "Monday" },
  { key: "tuesday", label: "Tuesday" },
  { key: "wednesday", label: "Wednesday" },
  { key: "thursday", label: "Thursday" },
  { key: "friday", label: "Friday" },
];

// Time slots grouped by day for the grid - Kept for potential future use, though not used in current template
const timeSlotsByDay = computed(() => {
  const slotsByDay = {};

  // Initialize all days
  days.forEach(day => {
    slotsByDay[day.key] = [];
  });

  // Group time slots by day
  timeSlots.value.forEach(slot => {
    if (slotsByDay[slot.day_of_week]) {
      slotsByDay[slot.day_of_week].push(slot);
    }
  });

  return slotsByDay;
});

// Get unique time slots for header (to avoid duplicates)
const uniqueTimeSlots = computed(() => {
  const unique = [];
  const seen = new Set();

  timeSlots.value.forEach(slot => {
    const key = `${slot.start}-${slot.end}`;
    if (!seen.has(key)) {
      seen.add(key);
      unique.push(slot);
    }
  });
  // console.log("Unique Time Slots:", unique); // Removed console.log for clean code
  return unique.sort((a, b) => a.start.localeCompare(b.start));
});

// Organize entries by day and time slot
const organizedEntries = computed(() => {
  const grid = {};

  // Initialize grid with all time slots for all days
  days.forEach(day => {
    grid[day.key] = {};
    uniqueTimeSlots.value.forEach(slot => {
      grid[day.key][slot.start] = null;
    });
  });

  // Fill grid with entries - match by day and time
  props.entries.forEach(entry => {
    if (entry.time_slot) {
      const day = entry.time_slot.day_of_week;
      const time = entry.time_slot.start_time;

      if (grid[day] && grid[day][time] !== undefined) {
        grid[day][time] = entry;
      }
    }
  });

  return grid;
});

// Debug information - Kept as is
const debugInfo = computed(() => {
  return {
    totalEntries: props.entries.length,
    totalTimeSlots: props.timeSlots.length,
    filteredTimeSlots: timeSlots.value.length,
    uniqueTimeSlotsCount: uniqueTimeSlots.value.length,
    currentFilters: {
      year: filterYear.value,
      semester: filterSemester.value,
      program: filterProgram.value,
      level: filterLevel.value,
      section: filterSection.value,
    },
    fridayEntries: props.entries.filter(entry =>
      entry.time_slot && entry.time_slot.day_of_week === 'friday'
    ).length,
  };
});


const uniqueSubjects = computed(() => {
  const subjectsMap = new Map();

  props.entries.forEach(entry => {
    if (entry.subject) {
      const subjectId = entry.subject.id;

      // Initialize subject entry in map if it doesn't exist
      if (!subjectsMap.has(subjectId)) {
        subjectsMap.set(subjectId, {
          id: subjectId,
          code: entry.subject.code,
          name: entry.subject.name,
          teachers: new Set(), // Use a Set to store unique teacher names
        });
      }

      // Add teachers from the current entry to the subject's teacher list
      if (entry.teachers && entry.teachers.length > 0) {
        const subjectEntry = subjectsMap.get(subjectId);
        entry.teachers.forEach(teacher => {
          subjectEntry.teachers.add(teacher.name);
        });
      }
    }
  });

  // Convert map values to an array, and convert teacher sets to comma-separated strings
  return Array.from(subjectsMap.values())
    .map(subject => ({
      ...subject,
      teacherNames: Array.from(subject.teachers).join(' ၊ ') || 'N/A',
    }))
    .sort((a, b) => a.code.localeCompare(b.code));
});

// Check if a time slot is lunch - Kept as is
const isLunch = (timeSlot) => {
  return timeSlot.is_lunch_period === 1;
};

// Get entry for specific day and time - Kept as is
const getEntry = (day, timeSlot) => {
  return organizedEntries.value[day]?.[timeSlot.start] || null;
};

// Get subject code - Kept as is
const getSubjectDisplay = (entry) => {
  if (!entry || !entry.subject) return "";
  return `${entry.subject.code}`;
};

// Get teacher names (multiple teachers) - Kept as is
const getTeacherDisplay = (entry) => {
  if (!entry || !entry.teachers || entry.teachers.length === 0) return "";
  if (entry.teachers.length === 1) return entry.teachers[0].name;
  return entry.teachers.map(t => t.name).join(", ");
};

// Get classroom - Kept as is
const getClassroomDisplay = (entry) => {
  if (!entry || !entry.classroom) return "";
  return entry.classroom.room_no;
};

// Download PDF - Kept as is
const downloadPDF = () => {
  const url = route('timetable_entry:generate', {
    filterYear: filterYear.value,
    filterSemester: filterSemester.value,
    filterProgram: filterProgram.value,
    filterLevel: filterLevel.value,
    filterSection: filterSection.value,
  });
  window.open(url, '_blank');
};
const exportExcel = () => {
  const url = route('timetable_entry:export', {
    filterYear: filterYear.value,
    filterSemester: filterSemester.value,
    filterProgram: filterProgram.value,
    filterLevel: filterLevel.value,
    filterSection: filterSection.value,
  });
  window.open(url, '_blank');

  //window.open(route('timetable_entry.export') + '?' + params.toString(), '_blank');
};




</script>

<template>
  <LayoutAuthenticated>
    <div class="py-6 max-w-7xl mx-auto sm:px-6 lg:px-8">
      <SectionTitleLineWithButton :icon="mdiGrid" title="Timetable Grid View" class="mb-6">
        <div class="flex space-x-3">
          <BaseButton @click="router.get(route('timetable_entry:all'))" color="info" :icon="mdiTable" label="Table View"
            class="shadow-md dark:shadow-none" />
          <BaseButton @click="downloadPDF" color="success" icon="mdi-download" label="Download PDF"
            class="shadow-md dark:shadow-none" />
          <BaseButton @click="exportExcel" color="warning" icon="mdi-download" label="Export Excel"
            class="shadow-md dark:shadow-none" />
        </div>
      </SectionTitleLineWithButton>

      <div
        class="mb-8 grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 p-6 bg-white dark:bg-gray-800 rounded-xl shadow-lg ring-1 ring-gray-200 dark:ring-gray-700">
        <div>
          <InputLabel value="Academic Year" class="dark:text-gray-300 mb-1" />
          <select v-model="filterYear"
            class="w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 rounded-lg shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
            <option value="">All Years</option>
            <option v-for="y in props.academicYears" :key="y.id" :value="y.id">
              {{ y.name }}
            </option>
          </select>
        </div>
        <div>
          <InputLabel value="Program" class="dark:text-gray-300 mb-1" />
          <select v-model="filterProgram"
            class="w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 rounded-lg shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
            <option value="">All Programs</option>
            <option v-for="p in filteredPrograms" :key="p.id" :value="p.id">
              {{ p.name }}
            </option>
          </select>
        </div>
        <div>
          <InputLabel value="Level" class="dark:text-gray-300 mb-1" />
          <select v-model="filterLevel"
            class="w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 rounded-lg shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
            <option value="">All Levels</option>
            <option v-for="l in filteredLevels" :key="l.id" :value="l.id">
              {{ l.name }}
            </option>
          </select>
        </div>
        <div>
          <InputLabel value="Section" class="dark:text-gray-300 mb-1" />
          <select v-model="filterSection"
            class="w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 rounded-lg shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
            <option value="">All Sections</option>
            <option v-for="s in filteredSections" :key="s.id" :value="s.id">
              {{ s.name }}
            </option>
          </select>
        </div>
        <div>
          <InputLabel value="Semester" class="dark:text-gray-300 mb-1" />
          <select v-model="filterSemester"
            class="w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 rounded-lg shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
            <option value="">All Semesters</option>
            <option v-for="s in filteredSemesters" :key="s.id" :value="s.id">
              {{ s.name }}
            </option>
          </select>
        </div>
      </div>

      <div v-if="!allSelectionsComplete"
        class="mb-8 p-6 bg-blue-50 dark:bg-blue-900/50 border border-blue-200 dark:border-blue-700 rounded-xl shadow-md">
        <div class="flex items-start">
          <div class="flex-shrink-0 pt-1">
            <svg class="h-6 w-6 text-blue-500 dark:text-blue-400" viewBox="0 0 20 20" fill="currentColor">
              <path fill-rule="evenodd"
                d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z"
                clip-rule="evenodd" />
            </svg>
          </div>
          <div class="ml-4">
            <h3 class="text-lg font-bold text-blue-800 dark:text-blue-300 mb-2">
              ⚠️ Selection Required
            </h3>
            <div class="text-sm text-blue-700 dark:text-blue-400 space-y-2">
              <p>
                To generate the timetable grid, please select all five required
                criteria:
              </p>
              <ul class="list-disc list-inside ml-4 grid grid-cols-2 sm:grid-cols-3 gap-y-1">
                <li class="font-semibold">Academic Year</li>
                <li class="font-semibold">Semester</li>
                <li class="font-semibold">Academic Program</li>
                <li class="font-semibold">Academic Level</li>
                <li class="font-semibold">Section</li>
              </ul>
            </div>
          </div>
        </div>
      </div>

      <CardBox v-if="allSelectionsComplete" class="shadow-xl dark:shadow-2xl dark:shadow-indigo-900/30">
        <div class="overflow-x-auto">
          <table class="w-full border-collapse border border-gray-300 dark:border-gray-700">
            <thead>
              <tr>
                <th :colspan="1 + uniqueTimeSlots.length"
                  class="text-center font-extrabold text-xl p-4 border border-gray-300 dark:border-gray-700 bg-indigo-50 dark:bg-indigo-950 text-indigo-700 dark:text-indigo-300">
                  University of Computer Studies, Hinthada
                </th>
              </tr>
              <tr>
                <th :colspan="1 + uniqueTimeSlots.length"
                  class="text-center text-sm p-2 border border-gray-300 dark:border-gray-700 bg-gray-50 dark:bg-gray-800/80 text-gray-700 dark:text-gray-300">
                  <span v-if="selectedYear" class="font-bold text-base">
                    Academic Year: {{ selectedYear.name }}
                  </span>
                  <span v-if="selectedSemester" class="ml-4">
                    ({{ selectedSemester.name }})
                  </span>
                </th>
              </tr>
              <tr>
                <th :colspan="1 + uniqueTimeSlots.length"
                  class="text-center text-base font-semibold p-2 border border-gray-300 dark:border-gray-700 bg-gray-50 dark:bg-gray-800/80 text-teal-600 dark:text-teal-400">
                  Timetable For:
                  <span v-if="selectedLevel" class="ml-1">
                    {{ selectedLevel.name }}
                  </span>
                  (<span v-if="selectedProgram">{{ selectedProgram.name }}</span>)
                  <span v-if="selectedSection && selectedSection.name">
                    | Section: {{ selectedSection.name }}
                  </span>
                  <span class="block text-sm text-gray-600 text-right dark:text-gray-400 mt-1"
                    v-if="selectedSection && selectedSection?.classroom">Classroom:
                    {{ selectedSection.classroom.room_no }}
                  </span>
                </th>
              </tr>
              <tr>
                <th
                  class="border border-gray-300 dark:border-gray-700 p-3 bg-gray-100 dark:bg-gray-700 font-bold text-sm w-24 text-gray-800 dark:text-gray-200">
                  Day\Time
                </th>
                <th v-for="slot in uniqueTimeSlots" :key="slot.start"
                  class="border border-gray-300 dark:border-gray-700 p-3 font-bold text-sm text-center min-w-36 transition duration-150"
                  :class="{
                    'bg-orange-200 dark:bg-orange-900/50 text-orange-800 dark:text-orange-200': isLunch(slot),
                    'bg-gray-100 dark:bg-gray-700 text-gray-800 dark:text-gray-200': !isLunch(slot),
                  }">
                  {{ slot.label }}
                  <div v-if="isLunch(slot)" class="text-xs font-normal">
                    LUNCH BREAK
                  </div>
                </th>
              </tr>
            </thead>

            <tbody>
              <tr v-for="day in days" :key="day.key"
                class="hover:bg-gray-50 dark:hover:bg-gray-700/50 transition duration-150">
                <td
                  class="border border-gray-300 dark:border-gray-700 p-3 bg-gray-50 dark:bg-gray-800 font-bold text-center text-gray-800 dark:text-gray-300">
                  {{ day.label }}
                </td>
                <td v-for="slot in uniqueTimeSlots" :key="`${day.key}-${slot.start}`"
                  class="border border-gray-300 dark:border-gray-700 p-2 text-center align-top h-24" :class="{
                    'bg-orange-50 dark:bg-orange-900/30': isLunch(slot),
                    'bg-white dark:bg-gray-900': !isLunch(slot),
                  }">
                  <div v-if="getEntry(day.key, slot)" class="text-sm">
                    <div class="font-extrabold text-lg text-indigo-700 dark:text-indigo-400 leading-tight text-center pt-4">
                      {{ getSubjectDisplay(getEntry(day.key, slot)) }}
                    </div>

                  </div>
                  <div v-else-if="!isLunch(slot)" class="text-gray-400 dark:text-gray-600 text-md italic pt-4">
                    Lab/Library/<br>အားကစား/ဂီတ
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </CardBox>

      <CardBox v-if="allSelectionsComplete && uniqueSubjects.length > 0"
        class="mt-6 shadow-xl dark:shadow-2xl dark:shadow-indigo-900/30">

        <span v-if="selectedSection && selectedSection?.section_head_teacher"
          class="block text-md font-semibold text-teal-600 dark:text-teal-400 mt-1 mb-3">
          သင်တန်းမှူး -
          {{ selectedSection.section_head_teacher.name }}
        </span>


        <div class="overflow-x-auto">
          <table class="w-full border-collapse border border-gray-300 dark:border-gray-700">
            <thead class="bg-gray-50 dark:bg-gray-700">
              <tr>
                <th
                  class="border border-gray-300 dark:border-gray-700 p-3 font-bold w-32 text-gray-700 dark:text-gray-300">
                  Code
                </th>
                <th
                  class="border border-x border-gray-300 dark:border-gray-700 p-3 font-bold w-auto text-left text-gray-700 dark:text-gray-300">
                  Subject Name
                </th>
                <th
                  class="border border-gray-300 dark:border-gray-700 p-3 font-bold w-80 text-left text-gray-700 dark:text-gray-300">
                  Instructor(s)
                </th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="subject in uniqueSubjects" :key="subject.id"
                class="hover:bg-gray-50 dark:hover:bg-gray-800 transition duration-150">
                <td
                  class="border border-gray-300 dark:border-gray-700 p-2 text-sm text-center font-medium text-indigo-700 dark:text-indigo-400">
                  {{ subject.code }}
                </td>
                <td
                  class="border border-x border-gray-300 dark:border-gray-700 p-2 text-sm font-semibold text-gray-800 dark:text-gray-200 text-left">
                  {{ subject.name }}
                </td>
                <td
                  class="border border-gray-300 dark:border-gray-700 p-2 text-sm text-gray-700 dark:text-gray-300 text-left">
                  {{ subject.teacherNames || 'Unassigned' }}
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </CardBox>

      <div
        class="mt-6 p-4 bg-gray-50 dark:bg-gray-800 rounded-xl shadow-md border border-gray-200 dark:border-gray-700">
        <div class="flex flex-wrap gap-6 text-sm dark:text-gray-300">
          <div class="flex items-center">
            <div
              class="w-4 h-4 bg-orange-200 dark:bg-orange-900/50 border border-orange-300 dark:border-orange-700 mr-2 rounded-sm">
            </div>
            <span>Lunch Period (Break)</span>
          </div>
          <div class="flex items-center">
            <div class="w-4 h-4 bg-white dark:bg-gray-900 border border-gray-300 dark:border-gray-600 mr-2 rounded-sm">
            </div>
            <span>Regular Class / Subject</span>
          </div>
          <div class="flex items-center">
            <div
              class="w-4 h-4 bg-gray-100 dark:bg-gray-700 border border-gray-300 dark:border-gray-600 mr-2 rounded-sm">
            </div>
            <span>Day / Time Header</span>
          </div>
        </div>
      </div>
    </div>
  </LayoutAuthenticated>
</template>