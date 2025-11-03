<script setup>
import { ref, watch } from "vue";
import { useForm, router } from "@inertiajs/vue3";
import toast from "@/Stores/toast";

// Components
import LayoutAuthenticated from "@/Layouts/LayoutAuthenticated.vue";
import SectionTitleLineWithButton from "@/Components/SectionTitleLineWithButton.vue";
import CardBox from "@/Components/CardBox.vue";
import PaginationLinks from "@/Components/PaginationLinks.vue";
import Modal from "@/Components/Modal.vue";
import TextInput from "@/Components/TextInput.vue";
import InputLabel from "@/Components/InputLabel.vue";
import InputError from "@/Components/InputError.vue";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import SecondaryButton from "@/Components/SecondaryButton.vue";
import { mdiShapePlus } from "@mdi/js";

// Props
const props = defineProps({
  academicYears: {
    type: Object,
    default: () => ({ data: [], meta: {}, links: [] }),
  },
  filters: {
    type: Object,
    default: () => ({}),
  },
  semsesterOptions: {
    type: Array,
    default: () => [],
  },
});

// Semester Form
const semesterForm = useForm({
  name: "",
  start_date: "",
  end_date: "",
});

// Semester States
const confirmingSemesterModal = ref(false);
const showingSemesterFormModal = ref(false);
const editingSemester = ref(null);
const deletingSemester = ref(null);
const selectedAcademicYear = ref(null);

// Filter
const filterName = ref(props.filters.filterName || "");
watch(filterName, (newVal) => {
  router.get(
    route("academic-year:all"),
    { filterName: newVal },
    { preserveState: true, replace: true }
  );
});

// Form
const form = useForm({
  name: "",
  start_date: "",
  end_date: "",
});

// Modals & States
const confirmingAcademicYearCreation = ref(false);
const editingAcademicYear = ref(null);
const showDeleteModal = ref(false);
const deletingAcademicYear = ref(null);

// Modal Handlers
const showCreateModal = () => {
  confirmingAcademicYearCreation.value = true;
  form.reset();
  editingAcademicYear.value = null;
};

const showEditModal = (academicYear) => {
  editingAcademicYear.value = academicYear;
  form.name = academicYear.name;
  form.start_date = academicYear.start_date;
  form.end_date = academicYear.end_date;
  confirmingAcademicYearCreation.value = true;
};

const closeModal = () => {
  confirmingAcademicYearCreation.value = false;
  form.reset();
  editingAcademicYear.value = null;
};

// CRUD Handlers
const createOrUpdateAcademicYear = () => {
  if (editingAcademicYear.value) {
    form.post(
      route("academic-year:update", { academicYear: editingAcademicYear.value.id }),
      {
        preserveScroll: true,
        onSuccess: () => {
          closeModal();
          toast.add({ message: "Academic Year updated!" });
        },
        onError: () => {
          toast.add({ message: "Error updating Academic Year.", type: "error" });
        },
      }
    );
  } else {
    form.post(route("academic-year:create"), {
      preserveScroll: true,
      onSuccess: () => {
        closeModal();
        toast.add({ message: "Academic Year created!" });
      },
      onError: () => {
        toast.add({ message: "Error creating Academic Year.", type: "error" });
      },
    });
  }
};

const showDeleteAcademicYearModal = (academicYear) => {
  showDeleteModal.value = true;
  deletingAcademicYear.value = academicYear;
};

const closeDeleteModal = () => {
  showDeleteModal.value = false;
  deletingAcademicYear.value = null;
};

const deleteAcademicYear = () => {
  router.delete(route("academic-year:delete", deletingAcademicYear.value), {
    preserveScroll: true,
    onSuccess: () => {
      closeDeleteModal();
      toast.add({ message: "Academic Year deleted!" });
    },
  });
};

const toggleStatus = (academicYear) => {
  router.post(
    route("academic-year:toggle-status", { academicYear: academicYear.id }),
    {},
    {
      preserveScroll: true,
      onSuccess: () => {
        // Toggle the status locally (the server will handle the actual toggle)
        academicYear.status = academicYear.status === 1 ? 0 : 1;
        toast.add({ message: `Status updated to ${academicYear.status ? 'Active' : 'Inactive'}!` });
      },
    }
  );
};

// Semester Modal Handlers
const openSemesterModal = (academicYear) => {
  selectedAcademicYear.value = academicYear;
  confirmingSemesterModal.value = true;
  semesterForm.reset();
  editingSemester.value = null;
};

const closeSemesterModal = () => {
  confirmingSemesterModal.value = false;
  showingSemesterFormModal.value = false;
  semesterForm.reset();
  editingSemester.value = null;
  selectedAcademicYear.value = null;
};

const openEditSemesterModal = (semester) => {
  editingSemester.value = semester;
  showingSemesterFormModal.value = true;
  if (semester) {
    semesterForm.name = semester.name;
    semesterForm.start_date = semester.start_date;
    semesterForm.end_date = semester.end_date;
  } else {
    semesterForm.reset();
  }
};

const closeEditSemesterModal = () => {
  showingSemesterFormModal.value = false;
  editingSemester.value = null;
  // semesterForm.reset();
};

const saveSemester = () => {
  const url = editingSemester.value
    ? route("academic-year:semesters:update", { academicYear: selectedAcademicYear.value.id, semester: editingSemester.value.id })
    : route("academic-year:semesters:create", { academicYear: selectedAcademicYear.value.id });

  semesterForm.post(url, {
    preserveScroll: true,
    onSuccess: () => {
      closeEditSemesterModal();
      closeSemesterModal();
      toast.add({
        message: editingSemester.value
          ? "✅ Semester updated!"
          : "✅ Semester created!",
      });
    },
    onError: () => semesterForm.reset(),
  });
};

const confirmDeleteSemester = (semester) => {
  deletingSemester.value = semester;
};

const deleteSemester = () => {
  if (!deletingSemester.value) return;
  router.delete(route("academic-year:semesters:delete", { academicYear: selectedAcademicYear.value.id, semester: deletingSemester.value.id }), {
    preserveScroll: true,
    onSuccess: () => {
      deletingSemester.value = null;
      toast.add({ message: "✅ Semester deleted!" });
    },
  });
};

const toggleSemesterStatus = (semester) => {
  router.post(route("academic-year:semesters:toggle-status", { academicYear: selectedAcademicYear.value.id, semester: semester.id }), {
    data: { status: semester.status === 'active' ? 'inactive' : 'active' },
    preserveScroll: true,
    onSuccess: () => {
      toast.add({ message: "✅ Status updated!" });
    },
  });
};
</script>

<template>
  <LayoutAuthenticated>
    <div class="py-6 max-w-7xl mx-auto sm:px-6 lg:px-8">
      
      <SectionTitleLineWithButton :icon="mdiShapePlus" title="Academic Years Management" class="mx-auto text-gray-800 dark:text-white mb-6">
        <PrimaryButton @click.prevent="showCreateModal" class="shadow-md hover:shadow-lg transition duration-200">
          <span class="mr-1">✨</span> Add Academic Year
        </PrimaryButton>
      </SectionTitleLineWithButton>

      <div class="mb-6 p-4 bg-white dark:bg-gray-800 rounded-xl shadow-lg border border-gray-100 dark:border-gray-700">
        <InputLabel for="filterName" value="Search Academic Year" class="text-gray-600 dark:text-gray-300 font-medium" />
        <TextInput 
          id="filterName" 
          v-model="filterName" 
          type="text" 
          placeholder="Enter academic year name to search..." 
          class="w-full mt-1 border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg focus:border-cyan-500 focus:ring-cyan-500" />
      </div>

      <CardBox has-table class="shadow-xl dark:shadow-2xl dark:shadow-gray-950/50">
        <div class="overflow-x-auto">
          <table class="w-full text-sm text-left dark:text-gray-300">
            <thead class="text-xs uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400 border-b dark:border-gray-600">
              <tr>
                <th class="px-4 py-3 text-center rounded-tl-xl">#</th>
                <th class="px-4 py-3">Name</th>
                <th class="px-4 py-3 whitespace-nowrap">Start Date</th>
                <th class="px-4 py-3 whitespace-nowrap">End Date</th>
                <th class="px-4 py-3 text-center">Status</th>
                <th class="px-4 py-3 text-center">Actions</th>
                <th class="px-4 py-3 text-center">Semesters</th>
                <th class="px-4 py-3 text-center rounded-tr-xl">Programs</th>
              </tr>
            </thead>
            <tbody>
              <tr 
                v-for="(academicYear, index) in props.academicYears.data" 
                :key="academicYear.id"
                class="border-b dark:border-gray-700 hover:bg-gray-100/50 dark:hover:bg-gray-800/50 transition duration-150 ease-in-out">
                
                <td class="px-4 py-3 text-center font-mono text-gray-500 dark:text-gray-400">
                  {{ index + 1 + (props.academicYears.per_page * (props.academicYears.current_page - 1)) }}
                </td>
                <td class="px-4 py-3 font-semibold text-gray-900 dark:text-white">{{ academicYear.name }}</td>
                <td class="px-4 py-3 text-gray-600 dark:text-gray-400">{{ academicYear.start_date }}</td>
                <td class="px-4 py-3 text-gray-600 dark:text-gray-400">{{ academicYear.end_date }}</td>
                
                <td class="px-4 py-3 text-center">
                  <button @click.prevent="toggleStatus(academicYear)" :class="[
                    'px-3 py-1 rounded-full text-xs font-bold transition-all duration-200 shadow-sm whitespace-nowrap',
                    academicYear.status === 'active'
                      ? 'bg-emerald-100 text-emerald-800 dark:bg-emerald-900/40 dark:text-emerald-400 hover:bg-emerald-200 dark:hover:bg-emerald-900/60'
                      : 'bg-gray-200 text-gray-600 dark:bg-gray-600/50 dark:text-gray-300 hover:bg-gray-300 dark:hover:bg-gray-600/70'
                  ]">
                    {{ academicYear.status === 'active' ? 'Active ✅' : 'Inactive ⏸' }}
                  </button>
                </td>
                
                <td class="px-4 py-3 flex justify-center gap-3">
                  <button @click.prevent="showEditModal(academicYear)"
                    class="p-2 rounded-full text-blue-500 hover:bg-blue-100 dark:hover:bg-gray-700/50 transition duration-150"
                    title="Edit">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-7-3l3-3m-3 3l-6 6"></path></svg>
                  </button>
                  <button @click.prevent="showDeleteAcademicYearModal(academicYear)"
                    class="p-2 rounded-full text-red-500 hover:bg-red-100 dark:hover:bg-gray-700/50 transition duration-150"
                    title="Delete">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.042A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.897L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                  </button>
                </td>
                
                <td class="px-4 py-3 text-center">
                  <button @click.prevent="openSemesterModal(academicYear)"
                    class="px-3 py-1 rounded-lg text-sm text-cyan-600 border border-cyan-300 dark:border-cyan-600/50 hover:bg-cyan-50 dark:hover:bg-cyan-900/30 transition duration-150 whitespace-nowrap">
                    Semesters ({{ academicYear.semesters ? academicYear.semesters.length : 0 }})
                  </button>
                </td>
                
                <td class="px-4 py-3 text-center">
                  <button @click.prevent="$inertia.visit(route('academic-year:programs', academicYear.id))"
                    class="px-3 py-1 rounded-lg text-sm text-indigo-600 border border-indigo-300 dark:border-indigo-600/50 hover:bg-indigo-50 dark:hover:bg-indigo-900/30 transition duration-150 whitespace-nowrap">
                    Programs ({{ academicYear.academic_programs ? academicYear.academic_programs.length : 0 }})
                  </button>
                </td>

              </tr>
              <tr v-if="!props.academicYears.data || props.academicYears.data.length === 0">
                  <td colspan="8" class="px-4 py-4 text-center text-gray-500 dark:text-gray-400">
                    No academic years found.
                  </td>
              </tr>
            </tbody>
          </table>
        </div>
      </CardBox>

      <div class="mt-6 p-4 bg-white dark:bg-gray-800 rounded-xl shadow-lg border border-gray-100 dark:border-gray-700">
        <PaginationLinks :links="props.academicYears.links" />
      </div>

      <Modal :show="confirmingAcademicYearCreation" @close="closeModal" max-width="lg">
        <div class="p-6 bg-white dark:bg-gray-800 rounded-lg">
          <h2 class="text-2xl font-bold text-gray-900 dark:text-white border-b border-gray-200 dark:border-gray-700 pb-3 mb-6">
            {{ editingAcademicYear ? "Edit Academic Year" : "Add New Academic Year" }}
          </h2>

          <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
              <InputLabel for="name" value="Name" class="dark:text-gray-300" />
              <TextInput id="name" v-model="form.name" type="text" placeholder="e.g., 2024-2025" 
                class="w-full mt-1 dark:bg-gray-700 dark:border-gray-600 dark:text-white" />
              <InputError :message="form.errors.name" />
            </div>

            <div>
              <InputLabel for="start_date" value="Start Date" class="dark:text-gray-300" />
              <TextInput id="start_date" v-model="form.start_date" type="date" 
                class="w-full mt-1 dark:bg-gray-700 dark:border-gray-600 dark:text-white" />
              <InputError :message="form.errors.start_date" />
            </div>

            <div class="md:col-span-2"> <InputLabel for="end_date" value="End Date" class="dark:text-gray-300" />
              <TextInput id="end_date" v-model="form.end_date" type="date" 
                class="w-full mt-1 dark:bg-gray-700 dark:border-gray-600 dark:text-white" />
              <InputError :message="form.errors.end_date" />
            </div>
          </div>

          <div class="mt-8 flex justify-end gap-3 border-t border-gray-100 dark:border-gray-700 pt-4">
            <SecondaryButton @click.prevent="closeModal" class="dark:bg-gray-600 dark:hover:bg-gray-700 dark:text-white">Cancel</SecondaryButton>
            <PrimaryButton :class="{ 'opacity-50': form.processing }" :disabled="form.processing"
              @click.prevent="createOrUpdateAcademicYear">
              {{ editingAcademicYear ? "Update Year" : "Create Year" }}
            </PrimaryButton>
          </div>
        </div>
      </Modal>

      <Modal :show="showDeleteModal" @close="closeDeleteModal" max-width="sm">
        <div class="p-6 bg-white dark:bg-gray-800 rounded-lg">
          <h2 class="text-xl font-bold text-red-600 dark:text-red-400 mb-4">Delete Academic Year</h2>
          <p class="text-gray-600 dark:text-gray-400">
            Are you sure you want to delete this academic year? This action cannot be undone and will affect related semesters and programs.
          </p>
          <div class="mt-6 flex justify-end gap-3">
            <SecondaryButton @click.prevent="closeDeleteModal" class="dark:bg-gray-600 dark:hover:bg-gray-700 dark:text-white">Cancel</SecondaryButton>
            <PrimaryButton class="bg-red-600 hover:bg-red-700 text-white dark:bg-red-700 dark:hover:bg-red-800" :class="{ 'opacity-50': form.processing }"
              :disabled="form.processing" @click.prevent="deleteAcademicYear">
              Confirm Delete
            </PrimaryButton>
          </div>
        </div>
      </Modal>

      <Modal :show="confirmingSemesterModal" @close="closeSemesterModal" max-width="3xl">
        <div class="p-6 bg-white dark:bg-gray-800 rounded-lg">
          <h2 class="text-2xl font-bold text-gray-900 dark:text-white border-b border-gray-200 dark:border-gray-700 pb-3 mb-4">
            Semesters for {{ selectedAcademicYear?.name }}
          </h2>

          <div class="mb-4 flex justify-end">
            <PrimaryButton @click.prevent="openEditSemesterModal(null)" class="shadow-md hover:shadow-lg transition duration-200">
              <span class="mr-1">➕</span> Add Semester
            </PrimaryButton>
          </div>

          <div class="overflow-x-auto border rounded-lg border-gray-200 dark:border-gray-700">
            <table class="w-full text-sm text-left dark:text-gray-300">
              <thead class="bg-gray-50 dark:bg-gray-700 text-xs uppercase dark:text-gray-400">
                <tr>
                  <th class="px-4 py-3">#</th>
                  <th class="px-4 py-3">Name</th>
                  <th class="px-4 py-3">Start Date</th>
                  <th class="px-4 py-3">End Date</th>
                  <th class="px-4 py-3 text-center">Status</th>
                  <th class="px-4 py-3 text-center">Actions</th>
                </tr>
              </thead>
              <tbody>
                <tr 
                  v-for="(semester, index) in selectedAcademicYear?.semesters" 
                  :key="semester.id"
                  class="border-t dark:border-gray-700 hover:bg-gray-100/50 dark:hover:bg-gray-700/50 transition duration-150">
                  
                  <td class="px-4 py-3 font-mono text-gray-500 dark:text-gray-400">{{ index + 1 }}</td>
                  <td class="px-4 py-3 font-medium text-gray-900 dark:text-white">{{ semester.name }}</td>
                  <td class="px-4 py-3 text-gray-600 dark:text-gray-400">{{ semester.start_date }}</td>
                  <td class="px-4 py-3 text-gray-600 dark:text-gray-400">{{ semester.end_date }}</td>
                  
                  <td class="px-4 py-3 text-center">
                    <button @click.prevent="toggleSemesterStatus(semester)" :class="[
                      'px-3 py-1 rounded-full text-xs font-bold transition-all duration-200 shadow-sm whitespace-nowrap',
                      semester.status === 'active'
                        ? 'bg-emerald-100 text-emerald-800 dark:bg-emerald-900/40 dark:text-emerald-400 hover:bg-emerald-200 dark:hover:bg-emerald-900/60'
                        : 'bg-gray-200 text-gray-600 dark:bg-gray-600/50 dark:text-gray-300 hover:bg-gray-300 dark:hover:bg-gray-600/70'
                    ]">
                      {{ semester.status === 'active' ? 'Active' : 'Inactive' }}
                    </button>
                  </td>
                  
                  <td class="px-4 py-3 flex justify-center gap-2">
                    <button @click.prevent="openEditSemesterModal(semester)"
                      class="p-1 rounded-full text-blue-500 hover:bg-blue-100 dark:hover:bg-gray-700/50 transition duration-150" title="Edit">
                      <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-7-3l3-3m-3 3l-6 6"></path></svg>
                    </button>
                    <button @click.prevent="confirmDeleteSemester(semester)"
                      class="p-1 rounded-full text-red-500 hover:bg-red-100 dark:hover:bg-gray-700/50 transition duration-150" title="Delete">
                      <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.042A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.897L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                    </button>
                  </td>
                </tr>
                <tr v-if="!selectedAcademicYear?.semesters?.length">
                  <td colspan="6" class="px-4 py-4 text-center text-gray-500 dark:text-gray-400">No semesters found.</td>
                </tr>
              </tbody>
            </table>
          </div>

          <div class="mt-6 flex justify-end">
            <SecondaryButton @click.prevent="closeSemesterModal" class="dark:bg-gray-600 dark:hover:bg-gray-700 dark:text-white">Close</SecondaryButton>
          </div>
        </div>
      </Modal>

      <Modal :show="showingSemesterFormModal" @close="closeEditSemesterModal" max-width="md">
        <div class="p-6 bg-white dark:bg-gray-800 rounded-lg">
          <h2 class="text-xl font-bold text-gray-900 dark:text-white mb-4 border-b border-gray-200 dark:border-gray-700 pb-2">
            {{ editingSemester ? "Edit Semester" : "Add New Semester" }}
          </h2>
          <div class="space-y-4">
            <div>
              <InputLabel for="semester_name" value="Semester Name" class="dark:text-gray-300" />
              <select id="semester_name" v-model="semesterForm.name" 
                class="w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg shadow-sm focus:border-cyan-500 focus:ring-cyan-500 mt-1">
                <option value="" disabled>Select Semester</option>
                <option v-for="option in props.semsesterOptions" :key="option" :value="option">{{ option }}</option>
              </select>
              <InputError :message="semesterForm.errors.name" />
            </div>
            <div>
              <InputLabel for="semester_start_date" value="Start Date" class="dark:text-gray-300" />
              <TextInput id="semester_start_date" v-model="semesterForm.start_date" type="date" 
                class="w-full mt-1 dark:bg-gray-700 dark:border-gray-600 dark:text-white" />
              <InputError :message="semesterForm.errors.start_date" />
            </div>
            <div>
              <InputLabel for="semester_end_date" value="End Date" class="dark:text-gray-300" />
              <TextInput id="semester_end_date" v-model="semesterForm.end_date" type="date" 
                class="w-full mt-1 dark:bg-gray-700 dark:border-gray-600 dark:text-white" />
              <InputError :message="semesterForm.errors.end_date" />
            </div>
          </div>
          <div class="mt-6 flex justify-end space-x-3 border-t border-gray-100 dark:border-gray-700 pt-4">
            <SecondaryButton @click.prevent="closeEditSemesterModal" class="dark:bg-gray-600 dark:hover:bg-gray-700 dark:text-white">Cancel</SecondaryButton>
            <PrimaryButton :disabled="semesterForm.processing" @click.prevent="saveSemester">
              {{ editingSemester ? "Update Semester" : "Add Semester" }}
            </PrimaryButton>
          </div>
        </div>
      </Modal>

      <Modal :show="!!deletingSemester" @close="() => (deletingSemester = null)" max-width="sm">
        <div class="p-6 bg-white dark:bg-gray-800 rounded-lg">
          <h2 class="text-xl font-bold text-red-600 dark:text-red-400 mb-4">Delete Semester</h2>
          <p class="text-gray-600 dark:text-gray-400">
            Are you sure you want to delete the semester **{{ deletingSemester?.name }}**? This action cannot be undone.
          </p>
          <div class="mt-6 flex justify-end space-x-3">
            <SecondaryButton @click.prevent="() => (deletingSemester = null)" class="dark:bg-gray-600 dark:hover:bg-gray-700 dark:text-white">
              Cancel
            </SecondaryButton>
            <PrimaryButton class="bg-red-600 hover:bg-red-700 text-white dark:bg-red-700 dark:hover:bg-red-800" :disabled="semesterForm.processing"
              @click.prevent="deleteSemester">
              Confirm Delete
            </PrimaryButton>
          </div>
        </div>
      </Modal>

    </div>
  </LayoutAuthenticated>
</template>
