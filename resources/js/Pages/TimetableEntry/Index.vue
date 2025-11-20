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

// === Constants ===
const DAYS_OF_WEEK = [
  { value: "monday", name: "Monday" },
  { value: "tuesday", name: "Tuesday" },
  { value: "wednesday", name: "Wednesday" },
  { value: "thursday", name: "Thursday" },
  { value: "friday", name: "Friday" },
];

const DAY_NAMES_MAP = Object.fromEntries(DAYS_OF_WEEK.map(d => [d.value, d.name]));

// === Filters (Listing Page) ===
const filters = ref({
  filterYear: props.filters.filterYear || "",
  filterSemester: props.filters.filterSemester || "",
  filterProgram: props.filters.filterProgram || "",
  filterLevel: props.filters.filterLevel || "",
  filterSection: props.filters.filterSection || "",
  filterClassroom: props.filters.filterClassroom || "",
  filterSubject: props.filters.filterSubject || "",
  filterDay: props.filters.filterDay || "",
});

// Destructure for template usage
const { filterYear, filterSemester, filterProgram, filterLevel, filterSection, filterClassroom, filterSubject, filterDay } = filters.value;

// Watch filters with debounce
watch(
  filters,
  debounce(() => {
    router.get(
      route("timetable_entry:all"),
      filters.value,
      { preserveState: true, replace: true }
    );
  }, 300),
  { deep: true }
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
});

// === Modal State ===
const modalState = ref({
  showCreate: false,
  showDelete: false,
  editingEntry: null,
  deletingEntry: null,
  showSubjectDropdown: false,
});

// === Form Selection State ===
const selections = ref({
  year: "",
  program: "",
  level: "",
  section: "",
  semester: "",
});

// Sync selections to form
watch(() => selections.value.year, (val) => form.academic_year_id = val);
watch(() => selections.value.program, (val) => form.program_id = val);
watch(() => selections.value.level, (val) => form.level_id = val);
watch(() => selections.value.section, (val) => form.section_id = val);
watch(() => selections.value.semester, (val) => form.semester_id = val);

// === Cascading Resets ===
watch(() => selections.value.program, (newVal, oldVal) => {
  if (newVal !== oldVal) {
    Object.assign(form, {
      level_id: "",
      section_id: "",
      classroom_id: "",
      subject_id: "",
    });
    Object.assign(selections.value, {
      level: "",
      section: "",
    });
  }
});

watch(() => selections.value.level, (newVal, oldVal) => {
  if (newVal !== oldVal) {
    Object.assign(form, {
      section_id: "",
      classroom_id: "",
      subject_id: "",
      semester_id: "",
    });
    selections.value.section = "";
  }
});

watch(() => form.subject_id, (newVal, oldVal) => {
  if (newVal !== oldVal) form.teacher_ids = [];
});

// === Utility Functions ===
const getUniqueItems = (items, key) => {
  const seen = new Set();
  return items.filter((item) => {
    const val = item[key];
    if (seen.has(val)) return false;
    seen.add(val);
    return true;
  });
};
watch(() => form.subject_id, (newSubject) => {
  if (!newSubject) {
    form.teacher_ids = [];
    return;
  }

  // Load teachers filtered by subject
  const teachers = filteredTeachers.value;

  // Auto-select all assigned teachers
  form.teacher_ids = teachers.map(t => t.id);
});

const getDayName = (day) => DAY_NAMES_MAP[day] || day;

// === Computed - Unique Lists ===
const uniqueLists = computed(() => ({
  academicYears: getUniqueItems(props.academicYears, "name"),
  semesters: getUniqueItems(props.semesters, "name"),
  programs: getUniqueItems(props.programs, "name"),
  levels: getUniqueItems(props.levels, "name"),
  sections: getUniqueItems(props.sections, "name"),
  classrooms: getUniqueItems(props.classrooms, "room_no"),
}));

// === Computed - Filtered Options ===
const filteredPrograms = computed(() =>
  selections.value.year
    ? props.programs.filter((p) => p.academic_year_id == selections.value.year)
    : props.programs
);

const filteredLevels = computed(() =>
  selections.value.program
    ? props.levels.filter((l) => l.program_id == selections.value.program)
    : props.levels
);

const currentLevelObject = computed(() =>
  props.levels.find((l) => l.id == selections.value.level)
);

const uniqueFilteredLevels = computed(() =>
  getUniqueItems(filteredLevels.value, "name")
);

const filteredSections = computed(() => {
  const levelName = currentLevelObject.value?.name;
  if (!selections.value.program || !levelName) return props.sections;

  const matchingLevelIds = props.levels
    .filter(l => l.name === levelName && l.program_id == selections.value.program)
    .map(l => l.id);

  return props.sections.filter(s => matchingLevelIds.includes(s.level_id));
});

const filteredClassrooms = computed(() =>
  selections.value.section
    ? props.classrooms.filter((c) => c.section_id == selections.value.section)
    : props.classrooms
);

const filteredSemesters = computed(() =>
  selections.value.year
    ? props.semesters.filter((s) => s.academic_year_id == selections.value.year)
    : props.semesters
);

const availableSemesters = computed(() => {
  const currentLevelId = selections.value.level;
  if (!currentLevelId) return [];

  const levelRecord = props.levels.find(l => l.id == currentLevelId);

  if (levelRecord?.semester_id) {
    const semester = props.semesters.find(s => s.id == levelRecord.semester_id);

    if (semester && semester.academic_year_id == selections.value.year) {
      form.semester_id = semester.id;
      return [semester];
    }
  }

  form.semester_id = "";
  return [];
});

// === Subject Search ===
const subjectSearchTerm = ref("");
const filteredSubjects = ref(props.subjects);

const updateSubjects = debounce(() => {
  const term = subjectSearchTerm.value.toLowerCase().trim();
  filteredSubjects.value = term
    ? props.subjects.filter((s) => s.name.toLowerCase().includes(term))
    : props.subjects;
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
  if (!selections.value.year) return [];
  const sem = props.semesters.find((s) => s.id == form.semester_id)?.name || "";

  return props.timeSlots.filter((slot) => {
    if (slot.academic_year_id != selections.value.year) return false;
    if (sem && slot.semester?.toLowerCase() !== sem.toLowerCase()) return false;
    if (form.day_of_week && slot.day_of_week?.toLowerCase() !== form.day_of_week.toLowerCase()) return false;
    return true;
  });
});

watch(filteredTimeSlots, (newSlots) => {
  form.time_slot_ids = form.time_slot_ids.filter(id =>
    newSlots.some(s => s.id === id)
  );
});

// === Validation ===
const allSelectionsComplete = computed(() =>
  Boolean(
    selections.value.year &&
    selections.value.program &&
    selections.value.level &&
    selections.value.section &&
    form.semester_id &&
    form.subject_id &&
    form.day_of_week &&
    form.time_slot_ids.length > 0
  )
);

// === Modal Handlers ===
const showCreateModal = () => {
  modalState.value.showCreate = true;
  modalState.value.editingEntry = null;
};

const showEditModal = (entry) => {
  modalState.value.editingEntry = entry;
  Object.assign(form, entry);
  Object.assign(selections.value, {
    year: entry.academic_year_id,
    program: entry.program_id,
    level: entry.level_id,
    section: entry.section_id,
  });
  form.teacher_ids = entry.teachers?.map((t) => t.id) || [];
  modalState.value.showCreate = true;
};

const closeModal = () => {
  modalState.value.showCreate = false;
  modalState.value.editingEntry = null;
  form.reset();
  Object.assign(selections.value, {
    year: "",
    program: "",
    level: "",
    section: "",
    semester: "",
  });
};

const showDeleteEntryModal = (entry) => {
  modalState.value.deletingEntry = entry;
  modalState.value.showDelete = true;
};

const closeDeleteModal = () => {
  modalState.value.deletingEntry = null;
  modalState.value.showDelete = false;
};

// === Error Handling ===
const showErrors = (errors) => {
  Object.values(errors).forEach((msg) =>
    toast.add({ message: msg, type: "error" })
  );
};

// === CRUD Operations ===
const createOrUpdateEntry = () => {
  const isEdit = !!modalState.value.editingEntry;
  const method = isEdit ? "put" : "post";
  const routeUrl = isEdit
    ? route("timetable_entry:update", { timetableEntry: modalState.value.editingEntry.id })
    : route("timetable_entry:create");

  form[method](routeUrl, {
    preserveScroll: true,
    preserveState: true,
    onSuccess: () => {
      toast.add({
        message: isEdit
          ? "✅ Timetable Entry updated successfully!"
          : "✅ Timetable Entry created successfully!",
      });
      router.reload({ only: ["entries"] });
    },
    onError: showErrors,
  });
};

const deleteEntry = () => {
  router.delete(route("timetable_entry:delete", modalState.value.deletingEntry.id), {
    preserveScroll: true,
    onSuccess: () => {
      closeDeleteModal();
      toast.add({ message: "🗑️ Timetable entry deleted!" });
    },
  });
};

// === Navigation ===
const navigateToGridView = () => router.visit(route("timetable_entry:grid"));
const navigateToSubjectTeacherManagement = () =>
  router.visit(route("{subject}/assign-teacher"));

// === Table Index Calculation ===
const getRowIndex = (index) =>
  index + 1 + (props.entries.per_page * (props.entries.current_page - 1));
</script>

<template>
  <LayoutAuthenticated>

    <Head title="Timetable Entries" />
    <SectionMain v-if="['admin', 'teacher'].includes($page.props.auth.user.user_type) || hasPermission">
      <div class="py-6 max-w-7xl mx-auto sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="flex justify-between items-center mb-6">
          <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Timetable Entries</h1>
          <PrimaryButton @click="showCreateModal"
            class="bg-indigo-600 hover:bg-indigo-700 dark:bg-indigo-500 dark:hover:bg-indigo-600 focus:ring-indigo-500 text-white font-semibold py-2 px-4 rounded-lg shadow-md transition">
            + Add Entry
          </PrimaryButton>
        </div>

        <!-- Filters -->
        <div
          class="mb-6 grid grid-cols-1 md:grid-cols-2 lg:grid-cols-6 gap-4 bg-white dark:bg-gray-800 p-6 rounded-xl shadow-lg border border-gray-100 dark:border-gray-700">
          <div v-for="filter in [
            { model: 'filterYear', label: 'Academic Year', options: uniqueLists.academicYears, key: 'name' },
            { model: 'filterSemester', label: 'Semester', options: uniqueLists.semesters, key: 'name' },
            { model: 'filterProgram', label: 'Academic Program', options: uniqueLists.programs, key: 'name' },
            { model: 'filterLevel', label: 'Academic Level', options: uniqueLists.levels, key: 'name' },
            { model: 'filterSection', label: 'Section', options: uniqueLists.sections, key: 'name' },
            { model: 'filterDay', label: 'Day', options: DAYS_OF_WEEK, key: 'name' }
          ]" :key="filter.model">
            <InputLabel :value="filter.label" class="text-gray-700 dark:text-gray-300 mb-1" />
            <select v-model="filters[filter.model]"
              class="w-full border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 rounded-lg shadow-sm focus:ring-indigo-500 focus:border-indigo-500 p-2.5 transition">
              <option value="">All {{ filter.label }}s</option>
              <option v-for="item in filter.options" :key="item.id || item.value" :value="item.name || item.value">
                {{ item[filter.key] || item.name }}
              </option>
            </select>
          </div>
        </div>

        <!-- Table -->
        <div
          class="bg-white dark:bg-gray-800 shadow-xl rounded-xl overflow-hidden border border-gray-200 dark:border-gray-700">
          <div class="overflow-x-auto">
            <table class="w-full text-sm text-center">
              <thead class="bg-indigo-600 dark:bg-indigo-700 text-white uppercase text-xs tracking-wider">
                <tr>
                  <th
                    v-for="header in ['#', 'Academic Year', 'Semester', 'Academic Program', 'Level', 'Section', 'Subject', 'Teachers', 'Assignment', 'Day', 'Time', 'Actions']"
                    :key="header" class="px-4 py-3 font-medium">{{ header }}</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                <tr v-for="(entry, index) in props.entries.data" :key="entry.id"
                  class="bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700/50 transition text-gray-700 dark:text-gray-300">
                  <td class="px-4 py-3 border-b dark:border-gray-700">{{ getRowIndex(index) }}</td>
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
                    <div v-if="entry.teachers?.length" class="flex flex-wrap justify-center gap-1">
                      <span v-for="teacher in entry.teachers" :key="teacher.id"
                        class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-200 text-gray-800 dark:bg-gray-600 dark:text-gray-100 whitespace-nowrap">
                        {{ teacher.name }}
                      </span>
                    </div>
                    <span v-else class="text-gray-400 dark:text-gray-500 text-xs">No teachers assigned</span>
                  </td>
                  <td class="px-4 py-3 border-b dark:border-gray-700">
                    <span v-if="entry.teachers?.length"
                      class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-green-100 text-green-800 dark:bg-green-900/50 dark:text-green-300">
                      ✓ {{ entry.teachers.length }} teacher{{ entry.teachers.length > 1 ? 's' : '' }}
                    </span>
                    <span v-else-if="entry.subject_id"
                      class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-yellow-100 text-yellow-800 dark:bg-yellow-900/50 dark:text-yellow-300">
                      ⚠ No teachers
                    </span>
                    <span v-else class="text-gray-400 dark:text-gray-500 text-xs">N/A</span>
                  </td>
                  <td class="px-4 py-3 border-b dark:border-gray-700">{{ getDayName(entry.day_of_week) }}</td>
                  <td class="px-4 py-3 border-b dark:border-gray-700 font-mono text-xs">
                    {{ entry.time_slot?.name || 'N/A' }}<br>({{ entry.start_time }} - {{ entry.end_time }})
                  </td>
                  <td class="px-4 py-3 border-b dark:border-gray-700 space-x-1 whitespace-nowrap">
                    <button @click="showEditModal(entry)"
                      class="p-2 rounded-full text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-300 transition hover:bg-blue-50 dark:hover:bg-gray-700"
                      title="Edit">
                      <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
                      </svg>
                    </button>
                    <button @click="showDeleteEntryModal(entry)"
                      class="p-2 rounded-full text-red-600 hover:text-red-800 dark:text-red-400 dark:hover:text-red-300 transition hover:bg-red-50 dark:hover:bg-gray-700"
                      title="Delete">
                      <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                      </svg>
                    </button>
                  </td>
                </tr>
                <tr v-if="!props.entries.data.length">
                  <td colspan="12" class="py-6 text-gray-500 dark:text-gray-400">No timetable entries found.</td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>

        <!-- Pagination -->
        <div class="p-4 border-t border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 rounded-b-xl">
          <PaginationLinks :links="props.entries.links" />
        </div>

        <!-- Create/Edit Modal -->
        <Modal :show="modalState.showCreate" @close="closeModal">
          <div class="p-6 bg-white dark:bg-gray-800 rounded-lg">
            <h2
              class="text-xl font-bold text-gray-900 dark:text-white mb-6 border-b border-gray-200 dark:border-gray-700 pb-3">
              {{ modalState.editingEntry ? "Edit Timetable Entry" : "Add Timetable Entry" }}
            </h2>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
              <!-- Academic Year -->
              <div>
                <InputLabel value="Academic Year" class="text-gray-700 dark:text-gray-300 mb-1" />
                <select v-model="selections.year"
                  class="w-full border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 rounded-lg shadow-sm focus:ring-indigo-500 focus:border-indigo-500 p-2.5">
                  <option value="">Select Year</option>
                  <option v-for="y in props.academicYears" :key="y.id" :value="y.id">{{ y.name }}</option>
                </select>
              </div>

              <!-- Academic Program -->
              <div>
                <InputLabel value="Academic Program" class="text-gray-700 dark:text-gray-300 mb-1" />
                <select v-model="selections.program"
                  class="w-full border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 rounded-lg shadow-sm focus:ring-indigo-500 focus:border-indigo-500 p-2.5"
                  :disabled="!filteredPrograms.length && selections.year">
                  <option value="">Select Academic Program</option>
                  <option v-for="p in filteredPrograms" :key="p.id" :value="p.id">{{ p.name }}</option>
                </select>
              </div>

              <!-- Level -->
              <div>
                <InputLabel value="Level" class="text-gray-700 dark:text-gray-300 mb-1" />
                <select v-model="selections.level"
                  class="w-full border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 rounded-lg shadow-sm focus:ring-indigo-500 focus:border-indigo-500 p-2.5">
                  <option value="">Select Level</option>
                  <option v-for="level in uniqueFilteredLevels" :key="level.id" :value="level.id">{{ level.name }}
                  </option>
                </select>
              </div>

              <!-- Section -->
              <div>
                <InputLabel value="Section" class="text-gray-700 dark:text-gray-300 mb-1" />
                <select v-model="selections.section"
                  class="w-full border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 rounded-lg shadow-sm focus:ring-indigo-500 focus:border-indigo-500 p-2.5">
                  <option value="">Select Section</option>
                  <option v-for="s in filteredSections" :key="s.id" :value="s.id">{{ s.name }}</option>
                </select>
              </div>

              <!-- Classroom -->
              <div>
                <InputLabel value="Classroom" class="text-gray-700 dark:text-gray-300 mb-1" />
                <select v-model="form.classroom_id"
                  class="w-full border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 rounded-lg shadow-sm focus:ring-indigo-500 focus:border-indigo-500 p-2.5">
                  <option value="">Select Classroom</option>
                  <option v-for="c in filteredClassrooms" :key="c.id" :value="c.id">{{ c.room_no }}</option>
                </select>
              </div>

              <!-- Semester -->
              <div>
                <InputLabel value="Semester" class="text-gray-700 dark:text-gray-300 mb-1" />
                <select v-model="form.semester_id"
                  class="w-full border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 rounded-lg shadow-sm focus:ring-indigo-500 focus:border-indigo-500 p-2.5">
                  <option value="">Select Semester</option>
                  <option v-for="s in filteredSemesters" :key="s.id" :value="s.id">{{ s.name }}</option>
                </select>
              </div>

              <!-- Subject Search -->
              <div class="relative">
                <InputLabel value="Subject" class="text-gray-700 dark:text-gray-300 mb-1" />
                <TextInput v-model="subjectSearchTerm" @focus="modalState.showSubjectDropdown = true"
                  @blur="setTimeout(() => modalState.showSubjectDropdown = false, 150)"
                  placeholder="Type to search subject..." class="w-full !p-2.5" />
                <InputError :message="form.errors.subject_id" class="mt-2" />
                <div v-show="modalState.showSubjectDropdown"
                  class="absolute z-30 w-full mt-1 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg shadow-lg max-h-48 overflow-y-auto">
                  <ul v-if="filteredSubjects.length" class="divide-y divide-gray-200 dark:divide-gray-600">
                    <li v-for="sub in filteredSubjects" :key="sub.id"
                      @mousedown.prevent="form.subject_id = sub.id; subjectSearchTerm = sub.name; modalState.showSubjectDropdown = false;"
                      :class="[
                        'cursor-pointer p-2 text-sm transition',
                        'hover:bg-indigo-50 dark:hover:bg-indigo-900/50',
                        String(form.subject_id) === String(sub.id) ? 'bg-indigo-100 dark:bg-indigo-900 font-semibold' : ''
                      ]">
                      {{ sub.name }}
                    </li>
                  </ul>
                  <div v-else class="p-2 text-sm text-gray-500 dark:text-gray-400">No subjects found</div>
                </div>
              </div>

              <!-- Teachers -->
              <div>
                <InputLabel value="Teachers" class="text-gray-700 dark:text-gray-300 mb-1" />
                <div v-if="!filteredTeachers.length && form.subject_id"
                  class="w-full border border-yellow-300 dark:border-yellow-700 rounded-lg p-3 bg-yellow-50 dark:bg-yellow-900/20">
                  <p class="text-sm text-yellow-800 dark:text-yellow-300">
                    No teachers assigned to this subject.
                    <button @click="navigateToSubjectTeacherManagement"
                      class="text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-300 underline ml-1 font-medium">
                      Assign Teachers
                    </button>
                  </p>
                </div>
                <div v-else-if="filteredTeachers.length"
                  class="w-full border border-gray-300 dark:border-gray-600 rounded-lg p-3 max-h-48 overflow-y-auto bg-gray-50 dark:bg-gray-700/50">

                  <div v-for="teacher in filteredTeachers" :key="teacher.id" class="flex items-center mb-2">
                    <span class="ml-2 text-sm text-gray-700 dark:text-gray-300">
                      {{ teacher.name }}
                    </span>
                  </div>

                  <p
                    class="text-xs text-gray-500 dark:text-gray-400 mt-2 border-t border-gray-200 dark:border-gray-600 pt-2">
                    {{ filteredTeachers.length }} teachers found
                  </p>
                </div>

                <div v-else
                  class="w-full border border-gray-300 dark:border-gray-600 rounded-lg p-3 bg-gray-50 dark:bg-gray-700/50">
                  <p class="text-sm text-gray-500 dark:text-gray-400">Select a subject first</p>
                </div>
              </div>

              <!-- Day of Week -->
              <div>
                <InputLabel value="Day of Week" class="text-gray-700 dark:text-gray-300 mb-1" />
                <select v-model="form.day_of_week"
                  class="w-full border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 rounded-lg shadow-sm focus:ring-indigo-500 focus:border-indigo-500 p-2.5">
                  <option value="">Select Day</option>
                  <option v-for="day in DAYS_OF_WEEK" :key="day.value" :value="day.value">{{ day.name }}</option>
                </select>
              </div>

              <!-- Time Slots -->
              <div>
                <InputLabel value="Time Slots" class="text-gray-700 dark:text-gray-300 mb-1" />
                <div v-if="filteredTimeSlots.length"
                  class="w-full border border-gray-300 dark:border-gray-600 rounded-lg p-3 max-h-48 overflow-y-auto bg-gray-50 dark:bg-gray-700/50">
                  <div v-for="slot in filteredTimeSlots" :key="slot.id" class="flex items-center gap-2 mb-2">
                    <input type="checkbox" :value="slot.id" v-model="form.time_slot_ids" :id="`slot-${slot.id}`"
                      class="h-4 w-4 text-indigo-600 border-gray-300 dark:border-gray-500 rounded focus:ring-indigo-500" />
                    <label :for="`slot-${slot.id}`" class="text-sm text-gray-900 dark:text-gray-100">
                      {{ slot.name || slot.id }} ({{ slot.start_time }} - {{ slot.end_time }})
                    </label>
                  </div>
                </div>
                <div v-else
                  class="w-full border border-gray-300 dark:border-gray-600 rounded-lg p-3 bg-gray-50 dark:bg-gray-700/50">
                  <p class="text-sm text-gray-500 dark:text-gray-400">
                    {{ selections.year ? 'Select day to see time slots' : 'Select academic year first' }}
                  </p>
                </div>
              </div>
            </div>

            <!-- Validation Warning -->
            <div v-if="!allSelectionsComplete"
              class="mt-6 p-4 bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-700 rounded-lg">
              <div class="flex items-center">
                <svg class="h-5 w-5 text-blue-500 dark:text-blue-400 flex-shrink-0" viewBox="0 0 20 20"
                  fill="currentColor">
                  <path fill-rule="evenodd"
                    d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z"
                    clip-rule="evenodd" />
                </svg>
                <p class="ml-3 text-sm font-medium text-blue-800 dark:text-blue-300">
                  Please complete all required selections to create the timetable entry.
                </p>
              </div>
            </div>

            <!-- Action Buttons -->
            <div class="mt-8 flex justify-end space-x-3">
              <SecondaryButton @click="closeModal"
                class="border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-600/50 transition py-2 px-4 rounded-lg">
                Cancel
              </SecondaryButton>
              <PrimaryButton :disabled="form.processing || !allSelectionsComplete" @click="createOrUpdateEntry"
                class="bg-indigo-600 hover:bg-indigo-700 dark:bg-indigo-500 dark:hover:bg-indigo-600 disabled:opacity-50 disabled:cursor-not-allowed focus:ring-indigo-500 text-white font-semibold py-2 px-4 rounded-lg shadow-md transition">
                {{ modalState.editingEntry ? "Update" : "Create" }}
              </PrimaryButton>
            </div>
          </div>
        </Modal>

        <!-- Delete Confirmation Modal -->
        <Modal :show="modalState.showDelete" @close="closeDeleteModal">
          <div class="p-6 bg-white dark:bg-gray-800 rounded-lg">
            <h2
              class="text-xl font-bold text-red-600 dark:text-red-500 mb-2 border-b border-gray-200 dark:border-gray-700 pb-2">
              Delete Timetable Entry
            </h2>
            <p class="text-sm text-gray-600 dark:text-gray-400 mt-4">
              Are you sure you want to delete this timetable entry? This action cannot be undone.
            </p>
            <div class="mt-6 flex justify-end space-x-3">
              <SecondaryButton @click="closeDeleteModal"
                class="border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-600/50 transition py-2 px-4 rounded-lg">
                Cancel
              </SecondaryButton>
              <PrimaryButton :disabled="form.processing" @click="deleteEntry"
                class="bg-red-600 hover:bg-red-700 dark:bg-red-500 dark:hover:bg-red-600 disabled:opacity-50 disabled:cursor-not-allowed text-white font-semibold py-2 px-4 rounded-lg shadow-md transition">
                Delete
              </PrimaryButton>
            </div>
          </div>
        </Modal>
      </div>
    </SectionMain>
    <SectionMain v-else>
      <div class="text-center py-12">
        <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-2">Access Denied</h2>
        <p class="text-gray-600 dark:text-gray-400">You don't have permission to view timetable entries.</p>
      </div>
    </SectionMain>
  </LayoutAuthenticated>
</template>