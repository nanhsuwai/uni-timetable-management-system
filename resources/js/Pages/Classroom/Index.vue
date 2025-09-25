<script setup>
import { Head, useForm, router } from "@inertiajs/vue3";
import { ref, watch } from "vue";
import { debounce } from "lodash";
import PaginationLinks from "@/Components/PaginationLinks.vue";
import Modal from "@/Components/Modal.vue";
import toast from "@/Stores/toast";
import TextInput from "@/Components/TextInput.vue";
import InputError from "@/Components/InputError.vue";
import SecondaryButton from "@/Components/SecondaryButton.vue";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import LayoutAuthenticated from "@/Layouts/LayoutAuthenticated.vue";

const props = defineProps({
  classrooms: { type: Object, default: () => ({ data: [], meta: {}, links: [] }) },
  filters: { type: Object, default: () => ({}) },
});

// Filter
const filterRoomNo = ref(props.filters.filterRoomNo || "");
watch(
  filterRoomNo,
  debounce((newRoomNo) => {
    router.get(route("classroom:all"), { filterRoomNo: newRoomNo }, { preserveState: true, replace: true });
  }, 500)
);

// Form
const form = useForm({ room_no: "" });
const confirmingClassroom = ref(false);
const editingClassroom = ref(null);
const deletingClassroom = ref(null);

const showCreateModal = () => {
  confirmingClassroom.value = true;
  editingClassroom.value = null;
  form.reset();
};

const showEditModal = (classroom) => {
  editingClassroom.value = classroom;
  form.room_no = classroom.room_no;
  confirmingClassroom.value = true;
};

const closeModal = () => {
  confirmingClassroom.value = false;
  editingClassroom.value = null;
  form.reset();
};

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

const toggleAvailability = (classroom) => {
  router.post(route('classroom:toggle-availability', classroom.id), {}, {
    onSuccess: () => {
      toast.add({ message: `Classroom ${classroom.is_available ? 'marked unavailable' : 'marked available'}!` });
      router.reload({ only: ['classrooms'] });
    },
  });
};
</script>

<template>
  <LayoutAuthenticated>
    <Head title="Classrooms" />

    <div class="py-6 max-w-7xl mx-auto sm:px-6 lg:px-8">
      <!-- Header -->
      <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold text-gray-900">Classrooms</h1>
        <PrimaryButton class="flex items-center gap-2" @click="showCreateModal">
          <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="currentColor">
            <path d="M12 5v14m-7-7h14" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
          </svg>
          Add Classroom
        </PrimaryButton>
      </div>

      <!-- Filter -->
      <div class="mb-6">
        <TextInput v-model="filterRoomNo" placeholder="Search by Room No..." class="w-full md:w-1/3" />
      </div>

      <!-- Classroom Cards -->
      <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
        <div v-for="classroom in props.classrooms.data" :key="classroom.id"
          class="bg-white rounded-xl shadow-md p-5 hover:shadow-xl transition-shadow">
          
          <div class="flex justify-between items-center mb-3">
            <h2 class="text-lg font-semibold">{{ classroom.room_no }}</h2>
            <div class="flex gap-2">
              <!-- Status Badge -->
              <span :class="{
                'bg-green-100 text-green-800': classroom.status === 'active',
                'bg-red-100 text-red-800': classroom.status === 'inactive'
              }" class="px-2 py-1 rounded-full text-xs font-medium">
                {{ classroom.status.charAt(0).toUpperCase() + classroom.status.slice(1) }}
              </span>

              <!-- Availability Badge -->
              <span :class="{
                'bg-green-100 text-green-800 px-2 py-1 rounded-full text-xs': classroom.is_available,
                'bg-red-100 text-red-800 px-2 py-1 rounded-full text-xs': !classroom.is_available
              }">
                {{ classroom.is_available ? 'Available' : 'Unavailable' }}
              </span>
            </div>
          </div>

          <!-- Section Info -->
          <p class="text-sm text-gray-600 mb-4">
            {{ classroom.section
              ? `${classroom.section.academic_level.academic_program.name} - ${classroom.section.academic_level.name} - ${classroom.section.name}`
              : '-' }}
          </p>

          <!-- Actions -->
          <div class="flex gap-2 justify-end">
            <SecondaryButton class="text-xs px-3 py-1" @click.prevent="showEditModal(classroom)">Edit</SecondaryButton>
            <PrimaryButton class="bg-red-500 text-white text-xs px-3 py-1" @click.prevent="showDeleteClassroomModal(classroom)">Delete</PrimaryButton>
            <button
              @click.prevent="toggleAvailability(classroom)"
              class="text-xs px-3 py-1 rounded-md text-white"
              :class="classroom.is_available ? 'bg-red-500 hover:bg-red-600' : 'bg-green-500 hover:bg-green-600'"
            >
              {{ classroom.is_available ? 'Mark Unavailable' : 'Mark Available' }}
            </button>
          </div>
        </div>
      </div>

      <!-- Pagination -->
      <div class="mt-6">
        <PaginationLinks :links="props.classrooms.links" />
      </div>

      <!-- Create / Edit Modal -->
      <Modal :show="confirmingClassroom" @close="closeModal">
        <div class="p-6 rounded-lg">
          <h2 class="text-xl font-semibold mb-4">
            {{ editingClassroom ? "Edit Classroom" : "Add Classroom" }}
          </h2>
          <TextInput v-model="form.room_no" placeholder="Room No" autofocus />
          <InputError :message="form.errors.room_no" class="mt-2" />
          <div class="mt-6 flex justify-end gap-2">
            <SecondaryButton @click.prevent="closeModal">Cancel</SecondaryButton>
            <PrimaryButton :disabled="form.processing" @click.prevent="createOrUpdateClassroom">
              {{ editingClassroom ? "Update" : "Add" }}
            </PrimaryButton>
          </div>
        </div>
      </Modal>

      <!-- Delete Modal -->
      <Modal :show="!!deletingClassroom" @close="closeDeleteModal">
        <div class="p-6 rounded-lg">
          <h2 class="text-lg font-semibold text-red-600 mb-2">Delete Classroom</h2>
          <p class="text-gray-700 mb-4">Are you sure you want to delete this classroom?</p>
          <div class="flex justify-end gap-2">
            <SecondaryButton @click.prevent="closeDeleteModal">Cancel</SecondaryButton>
            <PrimaryButton class="bg-red-500 hover:bg-red-600" @click.prevent="deleteClassroom">Delete</PrimaryButton>
          </div>
        </div>
      </Modal>
    </div>
  </LayoutAuthenticated>
</template>
