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
    <!-- Header -->
    

    <div class="py-6 max-w-7xl mx-auto sm:px-6 lg:px-8">
      <!-- Filter -->
       <SectionTitleLineWithButton :icon="mdiShapePlus" title="Academic Years" class="mx-auto">
      <PrimaryButton @click.prevent="showCreateModal">
        + Add Academic Year
      </PrimaryButton>
    </SectionTitleLineWithButton>
      <div class="mb-6">
        <InputLabel for="filterName" value="Search Academic Year" />
        <TextInput id="filterName" v-model="filterName" type="text" placeholder="e.g., 2024-2025" class="w-full" />
      </div>

      <!-- Table -->
      <CardBox has-table>
        <table class="w-full text-sm text-left border-collapse">
          <thead class="bg-gray-100">
            <tr>
              <th class="px-4 py-2 text-center">#</th>
              <th class="px-4 py-2">Name</th>
              <th class="px-4 py-2">Start Date</th>
              <th class="px-4 py-2">End Date</th>
              <th class="px-4 py-2 text-center">Status</th>
              <th class="px-4 py-2 text-center">Actions</th>
              <th class="px-4 py-2 text-center">Semesters</th>
              <th class="px-4 py-2 text-center">Programs</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="(academicYear, index) in props.academicYears.data" :key="academicYear.id"
              class="border-t hover:bg-gray-50">
              <td class="px-4 py-2 text-center">
                {{ index + 1 + (props.academicYears.per_page * (props.academicYears.current_page - 1)) }}
              </td>
              <td class="px-4 py-2 font-medium">{{ academicYear.name }}</td>
              <td class="px-4 py-2">{{ academicYear.start_date }}</td>
              <td class="px-4 py-2">{{ academicYear.end_date }}</td>
              <td class="px-4 py-2 text-center">
                <button @click.prevent="toggleStatus(academicYear)" :class="[
                  'px-3 py-1 rounded-full text-xs font-semibold',
                  academicYear.status === 'active'
                    ? 'bg-green-100 text-green-700 border border-green-400'
                    : 'bg-gray-200 text-gray-600 border border-gray-300'
                ]">
                  {{ academicYear.status === 'active' ? 'Active' : 'Inactive' }}
                </button>
              </td>
              <td class="px-4 py-2 flex justify-center gap-2">
                <button @click.prevent="showEditModal(academicYear)"
                  class="px-2 py-1 rounded text-blue-600 border border-blue-600 hover:bg-blue-600 hover:text-white text-xs">
                  Edit
                </button>
                <button @click.prevent="showDeleteAcademicYearModal(academicYear)"
                  class="px-2 py-1 rounded text-red-600 border border-red-600 hover:bg-red-600 hover:text-white text-xs">
                  Delete
                </button>
              </td>
              <td class="px-4 py-2 text-center">
                <button @click.prevent="openSemesterModal(academicYear)"
                  class="px-2 py-1 rounded text-green-600 border border-green-600 hover:bg-green-600 hover:text-white text-xs">
                  Semesters ({{ academicYear.semesters ? academicYear.semesters.length : 0 }})
                </button>
              </td>
              <td class="px-4 py-2 text-center">
                <button @click.prevent="$inertia.visit(route('academic-year:programs', academicYear.id))"
                  class="px-2 py-1 rounded text-purple-600 border border-purple-600 hover:bg-purple-600 hover:text-white text-xs">
                  Programs ({{ academicYear.academic_programs ? academicYear.academic_programs.length : 0 }})
                </button>
              </td>

            </tr>
          </tbody>
        </table>
      </CardBox>

      <!-- Pagination -->
      <div class="mt-4 p-3 lg:px-6 border-t border-gray-100 dark:border-slate-800">
        <PaginationLinks :links="props.academicYears.links" />
      </div>

      <!-- Create / Edit Modal -->
      <Modal :show="confirmingAcademicYearCreation" @close="closeModal">
        <div class="p-6">
          <h2 class="text-xl font-semibold text-gray-800 border-b pb-3 mb-4">
            {{ editingAcademicYear ? "Edit Academic Year" : "Add New Academic Year" }}
          </h2>

          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
              <InputLabel for="name" value="Name" />
              <TextInput id="name" v-model="form.name" type="text" placeholder="e.g., Academic Year 2024-2025" />
              <InputError :message="form.errors.name" />
            </div>

            <div>
              <InputLabel for="start_date" value="Start Date" />
              <TextInput id="start_date" v-model="form.start_date" type="date" />
              <InputError :message="form.errors.start_date" />
            </div>

            <div>
              <InputLabel for="end_date" value="End Date" />
              <TextInput id="end_date" v-model="form.end_date" type="date" />
              <InputError :message="form.errors.end_date" />
            </div>
          </div>

          <div class="mt-6 flex justify-end gap-3 border-t pt-4">
            <SecondaryButton @click.prevent="closeModal">Cancel</SecondaryButton>
            <PrimaryButton :class="{ 'opacity-25': form.processing }" :disabled="form.processing"
              @click.prevent="createOrUpdateAcademicYear">
              {{ editingAcademicYear ? "Update" : "Create" }}
            </PrimaryButton>
          </div>
        </div>
      </Modal>

      <!-- Delete Modal -->
      <Modal :show="showDeleteModal" @close="closeDeleteModal">
        <div class="p-6">
          <h2 class="text-lg font-semibold text-red-600 mb-2">Delete Academic Year</h2>
          <p class="text-gray-700">Are you sure you want to delete this academic year? This action cannot be undone.</p>
          <div class="mt-6 flex justify-end gap-3">
            <SecondaryButton @click.prevent="closeDeleteModal">Cancel</SecondaryButton>
            <PrimaryButton class="bg-red-600 hover:bg-red-700 text-white" :class="{ 'opacity-25': form.processing }"
              :disabled="form.processing" @click.prevent="deleteAcademicYear">
              Delete
            </PrimaryButton>
          </div>
        </div>
      </Modal>

      <!-- Semester Modal -->
      <Modal :show="confirmingSemesterModal" @close="closeSemesterModal" max-width="2xl">
        <div class="p-6">
          <h2 class="text-lg font-semibold text-gray-900 mb-4">
            Semesters for {{ selectedAcademicYear?.name }}
          </h2>

          <!-- Add Semester Button -->
          <div class="mb-4">
            <PrimaryButton @click.prevent="openEditSemesterModal(null)">
              + Add Semester
            </PrimaryButton>
          </div>

          <!-- Semester List -->
          <div class="overflow-x-auto">
            <table class="w-full text-sm text-left border-collapse border border-gray-300">
              <thead class="bg-gray-100">
                <tr>
                  <th class="px-4 py-2 border border-gray-300">#</th>
                  <th class="px-4 py-2 border border-gray-300">Name</th>
                  <th class="px-4 py-2 border border-gray-300">Start Date</th>
                  <th class="px-4 py-2 border border-gray-300">End Date</th>
                  <th class="px-4 py-2 border border-gray-300 text-center">Status</th>
                  <th class="px-4 py-2 border border-gray-300 text-center">Actions</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="(semester, index) in selectedAcademicYear?.semesters" :key="semester.id"
                  class="border-t hover:bg-gray-50">
                  <td class="px-4 py-2 border border-gray-300">{{ index + 1 }}</td>
                  <td class="px-4 py-2 border border-gray-300">{{ semester.name }}</td>
                  <td class="px-4 py-2 border border-gray-300">{{ semester.start_date }}</td>
                  <td class="px-4 py-2 border border-gray-300">{{ semester.end_date }}</td>
                  <td class="px-4 py-2 border border-gray-300 text-center">
                    <button @click.prevent="toggleSemesterStatus(semester)" :class="[
                      'px-3 py-1 rounded-full text-xs font-semibold',
                      semester.status === 'active'
                        ? 'bg-green-100 text-green-700 border border-green-400'
                        : 'bg-gray-200 text-gray-600 border border-gray-300'
                    ]">
                      {{ semester.status === 'active' ? 'Active' : 'Inactive' }}
                    </button>
                  </td>
                  <td class="px-4 py-2 border border-gray-300 flex justify-center gap-2">
                    <button @click.prevent="openEditSemesterModal(semester)"
                      class="px-2 py-1 rounded text-blue-600 border border-blue-600 hover:bg-blue-600 hover:text-white text-xs">
                      Edit
                    </button>
                    <button @click.prevent="confirmDeleteSemester(semester)"
                      class="px-2 py-1 rounded text-red-600 border border-red-600 hover:bg-red-600 hover:text-white text-xs">
                      Delete
                    </button>
                  </td>
                </tr>
                <tr v-if="!selectedAcademicYear?.semesters?.length">
                  <td colspan="6" class="px-4 py-2 text-center text-gray-500">No semesters found.</td>
                </tr>
              </tbody>
            </table>
          </div>

          <div class="mt-6 flex justify-end">
            <SecondaryButton @click.prevent="closeSemesterModal">Close</SecondaryButton>
          </div>
        </div>
      </Modal>

      <!-- Semester Create/Edit Modal -->
      <Modal :show="showingSemesterFormModal" @close="closeEditSemesterModal">
        <div class="p-6">
          <h2 class="text-lg font-semibold text-gray-900 mb-4">
            {{ editingSemester ? "Edit Semester" : "Add Semester" }}
          </h2>
          <div class="space-y-4">
            <div>
              <InputLabel for="semester_name" value="Semester Name" />
              <select id="semester_name" v-model="semesterForm.name"
                class="w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
                <option value="">Select Semester</option>
                <option value="First Semester">First Semester</option>
                <option value="Second Semester">Second Semester</option>
              </select>
              <InputError :message="semesterForm.errors.name" />
            </div>
            <div>
              <InputLabel for="semester_start_date" value="Start Date" />
              <TextInput id="semester_start_date" v-model="semesterForm.start_date" type="date" class="w-full" />
              <InputError :message="semesterForm.errors.start_date" />
            </div>
            <div>
              <InputLabel for="semester_end_date" value="End Date" />
              <TextInput id="semester_end_date" v-model="semesterForm.end_date" type="date" class="w-full" />
              <InputError :message="semesterForm.errors.end_date" />
            </div>
          </div>
          <div class="mt-6 flex justify-end space-x-2">
            <SecondaryButton @click.prevent="closeEditSemesterModal">Cancel</SecondaryButton>
            <PrimaryButton :disabled="semesterForm.processing" @click.prevent="saveSemester">
              {{ editingSemester ? "Update" : "Add" }}
            </PrimaryButton>
          </div>
        </div>
      </Modal>

      <!-- Delete Semester Modal -->
      <Modal :show="!!deletingSemester" @close="() => (deletingSemester = null)">
        <div class="p-6">
          <h2 class="text-lg font-semibold text-red-600 mb-2">Delete Semester</h2>
          <p class="text-sm text-gray-600">
            Are you sure you want to delete this semester? This action cannot be undone.
          </p>
          <div class="mt-6 flex justify-end space-x-2">
            <SecondaryButton @click.prevent="() => (deletingSemester = null)">
              Cancel
            </SecondaryButton>
            <PrimaryButton class="bg-red-500 hover:bg-red-600" :disabled="semesterForm.processing"
              @click.prevent="deleteSemester">
              Delete
            </PrimaryButton>
          </div>
        </div>
      </Modal>
    </div>
  </LayoutAuthenticated>
</template>
