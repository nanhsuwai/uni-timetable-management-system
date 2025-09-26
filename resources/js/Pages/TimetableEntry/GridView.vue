<script setup>
import { ref, computed, watch } from "vue";
import { useForm, router } from "@inertiajs/vue3";
import toast from "@/Stores/toast";

// Components
import LayoutAuthenticated from "@/Layouts/LayoutAuthenticated.vue";
import SectionTitleLineWithButton from "@/Components/SectionTitleLineWithButton.vue";
import CardBox from "@/Components/CardBox.vue";
import TextInput from "@/Components/TextInput.vue";
import InputLabel from "@/Components/InputLabel.vue";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import SecondaryButton from "@/Components/SecondaryButton.vue";
import BaseButton from "@/Components/BaseButton.vue";
import { mdiTable, mdiGrid, mdiShapePlus } from "@mdi/js";

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

// Time slots for the grid - using dynamic data from backend and filtering by academic year
const timeSlots = computed(() => {
  let slots = props.timeSlots;

  // Filter by academic year if selected
  if (filterYear.value) {
    slots = slots.filter(slot => slot.academic_year_id == filterYear.value);
  }

  return slots.map(slot => ({
    start: slot.start_time,
    end: slot.end_time,
    label: `${slot.start_time} - ${slot.end_time}`,
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

// Time slots grouped by day for the grid
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

// Debug information
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


// Check if a time slot is lunch
const isLunch = (timeSlot) => {
  return timeSlot.is_lunch_period === 1;
};

// Get entry for specific day and time
const getEntry = (day, timeSlot) => {
  return organizedEntries.value[day]?.[timeSlot.start] || null;
};

// Get subject name with code
const getSubjectDisplay = (entry) => {
  if (!entry || !entry.subject) return "";
  return `${entry.subject.name}`;
};

// Get teacher names (multiple teachers)
const getTeacherDisplay = (entry) => {
  if (!entry || !entry.teachers || entry.teachers.length === 0) return "";
  if (entry.teachers.length === 1) return entry.teachers[0].name;
  return entry.teachers.map(t => t.name).join(", ");
};

// Get classroom
const getClassroomDisplay = (entry) => {
  if (!entry || !entry.classroom) return "";
  return entry.classroom.room_no;
};

// Download PDF
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
</script>

<template>
  <LayoutAuthenticated>


    <div class="py-6 max-w-7xl mx-auto sm:px-6 lg:px-8">
      <SectionTitleLineWithButton
      :icon="mdiGrid"
      title="Timetable Grid View"
    >
      <BaseButton
        @click="router.get(route('timetable_entry:all'))"
        color="info"
        :icon="mdiTable"
        label="Table View"
      />
      <BaseButton
        v-if="allSelectionsComplete"
        @click="downloadPDF"
        color="success"
        icon="mdi-download"
        label="Download PDF"
      />
    </SectionTitleLineWithButton>
      <!-- Debug Information (only show if there are issues) -->
      <div v-if="debugInfo.fridayEntries === 0 && filterYear" class="mb-4 p-4 bg-yellow-50 border border-yellow-200 rounded-lg">
        <h4 class="font-semibold text-yellow-800 mb-2">Debug Information:</h4>
        <div class="text-sm text-yellow-700">
          <p>Total Entries: {{ debugInfo.totalEntries }}</p>
          <p>Total Time Slots: {{ debugInfo.totalTimeSlots }}</p>
          <p>Filtered Time Slots: {{ debugInfo.filteredTimeSlots }}</p>
          <p>Friday Entries: {{ debugInfo.fridayEntries }}</p>
          <p>Current Filters: {{ JSON.stringify(debugInfo.currentFilters) }}</p>
        </div>
      </div>

      <!-- Filters -->
      <div class="mb-6 grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
        <div>
          <InputLabel value="Academic Year" />
          <select v-model="filterYear" class="w-full border-gray-300 rounded">
            <option value="">All Years</option>
            <option v-for="y in props.academicYears" :key="y.id" :value="y.id">{{ y.name }}</option>
          </select>
        </div>
        <div>
          <InputLabel value="Program" />
          <select v-model="filterProgram" class="w-full border-gray-300 rounded">
            <option value="">All Programs</option>
            <option v-for="p in filteredPrograms" :key="p.id" :value="p.id">{{ p.name }}</option>
          </select>
        </div>
        <div>
          <InputLabel value="Level" />
          <select v-model="filterLevel" class="w-full border-gray-300 rounded">
            <option value="">All Levels</option>
            <option v-for="l in filteredLevels" :key="l.id" :value="l.id">{{ l.name }}</option>
          </select>
        </div>
        <div>
          <InputLabel value="Section" />
          <select v-model="filterSection" class="w-full border-gray-300 rounded">
            <option value="">All Sections</option>
            <option v-for="s in filteredSections" :key="s.id" :value="s.id">{{ s.name }}</option>
          </select>
        </div>
        <div>
          <InputLabel value="Semester" />
          <select v-model="filterSemester" class="w-full border-gray-300 rounded">
            <option value="">All Semesters</option>
            <option v-for="s in filteredSemesters" :key="s.id" :value="s.id">{{ s.name }}</option>
          </select>
        </div>
      </div>

      <!-- Selection Status Message -->
      <div v-if="!allSelectionsComplete" class="mb-6 p-4 bg-blue-50 border border-blue-200 rounded-lg">
        <div class="flex items-center">
          <div class="flex-shrink-0">
            <svg class="h-5 w-5 text-blue-400" viewBox="0 0 20 20" fill="currentColor">
              <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
            </svg>
          </div>
          <div class="ml-3">
            <h3 class="text-sm font-medium text-blue-800">
              Complete Your Selection
            </h3>
            <div class="mt-2 text-sm text-blue-700">
              <p>Please select all the following to view the timetable:</p>
              <ul class="list-disc list-inside mt-1">
                <li>Academic Year</li>
                <li>Semester</li>
                <li>Academic Program</li>
                <li>Academic Level</li>
                <li>Section</li>
              </ul>
            </div>
          </div>
        </div>
      </div>

      <!-- Timetable Grid -->
      <CardBox v-if="allSelectionsComplete">
        <div class="overflow-x-auto">
          <table class="w-full border-collapse">
            <!-- Header with time slots -->
            <thead>
              <tr>
                <th class="border border-gray-300 p-3 bg-gray-50 font-semibold text-sm w-24">Day</th>
                <th
                  v-for="slot in uniqueTimeSlots"
                  :key="slot.start"
                  class="border border-gray-300 p-3 bg-gray-50 font-semibold text-sm text-center min-w-32"
                  :class="{
                    'bg-orange-100': isLunch(slot)
                  }"
                >
                  {{ slot.label }}
                  <div v-if="isLunch(slot)" class="text-xs text-orange-700 font-normal">Lunch</div>
                </th>
              </tr>
            </thead>

            <!-- Body with days and entries -->
            <tbody>
              <tr v-for="day in days" :key="day.key">
                <td class="border border-gray-300 p-3 bg-gray-50 font-semibold text-center">
                  {{ day.label }}
                </td>
                <td
                  v-for="slot in uniqueTimeSlots"
                  :key="`${day.key}-${slot.start}`"
                  class="border border-gray-300 p-2 text-center min-h-20 align-top"
                  :class="{
                    'bg-orange-50': isLunch(slot)
                  }"
                >
                  <div v-if="getEntry(day.key, slot)" class="text-xs">
                    <div class="font-semibold text-blue-800 mb-1">
                      {{ getSubjectDisplay(getEntry(day.key, slot)) }}
                    </div>
                    <div class="text-gray-600 mb-1" :class="{
                      'text-xs': getEntry(day.key, slot).teachers && getEntry(day.key, slot).teachers.length > 1,
                      'text-sm': getEntry(day.key, slot).teachers && getEntry(day.key, slot).teachers.length === 1
                    }">
                      <span v-if="getEntry(day.key, slot).teachers && getEntry(day.key, slot).teachers.length > 0">
                        <span v-if="getEntry(day.key, slot).teachers.length === 1">
                          {{ getEntry(day.key, slot).teachers[0].name }}
                        </span>
                        <span v-else>
                          {{ getEntry(day.key, slot).teachers.length }} teachers
                        </span>
                      </span>
                      <span v-else class="text-gray-400">No teacher</span>
                    </div>
                    <div class="text-gray-500">
                      Room: {{ getClassroomDisplay(getEntry(day.key, slot)) }}
                    </div>
                  </div>
                  <div v-else-if="!isLunch(slot)" class="text-gray-400 text-xs italic">
                    No class
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </CardBox>

      <!-- Legend -->
      <div class="mt-4 p-4 bg-gray-50 rounded-lg">
        <h4 class="font-semibold mb-2">Legend:</h4>
        <div class="flex flex-wrap gap-4 text-sm">
          <div class="flex items-center">
            <div class="w-4 h-4 bg-yellow-100 border border-gray-300 mr-2"></div>
            <span>Break Period</span>
          </div>
          <div class="flex items-center">
            <div class="w-4 h-4 bg-orange-100 border border-gray-300 mr-2"></div>
            <span>Lunch Period</span>
          </div>
          <div class="flex items-center">
            <div class="w-4 h-4 bg-blue-50 border border-gray-300 mr-2"></div>
            <span>Regular Class</span>
          </div>
        </div>
      </div>
    </div>
  </LayoutAuthenticated>
</template>
