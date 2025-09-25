<script setup>
import { Head, useForm, router, Link } from "@inertiajs/vue3";
import { ref, watch } from "vue";
import Modal from "@/Components/Modal.vue";
import toast from "@/Stores/toast";
import TextInput from "@/Components/TextInput.vue";
import InputError from "@/Components/InputError.vue";
import InputLabel from "@/Components/InputLabel.vue";
import SecondaryButton from "@/Components/SecondaryButton.vue";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import LayoutAuthenticated from "@/Layouts/LayoutAuthenticated.vue";
import SectionTitleLineWithButton from "@/Components/SectionTitleLineWithButton.vue";
import CardBox from "@/Components/CardBox.vue";
import PaginationLinks from "@/Components/PaginationLinks.vue";
import BaseButton from "@/Components/BaseButton.vue";
import { mdiShapePlus } from "@mdi/js";

const props = defineProps({
  sections: { type: Object, default: () => ({ data: [], meta: {}, links: [] }) },
  filters: { type: Object, default: () => ({}) },
  academicLevels: { type: Array, default: () => [] },
});

// Filters
const filterName = ref(props.filters.filterName || "");
const filterLevel = ref(props.filters.filterLevel || "");

watch([filterName, filterLevel], ([newName, newLevel]) => {
  router.get(
    route("section:all"),
    { filterName: newName, filterLevel: newLevel },
    { preserveState: true, replace: true }
  );
});

// Form
const form = useForm({ level_id: "", name: "" });
const confirmingSectionModal = ref(false);
const editingSection = ref(null);
const deletingSection = ref(null);

// Modal Handling
const showCreateModal = () => { 
  confirmingSectionModal.value = true; 
  form.reset(); 
  editingSection.value = null; 
  if (filterLevel.value) {
    form.level_id = filterLevel.value;
  }
};
const showEditModal = (section) => { editingSection.value = section; form.level_id = section.level_id; form.name = section.name; confirmingSectionModal.value = true; };
const closeModal = () => { confirmingSectionModal.value = false; form.reset(); editingSection.value = null; };

// CRUD
const createOrUpdateSection = () => {
  const url = editingSection.value
    ? route("section:update", { section: editingSection.value.id })
    : route("section:create");

  form.post(url, {
    preserveScroll: true,
    onSuccess: () => {
      closeModal();
      toast.add({ message: editingSection.value ? "Section updated!" : "Section created!" });
    },
    onError: () => form.reset(),
  });
};

const showDeleteSectionModal = (section) => { deletingSection.value = section; };
const closeDeleteModal = () => { deletingSection.value = null; };
const deleteSection = () => {
  if (!deletingSection.value) return;
  router.delete(route("section:delete", deletingSection.value), {
    preserveScroll: true,
    onSuccess: () => { closeDeleteModal(); toast.add({ message: "Section deleted!" }); },
  });
};

const toggleStatus = (section) => {
  router.post(
    route("section:toggle-status", { section: section.id }),
    {},
    {
      preserveScroll: true,
      onSuccess: () => {
        // Toggle the status locally
        section.status = section.status === 'active' ? 'inactive' : 'active';
        toast.add({ message: `Status updated to ${section.status === 'active' ? 'Active' : 'Inactive'}!` });
      },
    }
  );
};

const getLevelName = (levelId) => {
  const level = props.academicLevels.find(l => l.id == levelId);
  return level ? level.name : '';
};
</script>

<template>
  <LayoutAuthenticated>
    <Head title="Sections" />

    <!-- Header with button -->
    <div class="flex justify-between items-center mb-6">
      <SectionTitleLineWithButton :icon="mdiShapePlus" title="Sections" />
      <PrimaryButton @click="showCreateModal">
        + Add Section
      </PrimaryButton>
    </div>

    <div class="py-6 max-w-7xl mx-auto sm:px-6 lg:px-8">
      <!-- Breadcrumb or Filter Info -->
      <div v-if="filterLevel" class="mb-4">
        <p class="text-sm text-gray-600">
          Showing sections for: <span class="font-medium">{{ getLevelName(filterLevel) }}</span>
          <router-link to="/academic-level" class="text-blue-500 hover:underline ml-2">Back to Academic Levels</router-link>
        </p>
      </div>

      <!-- Filters -->
      <div class="mb-4 grid grid-cols-1 sm:grid-cols-2 gap-4">
        <div>
          <InputLabel for="filterName" value="Filter by Name" />
          <TextInput id="filterName" v-model="filterName" type="text" placeholder="Search sections" class="w-full" />
        </div>
        <div>
          <InputLabel for="filterLevel" value="Filter by Academic Level" />
          <select id="filterLevel" v-model="filterLevel" class="w-full border-gray-300 rounded">
            <option value="">All Academic Levels</option>
            <option v-for="level in props.academicLevels" :key="level.id" :value="level.id">{{ level.name }}</option>
          </select>
        </div>
      </div>

      <!-- Data Table -->
      <CardBox has-table>
        <div class="overflow-x-auto">
          <table class="text-sm w-full border-collapse">
            <thead class="bg-gray-100 text-left">
              <tr>
                <th class="px-3 py-2">#</th>
                <th class="px-3 py-2">Name</th>
                <th class="px-3 py-2">Academic Level</th>
                <th class="px-3 py-2">Status</th>
                <th class="px-3 py-2">Actions</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="(section, index) in props.sections.data" :key="section.id" class="hover:bg-gray-50">
                <td class="px-3 py-2">{{ index + 1 + (props.sections.per_page * (props.sections.current_page - 1)) }}</td>
                <td class="px-3 py-2">{{ section.name }}</td>
                <td class="px-3 py-2">{{ section.academic_level?.name }}</td>
                <td class="px-3 py-2">
                  <span :class="section.status === 'active' ? 'text-green-600 font-medium' : 'text-red-600'">
                    {{ section.status === 'active' ? 'Active' : 'Inactive' }}
                  </span>
                </td>
                <td class="px-3 py-2 space-x-2">
                  <button
                    @click.prevent="toggleStatus(section)"
                    :class="[
                      'px-3 py-1 rounded-full text-xs font-semibold',
                      section.status === 'active'
                        ? 'bg-green-100 text-green-700 border border-green-400'
                        : 'bg-gray-200 text-gray-600 border border-gray-300'
                    ]"
                  >
                    {{ section.status === 'active' ? 'Active' : 'Inactive' }}
                  </button>
                  <SecondaryButton @click.prevent="showEditModal(section)">
                    Edit
                  </SecondaryButton>
                  <PrimaryButton class="bg-red-600 hover:bg-red-700" @click.prevent="showDeleteSectionModal(section)">
                    Delete
                  </PrimaryButton>
                </td>
              </tr>
              <tr v-if="props.sections.data.length === 0">
                <td colspan="5" class="py-6 text-center text-gray-500">No sections found.</td>
              </tr>
            </tbody>
          </table>
        </div>
      </CardBox>

      <!-- Pagination -->
      <div v-if="props.sections.links?.length" class="p-3 lg:px-6 border-t border-gray-100 dark:border-slate-800">
        <PaginationLinks :links="props.sections.links" />
      </div>

      <!-- Modal: Create/Edit -->
      <Modal :show="confirmingSectionModal" @close="closeModal">
        <div class="p-6">
          <h2 class="text-lg font-medium text-gray-900">{{ editingSection ? 'Edit Section' : 'Add Section' }}</h2>
          <div class="mt-4">
            <InputLabel for="level_id" value="Academic Level" />
            <select id="level_id" v-model="form.level_id" class="w-full border-gray-300 rounded">
              <option value="">Select Academic Level</option>
              <option v-for="level in props.academicLevels" :key="level.id" :value="level.id">{{ level.name }}</option>
            </select>
            <InputError :message="form.errors.level_id" />
          </div>
          <div class="mt-4">
            <InputLabel for="name" value="Name" />
            <TextInput id="name" v-model="form.name" type="text" />
            <InputError :message="form.errors.name" />
          </div>
          <div class="mt-6 flex justify-end space-x-2">
            <SecondaryButton @click.prevent="closeModal">Cancel</SecondaryButton>
            <PrimaryButton :class="{ 'opacity-25': form.processing }" :disabled="form.processing" @click.prevent="createOrUpdateSection">
              {{ editingSection ? 'Update' : 'Add' }}
            </PrimaryButton>
          </div>
        </div>
      </Modal>

      <!-- Modal: Delete -->
      <Modal :show="!!deletingSection" @close="closeDeleteModal">
        <div class="p-6">
          <h2 class="text-lg font-medium text-red-600">Delete Section</h2>
          <p>Are you sure you want to delete this section?</p>
          <div class="mt-6 flex justify-end space-x-2">
            <SecondaryButton @click.prevent="closeDeleteModal">Cancel</SecondaryButton>
            <PrimaryButton class="bg-red-600 hover:bg-red-700" :class="{ 'opacity-25': form.processing }" :disabled="form.processing" @click.prevent="deleteSection">
              Delete
            </PrimaryButton>
          </div>
        </div>
      </Modal>
    </div>
  </LayoutAuthenticated>
</template>
