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
  teachers: {
    type: Object,
    default: () => ({ data: [], meta: {} }),
  },
  filters: {
    type: Object,
    default: () => ({}),
  },
});

const filterCode = ref(props.filters.filterCode || "");
const filterName = ref(props.filters.filterName || "");
const filterEmail = ref(props.filters.filterEmail || "");
const filterDepartment = ref(props.filters.filterDepartment || "");

watch([filterCode, filterName, filterEmail, filterDepartment], ([newCode, newName, newEmail, newDepartment]) => {
  router.get(
    route("teacher:all"),
    { filterCode: newCode, filterName: newName, filterEmail: newEmail, filterDepartment: newDepartment },
    { preserveState: true, replace: true }
  );
});

const form = useForm({
  code: "",
  name: "",
  email: "",
  phone: "",
  department: "",
  head_of_department: false,
});

const confirmingTeacherCreation = ref(false);
const editingTeacher = ref(null);
const showDeleteModal = ref(false);
const deletingTeacher = ref(null);

const showCreateModal = () => {
  confirmingTeacherCreation.value = true;
  form.reset();
  editingTeacher.value = null;
};

const showEditModal = (teacher) => {
  editingTeacher.value = teacher;
  form.code = teacher.code;
  form.name = teacher.name;
  form.email = teacher.email;
  form.phone = teacher.phone;
  form.department = teacher.department;
  form.head_of_department = teacher.head_of_department;
  confirmingTeacherCreation.value = true;
};

const closeModal = () => {
  confirmingTeacherCreation.value = false;
  form.reset();
  editingTeacher.value = null;
};

const createOrUpdateTeacher = () => {
  if (editingTeacher.value) {
    form.post(
      route("teacher:update", { teacher: editingTeacher.value.id }),
      {
        preserveScroll: true,
        onSuccess: () => {
          closeModal();
          toast.add({ message: "✅ Teacher updated!" });
        },
        onError: () => form.reset(),
      }
    );
  } else {
    form.post(route("teacher:create"), {
      preserveScroll: true,
      onSuccess: () => {
        closeModal();
        toast.add({ message: "✅ Teacher created!" });
      },
      onError: () => form.reset(),
    });
  }
};

const showDeleteTeacherModal = (teacher) => {
  showDeleteModal.value = true;
  deletingTeacher.value = teacher;
};

const closeDeleteModal = () => {
  showDeleteModal.value = false;
  deletingTeacher.value = null;
};

const deleteTeacher = () => {
  router.delete(route("teacher:delete", deletingTeacher.value), {
    preserveScroll: true,
    onSuccess: () => {
      closeDeleteModal();
      toast.add({ message: "🗑️ Teacher deleted!" });
    },
  });
};
</script>

<template>
  <LayoutAuthenticated>
    <Head title="Teachers" />

    <div class="py-6 max-w-7xl mx-auto sm:px-6 lg:px-8">
      <!-- Page Header -->
      <div class="flex justify-between items-center mb-6">
        <h1 class="text-xl font-semibold text-gray-800">Teachers</h1>
        <PrimaryButton @click="showCreateModal">+ Add Teacher</PrimaryButton>
      </div>
      <!-- Filters -->
      <div
        class="mb-6 grid grid-cols-1 md:grid-cols-3 gap-4 bg-white p-4 rounded-lg shadow"
      >
        <div>
          <InputLabel for="filterCode" value="Search by Code" />
          <TextInput
            id="filterCode"
            v-model="filterCode"
            type="text"
            placeholder="Search teachers by code..."
            class="w-full"
          />
        </div>
        <div>
          <InputLabel for="filterName" value="Search by Name" />
          <TextInput
            id="filterName"
            v-model="filterName"
            type="text"
            placeholder="Search teachers by name..."
            class="w-full"
          />
        </div>
        <div>
          <InputLabel for="filterEmail" value="Search by Email" />
          <TextInput
            id="filterEmail"
            v-model="filterEmail"
            type="text"
            placeholder="Search teachers by email..."
            class="w-full"
          />
        </div>
      </div>
      <!-- Table -->
      <div class="bg-white shadow rounded-lg overflow-hidden">
        <div class="overflow-x-auto">
          <table class="w-full text-sm text-center min-w-max">
            <thead class="bg-gray-100 text-gray-700 uppercase text-xs sticky top-0">
              <tr>
                <th class="px-4 py-3">#</th>
                <th class="px-4 py-3">Code</th>
                <th class="px-4 py-3">Name</th>
                <th class="px-4 py-3">Email</th>
                <th class="px-4 py-3">Phone</th>
                <th class="px-4 py-3">Department</th>
                <th class="px-4 py-3">Head of Department</th>
                <th class="px-4 py-3">Actions</th>
              </tr>
            </thead>
            <tbody class="max-h-96 overflow-y-auto">
              <tr
                v-for="(teacher, index) in props.teachers.data"
                :key="teacher.id"
                class="hover:bg-gray-50 transition"
              >
                <td class="px-4 py-2 border-b">
                  {{ index + 1 + (props.teachers.per_page * (props.teachers.current_page - 1)) }}
                </td>
                <td class="px-4 py-2 border-b">{{ teacher.code }}</td>
                <td class="px-4 py-2 border-b">{{ teacher.name }}</td>
                <td class="px-4 py-2 border-b">{{ teacher.email }}</td>
                <td class="px-4 py-2 border-b">{{ teacher.phone }}</td>
                <td class="px-4 py-2 border-b">{{ teacher.department }}</td>
                <td class="px-4 py-2 border-b">{{ teacher.head_of_department ? 'Yes' : 'No' }}</td>
                <td class="px-4 py-2 border-b space-x-2">
                  <button
                    @click.prevent="showEditModal(teacher)"
                    class="px-3 py-1 rounded-md bg-blue-500 text-white text-xs hover:bg-blue-600"
                  >
                    Edit
                  </button>
                  <button
                    @click.prevent="showDeleteTeacherModal(teacher)"
                    class="px-3 py-1 rounded-md bg-red-500 text-white text-xs hover:bg-red-600"
                  >
                    Delete
                  </button>
                </td>
              </tr>
              <tr v-if="props.teachers.data.length === 0">
                <td colspan="8" class="py-6 text-gray-500 text-sm">
                  No teachers found.
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
      <!-- Pagination -->
      <div class="p-4 border-t bg-gray-50">
        <PaginationLinks :links="props.teachers.links" />
      </div>

      <!-- Create/Edit Modal -->
      <Modal :show="confirmingTeacherCreation" @close="closeModal">
        <div class="p-6">
          <h2 class="text-lg font-semibold text-gray-900 mb-4">
            {{ editingTeacher ? "Edit Teacher" : "Add Teacher" }}
          </h2>
          <div class="space-y-4">
            <div>
              <InputLabel for="code" value="Code" />
              <TextInput
                id="code"
                v-model="form.code"
                type="text"
                class="w-full"
              />
              <InputError :message="form.errors.code" />
            </div>
            <div>
              <InputLabel for="name" value="Name" />
              <TextInput
                id="name"
                v-model="form.name"
                type="text"
                class="w-full"
              />
              <InputError :message="form.errors.name" />
            </div>
            <div>
              <InputLabel for="email" value="Email" />
              <TextInput
                id="email"
                v-model="form.email"
                type="email"
                class="w-full"
              />
              <InputError :message="form.errors.email" />
            </div>
            <div>
              <InputLabel for="phone" value="Phone" />
              <TextInput
                id="phone"
                v-model="form.phone"
                type="text"
                class="w-full"
              />
              <InputError :message="form.errors.phone" />
            </div>
            <div>
              <InputLabel for="department" value="Department" />
              <TextInput
                id="department"
                v-model="form.department"
                type="text"
                class="w-full"
              />
              <InputError :message="form.errors.department" />
            </div>
            <div>
              <label class="flex items-center">
                <input
                  type="checkbox"
                  v-model="form.head_of_department"
                  :checked="form.head_of_department"
                  class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500"
                />
                <span class="ml-2 text-sm text-gray-600">Head of Department</span>
              </label>
              <InputError :message="form.errors.head_of_department" />
            </div>
          </div>
          <div class="mt-6 flex justify-end space-x-2">
            <SecondaryButton @click.prevent="closeModal">Cancel</SecondaryButton>
            <PrimaryButton
              :disabled="form.processing"
              @click.prevent="createOrUpdateTeacher"
            >
              {{ editingTeacher ? "Update" : "Create" }}
            </PrimaryButton>
          </div>
        </div>
      </Modal>

      <!-- Delete Modal -->
      <Modal :show="showDeleteModal" @close="closeDeleteModal">
        <div class="p-6">
          <h2 class="text-lg font-semibold text-red-600 mb-2">
            Delete Teacher
          </h2>
          <p class="text-sm text-gray-600">
            Are you sure you want to delete this teacher? This action
            cannot be undone.
          </p>
          <div class="mt-6 flex justify-end space-x-2">
            <SecondaryButton @click.prevent="closeDeleteModal">Cancel</SecondaryButton>
            <PrimaryButton
              class="bg-red-500 hover:bg-red-600"
              :disabled="form.processing"
              @click.prevent="deleteTeacher"
            >
              Delete
            </PrimaryButton>
          </div>
        </div>
      </Modal>
    </div>
  </LayoutAuthenticated>
</template>
