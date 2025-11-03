<script setup>
import { Head, useForm, router } from "@inertiajs/vue3";
import { ref, computed } from "vue";
import LayoutAuthenticated from "@/Layouts/LayoutAuthenticated.vue";
import SectionTitleLineWithButton from "@/Components/SectionTitleLineWithButton.vue";
import CardBox from "@/Components/CardBox.vue";
import BaseButton from "@/Components/BaseButton.vue";
import SecondaryButton from "@/Components/SecondaryButton.vue";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import { mdiArrowLeft, mdiAccountPlus, mdiAccountMinus } from "@mdi/js";
import SectionMain from "../../Components/SectionMain.vue";
import checkPermissionComposable from "../../Composables/Permission/checkPermission";

const props = defineProps({
  subject: {
    type: Object,
    required: true,
  },
  availableTeachers: {
    type: Array,
    default: () => [],
  },
  assignedTeachers: {
    type: Array,
    default: () => [],
  },
});

// Search functionality
const searchQuery = ref('');
const filteredAvailableTeachers = computed(() => {
  if (!searchQuery.value) return props.availableTeachers;
  const query = searchQuery.value.toLowerCase();
  return props.availableTeachers.filter(teacher =>
    teacher.name.toLowerCase().includes(query) ||
    (teacher.code && teacher.code.toLowerCase().includes(query))
  );
});

// Form for assigning teachers
const form = useForm({
  teacher_ids: props.assignedTeachers.map(teacher => teacher.id),
});

// Computed properties
const selectedTeachers = computed(() => {
  return props.availableTeachers.filter(teacher =>
    form.teacher_ids.includes(teacher.id)
  );
});

// Methods
const toggleTeacher = (teacherId) => {
  const index = form.teacher_ids.indexOf(teacherId);
  if (index > -1) {
    form.teacher_ids.splice(index, 1);
  } else {
    form.teacher_ids.push(teacherId);
  }
};

const assignTeachers = () => {
  form.post(route('subject:assign-teacher.store', props.subject.id), {
    preserveScroll: true,
    onSuccess: () => {
      // Success message will be shown via flash message
    },
  });
};

const removeTeacher = (teacher) => {
  router.delete(route('subject:remove-teacher', {
    subject: props.subject.id,
    teacher: teacher.id
  }), {
    preserveScroll: true,
    onSuccess: () => {
      // Teacher will be removed from assigned list
    },
  });
};

const goBack = () => {
  router.visit(route('subject:all'));
};

let hasPermission = ref(checkPermissionComposable("teacher_assign_manage"));
</script>

<template>
  <LayoutAuthenticated>

    <Head :title="`Assign Teachers - ${subject.name}`" />
    <SectionMain v-if="['admin'].includes($page.props.auth.user.user_type) || hasPermission">
      <SectionTitleLineWithButton title="Assign Teachers"
        :subtitle="`Manage teachers for ${subject.name} (${subject.code})`">
        <BaseButton @click="goBack" color="info" label="Back to Subjects" />
      </SectionTitleLineWithButton>

      <div class="py-6 max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
          <!-- Available Teachers -->
          <CardBox title="Available Teachers" icon="mdiAccountPlus">
            <div class="mb-4">
              <input v-model="searchQuery" type="text" placeholder="Search teachers by name..."
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" />
            </div>
            <div v-if="filteredAvailableTeachers.length === 0" class="text-gray-500 text-center py-4">
              {{ searchQuery ? 'No matching teachers found.' : 'No teachers available for assignment.' }}
            </div>
            <div v-else class="space-y-2 max-h-96 overflow-y-auto">
              <div v-for="teacher in filteredAvailableTeachers" :key="teacher.id"
                class="flex items-center justify-between p-3 border rounded-lg hover:bg-gray-50">
                <div>
                  <div class="font-medium">{{ teacher.name }}</div>
                  <div v-if="teacher.code" class="text-sm text-gray-500">
                    Code: {{ teacher.code }}
                  </div>
                </div>
                <BaseButton :color="form.teacher_ids.includes(teacher.id) ? 'success' : 'info'"
                  :label="form.teacher_ids.includes(teacher.id) ? 'Selected' : 'Select'"
                  @click="toggleTeacher(teacher.id)" size="small" />
              </div>
            </div>
          </CardBox>

          <!-- Assigned Teachers -->
          <CardBox title="Assigned Teachers" icon="mdiAccountMinus">
            <div v-if="assignedTeachers.length === 0" class="text-gray-500 text-center py-4">
              No teachers assigned to this subject yet.
            </div>
            <div v-else class="space-y-2 max-h-96 overflow-y-auto">
              <div v-for="teacher in assignedTeachers" :key="teacher.id"
                class="flex items-center justify-between p-3 border rounded-lg bg-green-50">
                <div>
                  <div class="font-medium">{{ teacher.name }}</div>
                  <div v-if="teacher.code" class="text-sm text-gray-500">
                    Code: {{ teacher.code }}
                  </div>
                </div>
                <BaseButton color="danger" label="Remove" @click="removeTeacher(teacher)" size="small" />
              </div>
            </div>
          </CardBox>
        </div>

        <!-- Action Buttons -->
        <div class="mt-6 flex justify-between items-center">
          <div class="text-sm text-gray-600">
            Selected: {{ form.teacher_ids.length }} teacher(s)
          </div>
          <div class="space-x-3">
            <SecondaryButton @click="goBack">Cancel</SecondaryButton>
            <PrimaryButton :class="{ 'opacity-25': form.processing }"
              :disabled="form.processing || form.teacher_ids.length === 0" @click="assignTeachers">
              {{ form.processing ? 'Assigning...' : 'Assign Teachers' }}
            </PrimaryButton>
          </div>
        </div>

        <!-- Subject Info -->
        <CardBox class="mt-6">
          <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div>
              <label class="block text-sm font-medium text-gray-700">Subject Code</label>
              <div class="mt-1 text-lg font-semibold">{{ subject.code }}</div>
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700">Subject Name</label>
              <div class="mt-1 text-lg font-semibold">{{ subject.name }}</div>
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700">Program</label>
              <div class="mt-1">{{ subject.program || 'N/A' }}</div>
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700">Semester</label>
              <div class="mt-1">{{ subject.semester || 'N/A' }}</div>
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700">Level</label>
              <div class="mt-1">{{ subject.level || 'N/A' }}</div>
            </div>
          </div>
        </CardBox>
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
