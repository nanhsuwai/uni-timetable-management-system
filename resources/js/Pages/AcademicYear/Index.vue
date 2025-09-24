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
        onError: () => form.reset(),
      }
    );
  } else {
    form.post(route("academic-year:create"), {
      preserveScroll: true,
      onSuccess: () => {
        closeModal();
        toast.add({ message: "Academic Year created!" });
      },
      onError: () => form.reset(),
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
</script>

<template>
  <LayoutAuthenticated>
    <!-- Header -->
    <SectionTitleLineWithButton
      :icon="mdiShapePlus"
      title="Academic Years"
    >
      <PrimaryButton @click.prevent="showCreateModal">
        + Add Academic Year
      </PrimaryButton>
    </SectionTitleLineWithButton>

    <div class="py-6 max-w-7xl mx-auto sm:px-6 lg:px-8">
      <!-- Filter -->
      <div class="mb-6">
        <InputLabel for="filterName" value="Search Academic Year" />
        <TextInput
          id="filterName"
          v-model="filterName"
          type="text"
          placeholder="e.g., 2024-2025"
          class="w-full"
        />
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
            </tr>
          </thead>
          <tbody>
            <tr
              v-for="(academicYear, index) in props.academicYears.data"
              :key="academicYear.id"
              class="border-t hover:bg-gray-50"
            >
              <td class="px-4 py-2 text-center">
                {{ index + 1 + (props.academicYears.per_page * (props.academicYears.current_page - 1)) }}
              </td>
              <td class="px-4 py-2 font-medium">{{ academicYear.name }}</td>
              <td class="px-4 py-2">{{ academicYear.start_date }}</td>
              <td class="px-4 py-2">{{ academicYear.end_date }}</td>
              <td class="px-4 py-2 text-center">
                <button
                  @click.prevent="toggleStatus(academicYear)"
                  :class="[
                    'px-3 py-1 rounded-full text-xs font-semibold',
                    academicYear.status === 'active'
                      ? 'bg-green-100 text-green-700 border border-green-400'
                      : 'bg-gray-200 text-gray-600 border border-gray-300'
                  ]"
                >
                  {{ academicYear.status === 'active' ? 'Active' : 'Inactive' }}
                </button>
              </td>
              <td class="px-4 py-2 flex justify-center gap-2">
                <button
                  @click.prevent="showEditModal(academicYear)"
                  class="px-2 py-1 rounded text-blue-600 border border-blue-600 hover:bg-blue-600 hover:text-white text-xs"
                >
                  Edit
                </button>
                <button
                  @click.prevent="showDeleteAcademicYearModal(academicYear)"
                  class="px-2 py-1 rounded text-red-600 border border-red-600 hover:bg-red-600 hover:text-white text-xs"
                >
                  Delete
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
              <TextInput
                id="name"
                v-model="form.name"
                type="text"
                placeholder="e.g., Academic Year 2024-2025"
              />
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
            <PrimaryButton
              :class="{ 'opacity-25': form.processing }"
              :disabled="form.processing"
              @click.prevent="createOrUpdateAcademicYear"
            >
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
            <PrimaryButton
              class="bg-red-600 hover:bg-red-700 text-white"
              :class="{ 'opacity-25': form.processing }"
              :disabled="form.processing"
              @click.prevent="deleteAcademicYear"
            >
              Delete
            </PrimaryButton>
          </div>
        </div>
      </Modal>
    </div>
  </LayoutAuthenticated>
</template>

