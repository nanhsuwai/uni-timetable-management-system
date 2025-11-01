<script setup>
import { Head, useForm, router } from "@inertiajs/vue3";
import { ref, computed, watch } from "vue";
import LayoutAuthenticated from "@/Layouts/LayoutAuthenticated.vue";
import Modal from "@/Components/Modal.vue";
import toast from "@/Stores/toast";
import TextInput from "@/Components/TextInput.vue";
import InputError from "@/Components/InputError.vue";
import InputLabel from "@/Components/InputLabel.vue";
import SecondaryButton from "@/Components/SecondaryButton.vue";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import SectionTitleLineWithButton from "@/Components/SectionTitleLineWithButton.vue";
import CardBox from "@/Components/CardBox.vue";
import PaginationLinks from "@/Components/PaginationLinks.vue";
import { mdiShapePlus } from "@mdi/js";

const props = defineProps({
  sections: { type: Object, default: () => ({ data: [], meta: {}, links: [] }) },
  filters: { type: Object, default: () => ({}) },
  academicLevels: { type: Array, default: () => [] },
  teachers: { type: Array, default: () => [] },
});

// ------------------------------------------------------
// 🌸 Filters
// ------------------------------------------------------
const filterName = ref(props.filters.filterName || "");
const filterLevel = ref(props.filters.filterLevel || "");

// Automatically refresh sections when filters change
watch([filterName, filterLevel], ([newName, newLevel]) => {
  router.get(
    route("section:all"),
    { filterName: newName, filterLevel: newLevel },
    { preserveState: true, replace: true }
  );
});

// ------------------------------------------------------
// 🧩 Computed: Only show approved teachers
// ------------------------------------------------------
const approvedTeachers = computed(() =>
  props.teachers.filter((t) => t.status === "approved")
);

// ------------------------------------------------------
// 📋 Form & Modal Handling
// ------------------------------------------------------
const form = useForm({
  level_id: "",
  name: "",
  section_head_teacher_id: "",
});

const confirmingSectionModal = ref(false);
const editingSection = ref(null);
const deletingSection = ref(null);

const showCreateModal = () => {
  form.reset();
  editingSection.value = null;
  confirmingSectionModal.value = true;
  if (filterLevel.value) form.level_id = filterLevel.value;
};

const showEditModal = (section) => {
  editingSection.value = section;
  confirmingSectionModal.value = true;
  form.fill({
    level_id: section.level_id,
    name: section.name,
    section_head_teacher_id: section.section_head_teacher_id || "",
  });
};

const closeModal = () => {
  confirmingSectionModal.value = false;
  editingSection.value = null;
  form.reset();
};

// ------------------------------------------------------
// 💾 CRUD Operations
// ------------------------------------------------------
const createOrUpdateSection = () => {
  const url = editingSection.value
    ? route("section:update", { section: editingSection.value.id })
    : route("section:create");

  form.post(url, {
    preserveScroll: true,
    onSuccess: () => {
      closeModal();
      toast.add({
        message: editingSection.value
          ? "✅ Section updated successfully!"
          : "✨ Section created successfully!",
      });
    },
    onError: () => {
      toast.add({
        message: "⚠️ Please check the form for errors.",
        type: "error",
      });
    },
  });
};

const showDeleteSectionModal = (section) => (deletingSection.value = section);

const closeDeleteModal = () => (deletingSection.value = null);

const deleteSection = () => {
  if (!deletingSection.value) return;

  router.delete(route("section:delete", deletingSection.value), {
    preserveScroll: true,
    onSuccess: () => {
      closeDeleteModal();
      toast.add({ message: "🗑️ Section deleted successfully!" });
    },
  });
};

const toggleStatus = (section) => {
  router.post(route("section:toggle-status", { section: section.id }), {}, {
    preserveScroll: true,
    onSuccess: () => {
      section.status = section.status === "active" ? "inactive" : "active";
      toast.add({
        message: `Section is now ${section.status === "active" ? "🟢 Active" : "🔴 Inactive"}.`,
      });
    },
  });
};

// ------------------------------------------------------
// 📚 Helper
// ------------------------------------------------------
const getLevelName = (levelId) => {
  const level = props.academicLevels.find((l) => l.id == levelId);
  return level ? level.name : "";
};
</script>


<template>
  
  <LayoutAuthenticated>
    <Head title="Sections" />
    <pre class="text-xs text-gray-500">{{ props.teachers }}</pre>
    <!-- 🔹 Page Header -->
    <div class="flex justify-between items-center mb-8">
      <SectionTitleLineWithButton :icon="mdiShapePlus" title="Sections" />
      <PrimaryButton @click="showCreateModal" class="!px-5 !py-2">
        + Add Section
      </PrimaryButton>
    </div>

    <div class="py-6 max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
      <!-- 🔹 Active Filter Info -->
      <div v-if="filterLevel" class="mb-2 text-sm text-gray-700">
        Showing sections for:
        <span class="font-semibold text-gray-900">
          {{ getLevelName(filterLevel) }}
        </span>
        <router-link
          to="/academic-level"
          class="ml-2 text-blue-600 hover:underline"
        >
          Back to Academic Levels
        </router-link>
      </div>

      <!-- 🔹 Filters -->
      <div
        class="grid grid-cols-1 sm:grid-cols-2 gap-4 bg-white dark:bg-slate-800 p-4 rounded-lg shadow-sm border border-gray-100"
      >
        <div>
          <InputLabel for="filterName" value="Filter by Name" />
          <TextInput
            id="filterName"
            v-model="filterName"
            type="text"
            placeholder="Search sections..."
            class="w-full"
          />
        </div>
        <div>
          <InputLabel for="filterLevel" value="Filter by Academic Level" />
          <select
            id="filterLevel"
            v-model="filterLevel"
            class="w-full border-gray-300 rounded-md text-sm py-2 focus:ring focus:ring-blue-200"
          >
            <option value="">All Academic Levels</option>
            <option
              v-for="level in props.academicLevels"
              :key="level.id"
              :value="level.id"
            >
              {{ level.name }}
            </option>
          </select>
        </div>
      </div>

      <!-- 🔹 Sections Table -->
      <CardBox has-table>
        <div class="overflow-x-auto">
          <table class="w-full text-sm text-left border-collapse">
            <thead class="bg-gray-100 text-gray-700 uppercase text-xs">
              <tr>
                <th class="px-3 py-2 font-semibold">#</th>
                <th class="px-3 py-2 font-semibold">Name</th>
                <th class="px-3 py-2 font-semibold">Academic Level</th>
                <th class="px-3 py-2 font-semibold">Section Head Teacher</th>
                <th class="px-3 py-2 font-semibold">Status</th>
                <th class="px-3 py-2 font-semibold text-center">Actions</th>
              </tr>
            </thead>

            <tbody>
              <tr
                v-for="(section, index) in props.sections.data"
                :key="section.id"
                class="hover:bg-gray-50 transition-colors"
              >
                <td class="px-3 py-2">
                  {{ index + 1 + (props.sections.per_page * (props.sections.current_page - 1)) }}
                </td>
                <td class="px-3 py-2 font-medium">{{ section.name }}</td>
                <td class="px-3 py-2">{{ section.academic_level?.name }}</td>
                <td class="px-3 py-2">
                  <span
                    v-if="section.section_head_teacher"
                    class="text-gray-800"
                  >
                    {{ section.section_head_teacher.name }}
                    
                  </span>
                  <span v-else class="italic text-gray-400">
                    Not Assigned
                  </span>
                </td>
                <td class="px-3 py-2">
                  <span
                    :class="[
                      'px-2 py-1 rounded-full text-xs font-medium',
                      section.status === 'active'
                        ? 'bg-green-100 text-green-700'
                        : 'bg-red-100 text-red-700',
                    ]"
                  >
                    {{ section.status === 'active' ? 'Active' : 'Inactive' }}
                  </span>
                </td>
                <td class="px-3 py-2 text-center space-x-2">
                  <button
                    @click.prevent="toggleStatus(section)"
                    :class="[
                      'px-3 py-1 text-xs font-semibold rounded-full border',
                      section.status === 'active'
                        ? 'bg-green-50 text-green-700 border-green-400 hover:bg-green-100'
                        : 'bg-gray-100 text-gray-600 border-gray-300 hover:bg-gray-200',
                    ]"
                  >
                    {{ section.status === 'active' ? 'Deactivate' : 'Activate' }}
                  </button>

                  <SecondaryButton
                    @click.prevent="showEditModal(section)"
                    class="!text-xs"
                  >
                    Edit
                  </SecondaryButton>

                  <PrimaryButton
                    class="bg-red-600 hover:bg-red-700 text-xs"
                    @click.prevent="showDeleteSectionModal(section)"
                  >
                    Delete
                  </PrimaryButton>
                </td>
              </tr>

              <tr v-if="props.sections.data.length === 0">
                <td
                  colspan="6"
                  class="py-6 text-center text-gray-500 italic"
                >
                  No sections found.
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </CardBox>

      <!-- 🔹 Pagination -->
      <div
        v-if="props.sections.links?.length"
        class="p-3 border-t border-gray-100 dark:border-slate-700"
      >
        <PaginationLinks :links="props.sections.links" />
      </div>

      <!-- 🔹 Modal: Create / Edit Section -->
      <Modal :show="confirmingSectionModal" @close="closeModal">
        <div class="p-6">
          <h2 class="text-lg font-semibold text-gray-800">
            {{ editingSection ? 'Edit Section' : 'Add Section' }}
          </h2>

          <!-- Academic Level -->
          <div class="mt-4">
            <InputLabel for="level_id" value="Academic Level" />
            <select
              id="level_id"
              v-model="form.level_id"
              class="w-full border-gray-300 rounded-md"
            >
              <option value="">Select Academic Level</option>
              <option
                v-for="level in props.academicLevels"
                :key="level.id"
                :value="level.id"
              >
                {{ level.name }}
              </option>
            </select>
            <InputError :message="form.errors.level_id" />
          </div>

          <!-- Name -->
          <div class="mt-4">
            <InputLabel for="name" value="Section Name" />
            <TextInput id="name" v-model="form.name" type="text" class="w-full" />
            <InputError :message="form.errors.name" />
          </div>

          <!-- Section Head Teacher (Approved Only) -->
          <div class="mt-4">
            <InputLabel
              for="section_head_teacher_id"
              value="Section Head Teacher"
            />
            <select
              id="section_head_teacher_id"
              v-model="form.section_head_teacher_id"
              class="w-full border-gray-300 rounded-md"
            >
              <option value="">No Teacher Assigned</option>
              <option
                v-for="teacher in approvedTeachers"
                :key="teacher.id"
                :value="teacher.id"
              >
                {{ teacher.name }}
              </option>
            </select>
            <InputError :message="form.errors.section_head_teacher_id" />
          </div>

          <!-- Buttons -->
          <div class="mt-6 flex justify-end space-x-2">
            <SecondaryButton @click.prevent="closeModal">Cancel</SecondaryButton>
            <PrimaryButton
              :disabled="form.processing"
              :class="{ 'opacity-50': form.processing }"
              @click.prevent="createOrUpdateSection"
            >
              {{ editingSection ? 'Update' : 'Add' }}
            </PrimaryButton>
          </div>
        </div>
      </Modal>

      <!-- 🔹 Modal: Delete Confirmation -->
      <Modal :show="!!deletingSection" @close="closeDeleteModal">
        <div class="p-6">
          <h2 class="text-lg font-semibold text-red-600">Delete Section</h2>
          <p class="text-gray-700 mt-1">
            Are you sure you want to delete this section? This action cannot be undone.
          </p>
          <div class="mt-6 flex justify-end space-x-2">
            <SecondaryButton @click.prevent="closeDeleteModal">
              Cancel
            </SecondaryButton>
            <PrimaryButton
              class="bg-red-600 hover:bg-red-700"
              :disabled="form.processing"
              :class="{ 'opacity-50': form.processing }"
              @click.prevent="deleteSection"
            >
              Delete
            </PrimaryButton>
          </div>
        </div>
      </Modal>
    </div>
  </LayoutAuthenticated>
</template>
