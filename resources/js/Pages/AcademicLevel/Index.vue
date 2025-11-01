<script setup>
import { Head, useForm, router } from "@inertiajs/vue3";
import { ref, watch, computed } from "vue";
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
  levels: { type: Object, default: () => ({ data: [], meta: {} }) },
  filters: { type: Object, default: () => ({}) },
  academicPrograms: { type: Array, default: () => [] },
  classrooms: { type: Array, default: () => [] },
  fixedLevels: { type: Array, default: () => [] },
  academicYears: { type: Array, default: () => [] },
  teachers: { type: Array, default: () => [] },
  assignTeachers: { type: Array, default: () => []},
  assignClassrooms: { type: Array, default: () => [] }
});
// Filters
const filterName = ref(props.filters.filterName || "");
const filterProgram = ref(props.filters.filterProgram || "");

watch([filterName, filterProgram], ([newName, newProgram]) => {
  router.get(
    route("academic_level:all"),
    { filterName: newName, filterProgram: newProgram },
    { preserveState: true, replace: true }
  );
});

// Form
const form = useForm({
  academic_year_id: "",
  program_id: "",
  name: "",
});

// Section Form
const sectionForm = useForm({
  name: "",
  classroom_id: "",
  section_head_teacher_id: "",
  academic_level_id: "",
});

//teacher list not include assignteacher id in sections table
const propsAvailableTeachers = computed(() => {
  return props.teachers.filter(t1 => !props.assignTeachers.some(t2 => t2.id === t1.id && t2.id !== Number(sectionForm.section_head_teacher_id)) );
});
//classroom list not include assignclassroom id in sections table
const propsAvailableClassrooms = computed(() => { 
  return props.classrooms.filter(c1 => !props.assignClassrooms.some(c2 => c2.id === c1.id && c2.id !== Number(sectionForm.classroom_id)) );d
});





// State
const confirmingModal = ref(false);
const editingLevel = ref(null);
const deletingLevel = ref(null);

// Section State
const showingSectionsModal = ref(false);
const showingSectionFormModal = ref(false);
const selectedLevel = ref(null);
const editingSection = ref(null);
const deletingSection = ref(null);

// Dependent Programs
const filteredPrograms = computed(() => {
  if (!form.academic_year_id) return [];
  return props.academicPrograms.filter(
    (p) => p.academic_year_id === form.academic_year_id
  );
});

// Watch year change to reset program if invalid
watch(
  () => form.academic_year_id,
  (newYear) => {
    if (!filteredPrograms.value.find((p) => p.id === form.program_id)) {
      form.program_id = "";
    }
  }
);

// Modal Handling
const openCreateModal = () => {
  confirmingModal.value = true;
  form.reset();
  editingLevel.value = null;
};

const openEditModal = (level) => {
  editingLevel.value = level;
  form.academic_year_id = level.academic_year_id;
  form.program_id = level.program_id;
  form.name = level.name;
  confirmingModal.value = true;
};

const closeModal = () => {
  confirmingModal.value = false;
  form.reset();
  editingLevel.value = null;
};

// CRUD
const saveLevel = () => {
  const url = editingLevel.value
    ? route("academic_level:update", { academicLevel: editingLevel.value.id })
    : route("academic_level:create");

  form.post(url, {
    preserveScroll: true,
    onSuccess: () => {
      closeModal();
      toast.add({
        message: editingLevel.value
          ? "✅ Academic Level updated!"
          : "✅ Academic Level created!",
      });
    },
    onError: () => form.reset(),
  });
};

const confirmDelete = (level) => {
  deletingLevel.value = level;
};

const deleteLevel = () => {
  if (!deletingLevel.value) return;
  router.delete(route("academic_level:delete", deletingLevel.value), {
    preserveScroll: true,
    onSuccess: () => {
      deletingLevel.value = null;
      toast.add({ message: "🗑️ Academic Level deleted!" });
    },
  });
};

// Sections
const openSectionsModal = (level) => {
  selectedLevel.value = level;
  showingSectionsModal.value = true;
};

const closeSectionsModal = () => {
  showingSectionsModal.value = false;
  sectionForm.reset();
  editingSection.value = null;
  selectedLevel.value = null;
};

const openEditSectionModal = (section) => {
  editingSection.value = section;
  if (section) {
    sectionForm.name = section.name;
    sectionForm.classroom_id = section.classroom?.id?.toString() || "";
    sectionForm.section_head_teacher_id = section.section_head_teacher_id?.toString() || "";
    sectionForm.academic_level_id = section.academic_level_id;
  } else {
    sectionForm.academic_level_id = selectedLevel.value.id;
    sectionForm.classroom_id = "";
  }
  showingSectionFormModal.value = true;
};

const closeEditSectionModal = () => {
  showingSectionFormModal.value = false;
  editingSection.value = null;
  sectionForm.reset();
};

const saveSection = () => {
  sectionForm.academic_level_id = selectedLevel.value.id;
  const url = editingSection.value
    ? route("academic_level:section.update", { section: editingSection.value.id })
    : route("academic_level:section.create");

  sectionForm.post(url, {
    preserveScroll: true,
    onSuccess: () => {
      closeEditSectionModal();
      closeSectionsModal();
      router.reload({ only: ["levels"] });
      toast.add({
        message: editingSection.value ? "✅ Section updated!" : "✅ Section created!",
      });
    },
    onError: (error) =>{
      toast.add({
        message: error.general || "Please check the form for errors.",
        type: "error",
      });
    },
  });
};

const confirmDeleteSection = (section) => {
  deletingSection.value = section;
};

const deleteSection = () => {
  if (!deletingSection.value) return;
  router.delete(route("academic_level:section.delete", { section: deletingSection.value.id }), {
    preserveScroll: true,
    onSuccess: () => {
      closeSectionsModal();
      deletingSection.value = null;
      router.reload({ only: ["levels"] });
      toast.add({ message: "🗑️ Section deleted!" });
    },
  });
};

const toggleSectionStatus = (section, newStatus) => {
  router.post(
    route("academic_level:section.toggle-status", { section: section.id }),
    { status: newStatus },
    {
      preserveScroll: true,
      onSuccess: () => {
        closeSectionsModal();
        router.reload({ only: ["levels"] });
        toast.add({ message: "✅ Status updated!" });
      },
    }
  );
};
</script>


<template>
  <LayoutAuthenticated>

    <Head title="Academic Levels" />
     <SectionMain v-if="
          $page.props.auth.user.user_type == 'admin' ||
          hasPermission
        ">
    <div class="py-6 max-w-7xl mx-auto sm:px-6 lg:px-8">
      <!-- Header -->
      <div class="flex justify-between items-center mb-6">
        <h1 class="text-xl font-semibold text-gray-800">Academic Levels</h1>
        <PrimaryButton @click="openCreateModal">+ Add Level</PrimaryButton>
      </div>

      <!-- Filters -->
      <div class="mb-6 grid grid-cols-1 sm:grid-cols-2 gap-4 bg-white p-4 rounded-lg shadow">
        <div>
          <InputLabel for="filterName" value="Filter by Name" />
          <TextInput id="filterName" v-model="filterName" type="text" placeholder="Search levels..." class="w-full" />
        </div>
        <div>
          <InputLabel for="filterProgram" value="Filter by Program" />
          <select id="filterProgram" v-model="filterProgram"
            class="w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
            <option value="">All Programs</option>
            <option v-for="program in props.academicPrograms" :key="program" :value="program">
              {{ program.name }}
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
              <th class="px-4 py-3">Name</th>
              <th class="px-4 py-3">Academic Program</th>
              <th class="px-4 py-3">Actions</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="(level, index) in props.levels.data" :key="level.id" class="hover:bg-gray-50 transition">
              <td class="px-4 py-2 border-b">
                {{ index + 1 + (props.levels.per_page * (props.levels.current_page - 1)) }}
              </td>
              <td class="px-4 py-2 border-b">{{ level.name }}</td>
              <td class="px-4 py-2 border-b">{{ level.academic_program.name }}</td>
              <td class="px-4 py-2 border-b space-x-2">
                <button @click.prevent="openSectionsModal(level)"
                  class="px-3 py-1 rounded-md bg-green-500 text-white text-xs hover:bg-green-600">
                  Sections ({{ level.sections.length }})
                </button>
                <button @click.prevent="openEditModal(level)"
                  class="px-3 py-1 rounded-md bg-blue-500 text-white text-xs hover:bg-blue-600">
                  Edit
                </button>
                <button @click.prevent="confirmDelete(level)"
                  class="px-3 py-1 rounded-md bg-red-500 text-white text-xs hover:bg-red-600">
                  Delete
                </button>
              </td>
            </tr>
            <tr v-if="props.levels.data.length === 0">
              <td colspan="4" class="py-6 text-gray-500 text-sm">
                No academic levels found.
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- Pagination -->
      <div class="p-4 border-t bg-gray-50">
        <PaginationLinks :links="props.levels.links" />
      </div>

      <!-- Create/Edit Modal -->
      <Modal :show="confirmingModal" @close="closeModal">
        <div class="p-6">
          <h2 class="text-lg font-semibold text-gray-900 mb-4">
            {{ editingLevel ? "Edit Academic Level" : "Add Academic Level" }}
          </h2>
          <div class="space-y-4">
            <!-- academic_year selection option -->
            <div>
              <InputLabel for="academic_year_id" value="Academic Year" />
              <select id="academic_year_id" v-model="form.academic_year_id"
                class="w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
                <option value="">Select Academic Year</option>
                <option v-for="year in props.academicYears" :key="year.id" :value="year.id">
                  {{ year.name }}
                </option>
              </select>
              <InputError :message="form.errors.academic_year_id" />
            </div>

            <div>
              <InputLabel for="program_id" value="Academic Program" />
              <select id="program_id" v-model="form.program_id"
                class="w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
                <option value="">Select Program</option>
                <option v-for="program in filteredPrograms" :key="program.id" :value="program.id">
                  {{ program.name }}
                </option>
              </select>
              <InputError :message="form.errors.program_id" />
            </div>

            <div>
              <InputLabel for="name" value="Level Name" />
              <select id="name" v-model="form.name"
                class="w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
                <option value="">Select Level</option>
                <option v-for="level in props.fixedLevels" :key="level" :value="level">
                  {{ level }}
                </option>
              </select>
              <InputError :message="form.errors.name" />
            </div>

          </div>
          <div class="mt-6 flex justify-end space-x-2">
            <SecondaryButton @click.prevent="closeModal">Cancel</SecondaryButton>
            <PrimaryButton :disabled="form.processing" @click.prevent="saveLevel">
              {{ editingLevel ? "Update" : "Add" }}
            </PrimaryButton>
          </div>
        </div>
      </Modal>

      <!-- Delete Modal -->
      <Modal :show="!!deletingLevel" @close="() => (deletingLevel = null)">
        <div class="p-6">
          <h2 class="text-lg font-semibold text-red-600 mb-2">Delete Academic Level</h2>
          <p class="text-sm text-gray-600">
            Are you sure you want to delete this academic level? This action cannot be undone.
          </p>
          <div class="mt-6 flex justify-end space-x-2">
            <SecondaryButton @click.prevent="() => (deletingLevel = null)">
              Cancel
            </SecondaryButton>
            <PrimaryButton class="bg-red-500 hover:bg-red-600" :disabled="form.processing" @click.prevent="deleteLevel">
              Delete
            </PrimaryButton>
          </div>
        </div>
      </Modal>

      <!-- Sections List Modal -->
      <Modal :show="showingSectionsModal" @close="closeSectionsModal" :maxWidth="'2xl'">
        <div class="p-6">
          <div class="flex justify-between items-center mb-4">
            <h2 class="text-lg font-semibold text-gray-900">Sections for {{ selectedLevel?.name }}</h2>
            <PrimaryButton @click="openEditSectionModal(null)" class="ml-2">+ Add Section</PrimaryButton>
          </div>
          <div v-if="selectedLevel?.sections && selectedLevel.sections.length > 0"
            class="bg-white shadow rounded-lg overflow-hidden">
            <table class="w-full text-sm text-center">
              <thead class="bg-gray-100 text-gray-700 uppercase text-xs">
                <tr>
                  <th class="px-4 py-3">#</th>
                  <th class="px-4 py-3">Name</th>
                  <th class="px-4 py-3">Status</th>
                  <th class="px-4 py-3">Classroom</th>
                  <th class="px-4 py-3">Section Head Teacher</th>
                  <th class="px-4 py-3">Actions</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="(section, index) in selectedLevel.sections" :key="section.id"
                  class="hover:bg-gray-50 transition">
                  <td class="px-4 py-2 border-b">{{ index + 1 }}</td>
                  <td class="px-4 py-2 border-b">{{ section.name }}</td>
                  <td class="px-4 py-2 border-b">
                    <span :class="{
                      'bg-green-100 text-green-800': section.status === 'active',
                      'bg-yellow-100 text-yellow-800': section.status === 'inactive',
                      'bg-gray-100 text-gray-800': section.status === 'archived'
                    }" class="px-2 py-1 rounded-full text-xs font-medium capitalize">
                      {{ section.status }}
                    </span>
                  </td>
                  <td class="px-4 py-2 border-b">
                    {{ section.classroom ? section.classroom.room_no : '-' }}
                  </td>
                  <td class="px-4 py-2 border-b">
                    {{ section.section_head_teacher ? section.section_head_teacher.name : '-' }}
                  </td>
                  <td class="px-4 py-2 border-b space-x-2">
                    <button
                      @click.prevent="toggleSectionStatus(section, section.status === 'active' ? 'inactive' : 'active')"
                      class="px-2 py-1 rounded-md bg-blue-500 text-white text-xs hover:bg-blue-600">
                      {{ section.status === 'active' ? 'Inactive' : 'Active' }}
                    </button>
                    <button @click.prevent="openEditSectionModal(section)"
                      class="px-2 py-1 rounded-md bg-indigo-500 text-white text-xs hover:bg-indigo-600">
                      Edit
                    </button>
                    <button @click.prevent="confirmDeleteSection(section)"
                      class="px-2 py-1 rounded-md bg-red-500 text-white text-xs hover:bg-red-600">
                      Delete
                    </button>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
          <div v-else class="text-center py-8 text-gray-500">
            No sections found for this level.
          </div>
          <div class="mt-6 flex justify-end">
            <SecondaryButton @click.prevent="closeSectionsModal">Close</SecondaryButton>
          </div>
        </div>
      </Modal>

      <!-- Section Form Modal -->
      <Modal :show="showingSectionFormModal" @close="closeEditSectionModal" class="z-10">
        <div class="p-6">
          <h2 class="text-lg font-semibold text-gray-900 mb-4">
            {{ editingSection ? "Edit Section" : "Add Section" }} - {{ selectedLevel?.name }}
          </h2>
          <div class="space-y-4">
            <div>
              <InputLabel for="section_name" value="Section Name" />
              <TextInput id="section_name" v-model="sectionForm.name" type="text" class="w-full"
                placeholder="Enter section name" />
              <InputError :message="sectionForm.errors.name" />
            </div>
            <div>
              <InputLabel for="classroom_id" value="Classroom" />
              <select id="classroom_id" v-model="sectionForm.classroom_id"
                class="w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
                <option value="">Select Classroom</option>
                <option v-for="classroom in propsAvailableClassrooms" :key="classroom.id" :value="classroom.id">
                  {{ classroom.room_no }}
                </option>
              </select>
              <InputError :message="sectionForm.errors.classroom_id" />
            </div>

            <div>
              <InputLabel for="section_head_teacher_id" value="Section Head Teacher" />
              <select id="section_head_teacher_id" v-model="sectionForm.section_head_teacher_id"
                class="w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
                <option value="">Select Teacher</option>
                <option v-for="teacher in propsAvailableTeachers" :key="teacher.id" :value="String(teacher.id)">
                  {{ teacher.name }}
                </option>
              </select>
              <InputError :message="sectionForm.errors.section_head_teacher_id" />
            </div>
           

          </div>
          <div class="mt-6 flex justify-end space-x-2">
            <SecondaryButton @click.prevent="closeEditSectionModal">Cancel</SecondaryButton>
            <PrimaryButton :disabled="sectionForm.processing" @click.prevent="saveSection">
              {{ editingSection ? "Update" : "Add" }}
            </PrimaryButton>
          </div>
        </div>
      </Modal>

      <!-- Delete Section Modal -->
      <Modal :show="!!deletingSection" @close="() => (deletingSection = null)">
        <div class="p-6">
          <h2 class="text-lg font-semibold text-red-600 mb-2">Delete Section</h2>
          <p class="text-sm text-gray-600">
            Are you sure you want to delete "{{ deletingSection?.name }}"?
          </p>
          <div class="mt-6 flex justify-end space-x-2">
            <SecondaryButton @click.prevent="() => (deletingSection = null)">
              Cancel
            </SecondaryButton>
            <PrimaryButton class="bg-red-500 hover:bg-red-600" :disabled="sectionForm.processing"
              @click.prevent="deleteSection">
              Delete
            </PrimaryButton>
          </div>
        </div>
      </Modal>
    </div>
     </SectionMain>
    <SectionMain v-else>
      <h2>No Permissions</h2>
    </SectionMain>
  </LayoutAuthenticated>
</template>
