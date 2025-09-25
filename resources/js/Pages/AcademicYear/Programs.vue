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
  programOptions: { type: Array, default: () => [] },
  academicYear: { type: Object, default: () => ({}) },
});

const filterName = ref(props.filters.filterName || "");

watch(filterName, (newName) => {
  router.get(
    route("academic_program:all"),
    { filterName: newName },
    { preserveState: true, replace: true }
  );
});

const form = useForm({
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
  editingProgram.value = null;
};

const showEditModal = (program) => {
  editingProgram.value = program;
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
    // ✅ Use update route when editing
    form.post(
      route("academic-year:programs:update", {
        academicYear: form.academic_year_id,
        academicProgram: editingProgram.value.id
      }),
      {
        preserveScroll: true,
        onSuccess: () => {
          closeModal();
          toast.add({ message: "✅ Academic Program updated!" });
        },
      }
    );
  } else {
    // ✅ Use create route when adding new
    form.post(
      route("academic-year:programs:create", {
        academicYear: props.academicYear.id
      }),
      {
        preserveScroll: true,
        onSuccess: () => {
          closeModal();
          toast.add({ message: "✅ Academic Program created!" });
        },
      }
    );
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

      <!-- Header with Academic Year Info -->
      <div class="flex justify-between items-center mb-6">
        <div class="flex items-center space-x-4">
          <PrimaryButton class="bg-gray-200 text-gray-700 hover:bg-gray-300"
            @click="$inertia.visit(route('academic-year:all'))">
            ← Back to Academic Years
          </PrimaryButton>
          <div>
            <h1 class="text-xl font-semibold text-gray-800">Academic Programs</h1>
            <p class="text-sm text-gray-500 mt-1">
              Academic Year:
              <span :class="props.academicYear.status === 'active' ? 'text-green-600 font-medium' : ''">
                {{ props.academicYear.name }}
                <span v-if="props.academicYear.status === 'active'" class="ml-1 text-xs">(Active)</span>
              </span>
            </p>
          </div>
        </div>
        <PrimaryButton @click="showCreateModal">+ Add Program</PrimaryButton>
      </div>


      <!-- Filter by Name -->
      <div class="mb-6 bg-white p-4 rounded-lg shadow">
        <InputLabel for="filterName" value="Search by Name" />
        <TextInput id="filterName" v-model="filterName" type="text" placeholder="Search academic programs..."
          class="w-full" />
      </div>

      <!-- Programs Table -->
      <div class="bg-white shadow rounded-lg overflow-hidden">
        <table class="w-full text-sm text-center">
          <thead class="bg-gray-100 text-gray-700 uppercase text-xs">
            <tr>
              <th class="px-4 py-3">#</th>
              <th class="px-4 py-3">Program Name</th>
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
                <span :class="program.status === 'active' ? 'text-green-600 font-medium' : 'text-red-600'">
                  {{ program.status === 'active' ? 'Active' : 'Inactive' }}
                </span>
              </td>
              <td class="px-4 py-2 border-b space-x-2 flex justify-center items-center">
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
            Are you sure you want to delete this academic program? This action cannot be undone.
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
