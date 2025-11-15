<script setup>
import { Head, useForm, router } from "@inertiajs/vue3";
import { ref, watch, computed } from "vue";
import debounce from "lodash/debounce";
import toast from "@/Stores/toast";
import checkPermissionComposable from "@/Composables/Permission/checkPermission";

import LayoutAuthenticated from "@/Layouts/LayoutAuthenticated.vue";
import PaginationLinks from "@/Components/PaginationLinks.vue";
import Modal from "@/Components/Modal.vue";
import TextInput from "@/Components/TextInput.vue";
import InputLabel from "@/Components/InputLabel.vue";
import InputError from "@/Components/InputError.vue";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import SecondaryButton from "@/Components/SecondaryButton.vue";
import SectionMain from "../../Components/SectionMain.vue";

// === Props ===
const props = defineProps({
  entries: Object,
  filters: Object,
  academicYears: Array,
  programs: Array,
  levels: Array,
  sections: Array,
  classrooms: Array,
  subjects: Array,
  teachers: Array,
  semesters: Array,
  timeSlots: Array,
});

// === Permissions ===
const hasPermission = ref(checkPermissionComposable("timetable_entry_manage"));

// === Filters (Listing Page) ===
const filterYear = ref(props.filters.filterYear || "");
const filterSemester = ref(props.filters.filterSemester || "");
const filterProgram = ref(props.filters.filterProgram || "");
const filterLevel = ref(props.filters.filterLevel || "");
const filterSection = ref(props.filters.filterSection || "");
const filterClassroom = ref(props.filters.filterClassroom || "");
const filterSubject = ref(props.filters.filterSubject || "");
const filterDay = ref(props.filters.filterDay || "");

// --- Refresh only filtered entries when filter changes ---
watch(
  [
    filterYear,
    filterSemester,
    filterProgram,
    filterLevel,
    filterSection,
    filterClassroom,
    filterSubject,
    filterDay,
  ],
  debounce(() => {
    router.get(
      route("timetable_entry:all"),
      {
        filterYear: filterYear.value,
        filterSemester: filterSemester.value,
        filterProgram: filterProgram.value,
        filterLevel: filterLevel.value,
        filterSection: filterSection.value,
        filterClassroom: filterClassroom.value,
        filterSubject: filterSubject.value,
        filterDay: filterDay.value,
      },
      { preserveState: true, replace: true }
    );
  }, 300)
);

// === Form ===
const form = useForm({
  academic_year_id: "",
  semester_id: "",
  program_id: "",
  level_id: "",
  section_id: "",
  classroom_id: "",
  subject_id: "",
  teacher_ids: [],
  time_slot_ids: [],
  day_of_week: "",
  start_time: "",
  end_time: "",
  break_start: "",
  break_end: "",
});

// === Modal State ===
const confirmingEntryCreation = ref(false);
const editingEntry = ref(null);
const showDeleteModal = ref(false);
const deletingEntry = ref(null);

// === Select dependencies ===
const selectedYear = ref("");
const selectedProgram = ref("");
const selectedLevel = ref("");
const selectedSection = ref("");

// === Sync selections to form ===
watch(selectedYear, (val) => (form.academic_year_id = val));
watch(selectedProgram, (val) => (form.program_id = val));
watch(selectedLevel, (val) => (form.level_id = val));
watch(selectedSection, (val) => (form.section_id = val));

// === Reset dependent fields efficiently ===
watch(selectedProgram, (newVal, oldVal) => {
  if (newVal !== oldVal) {
    Object.assign(form, {
      level_id: "",
      section_id: "",
      classroom_id: "",
      subject_id: "",
    });
    selectedLevel.value = "";
    selectedSection.value = "";
  }
});

watch(selectedLevel, (newVal, oldVal) => {
  if (newVal !== oldVal) {
    Object.assign(form, {
      section_id: "",
      classroom_id: "",
      subject_id: "",
    });
    selectedSection.value = "";
  }
});

watch(() => form.subject_id, (newSub, oldSub) => {
  if (newSub !== oldSub) form.teacher_ids = [];
});

// === Utility: Unique Filter Lists ===
const getUniqueItems = (items, key) => {
  const seen = new Set();
  return items.filter((item) => {
    const val = item[key];
    if (seen.has(val)) return false;
    seen.add(val);
    return true;
  });
};

const uniqueAcademicYears = computed(() => getUniqueItems(props.academicYears, "name"));
const uniqueSemesters = computed(() => getUniqueItems(props.semesters, "name"));
const uniquePrograms = computed(() => getUniqueItems(props.programs, "name"));
const uniqueLevels = computed(() => getUniqueItems(props.levels, "name"));
const uniqueSections = computed(() => getUniqueItems(props.sections, "name"));
const uniqueClassrooms = computed(() => getUniqueItems(props.classrooms, "room_no"));
const uniqueDays = [
  { value: "monday", name: "Monday" },
  { value: "tuesday", name: "Tuesday" },
  { value: "wednesday", name: "Wednesday" },
  { value: "thursday", name: "Thursday" },
  { value: "friday", name: "Friday" },
];

// === Dependent Select Options ===
const filteredPrograms = computed(() =>
  selectedYear.value
    ? props.programs.filter((p) => p.academic_year_id == selectedYear.value)
    : props.programs
);

const filteredLevels = computed(() =>
  selectedProgram.value
    ? props.levels.filter((l) => l.program_id == selectedProgram.value)
    : props.levels
);

const filteredSections = computed(() =>
  selectedLevel.value
    ? props.sections.filter((s) => s.level_id == selectedLevel.value)
    : props.sections
);

const filteredClassrooms = computed(() =>
  selectedSection.value
    ? props.classrooms.filter((c) => c.section_id == selectedSection.value)
    : props.classrooms
);

const filteredSemesters = computed(() =>
  selectedYear.value
    ? props.semesters.filter((s) => s.academic_year_id == selectedYear.value)
    : props.semesters
);

// === Subject Search (Debounced) ===
const subjectSearchTerm = ref("");
const filteredSubjects = ref(props.subjects);

const updateSubjects = debounce(() => {
  const term = subjectSearchTerm.value.toLowerCase().trim();
  filteredSubjects.value = props.subjects.filter((s) =>
    s.name.toLowerCase().includes(term)
  );
}, 250);

watch(subjectSearchTerm, updateSubjects, { immediate: true });

// === Filtered Teachers ===
const filteredTeachers = computed(() => {
  const subject = props.subjects.find((s) => s.id == form.subject_id);
  if (!subject?.teachers) return [];
  const ids = subject.teachers.map((t) => t.id);
  return props.teachers.filter((t) => ids.includes(t.id));
});

// === Filtered Time Slots ===
const filteredTimeSlots = computed(() => {
  if (!selectedYear.value) return [];
  const sem = props.semesters.find((s) => s.id == form.semester_id)?.name || "";
  return props.timeSlots.filter((slot) => {
    if (slot.academic_year_id != selectedYear.value) return false;
    if (sem && slot.semester?.toLowerCase() !== sem.toLowerCase()) return false;
    if (form.day_of_week && slot.day_of_week?.toLowerCase() !== form.day_of_week.toLowerCase()) return false;
    return true;
  });
});

/* watch(filteredTimeSlots, (newSlots) => {
  if (form.time_slot_ids && !newSlots.some((s) => s.id == form.time_slot_ids))
    form.time_slot_ids = [];
}); */
watch(filteredTimeSlots, (newSlots) => {
  // Keep only the IDs that still exist in filtered slots
  form.time_slot_ids = form.time_slot_ids.filter(id =>
    newSlots.some(s => s.id === id)
  );
});


// === Helpers ===
const allSelectionsComplete = computed(() =>
  Boolean(
    selectedYear.value &&
    selectedProgram.value &&
    selectedLevel.value &&
    selectedSection.value &&
    form.semester_id &&
    form.subject_id &&
    form.day_of_week &&
    form.time_slot_ids
  )
);

const getTeachersForSubject = (subjectId) =>
  props.subjects.find((s) => s.id == subjectId)?.teachers || [];

// === Modal Handlers ===
const showCreateModal = () => {
  confirmingEntryCreation.value = true;
  editingEntry.value = null;
};

const showEditModal = (entry) => {
  editingEntry.value = entry;
  Object.assign(form, entry);
  selectedYear.value = entry.academic_year_id;
  selectedProgram.value = entry.program_id;
  selectedLevel.value = entry.level_id;
  selectedSection.value = entry.section_id;
  form.teacher_ids = entry.teachers?.map((t) => t.id) || [];
  confirmingEntryCreation.value = true;
};

const closeModal = () => {
  confirmingEntryCreation.value = false;
  editingEntry.value = null;
  form.reset();
};

// === Validation Errors ===
const showErrors = (errors) => {
  Object.values(errors).forEach((msg) => toast.add({ message: msg, type: "error" }));
  if ($page.props.flash?.error)
    toast.add({ message: $page.props.flash.error, type: "error" });
};

// === CRUD ===
const createOrUpdateEntry = () => {
  const method = editingEntry.value ? "put" : "post";
  const routeUrl = editingEntry.value
    ? route("timetable_entry:update", { timetableEntry: editingEntry.value.id })
    : route("timetable_entry:create");

  form[method](routeUrl, {
    preserveScroll: true,
    preserveState: true,
    onSuccess: () => {
      toast.add({
        message: editingEntry.value
          ? "✅ Timetable Entry updated successfully!"
          : "✅ Timetable Entry created successfully!",
      });
      // ✅ Keep form open and retain data
      router.reload({ only: ["entries"] });
    },
    onError: showErrors,
  });
};

// === Delete Entry ===
const showDeleteEntryModal = (entry) => {
  deletingEntry.value = entry;
  showDeleteModal.value = true;
};

const closeDeleteModal = () => {
  deletingEntry.value = null;
  showDeleteModal.value = false;
};

const deleteEntry = () => {
  router.delete(route("timetable_entry:delete", deletingEntry.value.id), {
    preserveScroll: true,
    onSuccess: () => {
      closeDeleteModal();
      toast.add({ message: "🗑️ Timetable entry deleted!" });
    },
  });
};

// === Navigation ===
const getDayName = (day) =>
({
  monday: "Monday",
  tuesday: "Tuesday",
  wednesday: "Wednesday",
  thursday: "Thursday",
  friday: "Friday",
}[day] || day);

const navigateToGridView = () => router.visit(route("timetable_entry:grid"));
const navigateToAcademicPrograms = () => router.visit(route("academic_program:index"));
const navigateToSubjectTeacherManagement = () =>
  router.visit(route("{subject}/assign-teacher"));
</script>

<template>
  <LayoutAuthenticated>

    <Head title="Timetable Entries" />
    <SectionMain v-if="['admin', 'teacher'].includes($page.props.auth.user.user_type) || hasPermission">

      <div class="py-6 max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="flex justify-between items-center mb-6">
          <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Timetable Entries</h1>
          <PrimaryButton @click="showCreateModal"
            class="bg-indigo-600 hover:bg-indigo-700 dark:bg-indigo-500 dark:hover:bg-indigo-600 focus:ring-indigo-500 text-white font-semibold py-2 px-4 rounded-lg shadow-md transition duration-150 ease-in-out">
            + Add Entry
          </PrimaryButton>
        </div>

        <div
          class="mb-6 grid grid-cols-1 md:grid-cols-2 lg:grid-cols-6 gap-4 bg-white dark:bg-gray-800 p-6 rounded-xl shadow-lg border border-gray-100 dark:border-gray-700">
          <div>
            <InputLabel value="Academic Year" class="text-gray-700 dark:text-gray-300 mb-1" />
            <select v-model="filterYear"
              class="w-full border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 rounded-lg shadow-sm focus:ring-indigo-500 focus:border-indigo-500 p-2.5 transition duration-150">
              <option value="">All Years</option>
              <option v-for="y in uniqueAcademicYears" :key="y.id" :value="y.id">{{ y.name }}</option>
            </select>
          </div>

          <div>
            <InputLabel value="Semester" class="text-gray-700 dark:text-gray-300 mb-1" />
            <select v-model="filterSemester"
              class="w-full border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 rounded-lg shadow-sm focus:ring-indigo-500 focus:border-indigo-500 p-2.5 transition duration-150">
              <option value="">All Semesters</option>
              <option v-for="s in uniqueSemesters" :key="s.id" :value="s.id">{{ s.name }}</option>
            </select>
          </div>

          <div>
            <InputLabel value="Academic Program" class="text-gray-700 dark:text-gray-300 mb-1" />
            <select v-model="filterProgram"
              class="w-full border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 rounded-lg shadow-sm focus:ring-indigo-500 focus:border-indigo-500 p-2.5 transition duration-150">
              <option value="">All Programs</option>
              <option v-for="p in uniquePrograms" :key="p.id" :value="p.id">{{ p.name }}</option>
            </select>
          </div>

          <div>
            <InputLabel value="Academic Level" class="text-gray-700 dark:text-gray-300 mb-1" />
            <select v-model="filterLevel"
              class="w-full border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 rounded-lg shadow-sm focus:ring-indigo-500 focus:border-indigo-500 p-2.5 transition duration-150">
              <option value="">All Levels</option>
              <option v-for="l in uniqueLevels" :key="l.id" :value="l.id">{{ l.name }}</option>
            </select>
          </div>

          <div>
            <InputLabel value="Section" class="text-gray-700 dark:text-gray-300 mb-1" />
            <select v-model="filterSection"
              class="w-full border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 rounded-lg shadow-sm focus:ring-indigo-500 focus:border-indigo-500 p-2.5 transition duration-150">
              <option value="">All Sections</option>
              <option v-for="sec in uniqueSections" :key="sec.id" :value="sec.id">{{ sec.name }}</option>
            </select>
          </div>

          <div>
            <InputLabel value="Day" class="text-gray-700 dark:text-gray-300 mb-1" />
            <select v-model="filterDay"
              class="w-full border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 rounded-lg shadow-sm focus:ring-indigo-500 focus:border-indigo-500 p-2.5 transition duration-150">
              <option value="">All Days</option>
              <option v-for="day in uniqueDays" :key="day.value" :value="day.value">{{ day.name }}</option>
            </select>
          </div>
        </div>

        <div
          class="bg-white dark:bg-gray-800 shadow-xl rounded-xl overflow-hidden border border-gray-200 dark:border-gray-700">
          <div class="overflow-x-auto">
            <table class="w-full text-sm text-center">
              <thead
                class="bg-indigo-600 dark:bg-indigo-700 text-white dark:text-gray-100 uppercase text-xs tracking-wider">
                <tr>
                  <th class="px-4 py-3 font-medium">#</th>
                  <th class="px-4 py-3 font-medium">Academic Year</th>
                  <th class="px-4 py-3 font-medium">Semester</th>
                  <th class="px-4 py-3 font-medium">Academic Program</th>
                  <th class="px-4 py-3 font-medium">Level</th>
                  <th class="px-4 py-3 font-medium">Section</th>
                  <th class="px-4 py-3 font-medium">Subject</th>
                  <th class="px-4 py-3 font-medium">Teachers</th>
                  <th class="px-4 py-3 font-medium">Assignment</th>
                  <th class="px-4 py-3 font-medium">Day</th>
                  <th class="px-4 py-3 font-medium">Time</th>
                  <th class="px-4 py-3 font-medium">Actions</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                <tr v-for="(entry, index) in props.entries.data" :key="entry.id"
                  class="bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700/50 transition duration-150 text-gray-700 dark:text-gray-300">
                  <td class="px-4 py-3 border-b dark:border-gray-700">
                    {{ index + 1 + (props.entries.per_page * (props.entries.current_page - 1)) }}
                  </td>
                  <td class="px-4 py-3 border-b dark:border-gray-700">{{ entry.academic_year?.name }}</td>
                  <td class="px-4 py-3 border-b dark:border-gray-700">{{ entry.semester?.name }}</td>
                  <td class="px-4 py-3 border-b dark:border-gray-700">
                    <span
                      class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-blue-100 text-blue-800 dark:bg-blue-900/50 dark:text-blue-300">
                      {{ entry.academic_program?.name || 'N/A' }}
                    </span>
                  </td>
                  <td class="px-4 py-3 border-b dark:border-gray-700">{{ entry.academic_level?.name }}</td>
                  <td class="px-4 py-3 border-b dark:border-gray-700">{{ entry.section?.name }}</td>
                  <td class="px-4 py-3 border-b dark:border-gray-700 font-medium">{{ entry.subject?.name }}</td>
                  <td class="px-4 py-3 border-b dark:border-gray-700">
                    <div v-if="entry.teachers && entry.teachers.length > 0" class="flex flex-wrap justify-center gap-1">
                      <span v-for="teacher in entry.teachers" :key="teacher.id"
                        class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-200 text-gray-800 dark:bg-gray-600 dark:text-gray-100 whitespace-nowrap">
                        {{ teacher.name }}
                      </span>
                    </div>
                    <span v-else class="text-gray-400 dark:text-gray-500 text-xs">No teachers assigned</span>
                  </td>
                  <td class="px-4 py-3 border-b dark:border-gray-700">
                    <span v-if="entry.subject_id && entry.teachers && entry.teachers.length > 0"
                      class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-green-100 text-green-800 dark:bg-green-900/50 dark:text-green-300">
                      <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                        xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                      </svg>
                      {{ entry.teachers.length }} teacher{{ entry.teachers.length > 1 ? 's' : '' }} assigned
                    </span>
                    <span v-else-if="entry.subject_id && (!entry.teachers || entry.teachers.length === 0)"
                      class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-yellow-100 text-yellow-800 dark:bg-yellow-900/50 dark:text-yellow-300">
                      <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                        xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.3 16c-.77 1.333.192 3 1.732 3z">
                        </path>
                      </svg>
                      No teachers assigned
                    </span>
                    <span v-else class="text-gray-400 dark:text-gray-500 text-xs">N/A</span>
                  </td>
                  <td class="px-4 py-3 border-b dark:border-gray-700">{{ getDayName(entry.day_of_week) }}</td>
                  <td class="px-4 py-3 border-b dark:border-gray-700 font-mono">
                    {{ entry.time_slot?.name || 'N/A' }} ({{ entry.start_time }} - {{ entry.end_time }})
                  </td>
                  <td class="px-4 py-3 border-b dark:border-gray-700 space-x-1 whitespace-nowrap">
                    <button @click.prevent="showEditModal(entry)"
                      class="p-2 rounded-full text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-300 transition duration-150 hover:bg-blue-50 dark:hover:bg-gray-700"
                      title="Edit">
                      <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                        xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z">
                        </path>
                      </svg>
                    </button>
                    <button @click.prevent="showDeleteEntryModal(entry)"
                      class="p-2 rounded-full text-red-600 hover:text-red-800 dark:text-red-400 dark:hover:text-red-300 transition duration-150 hover:bg-red-50 dark:hover:bg-gray-700"
                      title="Delete">
                      <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                        xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16">
                        </path>
                      </svg>
                    </button>
                  </td>
                </tr>
                <tr v-if="props.entries.data.length === 0"
                  class="bg-white dark:bg-gray-800 transition duration-150 text-gray-700 dark:text-gray-300">
                  <td colspan="12" class="py-6 text-gray-500 dark:text-gray-400 text-base">No timetable entries found.
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>

        <div class="p-4 border-t border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 rounded-b-xl">
          <PaginationLinks :links="props.entries.links" />
        </div>

        <Modal :show="confirmingEntryCreation" @close="closeModal">
          <div class="p-6 bg-white dark:bg-gray-800 rounded-lg">
            <h2
              class="text-xl font-bold text-gray-900 dark:text-white mb-6 border-b border-gray-200 dark:border-gray-700 pb-3">
              {{ editingEntry ? "Edit Timetable Entry" : "Add Timetable Entry" }}
            </h2>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
              <div>
                <InputLabel value="Academic Year" class="text-gray-700 dark:text-gray-300 mb-1" />
                <select v-model="selectedYear"
                  class="w-full border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 rounded-lg shadow-sm focus:ring-indigo-500 focus:border-indigo-500 p-2.5">
                  <option value="">Select Year</option>
                  <option v-for="y in props.academicYears" :key="y.id" :value="y.id">{{ y.name }}</option>
                </select>
              </div>

              <div>
                <InputLabel value="Academic Program" class="text-gray-700 dark:text-gray-300 mb-1" />
                <select v-model="selectedProgram"
                  class="w-full border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 rounded-lg shadow-sm focus:ring-indigo-500 focus:border-indigo-500 p-2.5"
                  :disabled="filteredPrograms.length === 0 && selectedYear">
                  <option value="">Select Academic Program</option>
                  <option v-for="p in filteredPrograms" :key="p.id" :value="p.id">{{ p.name }}</option>
                </select>
              </div>

              <div>
                <InputLabel value="Level" class="text-gray-700 dark:text-gray-300 mb-1" />
                <select v-model="selectedLevel"
                  class="w-full border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 rounded-lg shadow-sm focus:ring-indigo-500 focus:border-indigo-500 p-2.5">
                  <option value="">Select Level</option>
                  <option v-for="l in filteredLevels" :key="l.id" :value="l.id">{{ l.name }}</option>
                </select>
              </div>

              <div>
                <InputLabel value="Section" class="text-gray-700 dark:text-gray-300 mb-1" />
                <select v-model="selectedSection"
                  class="w-full border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 rounded-lg shadow-sm focus:ring-indigo-500 focus:border-indigo-500 p-2.5">
                  <option value="">Select Section</option>
                  <option v-for="s in filteredSections" :key="s.id" :value="s.id">{{ s.name }}</option>
                </select>
              </div>

              <div>
                <InputLabel value="Classroom" class="text-gray-700 dark:text-gray-300 mb-1" />
                <select v-model="form.classroom_id"
                  class="w-full border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 rounded-lg shadow-sm focus:ring-indigo-500 focus:border-indigo-500 p-2.5">
                  <option value="">Select Classroom</option>
                  <option v-for="c in filteredClassrooms" :key="c.id" :value="c.id">{{ c.room_no }}</option>
                </select>
              </div>

              <div>
                <InputLabel value="Semester" class="text-gray-700 dark:text-gray-300 mb-1" />
                <select v-model="form.semester_id"
                  class="w-full border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 rounded-lg shadow-sm focus:ring-indigo-500 focus:border-indigo-500 p-2.5">
                  <option value="">Select Semester</option>
                  <option v-for="s in filteredSemesters" :key="s.id" :value="s.id">{{ s.name }}</option>
                </select>
              </div>

              <div class="relative">
                <InputLabel value="Subject" class="text-gray-700 dark:text-gray-300 mb-1" />

                <TextInput v-model="subjectSearchTerm" @focus="showSubjectDropdown = true"
                  @blur="setTimeout(() => showSubjectDropdown = false, 150)" placeholder="Type to search subject..."
                  class="w-full !p-2.5" />

                <input type="hidden" :value="form.subject_id" />

                <InputError :message="form.errors.subject_id" class="mt-2" />

                <div v-show="showSubjectDropdown"
                  class="absolute z-30 w-full mt-1 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg shadow-lg max-h-48 overflow-y-auto">

                  <ul v-if="filteredSubjects.length > 0" class="divide-y divide-gray-200 dark:divide-gray-600">
                    <li v-for="sub in filteredSubjects" :key="sub.id" @mousedown.prevent="
                      form.subject_id = sub.id;
                    subjectSearchTerm = sub.name;
                    showSubjectDropdown = false;
                    " :class="{
                      'cursor-pointer p-2 text-sm transition duration-100': true,
                      'hover:bg-indigo-50 dark:hover:bg-indigo-900/50 text-gray-900 dark:text-gray-100': true,
                      'bg-indigo-100 dark:bg-indigo-900 font-semibold': String(form.subject_id) === String(sub.id)
                    }">
                      {{ sub.name }}
                    </li>
                  </ul>
                  <div v-else class="p-2 text-sm text-gray-500 dark:text-gray-400">
                    No subjects found matching your search.
                  </div>
                </div>
              </div>

              <div>
                <InputLabel value="Teachers" class="text-gray-700 dark:text-gray-300 mb-1" />
                <div v-if="filteredTeachers.length === 0 && form.subject_id"
                  class="w-full border border-yellow-300 dark:border-yellow-700 rounded-lg shadow-inner p-3 bg-yellow-50 dark:bg-yellow-900/20">
                  <p class="text-sm text-yellow-800 dark:text-yellow-300">
                    No teachers are assigned to teach this subject yet.
                    <button @click="navigateToSubjectTeacherManagement"
                      class="text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-300 underline ml-1 font-medium">Assign
                      Teachers to Subjects</button>
                  </p>
                </div>
                <div v-else-if="filteredTeachers.length > 0"
                  class="w-full border border-gray-300 dark:border-gray-600 rounded-lg shadow-sm p-3 max-h-48 overflow-y-auto bg-gray-50 dark:bg-gray-700/50">
                  <div v-for="teacher in filteredTeachers" :key="teacher.id" class="flex items-center mb-2">
                    <input type="checkbox" :id="`teacher-${teacher.id}`" :value="teacher.id" v-model="form.teacher_ids"
                      class="rounded border-gray-300 dark:border-gray-500 text-indigo-600 shadow-sm focus:ring-indigo-500 dark:bg-gray-600 dark:checked:bg-indigo-500" />
                    <label :for="`teacher-${teacher.id}`" class="ml-2 text-sm text-gray-700 dark:text-gray-300">{{
                      teacher.name }}</label>
                  </div>
                  <p
                    class="text-xs text-gray-500 dark:text-gray-400 mt-2 border-t border-gray-200 dark:border-gray-600 pt-2">
                    {{ form.teacher_ids.length }} of {{ filteredTeachers.length }}
                    teachers selected</p>
                </div>
                <div v-else
                  class="w-full border border-gray-300 dark:border-gray-600 rounded-lg shadow-inner p-3 bg-gray-50 dark:bg-gray-700/50">
                  <p class="text-sm text-gray-500 dark:text-gray-400">Select a subject first to see available teachers
                  </p>
                </div>
              </div>

              <div>
                <InputLabel value="Day of Week" class="text-gray-700 dark:text-gray-300 mb-1" />
                <select v-model="form.day_of_week"
                  class="w-full border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 rounded-lg shadow-sm focus:ring-indigo-500 focus:border-indigo-500 p-2.5">
                  <option value="">Select Day</option>
                  <option value="monday">Monday</option>
                  <option value="tuesday">Tuesday</option>
                  <option value="wednesday">Wednesday</option>
                  <option value="thursday">Thursday</option>
                  <option value="friday">Friday</option>
                </select>
              </div>

              <div>
                <InputLabel value="Time Slot" class="text-gray-700 dark:text-gray-300 mb-1" />

                <div class="space-y-2">
                  <div v-for="slot in filteredTimeSlots" :key="slot.id" class="flex items-center gap-2">
                    <input type="checkbox" :value="slot.id" v-model="form.time_slot_ids"
                      class="h-4 w-4 text-indigo-600 border-gray-300 rounded" />

                    <label class="text-gray-900 dark:text-gray-100">
                      {{ slot.name || slot.id }} ({{ slot.start_time }} - {{ slot.end_time }})
                    </label>
                  </div>
                </div>
              </div>



            </div>

            <div v-if="!allSelectionsComplete"
              class="mt-6 p-4 bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-700 rounded-lg">
              <div class="flex items-center">
                <div class="flex-shrink-0">
                  <svg class="h-5 w-5 text-blue-500 dark:text-blue-400" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd"
                      d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z"
                      clip-rule="evenodd" />
                  </svg>
                </div>
                <div class="ml-3">
                  <p class="text-sm font-medium text-blue-800 dark:text-blue-300">Please complete all required
                    selections
                    to
                    create the timetable entry.</p>
                </div>
              </div>
            </div>

            <div class="mt-8 flex justify-end space-x-3">
              <SecondaryButton @click.prevent="closeModal"
                class="border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-600/50 transition duration-150 py-2 px-4 rounded-lg">
                Cancel
              </SecondaryButton>
              <PrimaryButton :disabled="form.processing || !allSelectionsComplete" @click.prevent="createOrUpdateEntry"
                class="bg-indigo-600 hover:bg-indigo-700 dark:bg-indigo-500 dark:hover:bg-indigo-600 focus:ring-indigo-500 text-white font-semibold py-2 px-4 rounded-lg shadow-md transition duration-150 ease-in-out">
                {{ editingEntry ? "Update" : "Create" }}
              </PrimaryButton>
            </div>
          </div>
        </Modal>

        <Modal :show="showDeleteModal" @close="closeDeleteModal">
          <div class="p-6 bg-white dark:bg-gray-800 rounded-lg">
            <h2
              class="text-xl font-bold text-red-600 dark:text-red-500 mb-2 border-b border-gray-200 dark:border-gray-700 pb-2">
              Delete Timetable Entry
            </h2>
            <p class="text-sm text-gray-600 dark:text-gray-400 mt-4">Are you sure you want to delete this timetable
              entry?
              This
              action cannot be undone.</p>
            <div class="mt-6 flex justify-end space-x-3">
              <SecondaryButton @click.prevent="closeDeleteModal"
                class="border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-600/50 transition duration-150 py-2 px-4 rounded-lg">
                Cancel
              </SecondaryButton>
              <PrimaryButton
                class="bg-red-600 hover:bg-red-700 dark:bg-red-500 dark:hover:bg-red-600 text-white font-semibold py-2 px-4 rounded-lg shadow-md transition duration-150 ease-in-out"
                :disabled="form.processing" @click.prevent="deleteEntry">
                Delete
              </PrimaryButton>
            </div>
          </div>
        </Modal>
      </div>
    </SectionMain>
    <SectionMain v-else>
      <h2>No Permissions</h2>
    </SectionMain>
  </LayoutAuthenticated>
</template>
