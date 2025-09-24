<script setup>
import { Head, useForm, router } from "@inertiajs/vue3";
import { ref, watch } from "vue";
import { debounce } from "lodash";
import PaginationLinks from "@/Components/PaginationLinks.vue";
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
import BaseButton from "@/Components/BaseButton.vue";
import { mdiShapePlus } from "@mdi/js";

const props = defineProps({
  classrooms: { type: Object, default: () => ({ data: [], meta: {}, links: [] }) },
  filters: { type: Object, default: () => ({}) },
  sections: { type: Array, default: () => [] },
});

// Filters
const filterRoomNo = ref(props.filters.filterRoomNo || "");
const filterSection = ref(props.filters.filterSection || "");

watch(
  [filterRoomNo, filterSection],
  debounce(([newRoomNo, newSection]) => {
    router.get(
      route("classroom:all"),
      { filterRoomNo: newRoomNo, filterSection: newSection },
      { preserveState: true, replace: true }
    );
  }, 500)
);

// Form
const form = useForm({ section_id: "", room_no: "" });
const confirmingClassroom = ref(false);
const editingClassroom = ref(null);
const deletingClassroom = ref(null);

// Modal handlers
const showCreateModal = () => {
  confirmingClassroom.value = true;
  editingClassroom.value = null;
  form.reset();
};

const showEditModal = (classroom) => {
  editingClassroom.value = classroom;
  form.section_id = classroom.section_id;
  form.room_no = classroom.room_no;
  confirmingClassroom.value = true;
};

const closeModal = () => {
  confirmingClassroom.value = false;
  editingClassroom.value = null;
  form.reset();
};

// CRUD actions
const createOrUpdateClassroom = () => {
  const url = editingClassroom.value
    ? route("classroom:update", { classroom: editingClassroom.value.id })
    : route("classroom:create");

  form.post(url, {
    preserveScroll: true,
    onSuccess: () => {
      closeModal();
      toast.add({ message: editingClassroom.value ? "Classroom updated!" : "Classroom created!" });
    },
    onError: () => form.reset(),
  });
};

const showDeleteClassroomModal = (classroom) => { deletingClassroom.value = classroom; };
const closeDeleteModal = () => { deletingClassroom.value = null; };

const deleteClassroom = () => {
  if (!deletingClassroom.value) return;
  router.delete(route("classroom:delete", deletingClassroom.value), {
    preserveScroll: true,
    onSuccess: () => {
      closeDeleteModal();
      toast.add({ message: "Classroom deleted!" });
    },
  });
};
</script>

<template>
  <LayoutAuthenticated>
    <Head title="Classrooms" />

    <!-- Header -->
    <SectionTitleLineWithButton :icon="mdiShapePlus" title="Classrooms">
      <BaseButton color="info" label="Add Classroom" :icon="mdiShapePlus" @click="showCreateModal" />
    </SectionTitleLineWithButton>

    <div class="py-6 max-w-7xl mx-auto sm:px-6 lg:px-8">

      <!-- Filters -->
      <div class="mb-4 grid grid-cols-2 gap-4">
        <div>
          <InputLabel for="filterRoomNo" value="Filter by Room No" />
          <TextInput
            id="filterRoomNo"
            v-model="filterRoomNo"
            type="text"
            placeholder="Search classrooms"
            class="w-full"
          />
        </div>
        <div>
          <InputLabel for="filterSection" value="Filter by Section" />
          <select id="filterSection" v-model="filterSection" class="w-full border-gray-300 rounded">
            <option value="">All Sections</option>
            <option v-for="section in props.sections" :key="section.id" :value="section.id">
              {{ section.name }}
            </option>
          </select>
        </div>
      </div>

      <!-- Table -->
      <CardBox has-table>
        <table class="w-full text-xs">
          <thead>
            <tr class="bg-gray-50">
              <th class="px-2 py-1 text-left">#</th>
              <th class="px-2 py-1 text-left">Room No</th>
              <th class="px-2 py-1 text-left">Section</th>
              <th class="px-2 py-1 text-left">Actions</th>
            </tr>
          </thead>
          <tbody>
            <tr v-if="props.classrooms.data.length === 0">
              <td colspan="4" class="text-center py-6 text-gray-500">No classrooms found.</td>
            </tr>
            <tr
              v-for="(classroom, index) in props.classrooms.data"
              :key="classroom.id"
              class="hover:bg-gray-50 transition-colors"
            >
              <td class="px-2 py-1">{{ index + 1 + (props.classrooms.per_page * (props.classrooms.current_page - 1)) }}</td>
              <td class="px-2 py-1">{{ classroom.room_no }}</td>
              <td class="px-2 py-1">{{ classroom.section.name }}</td>
              <td class="px-2 py-1 space-x-1">
                <button
                  @click.prevent="showEditModal(classroom)"
                  class="border px-2 py-1 rounded hover:bg-blue-600 hover:text-white transition"
                >Edit</button>
                <button
                  @click.prevent="showDeleteClassroomModal(classroom)"
                  class="border px-2 py-1 rounded hover:bg-red-600 hover:text-white transition"
                >Delete</button>
              </td>
            </tr>
          </tbody>
        </table>
      </CardBox>

      <!-- Pagination -->
      <div v-if="props.classrooms.links?.length" class="p-3 lg:px-6 border-t border-gray-100">
        <PaginationLinks :links="props.classrooms.links" />
      </div>

      <!-- Create / Edit Modal -->
      <Modal :show="confirmingClassroom" @close="closeModal">
        <div class="p-6">
          <h2 class="text-lg font-medium text-gray-900">
            {{ editingClassroom ? "Edit Classroom" : "Add Classroom" }}
          </h2>
          <div class="mt-4">
            <InputLabel for="section_id" value="Section" />
            <select id="section_id" v-model="form.section_id" class="w-full border-gray-300 rounded">
              <option value="">Select Section</option>
              <option v-for="section in props.sections" :key="section.id" :value="section.id">{{ section.name }}</option>
            </select>
            <InputError :message="form.errors.section_id" />
          </div>
          <div class="mt-4">
            <InputLabel for="room_no" value="Room No" />
            <TextInput id="room_no" v-model="form.room_no" type="text" autofocus />
            <InputError :message="form.errors.room_no" />
          </div>
          <div class="mt-6 flex justify-end space-x-2">
            <SecondaryButton @click.prevent="closeModal">Cancel</SecondaryButton>
            <PrimaryButton
              :class="{ 'opacity-25': form.processing }"
              :disabled="form.processing"
              @click.prevent="createOrUpdateClassroom"
            >
              {{ editingClassroom ? "Update" : "Add" }}
            </PrimaryButton>
          </div>
        </div>
      </Modal>

      <!-- Delete Modal -->
      <Modal :show="!!deletingClassroom" @close="closeDeleteModal">
        <div class="p-6">
          <h2 class="text-lg font-medium text-red-600">Delete Classroom</h2>
          <p>Are you sure you want to delete this classroom?</p>
          <div class="mt-6 flex justify-end space-x-2">
            <SecondaryButton @click.prevent="closeDeleteModal">Cancel</SecondaryButton>
            <PrimaryButton
              :class="{ 'opacity-25': form.processing }"
              :disabled="form.processing"
              @click.prevent="deleteClassroom"
            >
              Delete
            </PrimaryButton>
          </div>
        </div>
      </Modal>
    </div>
  </LayoutAuthenticated>
</template>
