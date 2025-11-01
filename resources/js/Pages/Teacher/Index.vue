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

/*---------------------------------------
| ✅ Department Options
----------------------------------------*/
const DepartmentOption = {
  ITSM: "ITSM",
  FCST: "Faculty of Computer Technology",
  FCS: "Faculty of Computer Science",
  IS: "သုတသိပ္ပံမဟာဌာန",
  Physics: "Physics Department",
  Mathematics: "တွက်ချက်ရေးမဟာဌာန",
  English: "English Department",
  Myanmar: "မြန်မာစာဌာန",
};
const departmentOptions = Object.values(DepartmentOption);

/*---------------------------------------
| ✅ Props & Filters
----------------------------------------*/
const props = defineProps({
  teachers: Object,
  filters: Object,
});

const {
  filterName: initialFilterName = "",
  filterEmail: initialFilterEmail = "",
  filterDepartment: initialFilterDepartment = "",
  filterStatus: initialFilterStatus = "",
} = props.filters || {};

const filterName = ref(initialFilterName);
const filterEmail = ref(initialFilterEmail);
const filterDepartment = ref(initialFilterDepartment);
const filterStatus = ref(initialFilterStatus);

/*---------------------------------------
| 🔍 Watch Filters & Update List
----------------------------------------*/
watch(
  [filterName, filterEmail, filterDepartment, filterStatus],
  ([name, email, department, status]) => {
    router.get(
      route("teacher:all"),
      {
        filterName: name,
        filterEmail: email,
        filterDepartment: department,
        filterStatus: status.toLowerCase(), // normalize to lowercase for server
      },
      { preserveState: true, replace: true }
    );
  }
);

/*---------------------------------------
| ✏️ Form & Modal Handling
----------------------------------------*/
const form = useForm({
  name: "",
  email: "",
  phone: "",
  department: "",
  head_of_department: false,
});

const confirmingTeacherModal = ref(false);
const editingTeacher = ref(null);

/** Open Create Modal */
const showCreateModal = () => {
  editingTeacher.value = null;
  form.reset();
  confirmingTeacherModal.value = true;
};

/** Open Edit Modal */
const showEditModal = (teacher) => {
  editingTeacher.value = teacher;
  form.setData({
    name: teacher.name,
    email: teacher.email,
    phone: teacher.phone,
    department: teacher.department,
    head_of_department: teacher.head_of_department,
  });
  confirmingTeacherModal.value = true;
};

/** Close Modal */
const closeModal = () => {
  confirmingTeacherModal.value = false;
  editingTeacher.value = null;
  form.reset();
};

const createOrUpdateTeacher = () => {
  const isEditing = !!editingTeacher.value; // store state before it resets

  const submitRoute = isEditing
    ? route("teacher:update", { teacher: editingTeacher.value.id })
    : route("teacher:create");

  const httpMethod = isEditing ? "put" : "post";

  form[httpMethod](submitRoute, {
    preserveScroll: true,
    onSuccess: () => {
      closeModal();
      toast.add({ message: `✅ Teacher ${isEditing ? 'updated' : 'created'} successfully!` });
    },
    onError: (errors) => {
      console.error(errors);
      toast.add({ message: "⚠️ Something went wrong. Please check input fields." });
    },
  });
};


/*---------------------------------------
| ❌ Status Modals & Actions
----------------------------------------*/
const showDeleteModal = ref(false);
const deletingTeacher = ref(null);
const showApproveModal = ref(false);
const approvingTeacher = ref(null);
const showRejectModal = ref(false);
const rejectingTeacher = ref(null);

const setModalState = (type, teacher = null) => {
  switch (type) {
    case "delete":
      showDeleteModal.value = !!teacher;
      deletingTeacher.value = teacher;
      break;
    case "approve":
      showApproveModal.value = !!teacher;
      approvingTeacher.value = teacher;
      break;
    case "reject":
      showRejectModal.value = !!teacher;
      rejectingTeacher.value = teacher;
      break;
  }
};

const showDeleteTeacherModal = (teacher) => setModalState("delete", teacher);
const closeDeleteModal = () => setModalState("delete");
const showApproveTeacherModal = (teacher) => setModalState("approve", teacher);
const closeApproveModal = () => setModalState("approve");
const showRejectTeacherModal = (teacher) => setModalState("reject", teacher);
const closeRejectModal = () => setModalState("reject");

const deleteTeacher = () => {
  if (!deletingTeacher.value) return;
  router.delete(route("teacher:delete", deletingTeacher.value.id), {
    preserveScroll: true,
    onSuccess: () => {
      closeDeleteModal();
      toast.add({ message: "🗑️ Teacher deleted!" });
    },
  });
};

const updateTeacherStatus = (status) => {
  const teacher = status === "approved" ? approvingTeacher.value : rejectingTeacher.value;
  const closeModalFn = status === "approved" ? closeApproveModal : closeRejectModal;
  if (!teacher) return;

  router.put(route("teacher:status", teacher.id), { status }, {
    preserveScroll: true,
    onSuccess: () => {
      closeModalFn();
      toast.add({ message: status === "approved" ? "✅ Teacher approved!" : "❌ Teacher rejected!" });
    },
  });
};

const approveTeacher = () => updateTeacherStatus("approved");
const rejectTeacher = () => updateTeacherStatus("rejected");
</script>


<template>
  <LayoutAuthenticated>

    <Head title="Teachers" />

    <div class="p-4 sm:p-6 bg-gray-50 dark:bg-gray-900 min-h-screen">
      <!-- Header -->
      <div class="flex justify-between items-center mb-6">
        <h1 class="text-3xl font-bold text-gray-800 dark:text-gray-200">Teacher Management</h1>
        <PrimaryButton @click="showCreateModal">+ Add New Teacher</PrimaryButton>
      </div>

      <!-- Filters -->
      <div
        class="mb-6 grid grid-cols-1 sm:grid-cols-2 md:grid-cols-5 gap-4 bg-white dark:bg-gray-800 p-4 rounded-lg shadow-md">
        <div class="md:col-span-1">
          <InputLabel for="filterName" value="Name" />
          <TextInput id="filterName" v-model="filterName" type="text" placeholder="Filter by name" class="w-full" />
        </div>
        <div class="md:col-span-1">
          <InputLabel for="filterEmail" value="Email" />
          <TextInput id="filterEmail" v-model="filterEmail" type="text" placeholder="Filter by email" class="w-full" />
        </div>
        <div class="md:col-span-1">
          <InputLabel for="filterDepartment" value="Department" />
          <select id="filterDepartment" v-model="filterDepartment"
            class="w-full border-gray-300 dark:border-gray-700 rounded-md shadow-sm dark:bg-gray-900 dark:text-gray-300 focus:ring-indigo-500 focus:border-indigo-500">
            <option value="">All Departments</option>
            <option v-for="department in departmentOptions" :key="department" :value="department">{{ department }}
            </option>
          </select>
        </div>
        <div class="md:col-span-1">
          <InputLabel for="filterStatus" value="Status" />
          <select id="filterStatus" v-model="filterStatus"
            class="w-full border-gray-300 dark:border-gray-700 rounded-md shadow-sm dark:bg-gray-900 dark:text-gray-300 focus:ring-indigo-500 focus:border-indigo-500">
            <option value="">All Status</option>
            <option value="pending">Pending</option>
            <option value="approved">Approved</option>
            <option value="rejected">Rejected</option>
          </select>
        </div>
      </div>

      <!-- Teachers Table -->
      <div class="bg-white dark:bg-gray-800 shadow-xl rounded-lg overflow-hidden">
        <div class="overflow-x-auto">
          <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
            <thead class="bg-gray-100 dark:bg-gray-700">
              <tr>
                <th
                  class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider dark:text-gray-300">
                  #</th>
                <th
                  class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider dark:text-gray-300">
                  Name</th>
                <th
                  class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider dark:text-gray-300">
                  Email</th>
                <th
                  class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider dark:text-gray-300">
                  Phone</th>
                <th
                  class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider dark:text-gray-300">
                  Department</th>
                <th
                  class="px-6 py-3 text-center text-xs font-semibold text-gray-600 uppercase tracking-wider dark:text-gray-300">
                  Status</th>
                <th
                  class="px-6 py-3 text-center text-xs font-semibold text-gray-600 uppercase tracking-wider dark:text-gray-300">
                  HOD</th>
                <th
                  class="px-6 py-3 text-right text-xs font-semibold text-gray-600 uppercase tracking-wider dark:text-gray-300">
                  Actions</th>
              </tr>
            </thead>
            <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
              <tr v-for="(teacher, index) in teachers.data" :key="teacher.id"
                class="hover:bg-gray-50 dark:hover:bg-gray-700 transition duration-150">
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-300">
                  {{ index + 1 + (teachers.per_page * (teachers.current_page - 1)) || index + 1 }}
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-gray-100">{{
                  teacher.name }}</td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-300">{{ teacher.email }}
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-300">{{ teacher.phone }}
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-300">{{ teacher.department
                  }}</td>
                <td class="px-6 py-4 whitespace-nowrap text-center text-sm text-gray-500 dark:text-gray-300">
                  <span :class="[
                    'inline-flex px-3 py-1 text-xs font-semibold leading-5 rounded-full',
                    teacher.status === 'approved' ? 'bg-green-100 text-green-800 dark:bg-green-800 dark:text-green-100' :
                      teacher.status === 'pending' ? 'bg-yellow-100 text-yellow-800 dark:bg-yellow-800 dark:text-yellow-100' :
                        'bg-red-100 text-red-800 dark:bg-red-800 dark:text-red-100'
                  ]">
                    {{ teacher.status.charAt(0).toUpperCase() + teacher.status.slice(1) }}
                  </span>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-center text-sm text-gray-500 dark:text-gray-300">
                  <span :class="[
                    'inline-flex px-3 py-1 text-xs font-semibold leading-5 rounded-full',
                    teacher.head_of_department ? 'bg-green-100 text-green-800 dark:bg-green-800 dark:text-green-100' : 'bg-yellow-100 text-yellow-800 dark:bg-yellow-800 dark:text-yellow-100'
                  ]">
                    {{ teacher.head_of_department ? 'HOD' : 'Staff' }}
                  </span>
                </td>
               <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
    <div class="flex flex-col items-end space-y-2">

        <div v-if="teacher.status === 'pending'" class="flex items-center space-x-1"> 
            
            <button @click.prevent="showApproveTeacherModal(teacher)" 
                class="px-2 py-1 rounded-md bg-green-600 text-white text-xs font-medium hover:bg-green-700 disabled:opacity-50 transition duration-150 flex items-center space-x-1 shadow-sm"
                :disabled="router.pending">
                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                </svg>
                <span>Approve</span>
            </button>

            <button @click.prevent="showRejectTeacherModal(teacher)" 
                class="px-2 py-1 rounded-md bg-red-500 text-white text-xs font-medium hover:bg-red-600 disabled:opacity-50 transition duration-150 flex items-center space-x-1 shadow-sm"
                :disabled="router.pending">
                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
                <span>Reject</span>
            </button>
        </div>

        <button @click.prevent="showDeleteTeacherModal(teacher)"
            class="px-2 py-1 rounded-md bg-gray-500 text-white text-xs font-medium hover:bg-gray-600 disabled:opacity-50 transition duration-150 flex items-center space-x-1 shadow-sm"
            :disabled="router.pending">
            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
            </svg>
            <span>Delete</span>
        </button>

    </div>
</td>
              </tr>
              <tr v-if="teachers.data.length === 0">
                <td colspan="8" class="px-6 py-8 text-center text-lg text-gray-500 dark:text-gray-400 font-medium">
                  No teachers found matching the criteria.
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <!-- Pagination -->
        <div v-if="teachers.links && teachers.links.length > 3"
          class="p-4 border-t border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800">
          <PaginationLinks :links="teachers.links" />
        </div>
      </div>

      <!-- Modals -->
      <Modal :show="confirmingTeacherModal" @close="closeModal">
        <div class="p-6 dark:bg-gray-800">
          <h2 class="text-xl font-medium text-gray-900 dark:text-gray-100 mb-4">
            {{ editingTeacher ? 'Edit Teacher' : 'Add New Teacher' }}
          </h2>
          <form @submit.prevent="submitTeacherForm" class="grid grid-cols-1 gap-4">
            <div>
              <InputLabel for="name" value="Name" />
              <TextInput id="name" v-model="form.name" type="text" class="mt-1 block w-full"
                :class="{ 'border-red-500': form.errors.name }" required autofocus autocomplete="name" />
              <InputError class="mt-2" :message="form.errors.name" />
            </div>

            <div>
              <InputLabel for="email" value="Email" />
              <TextInput id="email" v-model="form.email" type="email" class="mt-1 block w-full"
                :class="{ 'border-red-500': form.errors.email }" autocomplete="email" />
              <InputError class="mt-2" :message="form.errors.email" />
            </div>

            <div>
              <InputLabel for="phone" value="Phone" />
              <TextInput id="phone" v-model="form.phone" type="text" class="mt-1 block w-full"
                :class="{ 'border-red-500': form.errors.phone }" autocomplete="phone" />
              <InputError class="mt-2" :message="form.errors.phone" />
            </div>

            <div>
              <InputLabel for="department" value="Department" />
              <select id="department" v-model="form.department"
                class="w-full border-gray-300 dark:border-gray-700 rounded-md shadow-sm mt-1 dark:bg-gray-900 dark:text-gray-300 focus:ring-indigo-500 focus:border-indigo-500"
                :class="{ 'border-red-500': form.errors.department }">
                <option value="" disabled>-- Select Department --</option>
                <option v-for="department in departmentOptions" :key="department" :value="department">
                  {{ department }}
                </option>
              </select>
              <InputError class="mt-2" :message="form.errors.department" />
            </div>

            <div class="flex items-center mt-4">
              <input id="head_of_department" type="checkbox" v-model="form.head_of_department"
                class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500 dark:bg-gray-700 dark:border-gray-600" />
              <InputLabel for="head_of_department" value="Head of Department (HOD)" class="ml-2" />
              <InputError class="mt-2" :message="form.errors.head_of_department" />
            </div>

            <div class="mt-6 flex justify-end space-x-3">
              <SecondaryButton @click="closeModal">Cancel</SecondaryButton>
              <PrimaryButton :class="{ 'opacity-25': form.processing }" :disabled="form.processing">
                {{ editingTeacher ? 'Update' : 'Create' }}
              </PrimaryButton>
            </div>
          </form>
        </div>
      </Modal>

    </div>
  </LayoutAuthenticated>
</template>
