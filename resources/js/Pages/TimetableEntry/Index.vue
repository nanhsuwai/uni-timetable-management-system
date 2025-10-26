<script setup>
import { Head, useForm, router } from "@inertiajs/vue3";
import { ref, watch, computed } from "vue";
import toast from "@/Stores/toast";

// Components
import LayoutAuthenticated from "@/Layouts/LayoutAuthenticated.vue";
import PaginationLinks from "@/Components/PaginationLinks.vue";
import Modal from "@/Components/Modal.vue";
import TextInput from "@/Components/TextInput.vue";
import InputLabel from "@/Components/InputLabel.vue";
import InputError from "@/Components/InputError.vue";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import SecondaryButton from "@/Components/SecondaryButton.vue";

// Props from Laravel
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

// Filters (Initialization)
const filterYear = ref(props.filters.filterYear || "");
const filterSemester = ref(props.filters.filterSemester || "");
const filterProgram = ref(props.filters.filterProgram || "");
const filterLevel = ref(props.filters.filterLevel || "");
const filterSection = ref(props.filters.filterSection || "");
const filterClassroom = ref(props.filters.filterClassroom || "");
const filterDay = ref(props.filters.filterDay || "");

// Update when filters change (list page)
watch(
    [
        filterYear,
        filterSemester,
        filterProgram,
        filterLevel,
        filterSection,
        filterClassroom,
        filterDay,
    ],
    () => {
        router.get(
            route("timetable_entry:all"),
            {
                filterYear: filterYear.value,
                filterSemester: filterSemester.value,
                filterProgram: filterProgram.value,
                filterLevel: filterLevel.value,
                filterSection: filterSection.value,
                filterClassroom: filterClassroom.value,
                filterDay: filterDay.value,
            },
            { preserveState: true, replace: true }
        );
    }
);

// Form
const form = useForm({
    academic_year_id: "",
    semester_id: "",
    program_id: "",
    level_id: "",
    section_id: "",
    classroom_id: "",
    subject_id: "",
    teacher_ids: [],
    time_slot_id: "",
    day_of_week: "",
    start_time: "",
    end_time: "",
    break_start: "",
    break_end: "",
});

// Modal & CRUD
const confirmingEntryCreation = ref(false);
const editingEntry = ref(null);
const showDeleteModal = ref(false);
const deletingEntry = ref(null);

// Dependent selects state (used for form creation/editing)
const selectedYear = ref("");
const selectedProgram = ref("");
const selectedLevel = ref("");
const selectedSection = ref("");

// Sync selected values to form
watch(selectedYear, (newValue) => (form.academic_year_id = newValue));
watch(selectedProgram, (newValue) => (form.program_id = newValue));
watch(selectedLevel, (newValue) => (form.level_id = newValue));
watch(selectedSection, (newValue) => (form.section_id = newValue));

// Clear selected teachers whenever subject changes
watch(
    () => form.subject_id,
    (newSubject, oldSubject) => {
        if (newSubject !== oldSubject) {
            form.teacher_ids = [];
        }
    }
);

// --- Unique Filter Options (Fixes Duplicates) ---

const getUniqueItems = (items, key = 'name') => {
    const uniqueItemsList = [];
    const seenValues = new Set();
    items.forEach(item => {
        const value = item[key] ? String(item[key]) : ''; // Ensure value is a string for Set
        if (!seenValues.has(value)) {
            seenValues.add(value);
            // Push the original object, assuming it has 'id' and 'name'
            uniqueItemsList.push(item);
        }
    });
    return uniqueItemsList;
};

const uniqueAcademicYears = computed(() => getUniqueItems(props.academicYears, 'name'));
const uniqueSemesters = computed(() => getUniqueItems(props.semesters, 'name'));
const uniquePrograms = computed(() => getUniqueItems(props.programs, 'name'));
const uniqueLevels = computed(() => getUniqueItems(props.levels, 'name'));
const uniqueSections = computed(() => getUniqueItems(props.sections, 'name'));
// For classrooms, assuming 'room_no' is the display name
const uniqueClassrooms = computed(() => getUniqueItems(props.classrooms, 'room_no'));

const uniqueDays = computed(() => ([
    { value: "monday", name: "Monday" },
    { value: "tuesday", name: "Tuesday" },
    { value: "wednesday", name: "Wednesday" },
    { value: "thursday", name: "Thursday" },
    { value: "friday", name: "Friday" },
]));

// --- Dependent Form Selects (Restored and Corrected) ---

const filteredPrograms = computed(() => {
    if (!selectedYear.value) return props.programs;
    return props.programs.filter((p) => String(p.academic_year_id) === String(selectedYear.value));
});

const filteredLevels = computed(() => {
    if (!selectedProgram.value) return props.levels;
    return props.levels.filter((l) => String(l.program_id) === String(selectedProgram.value));
});

const filteredSections = computed(() => {
    if (!selectedLevel.value) return props.sections;
    return props.sections.filter((s) => String(s.level_id) === String(selectedLevel.value));
});

const filteredClassrooms = computed(() => {
    if (!selectedSection.value) return props.classrooms;
    return props.classrooms.filter((c) => String(c.section_id) === String(selectedSection.value));
});

// Semesters available for selected year
const filteredSemesters = computed(() => {
    if (!selectedYear.value) return props.semesters;
    return props.semesters.filter((s) => String(s.academic_year_id) === String(selectedYear.value));
});

// Subjects filtered by Academic Year, Level, AND Semester (Corrected references)
const filteredSubjects = computed(() => {
    const academicYearIdFilter = form.academic_year_id;
    const levelIdFilter = form.level_id;
    const semesterIdFilter = form.semester_id;

    // Filter subjects by the IDs selected in the form
    return props.subjects.filter((subject) => {
        // NOTE: Your backend must ensure 'subject' objects contain the fields required for this filtering (e.g., academic_year_id, level_id, semester_id)
        // Since the original code implied filtering by *name* ('academic_year', 'level', 'semester'), 
        // I'm simplifying this to assume the subject object contains direct ID references, 
        // which is standard for dependent selects. If not, this logic needs review.

        // Assuming subject filtering is based on a relationship/attribute in the subject model
        // that matches the ID selected in the form.
        
        // This part is highly dependent on your Laravel Subject model structure. 
        // We'll stick to the original filtering logic based on names/attributes 
        // but reference the correct form IDs.

        const matchesYear = !academicYearIdFilter || subject.academic_year_id === academicYearIdFilter; // Adjusted
        const matchesLevel = !levelIdFilter || subject.level_id === levelIdFilter; // Adjusted
        const matchesSemester = !semesterIdFilter || subject.semester_id === semesterIdFilter; // Adjusted

        return matchesYear && matchesLevel && matchesSemester;
    });
});


// Teachers filtered by selected subject (only show teachers assigned to that subject)
const filteredTeachers = computed(() => {
    if (!form.subject_id) return [];
    const subject = props.subjects.find((s) => String(s.id) === String(form.subject_id));
    if (!subject || !subject.teachers) return [];
    const subjectTeacherIds = subject.teachers.map((t) => t.id);
    return props.teachers.filter((t) => subjectTeacherIds.includes(t.id));
});

// Time slots filtered by selected year, semester, and day_of_week
const filteredTimeSlots = computed(() => {
    if (!selectedYear.value) return [];

    const semName = props.semesters.find((s) => String(s.id) === String(form.semester_id))?.name || null;

    return props.timeSlots.filter((slot) => {
        if (String(slot.academic_year_id) !== String(selectedYear.value)) return false;
        if (semName && slot.semester && String(slot.semester).toLowerCase() !== String(semName).toLowerCase()) return false;
        if (form.day_of_week && slot.day_of_week && slot.day_of_week.toLowerCase() !== form.day_of_week.toLowerCase()) return false;
        return true;
    });
});

// If the selected time_slot_id isn't in the filtered list, clear it
watch(filteredTimeSlots, (newList) => {
    if (!form.time_slot_id) return;
    const exists = newList.some((s) => String(s.id) === String(form.time_slot_id));
    if (!exists) {
        form.time_slot_id = "";
    }
});

// Helper to get teachers for subject (display)
const getTeachersForSubject = (subjectId) => {
    const subject = props.subjects.find((s) => String(s.id) === String(subjectId));
    if (!subject || !subject.teachers) return [];
    return subject.teachers;
};

// Check completion
const allSelectionsComplete = computed(() => {
    return (
        selectedYear.value &&
        selectedProgram.value &&
        selectedLevel.value &&
        selectedSection.value &&
        form.semester_id &&
        form.subject_id &&
        form.day_of_week &&
        form.time_slot_id
    );
});

// Modal handlers
const showCreateModal = () => {
    confirmingEntryCreation.value = true;
    form.reset();
    form.teacher_ids = [];
    form.time_slot_id = "";
    selectedYear.value = "";
    selectedProgram.value = "";
    selectedLevel.value = "";
    selectedSection.value = "";
    editingEntry.value = null;
};

const showEditModal = (entry) => {
    editingEntry.value = entry;

    // Populate form values (only when editing)
    form.academic_year_id = entry.academic_year_id;
    form.program_id = entry.program_id;
    form.level_id = entry.level_id;
    form.section_id = entry.section_id;
    form.classroom_id = entry.classroom_id;
    form.subject_id = entry.subject_id;
    form.semester_id = entry.semester_id;
    form.time_slot_id = entry.time_slot_id;
    form.day_of_week = entry.day_of_week;
    form.start_time = entry.start_time;
    form.end_time = entry.end_time;
    form.break_start = entry.break_start;
    form.break_end = entry.break_end;

    // Preselect only teachers that belong to this entry
    form.teacher_ids = entry.teachers ? entry.teachers.map((t) => t.id) : [];

    // Sync selected variables so dropdowns reflect values
    selectedYear.value = entry.academic_year_id;
    selectedProgram.value = entry.program_id;
    selectedLevel.value = entry.level_id;
    selectedSection.value = entry.section_id;

    confirmingEntryCreation.value = true;
};

const closeModal = () => {
    confirmingEntryCreation.value = false;
    form.reset();
    form.teacher_ids = [];
    form.time_slot_id = "";
    selectedYear.value = "";
    selectedProgram.value = "";
    selectedLevel.value = "";
    selectedSection.value = "";
    editingEntry.value = null;
};

const showErrors = (errors) => {
    // Show custom Laravel `ValidationException` messages
    Object.values(errors).forEach((message) => {
        toast.add({ message, type: "error" });
    });

    // Also show global flash error (if sent by controller)
    if ($page.props.flash?.error) {
        toast.add({ message: $page.props.flash.error, type: "error" });
    }
};


// CRUD function
const createOrUpdateEntry = () => {
    const routeUrl = editingEntry.value
        ? route("timetable_entry:update", { timetableEntry: editingEntry.value.id })
        : route("timetable_entry:create");

    const successMessage = editingEntry.value
        ? "✅ Timetable entry updated!"
        : "✅ Timetable entry created!";

    // Use appropriate HTTP method for update (PUT/PATCH for update, POST for create)
    const method = editingEntry.value ? 'put' : 'post';
    
    form[method](routeUrl, {
        preserveScroll: true,
        onSuccess: () => {
            closeModal();
            toast.add({ message: successMessage });
        },
        onError: showErrors,
    });
};


const showDeleteEntryModal = (entry) => {
    showDeleteModal.value = true;
    deletingEntry.value = entry;
};

const closeDeleteModal = () => {
    showDeleteModal.value = false;
    deletingEntry.value = null;
};

const deleteEntry = () => {
    router.delete(route("timetable_entry:delete", deletingEntry.value.id), {
        preserveScroll: true,
        onSuccess: () => {
            closeDeleteModal();
            toast.add({ message: "Timetable entry deleted!" });
        },
    });
};

// Day name helper
const getDayName = (day) => {
    const days = {
        monday: "Monday",
        tuesday: "Tuesday",
        wednesday: "Wednesday",
        thursday: "Thursday",
        friday: "Friday",
    };
    return days[day] || day;
};

// Navigation helpers
const navigateToGridView = () => router.visit(route("timetable_entry:grid"));
const navigateToAcademicPrograms = () => router.visit(route("academic_program:index"));
const navigateToSubjectTeacherManagement = () => router.visit(route("subject:assign-teacher"));
</script>

<template>
  <LayoutAuthenticated>

    <Head title="Timetable Entries" />

    <div class="py-6 max-w-7xl mx-auto sm:px-6 lg:px-8">
      <!-- Page Header -->
      <div class="flex justify-between items-center mb-6">
        <h1 class="text-xl font-semibold text-gray-800">Timetable Entries</h1>
        <PrimaryButton @click="showCreateModal">+ Add Entry</PrimaryButton>
      </div>

      
      <!-- Filters -->
<div class="mb-6 grid grid-cols-1 md:grid-cols-2 lg:grid-cols-6 gap-4 bg-white p-4 rounded-lg shadow">
  <div>
    <InputLabel value="Academic Year" />
    <select v-model="filterYear"
      class="w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
      <option value="">All Years</option>
      <option v-for="y in uniqueAcademicYears" :key="y.id" :value="y.id">{{ y.name }}</option>
    </select>
  </div>

  <div>
    <InputLabel value="Semester" />
    <select v-model="filterSemester"
      class="w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
      <option value="">All Semesters</option>
      <option v-for="s in uniqueSemesters" :key="s.id" :value="s.id">{{ s.name }}</option>
    </select>
  </div>

  <div>
    <InputLabel value="Academic Program" />
    <select v-model="filterProgram"
      class="w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
      <option value="">All Programs</option>
      <option v-for="p in uniquePrograms" :key="p.id" :value="p.id">{{ p.name }}</option>
    </select>
  </div>

  <div>
    <InputLabel value="Academic Level" />
    <select v-model="filterLevel"
      class="w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
      <option value="">All Levels</option>
      <option v-for="l in uniqueLevels" :key="l.id" :value="l.id">{{ l.name }}</option>
    </select>
  </div>

  <div>
    <InputLabel value="Section" />
    <select v-model="filterSection"
      class="w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
      <option value="">All Sections</option>
      <option v-for="sec in uniqueSections" :key="sec.id" :value="sec.id">{{ sec.name }}</option>
    </select>
  </div>

  <div>
    <InputLabel value="Day" />
    <select v-model="filterDay"
      class="w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
      <option value="">All Days</option>
      <option v-for="day in uniqueDays" :key="day.value" :value="day.value">{{ day.name }}</option>
    </select>
  </div>
</div>

      <!-- Table -->
      <div class="bg-white shadow rounded-lg overflow-hidden">
        <div class="overflow-x-auto">
          <table class="w-full text-sm text-center">
            <thead class="bg-teal-500 text-gray-700 uppercase text-xs">
              <tr>
                <th class="px-4 py-3">#</th>
                <th class="px-4 py-3">Academic Year</th>
                <th class="px-4 py-3">Semester</th>
                <th class="px-4 py-3">Academic Program</th>
                <th class="px-4 py-3">Level</th>
                <th class="px-4 py-3">Section</th>
                <th class="px-4 py-3">Subject</th>
                <th class="px-4 py-3">Teachers</th>
                <th class="px-4 py-3">Assignment</th>
                <th class="px-4 py-3">Day</th>
                <th class="px-4 py-3">Time</th>
                <th class="px-4 py-3">Actions</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="(entry, index) in props.entries.data" :key="entry.id" class="hover:bg-gray-50 transition">
                <td class="px-4 py-2 border-b">
                  {{ index + 1 + (props.entries.per_page * (props.entries.current_page - 1)) }}
                </td>
                <td class="px-4 py-2 border-b">{{ entry.academic_year?.name }}</td>
                <td class="px-4 py-2 border-b">{{ entry.semester?.name }}</td>
                <td class="px-4 py-2 border-b">
                  <span
                    class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-teal-800">
                    {{ entry.academic_program?.name || 'N/A' }}
                  </span>
                </td>
                <td class="px-4 py-2 border-b">{{ entry.academic_level?.name }}</td>
                <td class="px-4 py-2 border-b">{{ entry.section?.name }}</td>
                <td class="px-4 py-2 border-b">{{ entry.subject?.name }}</td>
                <td class="px-4 py-2 border-b">
                  <div v-if="entry.teachers && entry.teachers.length > 0" class="flex flex-wrap gap-1">
                    <span v-for="teacher in entry.teachers" :key="teacher.id"
                      class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                      {{ teacher.name }}
                    </span>
                  </div>
                  <span v-else class="text-gray-400 text-xs">No teachers assigned</span>
                </td>
                <td class="px-4 py-2 border-b">
                  <span v-if="entry.subject_id && entry.teachers && entry.teachers.length > 0"
                    class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                    ✓ {{ entry.teachers.length }} teacher{{ entry.teachers.length > 1 ? 's' : '' }} assigned
                  </span>
                  <span v-else-if="entry.subject_id && (!entry.teachers || entry.teachers.length === 0)"
                    class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                    ⚠ No teachers assigned
                  </span>
                  <span v-else class="text-gray-400 text-xs">N/A</span>
                </td>
                <td class="px-4 py-2 border-b">{{ getDayName(entry.day_of_week) }}</td>
                <td class="px-4 py-2 border-b">{{ entry.time_slot?.name || 'N/A' }} ({{ entry.start_time }} - {{
                  entry.end_time }})</td>
                <td class="px-4 py-2 border-b space-x-2">
                  <button @click.prevent="showEditModal(entry)"
                    class="px-3 py-1 rounded-md text-white text-xs hover:bg-blue-600">
                    <img src="/images/pen.png" height="50" width="50" />
                  </button>
                  <button @click.prevent="showDeleteEntryModal(entry)"
                    class="px-3 py-1 rounded-md text-white text-xs hover:bg-red-600">
                    <img src="/images/bin.png" height="50" width="50" />
                  </button>
                </td>
              </tr>
              <tr v-if="props.entries.data.length === 0">
                <td colspan="12" class="py-6 text-gray-500 text-sm">No timetable entries found.</td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

      <!-- Pagination -->
      <div class="p-4 border-t bg-gray-50">
        <PaginationLinks :links="props.entries.links" />
      </div>

      <!-- Create / Edit Modal -->
      <Modal :show="confirmingEntryCreation" @close="closeModal">
        <div class="p-6">
          <h2 class="text-lg font-semibold text-gray-900 mb-4">
            {{ editingEntry ? "Edit Timetable Entry" : "Add Timetable Entry" }}
          </h2>

          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <!-- Academic Year -->
            <div>
              <InputLabel value="Academic Year" />
              <select v-model="selectedYear"
                class="w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
                <option value="">Select Year</option>
                <option v-for="y in props.academicYears" :key="y.id" :value="y.id">{{ y.name }}</option>
              </select>
            </div>

            <!-- Academic Program -->
            <div>
              <InputLabel value="Academic Program" />
              <select v-model="selectedProgram"
                class="w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500"
                :disabled="filteredPrograms.length === 0 && selectedYear">
                <option value="">Select Academic Program</option>
                <option v-for="p in filteredPrograms" :key="p.id" :value="p.id">{{ p.name }}</option>
              </select>
            </div>

            <!-- Level -->
            <div>
              <InputLabel value="Level" />
              <select v-model="selectedLevel"
                class="w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
                <option value="">Select Level</option>
                <option v-for="l in filteredLevels" :key="l.id" :value="l.id">{{ l.name }}</option>
              </select>
            </div>

            <!-- Section -->
            <div>
              <InputLabel value="Section" />
              <select v-model="selectedSection"
                class="w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
                <option value="">Select Section</option>
                <option v-for="s in filteredSections" :key="s.id" :value="s.id">{{ s.name }}</option>
              </select>
            </div>

            <!-- Classroom -->
            <div>
              <InputLabel value="Classroom" />
              <select v-model="form.classroom_id"
                class="w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
                <option value="">Select Classroom</option>
                <option v-for="c in filteredClassrooms" :key="c.id" :value="c.id">{{ c.room_no }}</option>
              </select>
            </div>

            <!-- Semester -->
            <div>
              <InputLabel value="Semester" />
              <select v-model="form.semester_id"
                class="w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
                <option value="">Select Semester</option>
                <option v-for="s in filteredSemesters" :key="s.id" :value="s.id">{{ s.name }}</option>
              </select>
            </div>

            <!-- Subject -->
            <div>
              <InputLabel value="Subject" />
              <select v-model="form.subject_id"
                class="w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
                <option value="">Select Subject</option>
                <option v-for="sub in filteredSubjects" :key="sub.id" :value="sub.id">{{ sub.name }}</option>
              </select>
            </div>

            <!-- Teachers -->
            <div>
              <InputLabel value="Teachers" />
              <div v-if="filteredTeachers.length === 0 && form.subject_id"
                class="w-full border border-gray-300 rounded-md shadow-sm p-3 bg-gray-50">
                <p class="text-sm text-gray-500">
                  No teachers are assigned to teach this subject yet.
                  <button @click="navigateToSubjectTeacherManagement"
                    class="text-blue-600 hover:text-blue-800 underline ml-1">Assign Teachers to Subjects</button>
                </p>
              </div>
              <div v-else-if="filteredTeachers.length > 0"
                class="w-full border border-gray-300 rounded-md shadow-sm p-3 max-h-40 overflow-y-auto">
                <div v-for="teacher in filteredTeachers" :key="teacher.id" class="flex items-center mb-2">
                  <input type="checkbox" :id="`teacher-${teacher.id}`" :value="teacher.id" v-model="form.teacher_ids"
                    class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500" />
                  <label :for="`teacher-${teacher.id}`" class="ml-2 text-sm text-gray-700">{{ teacher.name }}</label>
                </div>
                <p class="text-xs text-gray-500 mt-2">{{ form.teacher_ids.length }} of {{ filteredTeachers.length }}
                  teachers selected</p>
              </div>
              <div v-else class="w-full border border-gray-300 rounded-md shadow-sm p-3 bg-gray-50">
                <p class="text-sm text-gray-500">Select a subject first to see available teachers</p>
              </div>
            </div>

            <!-- Day -->
            <div>
              <InputLabel value="Day of Week" />
              <select v-model="form.day_of_week"
                class="w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
                <option value="">Select Day</option>
                <option value="monday">Monday</option>
                <option value="tuesday">Tuesday</option>
                <option value="wednesday">Wednesday</option>
                <option value="thursday">Thursday</option>
                <option value="friday">Friday</option>
              </select>
            </div>

            <!-- Time Slot -->
            <div>
              <InputLabel value="Time Slot" />
              <select v-model="form.time_slot_id"
                class="w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
                <option value="">Select Time Slot</option>
                <option v-for="slot in filteredTimeSlots" :key="slot.id" :value="slot.id">
                  {{ slot.name || slot.id }} ({{ slot.start_time }} - {{ slot.end_time }})
                </option>
              </select>
            </div>

          </div>

          <!-- Selection Status Message -->
          <div v-if="!allSelectionsComplete" class="mb-4 p-3 bg-blue-50 border border-blue-200 rounded-lg">
            <div class="flex items-center">
              <div class="flex-shrink-0">
                <svg class="h-4 w-4 text-blue-400" viewBox="0 0 20 20" fill="currentColor">
                  <path fill-rule="evenodd"
                    d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z"
                    clip-rule="evenodd" />
                </svg>
              </div>
              <div class="ml-2">
                <p class="text-sm text-blue-700">Please complete all required selections to create the timetable entry.
                </p>
              </div>
            </div>
          </div>

          <div class="mt-6 flex justify-end space-x-2">
            <SecondaryButton @click.prevent="closeModal">Cancel</SecondaryButton>
            <PrimaryButton :disabled="form.processing || !allSelectionsComplete" @click.prevent="createOrUpdateEntry">{{
              editingEntry ? "Update" : "Create" }}</PrimaryButton>
          </div>
        </div>
      </Modal>

      <!-- Delete Modal -->
      <Modal :show="showDeleteModal" @close="closeDeleteModal">
        <div class="p-6">
          <h2 class="text-lg font-semibold text-red-600 mb-2">Delete Timetable Entry</h2>
          <p class="text-sm text-gray-600">Are you sure you want to delete this timetable entry? This action cannot be
            undone.</p>
          <div class="mt-6 flex justify-end space-x-2">
            <SecondaryButton @click.prevent="closeDeleteModal">Cancel</SecondaryButton>
            <PrimaryButton class="bg-red-500 hover:bg-red-600" :disabled="form.processing" @click.prevent="deleteEntry">
              Delete</PrimaryButton>
          </div>
        </div>
      </Modal>
    </div>
  </LayoutAuthenticated>
</template>
