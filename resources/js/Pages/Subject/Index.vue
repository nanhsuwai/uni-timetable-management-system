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
  subjects: {
    type: Object,
    default: () => ({ data: [], meta: {} }),
  },
  academicYears: Array,
  semesters: Array,
  filters: {
    type: Object,
    default: () => ({}),
  },
});

// filters
const filterCode = ref(props.filters.filterCode || "");
const filterName = ref(props.filters.filterName || "");
const filterYear = ref(props.filters.filterYear || "");
const filterSemester = ref(props.filters.filterSemester || "");

watch([filterCode, filterName, filterYear, filterSemester], () => {
  router.get(
    route("subject:all"),
    {
      filterCode: filterCode.value,
      filterName: filterName.value,
      filterYear: filterYear.value,
      filterSemester: filterSemester.value,
    },
    { preserveState: true, replace: true }
  );
});

// form
const form = useForm({
  code: "",
  name: "",
  academic_year_id: "",
  semester_id: "",
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
  form.academic_year_id = subject.academic_year_id;
  form.semester_id = subject.semester_id;
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

    <div class="py-6 max-w-7xl mx-auto sm:px-6 lg:px-8">
      <!-- Page Header -->
      <div class="flex justify-between items-center mb-6">
        <h1 class="text-xl font-semibold text-gray-800">Subjects</h1>
        <PrimaryButton @click="showCreateModal">+ Add Subject</PrimaryButton>
      </div>
      <!-- Filters -->
      <div
        class="mb-6 grid grid-cols-1 md:grid-cols-4 gap-4 bg-white p-4 rounded-lg shadow"
      >
        <div>
          <InputLabel for="filterCode" value="Search by Code" />
          <TextInput
            id="filterCode"
            v-model="filterCode"
            type="text"
            placeholder="Search subjects by code..."
            class="w-full"
          />
        </div>
        <div>
          <InputLabel for="filterName" value="Search by Name" />
          <TextInput
            id="filterName"
            v-model="filterName"
            type="text"
            placeholder="Search subjects by name..."
            class="w-full"
          />
        </div>
        <div>
          <InputLabel for="filterYear" value="Academic Year" />
          <select
            id="filterYear"
            v-model="filterYear"
            class="w-full border-gray-300 rounded-md px-3 py-2"
          >
            <option value="">All Years</option>
            <option v-for="year in props.academicYears" :key="year.id" :value="year.id">
              {{ year.name }}
            </option>
          </select>
        </div>
        <div>
          <InputLabel for="filterSemester" value="Semester" />
          <select
            id="filterSemester"
            v-model="filterSemester"
            class="w-full border-gray-300 rounded-md px-3 py-2"
          >
            <option value="">All Semesters</option>
            <option v-for="sem in props.semesters" :key="sem.id" :value="sem.id">
              {{ sem.name }}
            </option>
          </select>
        </div>
      </div>

      <!-- Table -->
      <div class="bg-white shadow rounded-lg overflow-hidden">
        <table class="w-full text-sm text-center">
          <thead class="bg-gray-100 text-gray-700 uppercase text-xs">
            <tr>
              <th class="px-4 py-3">#</th>
              <th class="px-4 py-3">Code</th>
              <th class="px-4 py-3">Name</th>
              <th class="px-4 py-3">Academic Year</th>
              <th class="px-4 py-3">Semester</th>
              <th class="px-4 py-3">Status</th>
              <th class="px-4 py-3">Actions</th>
            </tr>
          </thead>
          <tbody>
            <tr
              v-for="(subject, index) in props.subjects.data"
              :key="subject.id"
              class="hover:bg-gray-50 transition"
            >
              <td class="px-4 py-2 border-b">
                {{ index + 1 + (props.subjects.per_page * (props.subjects.current_page - 1)) }}
              </td>
              <td class="px-4 py-2 border-b">{{ subject.code }}</td>
              <td class="px-4 py-2 border-b">{{ subject.name }}</td>
              <td class="px-4 py-2 border-b">{{ subject.academic_year?.name }}</td>
              <td class="px-4 py-2 border-b">{{ subject.semester?.name }}</td>
              <td class="px-4 py-2 border-b">
                <span
                  :class="[
                    'px-2 py-1 text-xs rounded-full font-medium',
                    subject.status === 'active'
                      ? 'bg-green-100 text-green-700'
                      : 'bg-gray-200 text-gray-600'
                  ]"
                >
                  {{ subject.status }}
                </span>
              </td>
              <td class="px-4 py-2 border-b space-x-2">
                <button
                  @click.prevent="showEditModal(subject)"
                  class="px-3 py-1 rounded-md bg-blue-500 text-white text-xs hover:bg-blue-600"
                >
                  Edit
                </button>
                <button
                  @click.prevent="assignTeachers(subject)"
                  class="px-3 py-1 rounded-md bg-green-500 text-white text-xs hover:bg-green-600"
                >
                  Assign Teachers
                </button>
                <button
                  @click.prevent="showDeleteSubjectModal(subject)"
                  class="px-3 py-1 rounded-md bg-red-500 text-white text-xs hover:bg-red-600"
                >
                  Delete
                </button>
              </td>
            </tr>
            <tr v-if="props.subjects.data.length === 0">
              <td colspan="7" class="py-6 text-gray-500 text-sm">
                No subjects found.
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- Pagination -->
      <div class="p-4 border-t bg-gray-50">
        <PaginationLinks :links="props.subjects.links" />
      </div>

      <!-- Create/Edit Modal -->
      <Modal :show="confirmingSubjectCreation" @close="closeModal">
        <div class="p-6">
          <h2 class="text-lg font-semibold text-gray-900 mb-4">
            {{ editingSubject ? "Edit Subject" : "Add Subject" }}
          </h2>
          <div class="space-y-4">
            <div>
              <InputLabel for="code" value="Subject Code" />
              <TextInput
                id="code"
                v-model="form.code"
                type="text"
                placeholder="e.g., CS101"
                class="w-full"
              />
              <InputError :message="form.errors.code" />
            </div>
            <div>
              <InputLabel for="name" value="Subject Name" />
              <TextInput
                id="name"
                v-model="form.name"
                type="text"
                placeholder="e.g., Introduction to Programming"
                class="w-full"
              />
              <InputError :message="form.errors.name" />
            </div>
            <div>
              <InputLabel for="academic_year_id" value="Academic Year" />
              <select
                v-model="form.academic_year_id"
                id="academic_year_id"
                class="w-full border-gray-300 rounded-md px-3 py-2"
              >
                <option value="">-- Select Academic Year --</option>
                <option
                  v-for="year in props.academicYears"
                  :key="year.id"
                  :value="year.id"
                >
                  {{ year.name }}
                </option>
              </select>
              <InputError :message="form.errors.academic_year_id" />
            </div>
            <div>
              <InputLabel for="semester_id" value="Semester" />
              <select
                v-model="form.semester_id"
                id="semester_id"
                class="w-full border-gray-300 rounded-md px-3 py-2"
              >
                <option value="">-- Select Semester --</option>
                <option
                  v-for="sem in props.semesters"
                  :key="sem.id"
                  :value="sem.id"
                >
                  {{ sem.name }}
                </option>
              </select>
              <InputError :message="form.errors.semester_id" />
            </div>
            <div>
              <InputLabel for="status" value="Status" />
              <select
                v-model="form.status"
                id="status"
                class="w-full border-gray-300 rounded-md px-3 py-2"
              >
                <option value="active">Active</option>
                <option value="inactive">Inactive</option>
              </select>
              <InputError :message="form.errors.status" />
            </div>
          </div>
          <div class="mt-6 flex justify-end space-x-2">
            <SecondaryButton @click.prevent="closeModal">Cancel</SecondaryButton>
            <PrimaryButton
              :disabled="form.processing"
              @click.prevent="createOrUpdateSubject"
            >
              {{ editingSubject ? "Update" : "Create" }}
            </PrimaryButton>
          </div>
        </div>
      </Modal>


      <!-- Delete Modal -->
      <Modal :show="showDeleteModal" @close="closeDeleteModal">
        <div class="p-6">
          <h2 class="text-lg font-semibold text-red-600 mb-2">
            Delete Subject
          </h2>
          <p class="text-sm text-gray-600">
            Are you sure you want to delete this subject? This action
            cannot be undone.
          </p>
          <div class="mt-6 flex justify-end space-x-2">
            <SecondaryButton @click.prevent="closeDeleteModal">Cancel</SecondaryButton>
            <PrimaryButton
              class="bg-red-500 hover:bg-red-600"
              :disabled="form.processing"
              @click.prevent="deleteSubject"
            >
              Delete
            </PrimaryButton>
          </div>
        </div>
      </Modal>
    </div>
  </LayoutAuthenticated>
</template>
