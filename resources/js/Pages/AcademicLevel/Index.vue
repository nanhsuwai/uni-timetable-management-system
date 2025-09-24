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
  levels: { type: Object, default: () => ({ data: [], meta: {} }) },
  filters: { type: Object, default: () => ({}) },
  academicPrograms: { type: Array, default: () => [] },
});

// Filters
const filterName = ref(props.filters.filterName || "");
const filterProgram = ref(props.filters.filterProgram || "");

watch([filterName, filterProgram], ([newName, newProgram]) => {
  router.get(
    route("academic_level:all"),
    { filterName: newName, filterProgram: newProgram },
    { preserveState: true, replace: true }
  );
});

// Form
const form = useForm({
  program_id: "",
  name: "",
});

// State
const confirmingModal = ref(false);
const editingLevel = ref(null);
const deletingLevel = ref(null);

// Modal Handling
const openCreateModal = () => {
  confirmingModal.value = true;
  form.reset();
  editingLevel.value = null;
};

const openEditModal = (level) => {
  editingLevel.value = level;
  form.program_id = level.program_id;
  form.name = level.name;
  confirmingModal.value = true;
};

const closeModal = () => {
  confirmingModal.value = false;
  form.reset();
  editingLevel.value = null;
};

// CRUD
const saveLevel = () => {
  const url = editingLevel.value
    ? route("academic_level:update", { academicLevel: editingLevel.value.id })
    : route("academic_level:create");

  form.post(url, {
    preserveScroll: true,
    onSuccess: () => {
      closeModal();
      toast.add({
        message: editingLevel.value
          ? "✅ Academic Level updated!"
          : "✅ Academic Level created!",
      });
    },
    onError: () => form.reset(),
  });
};

const confirmDelete = (level) => {
  deletingLevel.value = level;
};

const deleteLevel = () => {
  if (!deletingLevel.value) return;
  router.delete(route("academic_level:delete", deletingLevel.value), {
    preserveScroll: true,
    onSuccess: () => {
      deletingLevel.value = null;
      toast.add({ message: "🗑️ Academic Level deleted!" });
    },
  });
};
</script>

<template>
  <LayoutAuthenticated>
    <Head title="Academic Levels" />

    <div class="py-6 max-w-7xl mx-auto sm:px-6 lg:px-8">
      <!-- Header -->
      <div class="flex justify-between items-center mb-6">
        <h1 class="text-xl font-semibold text-gray-800">Academic Levels</h1>
        <PrimaryButton @click="openCreateModal">+ Add Level</PrimaryButton>
      </div>

      <!-- Filters -->
      <div class="mb-6 grid grid-cols-1 sm:grid-cols-2 gap-4 bg-white p-4 rounded-lg shadow">
        <div>
          <InputLabel for="filterName" value="Filter by Name" />
          <TextInput
            id="filterName"
            v-model="filterName"
            type="text"
            placeholder="Search levels..."
            class="w-full"
          />
        </div>
        <div>
          <InputLabel for="filterProgram" value="Filter by Program" />
          <select
            id="filterProgram"
            v-model="filterProgram"
            class="w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500"
          >
            <option value="">All Programs</option>
            <option
              v-for="program in props.academicPrograms"
              :key="program.id"
              :value="program.id"
            >
              {{ program.name }}
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
              <th class="px-4 py-3">Name</th>
              <th class="px-4 py-3">Academic Program</th>
              <th class="px-4 py-3">Actions</th>
            </tr>
          </thead>
          <tbody>
            <tr
              v-for="(level, index) in props.levels.data"
              :key="level.id"
              class="hover:bg-gray-50 transition"
            >
              <td class="px-4 py-2 border-b">
                {{ index + 1 + (props.levels.per_page * (props.levels.current_page - 1)) }}
              </td>
              <td class="px-4 py-2 border-b">{{ level.name }}</td>
              <td class="px-4 py-2 border-b">{{ level.academic_program.name }}</td>
              <td class="px-4 py-2 border-b space-x-2">
                <button
                  @click.prevent="openEditModal(level)"
                  class="px-3 py-1 rounded-md bg-blue-500 text-white text-xs hover:bg-blue-600"
                >
                  Edit
                </button>
                <button
                  @click.prevent="confirmDelete(level)"
                  class="px-3 py-1 rounded-md bg-red-500 text-white text-xs hover:bg-red-600"
                >
                  Delete
                </button>
              </td>
            </tr>
            <tr v-if="props.levels.data.length === 0">
              <td colspan="4" class="py-6 text-gray-500 text-sm">
                No academic levels found.
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- Pagination -->
      <div class="p-4 border-t bg-gray-50">
        <PaginationLinks :links="props.levels.links" />
      </div>

      <!-- Create/Edit Modal -->
      <Modal :show="confirmingModal" @close="closeModal">
        <div class="p-6">
          <h2 class="text-lg font-semibold text-gray-900 mb-4">
            {{ editingLevel ? "Edit Academic Level" : "Add Academic Level" }}
          </h2>
          <div class="space-y-4">
            <div>
              <InputLabel for="program_id" value="Academic Program" />
              <select
                id="program_id"
                v-model="form.program_id"
                class="w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500"
              >
                <option value="">Select Program</option>
                <option
                  v-for="program in props.academicPrograms"
                  :key="program.id"
                  :value="program.id"
                >
                  {{ program.name }}
                </option>
              </select>
              <InputError :message="form.errors.program_id" />
            </div>
            <div>
              <InputLabel for="name" value="Level Name" />
              <TextInput id="name" v-model="form.name" type="text" class="w-full" />
              <InputError :message="form.errors.name" />
            </div>
          </div>
          <div class="mt-6 flex justify-end space-x-2">
            <SecondaryButton @click.prevent="closeModal">Cancel</SecondaryButton>
            <PrimaryButton
              :disabled="form.processing"
              @click.prevent="saveLevel"
            >
              {{ editingLevel ? "Update" : "Add" }}
            </PrimaryButton>
          </div>
        </div>
      </Modal>

      <!-- Delete Modal -->
      <Modal :show="!!deletingLevel" @close="() => (deletingLevel = null)">
        <div class="p-6">
          <h2 class="text-lg font-semibold text-red-600 mb-2">Delete Academic Level</h2>
          <p class="text-sm text-gray-600">
            Are you sure you want to delete this academic level? This action cannot be undone.
          </p>
          <div class="mt-6 flex justify-end space-x-2">
            <SecondaryButton @click.prevent="() => (deletingLevel = null)">
              Cancel
            </SecondaryButton>
            <PrimaryButton
              class="bg-red-500 hover:bg-red-600"
              :disabled="form.processing"
              @click.prevent="deleteLevel"
            >
              Delete
            </PrimaryButton>
          </div>
        </div>
      </Modal>
    </div>
  </LayoutAuthenticated>
</template>
