<script setup>
import { Head, useForm, router } from "@inertiajs/vue3";
import { ref, watch } from "vue";
import PaginationLinks from "@/Components/PaginationLinks.vue";
import Modal from "@/Components/Modal.vue";
import toast from "@/Stores/toast";
import TextInput from "@/Components/TextInput.vue";
import InputError from "@/Components/InputError.vue";
import InputLabel from "@/Components/InputLabel.vue";
import SecondaryButton from "@/Components/SecondaryButton.vue";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import LayoutAuthenticated from "@/Layouts/LayoutAuthenticated.vue";

const props = defineProps({
  programs: { type: Object, default: () => ({ data: [], meta: {} }) },
  filters: { type: Object, default: () => ({}) },
  academicYears: { type: Array, default: () => [] },
  programOptions: { type: Array, default: () => [] },
  defaultAcademicYear: { type: Object, default: () => null },
});

const filterName = ref(props.filters.filterName || "");
const filterAcademicYear = ref(props.filters.filterAcademicYear || (props.defaultAcademicYear ? props.defaultAcademicYear.id : ""));

watch([filterName, filterAcademicYear], ([newName, newYear]) => {
  router.get(
    route("academic_program:all"),
    { filterName: newName, filterAcademicYear: newYear },
    { preserveState: true, replace: true }
  );
});

const form = useForm({
  academic_year_id: "",
  names: [],
  status: "active",
});

const confirmingProgramCreation = ref(false);
const editingProgram = ref(null);
const showDeleteModal = ref(false);
const deletingProgram = ref(null);

const showCreateModal = () => {
  confirmingProgramCreation.value = true;
  form.reset();
  form.academic_year_id = props.defaultAcademicYear ? props.defaultAcademicYear.id : "";
  editingProgram.value = null;
};

const showEditModal = (program) => {
  editingProgram.value = program;
  form.academic_year_id = program.academic_year_id;
  form.names = [program.name];
  form.status = program.status;
  confirmingProgramCreation.value = true;
};

const closeModal = () => {
  confirmingProgramCreation.value = false;
  form.reset();
  editingProgram.value = null;
};

const createOrUpdateProgram = () => {
  if (editingProgram.value) {
    form.post(
      route("academic_program:update", { academicProgram: editingProgram.value.id }),
      {
        preserveScroll: true,
        onSuccess: () => {
          closeModal();
          toast.add({ message: "✅ Academic Program updated!" });
        },
      }
    );
  } else {
    form.post(route("academic_program:create"), {
      preserveScroll: true,
      onSuccess: () => {
        closeModal();
        toast.add({ message: "✅ Academic Program created!" });
      },
    });
  }
};

const showDeleteProgramModal = (program) => {
  showDeleteModal.value = true;
  deletingProgram.value = program;
};

const closeDeleteModal = () => {
  showDeleteModal.value = false;
  deletingProgram.value = null;
};

const deleteProgram = () => {
  router.delete(route("academic_program:delete", deletingProgram.value), {
    preserveScroll: true,
    onSuccess: () => {
      closeDeleteModal();
      toast.add({ message: "🗑️ Academic Program deleted!" });
    },
  });
};

const toggleStatus = (program) => {
  router.post(
    route("academic_program:toggle-status", { academicProgram: program.id }),
    {},
    {
      preserveScroll: true,
      onSuccess: () => {
        // Toggle the status locally
        program.status = program.status === 'active' ? 'inactive' : 'active';
        toast.add({ message: `Status updated to ${program.status === 'active' ? 'Active' : 'Inactive'}!` });
      },
    }
  );
};
</script>

<template>
  <LayoutAuthenticated>

    <Head title="Academic Programs" />

    <div class="py-6 max-w-7xl mx-auto sm:px-6 lg:px-8">
      <!-- Page Header -->
      <div class="flex justify-between items-center mb-6">
        <h1 class="text-xl font-semibold text-gray-800">Academic Programs</h1>
        <PrimaryButton @click="showCreateModal">+ Add Program</PrimaryButton>
      </div>

      <!-- Filters -->
      <div class="mb-6 grid grid-cols-1 md:grid-cols-2 gap-4 bg-white p-4 rounded-lg shadow">
        <div>
          <InputLabel for="filterName" value="Search by Name" />
          <TextInput id="filterName" v-model="filterName" type="text" placeholder="Search academic programs..."
            class="w-full" />
        </div>
        <div>
          <InputLabel for="filterAcademicYear" value="Filter by Academic Year" />
          <select id="filterAcademicYear" v-model="filterAcademicYear"
            class="w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
            <option value="">All Years</option>
            <option v-for="year in props.academicYears" :key="year.id" :value="year.id">
              {{ year.name }}
              <span v-if="year.status === 'active'" class="ml-1 text-xs text-green-600">(Active)</span>
            </option>
          </select>
        </div>
      </div>

      <!-- Table -->
      <div class="bg-white shadow rounded-lg overflow-hidden">
        <table class="w-full text-sm text-center">
          <thead class="bg-gray-100 text-gray-700 uppercase text-xs">
            <tr>
              <th class="px-4 py-3">#</th>
              <th class="px-4 py-3">Program Name</th>
              <th class="px-4 py-3">Academic Year</th>
              <th class="px-4 py-3">Status</th>
              <th class="px-4 py-3">Actions</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="(program, index) in props.programs.data" :key="program.id" class="hover:bg-gray-50 transition">
              <td class="px-4 py-2 border-b">
                {{ index + 1 + (props.programs.per_page * (props.programs.current_page - 1)) }}
              </td>
              <td class="px-4 py-2 border-b">{{ program.name }}</td>
              <td class="px-4 py-2 border-b">
                <span :class="program.academic_year.status === 'active' ? 'text-green-600 font-medium' : ''">
                  {{ program.academic_year.name }}
                  <span v-if="program.academic_year.status === 'active'" class="ml-1 text-xs">(Active)</span>
                </span>
              </td>
              <td class="px-4 py-2 border-b">
                <span :class="program.status === 'active' ? 'text-green-600 font-medium' : 'text-red-600'">
                  {{ program.status === 'active' ? 'Active' : 'Inactive' }}
                </span>
              </td>
              <td class="px-4 py-2 border-b space-x-2">
                <button @click.prevent="toggleStatus(program)" :class="[
                  'px-3 py-1 rounded-full text-xs font-semibold',
                  program.status === 'active'
                    ? 'bg-green-100 text-green-700 border border-green-400'
                    : 'bg-gray-200 text-gray-600 border border-gray-300'
                ]">
                  {{ program.status === 'active' ? 'Active' : 'Inactive' }}
                </button>
                <button @click.prevent="showEditModal(program)"
                  class="px-3 py-1 rounded-md bg-blue-500 text-white text-xs hover:bg-blue-600">
                  Edit
                </button>
                <button @click.prevent="showDeleteProgramModal(program)"
                  class="px-3 py-1 rounded-md bg-red-500 text-white text-xs hover:bg-red-600">
                  Delete
                </button>
              </td>
            </tr>
            <tr v-if="props.programs.data.length === 0">
              <td colspan="4" class="py-6 text-gray-500 text-sm">
                No academic programs found.
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- Pagination -->
      <div class="p-4 border-t bg-gray-50">
        <PaginationLinks :links="props.programs.links" />
      </div>

      <!-- Create/Edit Modal -->
      <Modal :show="confirmingProgramCreation" @close="closeModal">
        <div class="p-6">
          <h2 class="text-lg font-semibold text-gray-900 mb-4">
            {{ editingProgram ? "Edit Academic Program" : "Add Academic Program" }}
          </h2>
          <div class="space-y-4">
            <div>
              <InputLabel for="academic_year_id" value="Academic Year" />
              <select id="academic_year_id" v-model="form.academic_year_id"
                class="w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
                <option value="">Select Year</option>
                <option v-for="year in props.academicYears" :key="year.id" :value="year.id">
                  {{ year.name }}
                  <span v-if="year.status === 'active'" class="ml-1 text-xs text-green-600">(Active)</span>
                </option>
              </select>
              <InputError :message="form.errors.academic_year_id" />
            </div>
            <div>
              <InputLabel for="names" value="Program Names" />
              <select id="names" v-model="form.names" multiple
                class="w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500"
                style="min-height: 120px;">
                <option v-for="option in props.programOptions" :key="option" :value="option">
                  {{ option }}
                </option>
              </select>
              <p class="text-xs text-gray-500 mt-1">Hold Ctrl/Cmd to select multiple programs</p>
              <InputError :message="form.errors.names" />
            </div>
            <div>
              <InputLabel for="status" value="Status" />
              <select id="status" v-model="form.status"
                class="w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
                <option value="active">Active</option>
                <option value="inactive">Inactive</option>
              </select>
              <InputError :message="form.errors.status" />
            </div>

          </div>
          <div class="mt-6 flex justify-end space-x-2">
            <SecondaryButton @click.prevent="closeModal">Cancel</SecondaryButton>
            <PrimaryButton :disabled="form.processing" @click.prevent="createOrUpdateProgram">
              {{ editingProgram ? "Update" : "Create" }}
            </PrimaryButton>
          </div>
        </div>
      </Modal>

      <!-- Delete Modal -->
      <Modal :show="showDeleteModal" @close="closeDeleteModal">
        <div class="p-6">
          <h2 class="text-lg font-semibold text-red-600 mb-2">
            Delete Academic Program
          </h2>
          <p class="text-sm text-gray-600">
            Are you sure you want to delete this academic program? This action
            cannot be undone.
          </p>
          <div class="mt-6 flex justify-end space-x-2">
            <SecondaryButton @click.prevent="closeDeleteModal">Cancel</SecondaryButton>
            <PrimaryButton class="bg-red-500 hover:bg-red-600" :disabled="form.processing"
              @click.prevent="deleteProgram">
              Delete
            </PrimaryButton>
          </div>
        </div>
      </Modal>
    </div>
  </LayoutAuthenticated>
</template>
