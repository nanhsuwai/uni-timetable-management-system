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

      <CardBox v-if="allSelectionsComplete"
                                class="p-0 border border-gray-200 mt-6 rounded-xl overflow-hidden">

                                <div class="overflow-x-auto">
                                    <table class="hidden md:table w-full border-collapse min-w-[600px] md:min-w-full">
                                        <thead>
                                            <tr>
                                                <th :colspan="1 + uniqueTimeSlots.length"
                                                    class="text-center font-extrabold text-2xl bg-teal-600 text-white p-4">
                                                    University of Computer Studies, Hinthada
                                                </th>
                                            </tr>
                                            <tr>
                                                <th :colspan="1 + uniqueTimeSlots.length"
                                                    class="text-center text-sm bg-gray-100 p-3 border-b border-gray-300 text-gray-700">
                                                    <span v-if="selectedYear" class="font-bold text-base text-teal-700">
                                                        Academic Year: {{ selectedYear.name }}
                                                    </span>
                                                    <span v-if="selectedSemester" class="ml-2 font-bold text-teal-700">
                                                        ({{ selectedSemester.name }})
                                                    </span>
                                                    <span class="block mt-1 text-base text-gray-600">
                                                        Timetable For: <span v-if="selectedLevel"
                                                            class="font-extrabold text-gray-800">{{ selectedLevel.name
                                                            }}</span>
                                                        (<span v-if="selectedProgram">{{ selectedProgram.name }}</span>)
                                                        <span v-if="selectedSection && selectedSection.name"
                                                            class="ml-2">
                                                            Section: **{{ selectedSection.name }}**
                                                        </span>
                                                    </span>
                                                    <span class="block text-right pr-4 text-xs text-gray-500"
                                                        v-if="selectedSection && selectedSection?.classroom">
                                                        Classroom: {{ selectedSection.classroom.room_no }}
                                                    </span>
                                                </th>
                                            </tr>
                                            <tr>
                                                <th
                                                    class="border border-gray-300 p-3 bg-teal-100 font-extrabold text-base text-teal-800 w-28">
                                                    Day\Time
                                                </th>
                                                <th v-for="slot in uniqueTimeSlots" :key="slot.start"
                                                    class="border border-gray-300 p-3 bg-teal-100 font-bold text-sm text-center"
                                                    :class="{
                                                        'bg-orange-100 text-orange-800': isLunch(slot)
                                                    }">
                                                    {{ slot.label }}
                                                    <div v-if="isLunch(slot)"
                                                        class="text-xs text-orange-700 font-semibold">
                                                        Lunch Break
                                                    </div>
                                                </th>
                                            </tr>
                                        </thead>

                                        <tbody>
                                            <tr v-for="day in days" :key="day.key" class="transition-colors">
                                                <td
                                                    class="border border-gray-300 p-3 bg-gray-50 text-base font-extrabold text-center text-teal-800">
                                                    {{ day.label }}
                                                </td>
                                                <td v-for="slot in uniqueTimeSlots" :key="`${day.key}-${slot.start}`"
                                                    class="border border-gray-300 p-2 text-center align-middle" :class="{
                                                        'bg-orange-50': isLunch(slot),
                                                        'bg-white hover:bg-teal-50/50 transition-colors': !isLunch(slot)
                                                    }">
                                                    <div v-if="getEntry(day.key, slot)" class="text-sm">
                                                        <div
                                                            class="text-sm font-extrabold text-teal-700 mb-1 leading-tight">
                                                            {{ getSubjectDisplay(getEntry(day.key, slot)) }}
                                                        </div>
                                                    </div>
                                                    <div v-else-if="!isLunch(slot)"
                                                        class="text-cyan-700 text-sm italic p-1">
                                                        Lab/Library/<br>အားကစား/ဂီတ
                                                    </div>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>

                                <div class="md:hidden space-y-6 p-4">
                                    <div v-for="day in days" :key="day.key"
                                        class="bg-white shadow-lg rounded-xl overflow-hidden border border-gray-200">
                                        <div class="bg-teal-600 text-white text-center py-3 font-extrabold text-lg">
                                            {{ day.label }}
                                        </div>

                                        <div v-for="slot in uniqueTimeSlots" :key="`${day.key}-${slot.start}`"
                                            class="p-4 border-b border-gray-200 flex items-center justify-between transition-colors"
                                            :class="{ 'bg-orange-50': isLunch(slot) }">

                                            <div class="font-medium text-gray-700 w-1/4 flex-shrink-0 pr-2">
                                                 <span class="text-sm font-semibold"> {{ slot.name || slot.id }}<br></br> {{ slot.label }}</span>
                                            </div>

                                            <div class="text-right w-3/4">
                                                <span v-if="isLunch(slot)"
                                                    class="text-base font-bold text-orange-700 block">
                                                    Lunch Break
                                                </span>
                                                <span v-else-if="getEntry(day.key, slot)"
                                                    class="text-lg font-extrabold text-teal-700 block leading-tight">
                                                    {{ getSubjectDisplay(getEntry(day.key, slot)) }}
                                                </span>
                                                <span v-else class="text-gray-400 italic text-sm block">
                                                    Library/Lab<br>အားကစား/ဂီတ  
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </CardBox>

                            <CardBox v-if="allSelectionsComplete && uniqueSubjects.length > 0"
                                class="mt-8 p-6 border border-gray-200 shadow-lg rounded-xl">
                                <h4 class="font-extrabold text-xl text-teal-800 mb-4 border-b-2 border-teal-100 pb-2"
                                    v-if="selectedSection && selectedSection?.section_head_teacher">
                                    သင်တန်းမှူး - {{ selectedSection.section_head_teacher.name }}
                                </h4>

                                <div class="overflow-x-auto">
                                    <table class="hidden md:table w-full border-collapse border border-gray-300">
                                        <thead>
                                            <tr>
                                                <th
                                                    class="border border-gray-300 p-3 bg-gray-50 font-bold text-sm w-24">
                                                    Code</th>
                                                <th
                                                    class="border border-x border-gray-300 p-3 bg-gray-50 font-bold text-sm w-auto">
                                                    Subject Name</th>
                                                <th
                                                    class="border border-gray-300 p-3 bg-gray-50 font-bold text-sm w-64">
                                                    Teacher Name(s)</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr v-for="subject in uniqueSubjects" :key="subject.id"
                                                class="hover:bg-teal-50 transition-colors">
                                                <td
                                                    class="border border-gray-300 p-3 text-sm text-gray-600 font-mono text-center">
                                                    {{ subject.code }}</td>
                                                <td
                                                    class="border border-x border-gray-300 p-3 text-sm text-gray-800 font-semibold">
                                                    {{ subject.name }}</td>
                                                <td class="border border-gray-300 p-3 text-sm text-gray-700">{{
                                                    subject.teacherNames }}</td>
                                            </tr>
                                        </tbody>
                                    </table>

                                    <div class="md:hidden space-y-4">
                                        <div v-for="subject in uniqueSubjects" :key="subject.id"
                                            class="border border-gray-200 rounded-lg shadow-sm p-4 bg-white hover:bg-teal-50 transition-colors">
                                            <div class="text-base font-extrabold text-teal-700 mb-1 leading-tight">
                                                <span class="font-mono text-sm text-gray-500 mr-2">{{ subject.code
                                                }}</span> {{ subject.name }}
                                            </div>
                                            <div class="text-sm text-gray-600 mt-2">
                                                <span class="font-bold text-gray-800">Teacher:</span>
                                                {{ subject.teacherNames }}
                                            </div>
                                        </div>
                                    </div>
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
         <!--  <div class="flex items-center">
            <div
              class="w-4 h-4 bg-gray-100 dark:bg-gray-700 border border-gray-300 dark:border-gray-600 mr-2 rounded-sm">
            </div>
            <span>Day / Time Header</span>
          </div> -->
        </div>
      </div>
    </div>
  </LayoutAuthenticated>
</template>