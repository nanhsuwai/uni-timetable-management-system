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
import checkPermissionComposable from "@/Composables/Permission/checkPermission";
import SectionMain from "../../Components/SectionMain.vue";

// Department enum options
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

let hasPermission = ref(checkPermissionComposable("teacher_manage"));

const filterName = ref(props.filters.filterName || "");
const filterEmail = ref(props.filters.filterEmail || "");
const filterDepartment = ref(props.filters.filterDepartment || "");
const filterStatus = ref(props.filters.filterStatus || "");

watch([filterName, filterEmail, filterDepartment, filterStatus], ([newName, newEmail, newDepartment, newStatus]) => {
  router.get(
    route("teacher:all"),
    { filterName: newName, filterEmail: newEmail, filterDepartment: newDepartment, filterStatus: newStatus },
    { preserveState: true, replace: true }
  );
});

const form = useForm({
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
const showApproveModal = ref(false);
const approvingTeacher = ref(null);
const showRejectModal = ref(false);
const rejectingTeacher = ref(null);

const showCreateModal = () => {
  confirmingTeacherCreation.value = true;
  form.reset();
  editingTeacher.value = null;
};

const showEditModal = (teacher) => {
  editingTeacher.value = teacher;

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
    form.put(
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

const showApproveTeacherModal = (teacher) => {
  showApproveModal.value = true;
  approvingTeacher.value = teacher;
};

const closeApproveModal = () => {
  showApproveModal.value = false;
  approvingTeacher.value = null;
};

const showRejectTeacherModal = (teacher) => {
  showRejectModal.value = true;
  rejectingTeacher.value = teacher;
};

const closeRejectModal = () => {
  showRejectModal.value = false;
  rejectingTeacher.value = null;
};

// Deletion logic: sends Inertia delete request
const deleteTeacher = () => {
  router.delete(route("teacher:delete", deletingTeacher.value), {
    preserveScroll: true,
    onFinish: () => {
      closeDeleteModal();
    },
    onSuccess: () => {
      // Check if the server redirected with an error message (like a foreign key violation)
      if (!router.page.props.flash.toast || !router.page.props.flash.toast.includes('❌')) {
        // If no error flash message is present, assume successful deletion and add a generic success toast
        toast.add({ message: "🗑️ Teacher deleted!" });
      }
    },
  });
};

const menuOpen = ref(null);

const approveTeacher = () => {
  router.put(route("teacher:status", approvingTeacher.value.id), { status: 'approved' }, {
    preserveScroll: true,
    onSuccess: () => {
      closeApproveModal();
      toast.add({ message: "✅ Teacher approved!" });
    },
  });
};

const rejectTeacher = () => {
  router.put(route("teacher:status", rejectingTeacher.value.id), { status: 'rejected' }, {
    preserveScroll: true,
    onSuccess: () => {
      closeRejectModal();
      toast.add({ message: "❌ Teacher rejected!" });
    },
  });
};
</script>

<template>
  <LayoutAuthenticated>

    <Head title="Teachers" />
    <SectionMain v-if="
      $page.props.auth.user.user_type == 'admin' ||
      hasPermission
    ">
      <div class="p-4 sm:p-6 bg-gray-50 dark:bg-gray-900 min-h-screen">
        <!-- Header and Add Button -->
        <div class="flex justify-between items-center mb-6">
          <h1 class="text-3xl font-bold text-gray-800 dark:text-gray-200">Teacher Management</h1>
          <PrimaryButton @click="showCreateModal">
            + Add New Teacher
          </PrimaryButton>
        </div>

        <!-- Filter Section -->
        <div
          class="mb-6 grid grid-cols-1 sm:grid-cols-2 md:grid-cols-5 gap-4 bg-white dark:bg-gray-800 p-4 rounded-lg shadow-md">
          <div class="md:col-span-1">
            <InputLabel for="filterName" value="Name" />
            <TextInput id="filterName" v-model="filterName" type="text" placeholder="Filter by name" class="w-full" />
          </div>
          <div class="md:col-span-1">
            <InputLabel for="filterEmail" value="Email" />
            <TextInput id="filterEmail" v-model="filterEmail" type="text" placeholder="Filter by email"
              class="w-full" />
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
      <th class="px-3 py-2 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider dark:text-gray-300">#</th>
      <th class="px-3 py-2 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider dark:text-gray-300">Name</th>
      <th class="px-3 py-2 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider dark:text-gray-300">Email</th>
      <th class="px-3 py-2 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider dark:text-gray-300">Phone</th>
      <th class="px-3 py-2 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider dark:text-gray-300">Department</th>
      <th class="px-3 py-2 text-center text-xs font-semibold text-gray-600 uppercase tracking-wider dark:text-gray-300">Status</th>
      <th class="px-3 py-2 text-center text-xs font-semibold text-gray-600 uppercase tracking-wider dark:text-gray-300">HOD</th>
      <th class="px-3 py-2 text-right text-xs font-semibold text-gray-600 uppercase tracking-wider dark:text-gray-300">Actions</th>
    </tr>
  </thead>

  <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
    <tr v-for="(teacher, index) in teachers.data" :key="teacher.id" class="hover:bg-gray-50 dark:hover:bg-gray-700 transition duration-150">
      
      <!-- Index -->
      <td class="px-3 py-2 whitespace-nowrap text-sm text-gray-500 dark:text-gray-300">
        {{ index + 1 + (props.teachers.per_page * (props.teachers.current_page - 1)) || index + 1 }}
      </td>

      <!-- Name -->
      <td class="px-3 py-2 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-gray-100">{{ teacher.name }}</td>

      <!-- Email -->
      <td class="px-3 py-2 whitespace-nowrap text-sm text-gray-500 dark:text-gray-300">{{ teacher.email }}</td>

      <!-- Phone -->
      <td class="px-3 py-2 whitespace-nowrap text-sm text-gray-500 dark:text-gray-300">{{ teacher.phone }}</td>

      <!-- Department -->
      <td class="px-3 py-2 whitespace-nowrap text-sm text-gray-500 dark:text-gray-300">{{ teacher.department }}</td>

      <!-- Status -->
      <td class="px-3 py-2 whitespace-nowrap text-center text-sm text-gray-500 dark:text-gray-300">
        <span :class="[
          'inline-flex px-2 py-1 text-xs font-semibold leading-5 rounded-full',
          teacher.status === 'approved' ? 'bg-green-100 text-green-800 dark:bg-green-800 dark:text-green-100' :
          teacher.status === 'pending' ? 'bg-yellow-100 text-yellow-800 dark:bg-yellow-800 dark:text-yellow-100' :
          'bg-red-100 text-red-800 dark:bg-red-800 dark:text-red-100'
        ]">
          {{ teacher.status === 'approved' ? 'Approved' : teacher.status === 'pending' ? 'Pending' : 'Rejected' }}
        </span>
      </td>

      <!-- HOD -->
      <td class="px-3 py-2 whitespace-nowrap text-center text-sm text-gray-500 dark:text-gray-300">
        <span :class="[
          'inline-flex px-2 py-1 text-xs font-semibold leading-5 rounded-full',
          teacher.head_of_department ? 'bg-green-100 text-green-800 dark:bg-green-800 dark:text-green-100' :
          'bg-yellow-100 text-yellow-800 dark:bg-yellow-800 dark:text-yellow-100'
        ]">
          {{ teacher.head_of_department ? 'HOD' : 'Staff' }}
        </span>
      </td>

      <!-- Actions -->
      <td class="px-3 py-2 whitespace-nowrap text-sm font-medium">
        <!-- Approve & Reject (first line) -->
        <div class="flex items-center gap-1 mb-1 flex-wrap">
          <button 
            @click.prevent="showApproveTeacherModal(teacher)" 
            v-if="teacher.status === 'pending'"
            class="px-2 py-1 rounded-md bg-green-500 text-white text-xs hover:bg-green-600 disabled:opacity-50 disabled:cursor-not-allowed flex items-center gap-1 transition"
            :disabled="router.pending"
          >
            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
            </svg>
            <span>Approve</span>
          </button>

          <button 
            @click.prevent="showRejectTeacherModal(teacher)" 
            v-if="teacher.status === 'pending'"
            class="px-2 py-1 rounded-md bg-red-500 text-white text-xs hover:bg-red-600 disabled:opacity-50 disabled:cursor-not-allowed flex items-center gap-1 transition"
            :disabled="router.pending"
          >
            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
            <span>Reject</span>
          </button>
        </div>

        <!-- Delete & Edit (second line, below) -->
        <div class="flex items-center gap-1 flex-wrap">
          <SecondaryButton 
            @click="showEditModal(teacher)" 
            v-if="teacher.status === 'approved'"
            class="px-2 py-1 text-xs"
          >
            Edit
          </SecondaryButton>

          <button 
            @click.prevent="showDeleteTeacherModal(teacher)"
            class="px-2 py-1 rounded-md bg-red-500 text-white text-xs hover:bg-red-600 disabled:opacity-50 disabled:cursor-not-allowed flex items-center gap-1 transition"
            :disabled="router.pending"
          >
            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
            </svg>
            <span>Delete</span>
          </button>
        </div>
      </td>

    </tr>

    <!-- No teachers message -->
    <tr v-if="teachers.data.length === 0">
      <td colspan="8" class="px-3 py-6 text-center text-sm text-gray-500 dark:text-gray-400 font-medium">
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
      </div>

      <!-- Create/Edit Modal -->
      <Modal :show="confirmingTeacherCreation" @close="closeModal">
        <div class="p-6 dark:bg-gray-800">
          <h2 class="text-xl font-medium text-gray-900 dark:text-gray-100 mb-4">
            {{ editingTeacher ? 'Edit Teacher' : 'Add New Teacher' }}
          </h2>

          <form @submit.prevent="createOrUpdateTeacher">
            <div class="grid grid-cols-1 gap-4">
              <!-- Name -->
              <div>
                <InputLabel for="name" value="Name" />
                <TextInput id="name" v-model="form.name" type="text" class="mt-1 block w-full"
                  :class="{ 'border-red-500': form.errors.name }" required autofocus autocomplete="name" />
                <InputError class="mt-2" :message="form.errors.name" />
              </div>

              <!-- Email -->
              <div>
                <InputLabel for="email" value="Email" />
                <TextInput id="email" v-model="form.email" type="email" class="mt-1 block w-full"
                  :class="{ 'border-red-500': form.errors.email }" autocomplete="email" />
                <InputError class="mt-2" :message="form.errors.email" />
              </div>

              <!-- Phone -->
              <div>
                <InputLabel for="phone" value="Phone" />
                <TextInput id="phone" v-model="form.phone" type="text" class="mt-1 block w-full"
                  :class="{ 'border-red-500': form.errors.phone }" autocomplete="phone" />
                <InputError class="mt-2" :message="form.errors.phone" />
              </div>

              <!-- Department -->
              <div>
                <InputLabel for="department" value="Department" />
                <select id="department" v-model="form.department"
                  class="w-full border-gray-300 dark:border-gray-700 rounded-md shadow-sm mt-1 dark:bg-gray-900 dark:text-gray-300 focus:ring-indigo-500 focus:border-indigo-500"
                  :class="{ 'border-red-500': form.errors.department }">
                  <option value="" disabled>-- Select Department --</option>
                  <option v-for="department in departmentOptions" :key="department" :value="department">{{ department }}
                  </option>
                </select>
                <InputError class="mt-2" :message="form.errors.department" />
              </div>

              <!-- Head of Department Checkbox -->
              <div class="flex items-center mt-4">
                <input id="head_of_department" type="checkbox" v-model="form.head_of_department"
                  class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500 dark:bg-gray-700 dark:border-gray-600" />
                <InputLabel for="head_of_department" value="Head of Department (HOD)" class="ml-2" />
                <InputError class="mt-2" :message="form.errors.head_of_department" />
              </div>
            </div>

            <div class="mt-6 flex justify-end space-x-3">
              <SecondaryButton @click="closeModal">
                Cancel
              </SecondaryButton>

              <PrimaryButton :class="{ 'opacity-25': form.processing }" :disabled="form.processing">
                {{ editingTeacher ? 'Update' : 'Create' }}
              </PrimaryButton>
            </div>
          </form>
        </div>
      </Modal>

      <!-- Approve Confirmation Modal -->
      <Modal :show="showApproveModal" @close="closeApproveModal">
        <div class="p-6 dark:bg-gray-800">
          <h2 class="text-xl font-bold text-green-600 dark:text-green-400">
            Confirm Teacher Approval
          </h2>

          <p class="mt-4 text-sm text-gray-600 dark:text-gray-400">
            You are about to approve the teacher
            <span class="font-semibold text-gray-900 dark:text-gray-100">{{ approvingTeacher?.name }}</span> ({{
              approvingTeacher?.email }}).
            <br>
            <strong class="text-green-500">This will activate their account and grant them access to the
              system.</strong> Are you sure you want to proceed?
          </p>

          <div class="mt-6 flex justify-end space-x-3">
            <SecondaryButton @click="closeApproveModal">
              Cancel
            </SecondaryButton>

            <button @click="approveTeacher" :disabled="router.pending"
              class="px-4 py-2 bg-green-600 text-white font-semibold rounded-md shadow-md hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 disabled:opacity-50 disabled:cursor-not-allowed transition flex items-center space-x-2">
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
              </svg>
              <span>Approve Teacher</span>
            </button>
          </div>
        </div>
      </Modal>

      <!-- Reject Confirmation Modal -->
      <Modal :show="showRejectModal" @close="closeRejectModal">
        <div class="p-6 dark:bg-gray-800">
          <h2 class="text-xl font-bold text-red-600 dark:text-red-400">
            Confirm Teacher Rejection
          </h2>

          <p class="mt-4 text-sm text-gray-600 dark:text-gray-400">
            You are about to reject the teacher
            <span class="font-semibold text-gray-900 dark:text-gray-100">{{ rejectingTeacher?.name }}</span> ({{
              rejectingTeacher?.email }}).
            <br>
            <strong class="text-red-500">This will deny their access to the system and mark their application as
              rejected.</strong> Are you sure you want to proceed?
          </p>

          <div class="mt-6 flex justify-end space-x-3">
            <SecondaryButton @click="closeRejectModal">
              Cancel
            </SecondaryButton>

            <button @click="rejectTeacher" :disabled="router.pending"
              class="px-4 py-2 bg-red-600 text-white font-semibold rounded-md shadow-md hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 disabled:opacity-50 disabled:cursor-not-allowed transition flex items-center space-x-2">
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
              </svg>
              <span>Reject Teacher</span>
            </button>
          </div>
        </div>
      </Modal>

      <!-- Delete Confirmation Modal -->
      <Modal :show="showDeleteModal" @close="closeDeleteModal">
        <div class="p-6 dark:bg-gray-800">
          <h2 class="text-xl font-bold text-red-600 dark:text-red-400">
            Confirm Teacher Deletion
          </h2>

          <p class="mt-4 text-sm text-gray-600 dark:text-gray-400">
            You are about to permanently delete the teacher
            <span class="font-semibold text-gray-900 dark:text-gray-100">{{ deletingTeacher?.name }}</span> ({{
              deletingTeacher?.email }}).
            <br>
            <strong class="text-red-500">This action cannot be undone.</strong> Are you sure you want to proceed?
          </p>

          <div class="mt-6 flex justify-end space-x-3">
            <SecondaryButton @click="closeDeleteModal">
              Cancel
            </SecondaryButton>

            <!-- Optimized Delete Button: Disabled when a router request is pending -->
            <button @click="deleteTeacher" :disabled="router.pending"
              class="px-4 py-2 bg-red-600 text-white font-semibold rounded-md shadow-md hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 disabled:opacity-50 disabled:cursor-not-allowed transition flex items-center space-x-2">
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
              </svg>
              <span>Delete Permanently</span>
            </button>
          </div>
        </div>
      </Modal>
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
