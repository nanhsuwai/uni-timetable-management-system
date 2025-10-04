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
import { mdiShapePlus } from "@mdi/js";

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

// Filters
const filterYear = ref(props.filters.filterYear || "");
const filterSemester = ref(props.filters.filterSemester || "");
const filterProgram = ref(props.filters.filterProgram || "");
const filterLevel = ref(props.filters.filterLevel || "");
const filterSection = ref(props.filters.filterSection || "");
const filterClassroom = ref(props.filters.filterClassroom || "");
const filterDay = ref(props.filters.filterDay || "");
const semesterName = ref("");


// Update when filters change
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

// Dependent selects state
const selectedYear = ref("");
const selectedProgram = ref("");
const selectedLevel = ref("");
const selectedSection = ref("");

// Watchers to sync selected values to form
watch(selectedYear, (newValue) => {
  form.academic_year_id = newValue;
});

watch(selectedProgram, (newValue) => {
  form.program_id = newValue;
});

watch(selectedLevel, (newValue) => {
  form.level_id = newValue;
});

watch(selectedSection, (newValue) => {
  form.section_id = newValue;
});

// Computed for dependent selects
const filteredPrograms = computed(() => {
  /*  console.log("Filtering programs for year:", selectedYear.value); */
  if (!selectedYear.value) return props.programs;

  const filtered = props.programs.filter(p => p.academic_year_id == selectedYear.value);

  return filtered;
});

const filteredLevels = computed(() => {
  if (!selectedProgram.value) return props.levels;
  return props.levels.filter(l => l.program_id == selectedProgram.value);
});

const filteredSections = computed(() => {
  if (!selectedLevel.value) return props.sections;
  return props.sections.filter(s => s.level_id == selectedLevel.value);
});

const filteredClassrooms = computed(() => {
  if (!selectedSection.value) return props.classrooms;
  return props.classrooms.filter(c => c.section_id == selectedSection.value);
});

const filteredSemesters = computed(() => {
  semesterName.value = props.semesters.find(s => s.id == form.semester_id)?.name || "";

  return props.semesters.filter(s => s.academic_year_id == selectedYear.value);
});

const filteredSubjects = computed(() => {
  const semesterName = props.semesters.find(s => s.id == form.semester_id)?.name || "";
  return props.subjects.filter(s =>
    s.semester == semesterName
  );
});

const filteredTimeSlots = computed(() => {
  /* console.log("Filtering time slots for year and day:", selectedYear.value, form.day_of_week); */
  if (!selectedYear.value) return props.timeSlots;
  return props.timeSlots.filter(t => t.academic_year_id == selectedYear.value && t.day_of_week === form.day_of_week);
});

// Computed for filtering teachers based on selected subject
const filteredTeachers = computed(() => {
  if (!form.subject_id) return props.teachers;

  const selectedSubject = props.subjects.find(s => s.id == form.subject_id);
  if (!selectedSubject || !selectedSubject.teachers) return props.teachers;

  // Filter teachers who are assigned to the selected subject
  const subjectTeacherIds = selectedSubject.teachers.map(t => t.id);
  return props.teachers.filter(t => subjectTeacherIds.includes(t.id));
});

// Computed for getting teachers who can teach a subject (for display purposes)
const getTeachersForSubject = (subjectId) => {
  const subject = props.subjects.find(s => s.id == subjectId);
  if (!subject || !subject.teachers) return [];
  return subject.teachers;
};

// Check if all required selections are complete
const allSelectionsComplete = computed(() => {
  return selectedYear.value &&
    selectedProgram.value &&
    selectedLevel.value &&
    selectedSection.value &&
    form.semester_id &&
    form.subject_id &&
    form.day_of_week &&
    form.time_slot_id;
});

// Modal handlers
const showCreateModal = () => {
  confirmingEntryCreation.value = true;
  form.reset();
  selectedYear.value = "";
  selectedProgram.value = "";
  selectedLevel.value = "";
  selectedSection.value = "";
  editingEntry.value = null;
};

const showEditModal = (entry) => {
  editingEntry.value = entry;

  // Set form values
  form.academic_year_id = entry.academic_year_id;
  form.program_id = entry.program_id;
  form.level_id = entry.level_id;
  form.section_id = entry.section_id;
  form.classroom_id = entry.classroom_id;
  form.subject_id = entry.subject_id;
  form.teacher_ids = entry.teachers ? entry.teachers.map(t => t.id) : [];
  form.semester_id = entry.semester_id;
  form.time_slot_id = entry.time_slot_id;
  form.day_of_week = entry.day_of_week;
  form.start_time = entry.start_time;
  form.end_time = entry.end_time;
  form.break_start = entry.break_start;
  form.break_end = entry.break_end;

  // Sync form values back to selected variables for dependent dropdowns
  selectedYear.value = entry.academic_year_id;
  selectedProgram.value = entry.program_id;
  selectedLevel.value = entry.level_id;
  selectedSection.value = entry.section_id;

  confirmingEntryCreation.value = true;
};

const closeModal = () => {
  confirmingEntryCreation.value = false;
  form.reset();
  selectedYear.value = "";
  selectedProgram.value = "";
  selectedLevel.value = "";
  selectedSection.value = "";
  editingEntry.value = null;
};

// CRUD functions
const createOrUpdateEntry = () => {
  if (editingEntry.value) {
    form.post(route("timetable_entry:update", { timetableEntry: editingEntry.value.id }), {
      preserveScroll: true,
      onSuccess: () => {
        // closeModal();
        toast.add({ message: "✅ Timetable entry updated!" });
      },
      onError: (err) => {
        toast.add({ message: err.errors.error, type: "error" });
      },
    });
  } else {
    form.post(route("timetable_entry:create"), {
      preserveScroll: true,
      onSuccess: () => {
        // closeModal();
        toast.add({ message: "Timetable entry created!" });
      },
      onError: (err) => {
        toast.add({ message: err.error, type: "error" });
      },
    });
  }
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
    friday: "Friday"
  };
  return days[day] || day;
};

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
      <div class="mb-6 grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 bg-white p-4 rounded-lg shadow">
        <div>
          <InputLabel value="Academic Year" />
          <select v-model="filterYear"
            class="w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
            <option value="">All Years</option>
            <option v-for="y in props.academicYears" :key="y.id" :value="y.id">{{ y.name }}</option>
          </select>
        </div>
        <div>
          <InputLabel value="Semester" />
          <select v-model="filterSemester"
            class="w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
            <option value="">All Semesters</option>
            <option v-for="s in props.semesters" :key="s.id" :value="s.id">{{ s.name }}</option>
          </select>
        </div>
        <div>
          <InputLabel value="Academic Program" />
          <select v-model="filterProgram"
            class="w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
            <option value="">All Programs</option>
            <option v-for="p in props.programs" :key="p.id" :value="p.id">{{ p.name }}</option>
          </select>
        </div>
        <div>
          <InputLabel value="Day" />
          <select v-model="filterDay"
            class="w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
            <option value="">All Days</option>
            <option value="monday">Monday</option>
            <option value="tuesday">Tuesday</option>
            <option value="wednesday">Wednesday</option>
            <option value="thursday">Thursday</option>
            <option value="friday">Friday</option>


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
                  <img src="/images/pen.png" height="50px" width="50px"> </img>
                </button>
                <button @click.prevent="showDeleteEntryModal(entry)"
                  class="px-3 py-1 rounded-md text-white text-xs hover:bg-red-600">
                  <img src="/images/bin.png" height="50px" width="50px"> </img>
                </button>
              </td>
            </tr>
            <tr v-if="props.entries.data.length === 0">
              <td colspan="12" class="py-6 text-gray-500 text-sm">
                No timetable entries found.
              </td>
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
              <p v-if="selectedYear && filteredPrograms.length === 0" class="text-sm text-gray-500 mt-1">
                No academic programs available for the selected year.
                <button @click="navigateToAcademicPrograms" class="text-blue-600 hover:text-blue-800 underline ml-1">
                  Manage Academic Programs
                </button>
              </p>
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
                    class="text-blue-600 hover:text-blue-800 underline ml-1">
                    Assign Teachers to Subjects
                  </button>
                </p>
              </div>
              <div v-else-if="filteredTeachers.length > 0"
                class="w-full border border-gray-300 rounded-md shadow-sm p-3 max-h-40 overflow-y-auto">
                <div v-for="teacher in filteredTeachers" :key="teacher.id" class="flex items-center mb-2">
                  <input type="checkbox" :id="`teacher-${teacher.id}`" :value="teacher.id" v-model="form.teacher_ids"
                    class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500">
                  <label :for="`teacher-${teacher.id}`" class="ml-2 text-sm text-gray-700">
                    {{ teacher.name }}
                  </label>
                </div>
                <p class="text-xs text-gray-500 mt-2">
                  {{ form.teacher_ids.length }} of {{ filteredTeachers.length }} teachers selected
                </p>
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
                  {{ slot.name }} ({{ slot.start_time }} - {{ slot.end_time }})
                </option>
              </select>
            </div>

            <!-- Breaks -->

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
                <p class="text-sm text-blue-700">
                  Please complete all required selections to create the timetable entry.
                </p>
              </div>
            </div>
          </div>

          <div class="mt-6 flex justify-end space-x-2">
            <SecondaryButton @click.prevent="closeModal">Cancel</SecondaryButton>
            <PrimaryButton :disabled="form.processing || !allSelectionsComplete" @click.prevent="createOrUpdateEntry">
              {{ editingEntry ? "Update" : "Create" }}
            </PrimaryButton>
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
              Delete
            </PrimaryButton>
          </div>
        </div>
      </Modal>
    </div>
  </LayoutAuthenticated>
</template>

// Navigation function
const navigateToGridView = () => {
router.visit(route("timetable_entry:grid"));
};

// Additional helper for academic program management
const navigateToAcademicPrograms = () => {
router.visit(route("academic_program:index"));
};

// Navigation function for subject-teacher management
const navigateToSubjectTeacherManagement = () => {
// For now, navigate to subjects page where teachers can be assigned
router.visit(route("subject:index"));
};
