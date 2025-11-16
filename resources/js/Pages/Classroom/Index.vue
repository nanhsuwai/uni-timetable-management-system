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
import checkPermissionComposable from "@/Composables/Permission/checkPermission";
import SectionMain from "../../Components/SectionMain.vue";
const props = defineProps({
  classrooms: { type: Object, default: () => ({ data: [], meta: {}, links: [] }) },
  filters: { type: Object, default: () => ({}) },
});

let hasPermission = ref(checkPermissionComposable("classroom_manage"));
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
    <SectionMain v-if="['teacher', 'admin'].includes($page.props.auth.user.user_type) || hasPermission">

      <div class="py-8 max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="flex justify-between items-center mb-8">
          <h1 class="text-3xl font-extrabold text-gray-900 dark:text-gray-100">
            Classrooms
          </h1>
          <PrimaryButton
            class="flex items-center gap-2 px-4 py-2 text-sm font-semibold shadow-lg hover:shadow-xl transition-all duration-300 transform hover:scale-[1.02]"
            @click="showCreateModal">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="none"
              stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
              <path d="M12 5v14m-7-7h14" />
            </svg>
            <span>Add Classroom</span>
          </PrimaryButton>
        </div>

        <!-- Filter -->
        <div class="mb-6">
          <TextInput v-model="filterRoomNo" placeholder="Search by Room No..." class="w-full md:w-1/3" />
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-8">
          <div v-for="classroom in props.classrooms.data" :key="classroom.id"
            class="bg-white dark:bg-gray-800 rounded-xl shadow-lg hover:shadow-2xl transition-all duration-300 p-6 border-t-4 border-indigo-500 dark:border-indigo-600 transform hover:-translate-y-0.5">
            <div class="flex justify-between items-start mb-4">
              <h2 class="text-2xl font-bold text-gray-800 dark:text-gray-100 tracking-wide">
                {{ classroom.room_no }}
              </h2>
              <div class="flex flex-col items-end gap-2">
               <!--  <span :class="{
                  'bg-green-100 text-green-800 dark:bg-green-700 dark:text-green-100':
                    classroom.status === 'active',
                  'bg-red-100 text-red-800 dark:bg-red-700 dark:text-red-100':
                    classroom.status === 'inactive',
                }" class="px-3 py-1 rounded-full text-xs font-semibold capitalize shadow-sm">
                  {{
                    classroom.status.charAt(0).toUpperCase() +
                    classroom.status.slice(1)
                  }}
                </span> -->

                <span :class="{
                  'bg-indigo-100 text-indigo-800 dark:bg-indigo-700 dark:text-indigo-100':
                    classroom.is_available,
                  'bg-gray-200 text-gray-800 dark:bg-gray-600 dark:text-gray-200':
                    !classroom.is_available,
                }" class="px-3 py-1 rounded-full text-xs font-semibold shadow-sm">
                  {{ classroom.is_available ? 'Available' : 'Assigned' }}
                </span>
              </div>
            </div>

            <div class="mb-5 border-t pt-4 dark:border-gray-700">
              <p class="text-sm font-medium text-gray-500 dark:text-gray-400 mb-1">
                Assigned Section:
              </p>
              <p class="text-base font-semibold text-gray-700 dark:text-gray-300">
                {{
                  classroom.section
                    ? `${classroom.section.academic_level.name} - ${classroom.section.name}
                (${classroom.section.academic_level.academic_program.name})`
                    : 'Unassigned'
                }}
              </p>
            </div>

            <div class="flex gap-3 justify-end pt-3 border-t dark:border-gray-700">
              <SecondaryButton
                class="text-xs px-3 py-1.5 rounded-lg font-semibold dark:bg-gray-700 dark:text-gray-300 dark:hover:bg-gray-600"
                @click.prevent="showEditModal(classroom)">
                Edit
              </SecondaryButton>

              <button @click.prevent="toggleAvailability(classroom)"
                class="text-xs px-3 py-1.5 rounded-lg font-semibold text-white transition duration-150 shadow-md"
                :class="classroom.is_available
                    ? 'bg-orange-500 hover:bg-orange-600 dark:bg-orange-600 dark:hover:bg-orange-700'
                    : 'bg-green-500 hover:bg-green-600 dark:bg-green-600 dark:hover:bg-green-700'
                  ">
                {{ classroom.is_available ? 'Mark Assigned' : 'Mark Available' }}
              </button>

              <PrimaryButton
                class="bg-red-500 hover:bg-red-600 text-white text-xs px-3 py-1.5 rounded-lg font-semibold dark:bg-red-600 dark:hover:bg-red-700"
                @click.prevent="showDeleteClassroomModal(classroom)">
                Delete
              </PrimaryButton>
            </div>
          </div>
        </div>

        <div v-if="props.classrooms.data.length === 0"
          class="text-center py-10 text-gray-500 dark:text-gray-400 italic text-lg">
          No classrooms match your filter.
        </div>


        <div class="mt-10 p-4 rounded-xl bg-white dark:bg-gray-800 shadow-lg ring-1 ring-gray-200 dark:ring-gray-700">
          <PaginationLinks :links="props.classrooms.links" />
        </div>

        <Modal :show="confirmingClassroom" @close="closeModal">
          <div class="p-6 bg-white dark:bg-gray-800 rounded-lg">
            <h2 class="text-xl font-semibold mb-4 border-b pb-3 text-gray-900 dark:text-gray-100 dark:border-gray-700">
              {{ editingClassroom ? 'Edit Classroom' : 'Add Classroom' }}
            </h2>
            <InputLabel for="room_no" value="Room Number / Name" class="dark:text-gray-300 mb-1" />
            <TextInput id="room_no" v-model="form.room_no" placeholder="Enter Room Number" autofocus
              class="w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 focus:border-indigo-500 dark:focus:border-indigo-400 focus:ring-indigo-500 dark:focus:ring-indigo-400 rounded-lg" />
            <InputError :message="form.errors.room_no" class="mt-2" />
            <div class="mt-6 flex justify-end gap-3 pt-4 border-t dark:border-gray-700">
              <SecondaryButton @click.prevent="closeModal">
                Cancel
              </SecondaryButton>
              <PrimaryButton :disabled="form.processing" @click.prevent="createOrUpdateClassroom">
                {{ editingClassroom ? 'Update' : 'Add' }}
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
    </SectionMain>
    <SectionMain v-else>
      <div class="py-6 max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white p-6 rounded-lg shadow text-center">
          <h2 class="text-xl font-semibold text-gray-800 mb-4">Access Denied</h2>
          <p class="text-gray-600">You do not have permission to view this page.</p>
        </div>
      </div>
    </SectionMain>
  </LayoutAuthenticated>
</template>
