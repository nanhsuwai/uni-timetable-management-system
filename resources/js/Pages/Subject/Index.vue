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

const props = defineProps({
  subjects: {
    type: Object,
    default: () => ({ data: [], meta: {} }),
  },
  semesters: Array,
  levels: Array,
  programs: Array,
  filters: {
    type: Object,
    default: () => ({}),
  },
});

let hasPermission = ref(checkPermissionComposable("subject_manage"));

// Filters
const filterCode = ref(props.filters.filterCode || "");
const filterName = ref(props.filters.filterName || "");
const filterLevel = ref(props.filters.filterLevel || "");
const filterProgram = ref(props.filters.filterProgram || "");
const filterSemester = ref(props.filters.filterSemester || "");

watch([filterCode, filterName, filterLevel, filterProgram, filterSemester], () => {
  router.get(
    route("subject:all"),
    {
      filterCode: filterCode.value,
      filterName: filterName.value,
      filterLevel: filterLevel.value,
      filterProgram: filterProgram.value,
      filterSemester: filterSemester.value,
    },
    { preserveState: true, replace: true }
  );
});

// Form
const form = useForm({
  code: "",
  name: "",
  level: "",
  program: "",
  semester: "",
  status: "active",
});

const confirmingSubjectCreation = ref(false);
const editingSubject = ref(null);
const showDeleteModal = ref(false);
const deletingSubject = ref(null);

const showCreateModal = () => {
  confirmingSubjectCreation.value = true;
  form.reset();
  editingSubject.value = null;
};

const showEditModal = (subject) => {
  editingSubject.value = subject;
  form.code = subject.code;
  form.name = subject.name;
  form.level = subject.level;
  form.program = subject.program;
  form.semester = subject.semester;
  form.status = subject.status;
  confirmingSubjectCreation.value = true;
};

const closeModal = () => {
  confirmingSubjectCreation.value = false;
  form.reset();
  editingSubject.value = null;
};

const createOrUpdateSubject = () => {
  if (editingSubject.value) {
    form.post(
      route("subject:update", { subject: editingSubject.value.id }),
      {
        preserveScroll: true,
        onSuccess: () => {
          closeModal();
          toast.add({ message: "✅ Subject updated!" });
        },
      }
    );
  } else {
    form.post(route("subject:create"), {
      preserveScroll: true,
      onSuccess: () => {
        closeModal();
        toast.add({ message: "✅ Subject created!" });
      },
    });
  }
};

const showDeleteSubjectModal = (subject) => {
  showDeleteModal.value = true;
  deletingSubject.value = subject;
};

const closeDeleteModal = () => {
  showDeleteModal.value = false;
  deletingSubject.value = null;
};

const deleteSubject = () => {
  router.delete(route("subject:delete", deletingSubject.value), {
    preserveScroll: true,
    onSuccess: () => {
      closeDeleteModal();
      toast.add({ message: "🗑️ Subject deleted!" });
    },
  });
};

const assignTeachers = (subject) => {
  router.visit(route("subject:assign-teacher", subject.id));
};
</script>

<template>
  <LayoutAuthenticated>

    <Head title="Subjects" />
    <SectionMain v-if="['teacher', 'admin'].includes($page.props.auth.user.user_type) || hasPermission">

      <div class="py-8 max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div
          class="flex justify-between items-center mb-8 p-4 bg-white dark:bg-gray-800 rounded-xl shadow-md dark:shadow-xl dark:shadow-indigo-900/20">
          <h1 class="text-2xl font-bold text-gray-900 dark:text-gray-100 tracking-wide">
            Curriculum Subjects
          </h1>
          <PrimaryButton @click="showCreateModal"
            class="px-4 py-2 font-semibold shadow-lg hover:shadow-xl transition-all duration-300 transform hover:scale-[1.02]">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-1 -ml-1" viewBox="0 0 24 24" fill="none"
              stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
              <path d="M12 5v14m-7-7h14" />
            </svg>
            Add Subject
          </PrimaryButton>
        </div>

        <div
          class="mb-8 grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-4 p-6 bg-white dark:bg-gray-800 rounded-xl shadow-lg ring-1 ring-gray-200 dark:ring-gray-700">
          <div>
            <InputLabel for="filterCode" value="Code" class="dark:text-gray-300" />
            <TextInput id="filterCode" v-model="filterCode" type="text" placeholder="Enter Subject Code"
              class="w-full dark:bg-gray-700 dark:border-gray-600 dark:text-gray-200 focus:border-indigo-500 dark:focus:border-indigo-400 focus:ring-indigo-500 dark:focus:ring-indigo-400" />
          </div>
          <div>
            <InputLabel for="filterName" value="Name" class="dark:text-gray-300" />
            <TextInput id="filterName" v-model="filterName" type="text" placeholder="Enter subject name"
              class="w-full dark:bg-gray-700 dark:border-gray-600 dark:text-gray-200 focus:border-indigo-500 dark:focus:border-indigo-400 focus:ring-indigo-500 dark:focus:ring-indigo-400" />
          </div>
          <div>
            <InputLabel for="filterLevel" value="Academic Level" class="dark:text-gray-300" />
            <select id="filterLevel" v-model="filterLevel"
              class="w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 rounded-lg px-3 py-2.5 shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
              <option value="">All Levels</option>
              <option v-for="level in props.levels" :key="level" :value="level">
                {{ level }}
              </option>
            </select>
          </div>
          <div>
            <InputLabel for="filterProgram" value="Academic Program" class="dark:text-gray-300" />
            <select id="filterProgram" v-model="filterProgram"
              class="w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 rounded-lg px-3 py-2.5 shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
              <option value="">All Programs</option>
              <option v-for="program in props.programs" :key="program" :value="program">
                {{ program }}
              </option>
            </select>
          </div>
        </div>

        <div
          class="bg-white dark:bg-gray-800 shadow-xl dark:shadow-2xl dark:shadow-indigo-900/20 rounded-xl overflow-hidden ring-1 ring-gray-200 dark:ring-gray-700">
          <div class="overflow-x-auto">
            <table class="w-full text-sm text-center table-auto border-collapse">
              <thead
                class="bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 uppercase text-xs tracking-wider">
                <tr>
                  <th class="px-4 py-3 w-12 border-b dark:border-gray-600 font-bold">
                    #
                  </th>
                  <th class="px-4 py-3 border-b dark:border-gray-600 text-left font-bold">
                    Code
                  </th>
                  <th class="px-4 py-3 border-b dark:border-gray-600 text-left font-bold">
                    Name
                  </th>
                  <th class="px-4 py-3 border-b dark:border-gray-600 font-bold">
                    Level
                  </th>
                  <th class="px-4 py-3 border-b dark:border-gray-600 font-bold">
                    Program
                  </th>
                  <th class="px-4 py-3 border-b dark:border-gray-600 font-bold">
                    Semester
                  </th>
                  <th class="px-4 py-3 w-48 border-b dark:border-gray-600 font-bold">
                    Status
                  </th>
                  <th class="px-4 py-3 w-56 border-b dark:border-gray-600 font-bold">
                    Actions
                  </th>
                </tr>
              </thead>
              <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                <tr v-for="(subject, index) in props.subjects.data" :key="subject.id"
                  class="bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300 hover:bg-indigo-50 dark:hover:bg-gray-700 transition duration-150">
                  <td class="px-4 py-3">
                    {{
                      index +
                      1 +
                      props.subjects.per_page * (props.subjects.current_page - 1)
                    }}
                  </td>
                  <td class="px-4 py-3 font-semibold text-indigo-600 dark:text-indigo-400 text-left">
                    {{ subject.code }}
                  </td>
                  <td class="px-4 py-3 text-left">
                    {{ subject.name }}
                  </td>
                  <td class="px-4 py-3">{{ subject.level }}</td>
                  <td class="px-4 py-3">{{ subject.program }}</td>
                  <td class="px-4 py-3">{{ subject.semester }}</td>
                  <td class="px-4 py-3 capitalize">
                    <span :class="[
                      'px-3 py-1 text-xs rounded-full font-semibold shadow-sm',
                      subject.status === 'active'
                        ? 'bg-green-100 text-green-700 dark:bg-green-800 dark:text-green-200'
                        : 'bg-red-100 text-red-700 dark:bg-red-800 dark:text-red-200',
                    ]">
                      {{ subject.status }}
                    </span>
                  </td>
                  <td class="px-4 py-3 space-x-2 flex justify-center items-center">
                    <button @click.prevent="assignTeachers(subject)"
                      class="px-3 py-1.5 rounded-lg bg-green-600 text-white text-xs font-medium hover:bg-green-700 transition shadow-md dark:bg-green-700 dark:hover:bg-green-600">
                      Assign Teachers
                    </button>

                    <button @click.prevent="showEditModal(subject)"
                      class="px-3 py-1.5 rounded-lg bg-blue-600 text-white text-xs font-medium hover:bg-blue-700 transition shadow-md dark:bg-blue-700 dark:hover:bg-blue-600">
                      Edit
                    </button>
                    <button @click.prevent="showDeleteSubjectModal(subject)"
                      class="px-3 py-1.5 rounded-lg bg-red-600 text-white text-xs font-medium hover:bg-red-700 transition shadow-md dark:bg-red-700 dark:hover:bg-red-600">
                      Delete
                    </button>
                  </td>
                </tr>
                <tr v-if="props.subjects.data.length === 0">
                  <td colspan="8" class="py-10 text-gray-500 dark:text-gray-400 text-base italic">
                    No subjects found matching your criteria.
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>

        <div
          class="mt-6 p-4 bg-white dark:bg-gray-800 rounded-xl shadow-md ring-1 ring-gray-200 dark:ring-gray-700 flex justify-center">
          <PaginationLinks :links="props.subjects.links" />
        </div>

        <Modal :show="confirmingSubjectCreation" @close="closeModal">
          <div class="p-6 bg-white dark:bg-gray-800 rounded-lg">
            <h2 class="text-xl font-bold text-gray-900 dark:text-gray-100 mb-6 border-b pb-3 dark:border-gray-700">
              {{ editingSubject ? 'Edit Subject' : 'Add Subject' }}
            </h2>
            <form @submit.prevent="createOrUpdateSubject" class="space-y-4">
              <div>
                <InputLabel for="code" value="Code" class="dark:text-gray-300" />
                <TextInput id="code" v-model="form.code" placeholder="Enter Subject code"
                  class="w-full dark:bg-gray-700 dark:border-gray-600 dark:text-gray-200" />
                <InputError :message="form.errors.code" />
              </div>
              <div>
                <InputLabel for="name" value="Name" class="dark:text-gray-300" />
                <TextInput id="name" v-model="form.name" placeholder="Enter Subject name"
                  class="w-full dark:bg-gray-700 dark:border-gray-600 dark:text-gray-200" />
                <InputError :message="form.errors.name" />
              </div>
              <div>
                <InputLabel for="level" value="Level" class="dark:text-gray-300" />
                <select v-model="form.level"
                  class="w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 rounded-lg px-3 py-2.5 shadow-sm">
                  <option value="">-- Select Level --</option>
                  <option v-for="level in props.levels" :key="level" :value="level">
                    {{ level }}
                  </option>
                </select>
                <InputError :message="form.errors.level" />
              </div>
              <div>
                <InputLabel for="program" value="Program" class="dark:text-gray-300" />
                <select v-model="form.program"
                  class="w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 rounded-lg px-3 py-2.5 shadow-sm">
                  <option value="">-- Select Program --</option>
                  <option v-for="program in props.programs" :key="program" :value="program">
                    {{ program }}
                  </option>
                </select>
                <InputError :message="form.errors.program" />
              </div>
              <div>
                <InputLabel for="semester" value="Semester" class="dark:text-gray-300" />
                <select v-model="form.semester"
                  class="w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 rounded-lg px-3 py-2.5 shadow-sm">
                  <option value="">-- Select Semester --</option>
                  <option v-for="sem in props.semesters" :key="sem" :value="sem">
                    {{ sem }}
                  </option>
                </select>
                <InputError :message="form.errors.semester" />
              </div>
              <div>
                <InputLabel for="status" value="Status" class="dark:text-gray-300" />
                <select v-model="form.status"
                  class="w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 rounded-lg px-3 py-2.5 shadow-sm">
                  <option value="active">Active</option>
                  <option value="inactive">Inactive</option>
                </select>
                <InputError :message="form.errors.status" />
              </div>
              <div class="mt-6 flex justify-end space-x-3 pt-4 border-t dark:border-gray-700">
                <SecondaryButton @click.prevent="closeModal">Cancel</SecondaryButton>
                <PrimaryButton :disabled="form.processing" type="submit">
                  {{ editingSubject ? 'Update' : 'Create' }}
                </PrimaryButton>
              </div>
            </form>
          </div>
        </Modal>

        <!-- Delete Modal -->
        <Modal :show="showDeleteModal" @close="closeDeleteModal">
          <div class="p-6">
            <h2 class="text-lg font-semibold text-red-600 mb-2">Delete Subject</h2>
            <p class="text-sm text-gray-600">Are you sure you want to delete this subject? This action cannot be undone.
            </p>
            <div class="mt-6 flex justify-end space-x-2">
              <SecondaryButton @click.prevent="closeDeleteModal">Cancel</SecondaryButton>
              <PrimaryButton class="bg-red-500 hover:bg-red-600" :disabled="form.processing"
                @click.prevent="deleteSubject">Delete</PrimaryButton>
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