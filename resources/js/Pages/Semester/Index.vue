<script setup>
import { Head, useForm, router } from "@inertiajs/vue3";
import { ref, watch } from "vue";

// Components
import LayoutAuthenticated from "@/Layouts/LayoutAuthenticated.vue";
import PaginationLinks from "@/Components/PaginationLinks.vue";
import Modal from "@/Components/Modal.vue";
import TextInput from "@/Components/TextInput.vue";
import InputError from "@/Components/InputError.vue";
import InputLabel from "@/Components/InputLabel.vue";
import SecondaryButton from "@/Components/SecondaryButton.vue";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import toast from "@/Stores/toast";

// Props
const props = defineProps({
  semesters: {
    type: Object,
    default: () => ({ data: [], meta: {}, links: [] }),
  },
  filters: {
    type: Object,
    default: () => ({}),
  },
  academicYears: {
    type: Array,
    default: () => [],
  },
  defaultAcademicYear: {
    type: Object,
    default: () => null,
  },
});

// Filters
const filterName = ref(props.filters.filterName || "");
const filterAcademicYear = ref(props.filters.filterAcademicYear || (props.defaultAcademicYear ? props.defaultAcademicYear.id : ""));

watch([filterName, filterAcademicYear], ([newName, newAcademicYear]) => {
  router.get(
    route("semester:all"),
    { filterName: newName, filterAcademicYear: newAcademicYear },
    { preserveState: true, replace: true }
  );
});

// Form state
const form = useForm({
  academic_year_id: "",
  name: "",
  start_date: "",
  end_date: "",
});

// Helper function to get academic year name by ID
const getAcademicYearName = (id) => {
  const year = props.academicYears.find(y => y.id == id);
  return year ? year.name : 'N/A';
};

// Modals
const confirmingSemesterCreation = ref(false);
const editingSemester = ref(null);
const showDeleteModal = ref(false);
const deletingSemester = ref(null);

// Modal Handlers
const showCreateModal = () => {
  confirmingSemesterCreation.value = true;
  form.reset();
  editingSemester.value = null;
};

const showEditModal = (semester) => {
  editingSemester.value = semester;
  form.academic_year_id = semester.academic_year_id;
  form.name = semester.name;
  form.start_date = semester.start_date;
  form.end_date = semester.end_date;
  confirmingSemesterCreation.value = true;
};

const closeModal = () => {
  confirmingSemesterCreation.value = false;
  form.reset();
  editingSemester.value = null;
};

// CRUD Handlers
const createOrUpdateSemester = () => {
  if (editingSemester.value) {
    form.post(route("semester:update", { semester: editingSemester.value.id }), {
      preserveScroll: true,
      onSuccess: () => {
        closeModal();
        toast.add({ message: "✅ Semester updated successfully!" });
      },
    });
  } else {
    form.post(route("semester:create"), {
      preserveScroll: true,
      onSuccess: () => {
        closeModal();
        toast.add({ message: "✅ Semester created successfully!" });
      },
    });
  }
};

const showDeleteSemesterModal = (semester) => {
  showDeleteModal.value = true;
  deletingSemester.value = semester;
};

const closeDeleteModal = () => {
  showDeleteModal.value = false;
  deletingSemester.value = null;
};

const deleteSemester = () => {
  router.delete(route("semester:delete", deletingSemester.value), {
    preserveScroll: true,
    onSuccess: () => {
      closeDeleteModal();
      toast.add({ message: "🗑️ Semester deleted successfully!" });
    },
  });
};
</script>

<template>
  <LayoutAuthenticated>
    <Head title="Semesters" />

    <div class="py-6 max-w-7xl mx-auto sm:px-6 lg:px-8">
      <!-- Page Header -->
      <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-semibold text-gray-800">Semesters</h1>
        <PrimaryButton @click="showCreateModal">
          + Add Semester
        </PrimaryButton>
      </div>

      <!-- Filters -->
      <div class="mb-6 bg-white p-4 rounded-lg shadow-sm border border-gray-200">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
          <div>
            <InputLabel for="filterName" value="Search by Name" />
            <TextInput
              id="filterName"
              v-model="filterName"
              type="text"
              placeholder="Search semesters..."
              class="w-full"
            />
          </div>
          <div>
            <InputLabel for="filterAcademicYear" value="Filter by Academic Year" />
            <select
              id="filterAcademicYear"
              v-model="filterAcademicYear"
              class="w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500"
            >
              <option value="">All Academic Years</option>
              <option
                v-for="year in props.academicYears"
                :key="year.id"
                :value="year.id"
              >
                {{ year.name }}
              </option>
            </select>
          </div>
        </div>
      </div>

      <!-- Table -->
      <div class="bg-white shadow-sm rounded-lg overflow-hidden border border-gray-200">
        <table class="w-full text-sm">
          <thead class="bg-gray-50 text-gray-700 uppercase text-xs">
            <tr>
              <th class="px-6 py-3 text-left font-medium">#</th>
              <th class="px-6 py-3 text-left font-medium">Name</th>
              <th class="px-6 py-3 text-left font-medium">Academic Year</th>
              <th class="px-6 py-3 text-left font-medium">Start Date</th>
              <th class="px-6 py-3 text-left font-medium">End Date</th>
              <th class="px-6 py-3 text-center font-medium">Actions</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-200">
            <tr
              v-for="(semester, index) in props.semesters.data"
              :key="semester.id"
              class="hover:bg-gray-50 transition-colors duration-150"
            >
              <td class="px-6 py-4 text-gray-900">
                {{
                  index + 1 +
                  (props.semesters.per_page *
                    (props.semesters.current_page - 1))
                }}
              </td>
              <td class="px-6 py-4 text-gray-900 font-medium">
                {{ semester.name }}
                <span v-if="semester.status === 'active'" class="ml-2 inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-green-100 text-green-800">
                  Active
                </span>
              </td>
              <td class="px-6 py-4 text-gray-600">
                <span :class="semester.academic_year?.status === 'active' ? 'text-green-600 font-medium' : ''">
                  {{ getAcademicYearName(semester.academic_year_id) }}
                  <span v-if="semester.academic_year?.status === 'active'" class="ml-1 text-xs">(Active)</span>
                </span>
              </td>
              <td class="px-6 py-4 text-gray-600">
                {{ semester.start_date }}
              </td>
              <td class="px-6 py-4 text-gray-600">
                {{ semester.end_date }}
              </td>
              <td class="px-6 py-4">
                <div class="flex justify-center space-x-2">
                  <button
                    @click.prevent="showEditModal(semester)"
                    class="px-3 py-1.5 rounded-md bg-blue-500 text-white text-xs font-medium hover:bg-blue-600 transition-colors duration-150"
                  >
                    Edit
                  </button>
                  <button
                    @click.prevent="showDeleteSemesterModal(semester)"
                    class="px-3 py-1.5 rounded-md bg-red-500 text-white text-xs font-medium hover:bg-red-600 transition-colors duration-150"
                  >
                    Delete
                  </button>
                </div>
              </td>
            </tr>
            <tr v-if="props.semesters.data.length === 0">
              <td colspan="6" class="py-8 text-center text-gray-500">
                <div class="flex flex-col items-center">
                  <svg class="w-12 h-12 text-gray-300 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                  </svg>
                  <p class="text-sm font-medium">No semesters found</p>
                  <p class="text-xs text-gray-400 mt-1">Get started by creating your first semester.</p>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- Pagination -->
      <div class="mt-4 bg-white px-4 py-3 border-t border-gray-200 rounded-b-lg">
        <PaginationLinks :links="props.semesters.links" />
      </div>

      <!-- Create / Edit Modal -->
      <Modal :show="confirmingSemesterCreation" @close="closeModal">
        <div class="p-6">
          <h2 class="text-lg font-semibold text-gray-900 mb-4">
            {{ editingSemester ? "Edit Semester" : "Add New Semester" }}
          </h2>
          <div class="space-y-4">
            <div>
              <InputLabel for="academic_year_id" value="Academic Year" />
              <select
                id="academic_year_id"
                v-model="form.academic_year_id"
                class="w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500"
              >
                <option value="">Select Academic Year</option>
                <option
                  v-for="year in props.academicYears"
                  :key="year.id"
                  :value="year.id"
                >
                  {{ year.name }}
                </option>
              </select>
              <InputError :message="form.errors.academic_year_id" />
            </div>

            <div>
              <InputLabel for="name" value="Semester Name" />
              <select
                id="name"
                v-model="form.name"
                class="w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500"
              >
                <option value="">Select Semester</option>
                <option value="First Semester">First Semester</option>
                <option value="Second Semester">Second Semester</option>
              </select>
              <InputError :message="form.errors.name" />
            </div>

            <div class="grid grid-cols-2 gap-4">
              <div>
                <InputLabel for="start_date" value="Start Date" />
                <TextInput
                  id="start_date"
                  v-model="form.start_date"
                  type="date"
                  class="w-full"
                />
                <InputError :message="form.errors.start_date" />
              </div>
              <div>
                <InputLabel for="end_date" value="End Date" />
                <TextInput
                  id="end_date"
                  v-model="form.end_date"
                  type="date"
                  class="w-full"
                />
                <InputError :message="form.errors.end_date" />
              </div>
            </div>
          </div>
          <div class="mt-6 flex justify-end space-x-3">
            <SecondaryButton @click.prevent="closeModal">
              Cancel
            </SecondaryButton>
            <PrimaryButton
              :disabled="form.processing"
              @click.prevent="createOrUpdateSemester"
            >
              {{ editingSemester ? "Update Semester" : "Create Semester" }}
            </PrimaryButton>
          </div>
        </div>
      </Modal>

      <!-- Delete Modal -->
      <Modal :show="showDeleteModal" @close="closeDeleteModal">
        <div class="p-6">
          <h2 class="text-lg font-semibold text-red-600 mb-2">
            Delete Semester
          </h2>
          <p class="text-sm text-gray-600 mb-4">
            Are you sure you want to delete the semester
            <span class="font-medium">{{ deletingSemester?.name }}</span>?
            This action cannot be undone.
          </p>
          <div class="flex justify-end space-x-3">
            <SecondaryButton @click.prevent="closeDeleteModal">
              Cancel
            </SecondaryButton>
            <PrimaryButton
              class="bg-red-500 hover:bg-red-600"
              :disabled="form.processing"
              @click.prevent="deleteSemester"
            >
              Delete Semester
            </PrimaryButton>
          </div>
        </div>
      </Modal>
    </div>
  </LayoutAuthenticated>
</template>
