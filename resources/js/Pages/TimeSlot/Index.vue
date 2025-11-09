
<script setup>
import { ref, watch } from "vue";
import { useForm, router } from "@inertiajs/vue3";
import toast from "@/Stores/toast";

// Components
import LayoutAuthenticated from "@/Layouts/LayoutAuthenticated.vue";
import SectionTitleLineWithButton from "@/Components/SectionTitleLineWithButton.vue";
import CardBox from "@/Components/CardBox.vue";
import PaginationLinks from "@/Components/PaginationLinks.vue";
import Modal from "@/Components/Modal.vue";
import TextInput from "@/Components/TextInput.vue";
import InputLabel from "@/Components/InputLabel.vue";
import InputError from "@/Components/InputError.vue";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import SecondaryButton from "@/Components/SecondaryButton.vue";
import { mdiShapePlus, mdiClockOutline, mdiFood } from "@mdi/js";
import SectionMain from "../../Components/SectionMain.vue";
import checkPermissionComposable from "../../Composables/Permission/checkPermission";

// Props
const props = defineProps({
    timeSlots: {
        type: Object,
        default: () => ({ data: [], meta: {}, links: [] }),
    },
    timeSlotTemplates: {
        type: Array,
        default: () => [],
    },
    academicYears: {
        type: Array,
        default: () => [],
    },
    semesters: {
        type: Array,
        default: () => [],
    },
    filters: {
        type: Object,
        default: () => ({}),
    },
});

// Filter
const filterName = ref(props.filters.filterName || "");
const filterDay = ref(props.filters.filterDay || "");
const filterAcademicYear = ref(props.filters.filterAcademicYear || "");
const filterSemester = ref(props.filters.filterSemester || "");

watch([filterName, filterDay, filterAcademicYear, filterSemester], ([newName, newDay, newAcademicYear, newSemester]) => {
    router.get(
        route("time-slot:all"),
        { filterName: newName, filterDay: newDay, filterAcademicYear: newAcademicYear, filterSemester: newSemester },
        { preserveState: true, replace: true }
    );
});

// Forms
const form = useForm({
    name: "",
    academic_year_id: "",
    semester_id: "",
    day_of_week: "",
    start_time: "",
    end_time: "",
    is_lunch_period: false,
});

const templateForm = useForm({
    name: "",
    start_time: "",
    end_time: "",
    period_number: "",
    is_lunch_period: false,
});

const generateForm = useForm({
    academic_year_id: "",
    clear_existing: false,
    semester: "",
});

// Modals & States
const confirmingTimeSlotCreation = ref(false);
const editingTimeSlot = ref(null);
const showDeleteModal = ref(false);
const deletingTimeSlot = ref(null);
const showGenerateModal = ref(false);
const showTemplateModal = ref(false);
const editingTemplate = ref(null);
const deletingTemplate = ref(null);
const confirmingTemplateDeletion = ref(false);

// Modal Handlers
const showCreateModal = () => {
    confirmingTimeSlotCreation.value = true;
    form.reset();
    editingTimeSlot.value = null;
};

const showEditModal = (timeSlot) => {
    editingTimeSlot.value = timeSlot;
    form.name = timeSlot.name;
    form.academic_year_id = timeSlot.academic_year_id;
    form.semester_id = timeSlot.semester_id || "";
    form.day_of_week = timeSlot.day_of_week;
    form.start_time = timeSlot.start_time;
    form.end_time = timeSlot.end_time;
    form.is_lunch_period = timeSlot.is_lunch_period || false;
    confirmingTimeSlotCreation.value = true;
};

const closeModal = () => {
    confirmingTimeSlotCreation.value = false;
    form.reset();
    editingTimeSlot.value = null;
};

const showGenerateTimeSlotsModal = () => {
    showGenerateModal.value = true;
    generateForm.reset();
};

const closeGenerateModal = () => {
    showGenerateModal.value = false;
    generateForm.reset();
};

const showCreateTemplateModal = () => {
    showTemplateModal.value = true;
    templateForm.reset();
    editingTemplate.value = null;
};

const showEditTemplateModal = (template) => {
    editingTemplate.value = template;
    templateForm.name = template.name;
    templateForm.start_time = template.start_time;
    templateForm.end_time = template.end_time;
    templateForm.period_number = template.period_number;
    templateForm.is_lunch_period = template.is_lunch_period || 0;
    showTemplateModal.value = true;
};

const closeTemplateModal = () => {
    showTemplateModal.value = false;
    templateForm.reset();
    editingTemplate.value = null;
};

const showDeleteTemplateModal = (template) => {
    confirmingTemplateDeletion.value = true;
    deletingTemplate.value = template;
};

const closeDeleteTemplateModal = () => {
    confirmingTemplateDeletion.value = false;
    deletingTemplate.value = null;
};

// CRUD Handlers
const createOrUpdateTimeSlot = () => {
    if (editingTimeSlot.value) {
        form.post(
            route("time-slot:update", { timeSlot: editingTimeSlot.value.id }),
            {
                preserveScroll: true,
                onSuccess: () => {
                    closeModal();
                    toast.add({ message: "Time Slot updated!" });
                },
                onError: () => form.reset(),
            }
        );
    } else {
        form.post(route("time-slot:create"), {
            preserveScroll: true,
            onSuccess: () => {
                closeModal();
                toast.add({ message: "Time Slot created!" });
            },
            onError: () => form.reset(),
        });
    }
};

const showDeleteTimeSlotModal = (timeSlot) => {
    showDeleteModal.value = true;
    deletingTimeSlot.value = timeSlot;
};

const closeDeleteModal = () => {
    showDeleteModal.value = false;
    deletingTimeSlot.value = null;
};

const deleteTimeSlot = () => {
    router.delete(route("time-slot:delete", deletingTimeSlot.value), {
        preserveScroll: true,
        onSuccess: () => {
            closeDeleteModal();
            toast.add({ message: "Time Slot deleted!" });
        },
    });
};

const createOrUpdateTemplate = () => {
    if (editingTemplate.value) {
        templateForm.post(
            route("time-slot-template:update", { template: editingTemplate.value.id }),
            {
                preserveScroll: true,
                onSuccess: () => {
                    closeTemplateModal();
                    toast.add({ message: "Template updated!" });
                },
                onError: () => {
                    toast.add({ message: "Error updating template", type: "error" });
                    // Error handling is done in the controller
                },
            }
        );
    } else {
        templateForm.post(route("time-slot-template:create"), {
            preserveScroll: true,
            onSuccess: () => {
                closeTemplateModal();
                toast.add({ message: "Template created!" });
            },
            onError: (errors) => {
                toast.add({ message: errors, type: "error" });
                // Error handling is done in the controller
            },
        });
    }
};

const deleteTemplate = () => {
    router.delete(route("time-slot-template:delete", deletingTemplate.value), {
        preserveScroll: true,
        onSuccess: () => {
            closeDeleteTemplateModal();
            toast.add({ message: "Template deleted!" });
        },
    });
};

const generateTimeSlots = () => {
    generateForm.post(route("time-slot:generate"), {
        preserveScroll: true,
        onSuccess: () => {
            closeGenerateModal();
            toast.add({ message: "Time slots generated successfully!" });
        },
        onError: () => {
            // Error handling is done in the controller
        },
    });
};

// Helper functions
const getDayName = (day) => {
    const days = {
        monday: 'Monday',
        tuesday: 'Tuesday',
        wednesday: 'Wednesday',
        thursday: 'Thursday',
        friday: 'Friday'
    };
    return days[day] || day;
};
const formatTime = (time) => {
    const [hour, minute] = time.split(':').map(Number);
    const period = hour >= 12 ? 'PM' : 'AM';
    const displayHour = hour % 12 || 12;
    return `${displayHour}:${minute.toString().padStart(2, '0')} ${period}`;
};



const isLunchPeriod = (timeSlot) => {
    return timeSlot.is_lunch_period === 1;
};

const getTemplateDisplayName = (template) => {
    return template.name;
};

let hasPermission = ref(checkPermissionComposable("time_slot_manage"));

</script>

<template>
    <LayoutAuthenticated>
         <SectionMain v-if="
          $page.props.auth.user.user_type == 'admin' ||
          hasPermission
        ">
        <div class="py-6 max-w-7xl mx-auto sm:px-6 lg:px-8">
            <SectionTitleLineWithButton :icon="mdiClockOutline" title="Time Slot Management" 
                class="border-b-2 border-teal-400 pb-2" />

            <div class="mb-6 flex flex-wrap gap-4">
                <PrimaryButton
    @click.prevent="showCreateModal"
    class="ml-0 bg-teal-600 hover:bg-teal-700 text-white"
    title="Add a new time slot for a day"
>
    <span class="flex items-center gap-2">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path
                stroke-linecap="round"
                stroke-linejoin="round"
                stroke-width="2"
                d="M12 6v6m0 0v6m0-6h6m-6 0H6">
            </path>
        </svg>
        Add Time Slot
    </span>
</PrimaryButton>

                <PrimaryButton @click.prevent="showCreateTemplateModal" class="bg-cyan-600 hover:bg-cyan-700 text-white">
                    <span class="flex items-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10">
                            </path>
                        </svg>
                        Manage Templates
                    </span>
                </PrimaryButton>
                <PrimaryButton
    @click.prevent="showGenerateTimeSlotsModal"
    class="bg-sky-600 hover:bg-sky-700 text-white"
    title="Generate time slots for a week"
>
    <span class="flex items-center gap-2">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path
                stroke-linecap="round"
                stroke-linejoin="round"
                stroke-width="2"
                d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z">
            </path>
        </svg>
        Generate Slots
    </span>
</PrimaryButton>

            </div>

            <div class="mb-6 grid grid-cols-1 md:grid-cols-4 gap-4">
                <div>
                    <InputLabel for="filterName" value="Filter by Name" />
                    <TextInput id="filterName" v-model="filterName" type="text" placeholder="Search time slots"
                        class="w-full" />
                </div>
                <div>
                    <InputLabel for="filterDay" value="Filter by Day" />
                    <select id="filterDay" v-model="filterDay"
                        class="w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-teal-500 dark:focus:border-teal-600 focus:ring-teal-500 dark:focus:ring-teal-600 rounded-md shadow-sm">
                        <option value="">All Days</option>
                        <option value="monday">Monday</option>
                        <option value="tuesday">Tuesday</option>
                        <option value="wednesday">Wednesday</option>
                        <option value="thursday">Thursday</option>
                        <option value="friday">Friday</option>
                        
                    </select>
                </div>
                <div>
                    <InputLabel for="filterAcademicYear" value="Filter by Academic Year" />
                    <TextInput id="filterAcademicYear" v-model="filterAcademicYear" type="text"
                        placeholder="Search by academic year" class="w-full" />
                </div>
                <div>
                    <InputLabel for="filterSemester" value="Filter by Semester" />
                    <select id="filterSemester" v-model="filterSemester"
                        class="w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-teal-500 dark:focus:border-teal-600 focus:ring-teal-500 dark:focus:ring-teal-600 rounded-md shadow-sm">
                        <option value="">All Semesters</option>
                        <option v-for="s in semesters" :key="s" :value="s">{{ s }}</option>
                    </select>
                </div>
            </div>

            <CardBox class="mb-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-semibold text-gray-900">Time Slot Templates</h3>
                    <PrimaryButton @click.prevent="showCreateTemplateModal" size="sm"
                        class="bg-cyan-600 hover:bg-cyan-700 text-white">
                        Add Template
                    </PrimaryButton>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                    <div v-for="template in timeSlotTemplates" :key="template.id"
                        class="border rounded-lg p-4 bg-gray-50 hover:bg-gray-100 transition-colors">
                        <div class="flex items-center justify-between mb-2">
                            <h4 class="font-medium text-gray-900">{{ getTemplateDisplayName(template) }}</h4>
                            <div class="flex gap-2">
                                <button @click.prevent="showEditTemplateModal(template)"
                                    class="text-sky-600 hover:text-sky-800 text-sm">Edit</button>
                                <button @click.prevent="showDeleteTemplateModal(template)"
                                    class="text-red-600 hover:text-red-800 text-sm">Delete</button>
                            </div>
                        </div>
                        <div class="text-sm text-gray-600">
                            <div>{{ formatTime(template.start_time) }} - {{ formatTime(template.end_time) }}</div>
                            <div class="mt-1">
                                <span :class="[
                                        'px-2 py-1 rounded text-xs font-medium capitalize',
                                        // Template Status: Using Teal for active
                                        template.status === 'active' ? 'bg-teal-100 text-teal-800' : 'bg-gray-100 text-gray-800'
                                    ]">
                                    {{ template.status }}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </CardBox>

            <CardBox has-table>
                <table class="text-xs w-full">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Name</th>
                            <th>Academic Year</th>
                            <th>Semester</th>
                            <th>Day</th>
                            <th>Start Time</th>
                            <th>End Time</th>
                            <th>Type</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="(timeSlot, index) in props.timeSlots.data" :key="timeSlot.id">
                            <td>{{ index + 1 + (props.timeSlots.per_page * (props.timeSlots.current_page - 1)) }}</td>
                            <td>{{ timeSlot.name }}</td>
                            <td>{{ timeSlot.academic_year?.name || 'N/A' }}</td>
                            <td>{{ timeSlot.semester || 'N/A' }}</td>
                            <td>{{ getDayName(timeSlot.day_of_week) }}</td>
                            <td>{{ formatTime(timeSlot.start_time) }}</td>
                            <td>{{ formatTime(timeSlot.end_time) }}</td>
                            <td>
                                <span v-if="isLunchPeriod(timeSlot)" class="flex items-center gap-1 text-orange-600">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.746 0 3.332.477 4.5 1.253v13C19.832 18.477 18.246 18 16.5 18c-1.746 0-3.332.477-4.5 1.253">
                                        </path>
                                    </svg>
                                    Lunch
                                </span>
                                <span v-else class="text-gray-600">Regular</span>
                            </td>
                            <td>
                                <span :class="[
                                        'px-2 py-1 rounded text-white text-xs',
                                        // Time Slot Status: Using Teal for active
                                        timeSlot.status === 'active' ? 'bg-teal-600' : 'bg-gray-400'
                                    ]">
                                    {{ timeSlot.status === 'active' ? 'Active' : 'Inactive' }}
                                </span>
                            </td>
                            <td class="flex gap-2">
                                <button @click.prevent="showEditModal(timeSlot)"
                                    class="border p-1 rounded hover:bg-sky-600 hover:text-white transition-colors">Edit</button>
                                <button @click.prevent="showDeleteTimeSlotModal(timeSlot)"
                                    class="border p-1 rounded hover:bg-red-600 hover:text-white transition-colors">Delete</button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </CardBox>

            <div class="p-3 lg:px-6 border-t border-gray-100 dark:border-slate-800">
                <PaginationLinks :links="props.timeSlots.links" />
            </div>

            <Modal :show="confirmingTimeSlotCreation" @close="closeModal">
                <div class="p-6 w-full">
                    <h2 class="text-xl font-semibold text-gray-900 mb-6">
                        {{ editingTimeSlot ? 'Edit Time Slot' : 'Add Time Slot' }}
                    </h2>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="col-span-2">
                            <InputLabel for="name" value="Name" />
                            <TextInput id="name" v-model="form.name" placeholder="Enter time slot name"
                                class="w-full rounded-lg border-gray-300 focus:ring-2 focus:ring-teal-400" />
                            <InputError :message="form.errors.name" />
                        </div>

                        <div class="col-span-2">
                            <InputLabel for="academic_year_id" value="Academic Year" />
                            <select id="academic_year_id" v-model="form.academic_year_id"
                                class="w-full rounded-lg border-gray-300 focus:ring-2 focus:ring-teal-400">
                                <option value="">Select Academic Year</option>
                                <option v-for="y in academicYears" :key="y.id" :value="y.id">{{ y.name }}</option>
                            </select>
                            <InputError :message="form.errors.academic_year_id" />
                        </div>


                        <div class="col-span-2">
                            <InputLabel for="semester_id" value="Semester (Optional)" />
                            <select id="semester_id" v-model="form.semester_id"
                                class="w-full rounded-lg border-gray-300 focus:ring-2 focus:ring-teal-400">
                                <option value="">Select Semester</option>
                                <option v-for="s in semesters" :key="s" :value="s">{{ s }}</option>
                            </select>
                            <InputError :message="form.errors.semester" />
                        </div>

                        <div class="col-span-2">
                            <InputLabel value="Day of Week" />
                            <div class="flex flex-wrap gap-2 mt-1">
                                <button
                                    v-for="day in ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday']"
                                    :key="day" @click.prevent="form.day_of_week = day" :class="[
                                            'px-3 py-1 rounded-full text-sm font-medium transition-colors',
                                            // Active Day Selection: Changed to Teal
                                            form.day_of_week === day
                                                ? 'bg-teal-600 text-white shadow-md'
                                                : 'bg-gray-200 text-gray-800 hover:bg-gray-300'
                                        ]">
                                    {{ day.charAt(0).toUpperCase() + day.slice(1) }}
                                </button>
                            </div>
                            <InputError :message="form.errors.day_of_week" />
                        </div>

                        <div class="col-span-2 flex gap-4">
                            <div class="flex-1">
                                <InputLabel for="start_time" value="Start Time" />
                                <TextInput id="start_time" type="time" v-model="form.start_time"
                                    class="w-full rounded-lg border-gray-300 focus:ring-2 focus:ring-teal-400" />
                                <InputError :message="form.errors.start_time" />
                            </div>
                            <div class="flex-1">
                                <InputLabel for="end_time" value="End Time" />
                                <TextInput id="end_time" type="time" v-model="form.end_time"
                                    class="w-full rounded-lg border-gray-300 focus:ring-2 focus:ring-teal-400" />
                                <InputError :message="form.errors.end_time" />
                            </div>
                        </div>

                        <div class="col-span-2">
                            <label class="flex items-center">
                                <input type="checkbox" v-model="form.is_lunch_period"
                                    class="rounded border-gray-300 text-teal-600 focus:ring-teal-500">
                                <span class="ml-2 text-sm text-gray-600">Mark as lunch period</span>
                            </label>
                            <InputError :message="form.errors.is_lunch_period" />
                        </div>
                    </div>

                    <div class="mt-6 flex justify-end gap-3">
                        <SecondaryButton @click.prevent="closeModal"
                            class="bg-gray-200 text-gray-700 hover:bg-gray-300">
                            Cancel
                        </SecondaryButton>
                        <PrimaryButton :class="{ 'opacity-50 cursor-not-allowed': form.processing }"
                            :disabled="form.processing" @click.prevent="createOrUpdateTimeSlot"
                            class="bg-teal-600 hover:bg-teal-700 text-white">
                            {{ editingTimeSlot ? 'Update Slot' : 'Add Slot' }}
                        </PrimaryButton>
                    </div>
                </div>
            </Modal>

            <Modal :show="showTemplateModal" @close="closeTemplateModal">
                <div class="p-6 w-full">
                    <h2 class="text-xl font-semibold text-gray-900 mb-6">
                        {{ editingTemplate ? 'Edit Template' : 'Add Template' }}
                    </h2>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="col-span-2">
                            <InputLabel for="template_name" value="Template Name" />
                            <TextInput id="template_name" v-model="templateForm.name" placeholder="Enter template name"
                                class="w-full rounded-lg border-gray-300 focus:ring-2 focus:ring-cyan-400" />
                            <InputError :message="templateForm.errors.name" />
                        </div>

                        <div class="col-span-2">
                            <InputLabel for="period_number" value="Period Number" />
                            <TextInput id="period_number" v-model="templateForm.period_number" type="number" min="1"
                                placeholder="Enter period number"
                                class="w-full rounded-lg border-gray-300 focus:ring-2 focus:ring-cyan-400" />
                            <InputError :message="templateForm.errors.period_number" />
                        </div>

                        <div class="col-span-2 flex gap-4">
                            <div class="flex-1">
                                <InputLabel for="template_start_time" value="Start Time" />
                                <TextInput id="template_start_time" type="time" v-model="templateForm.start_time"
                                    class="w-full rounded-lg border-gray-300 focus:ring-2 focus:ring-cyan-400" />
                                <InputError :message="templateForm.errors.start_time" />
                            </div>
                            <div class="flex-1">
                                <InputLabel for="template_end_time" value="End Time" />
                                <TextInput id="template_end_time" type="time" v-model="templateForm.end_time"
                                    class="w-full rounded-lg border-gray-300 focus:ring-2 focus:ring-cyan-400" />
                                <InputError :message="templateForm.errors.end_time" />
                            </div>
                        </div>

                        <div class="col-span-2 flex items-center mt-2">
                            <input 
                                id="is_lunch_period" 
                                type="checkbox" 
                                v-model="templateForm.is_lunch_period" 
                                :true-value="1" 
                                :false-value="0"
                                class="h-4 w-4 text-cyan-600 border-gray-300 rounded focus:ring-cyan-500" 
                            />
                            <label for="is_lunch_period" class="ml-2 block text-sm text-gray-700">
                                Mark as Lunch Period
                            </label>
                            <InputError :message="templateForm.errors.is_lunch_period" />
                        </div>
                    </div>

                    <div class="mt-6 flex justify-end gap-3">
                        <SecondaryButton @click.prevent="closeTemplateModal"
                            class="bg-gray-200 text-gray-700 hover:bg-gray-300">
                            Cancel
                        </SecondaryButton>
                        <PrimaryButton :class="{ 'opacity-50 cursor-not-allowed': templateForm.processing }"
                            :disabled="templateForm.processing" @click.prevent="createOrUpdateTemplate"
                            class="bg-cyan-600 hover:bg-cyan-700 text-white">
                            {{ editingTemplate ? 'Update Template' : 'Add Template' }}
                        </PrimaryButton>
                    </div>
                </div>
            </Modal>


            <Modal :show="showDeleteModal" @close="closeDeleteModal">
                <div class="p-6">
                    <h2 class="text-lg font-medium text-red-600">Delete Time Slot</h2>
                    <p>Are you sure you want to delete this time slot?</p>
                    <div class="mt-6 flex justify-end gap-2">
                        <SecondaryButton @click.prevent="closeDeleteModal">Cancel</SecondaryButton>
                        <PrimaryButton :class="{ 'opacity-25': form.processing }" :disabled="form.processing"
                            @click.prevent="deleteTimeSlot"
                            class="bg-red-600 hover:bg-red-700 text-white">
                            Delete
                        </PrimaryButton>
                    </div>
                </div>
            </Modal>

            <Modal :show="confirmingTemplateDeletion" @close="closeDeleteTemplateModal">
                <div class="p-6">
                    <h2 class="text-lg font-medium text-red-600">Delete Template</h2>
                    <p>Are you sure you want to delete this template?</p>
                    <div class="mt-6 flex justify-end gap-2">
                        <SecondaryButton @click.prevent="closeDeleteTemplateModal">Cancel</SecondaryButton>
                        <PrimaryButton :class="{ 'opacity-25': templateForm.processing }"
                            :disabled="templateForm.processing" @click.prevent="deleteTemplate"
                            class="bg-red-600 hover:bg-red-700 text-white">
                            Delete
                        </PrimaryButton>
                    </div>
                </div>
            </Modal>

            <Modal :show="showGenerateModal" @close="closeGenerateModal">
                <div class="p-6 w-full max-w-md">
                    <h2 class="text-xl font-semibold text-gray-900 mb-6">
                        Generate Time Slots
                    </h2>

                    <div class="space-y-4">
                        <div>
                            <InputLabel for="generate_academic_year_id" value="Academic Year" />
                            <select id="generate_academic_year_id" v-model="generateForm.academic_year_id"
                                class="w-full rounded-lg border-gray-300 focus:ring-2 focus:ring-sky-400">
                                <option value="">Select Academic Year</option>
                                <option v-for="y in academicYears" :key="y.id" :value="y.id">{{ y.name }}</option>
                            </select>
                            <InputError :message="generateForm.errors.academic_year_id" />
                        </div>
                        <div>
                            <InputLabel for="generate_semester_id" value="Semester (Optional)" />
                            <select id="generate_semester_id" v-model="generateForm.semester"
                                class="w-full rounded-lg border-gray-300 focus:ring-2 focus:ring-sky-400">
                                <option value="">Select Semester</option>
                                <option v-for="s in semesters" :key="s" :value="s">{{ s }}</option>
                            </select>
                            <InputError :message="generateForm.errors.semester" />
                        </div>
                        <div class="flex items-center">
                            <input id="clear_existing" type="checkbox" v-model="generateForm.clear_existing"
                                class="rounded border-gray-300 text-sky-600 focus:ring-sky-500">
                            <label for="clear_existing" class="ml-2 text-sm text-gray-600">
                                Clear existing time slots for this academic year
                            </label>
                        </div>

                        <div class="bg-gray-50 p-4 rounded-lg border border-sky-200">
                            <h4 class="font-medium text-gray-900 mb-2">Time Slots to be Generated:</h4>
                            <div class="text-sm text-gray-600 space-y-1">
                                <div>• Period 1: 9:00 - 10:00</div>
                                <div>• Period 2: 10:05 - 11:05</div>
                                <div>• Period 3: 11:10 - 12:10</div>
                                <div class="text-orange-600">• Lunch: 12:15 - 13:15</div>
                                <div>• Period 4: 13:20 - 14:20</div>
                                <div>• Period 5: 14:25 - 15:25</div>
                                <div>• Period 6: 15:30 - 16:30</div>
                            </div>
                            <div class="text-xs text-gray-500 mt-2">
                                * Will be created for all 7 days (Monday to Friday)
                            </div>
                        </div>
                    </div>

                    <div class="mt-6 flex justify-end gap-3">
                        <SecondaryButton @click.prevent="closeGenerateModal"
                            class="bg-gray-200 text-gray-700 hover:bg-gray-300">
                            Cancel
                        </SecondaryButton>
                        <PrimaryButton :class="{ 'opacity-50 cursor-not-allowed': generateForm.processing }"
                            :disabled="generateForm.processing || !generateForm.academic_year_id"
                            @click.prevent="generateTimeSlots"
                            class="bg-sky-600 hover:bg-sky-700 text-white">
                            {{ generateForm.processing ? 'Generating...' : 'Generate Slots' }}
                        </PrimaryButton>
                    </div>
                </div>
            </Modal>
        </div>
         </SectionMain>
         <SectionMain v-else>
           <div class="py-6 max-w-7xl mx-auto sm:px-6 lg:px-8">
             <CardBox>
               <div class="text-center py-10">
                 <h2 class="text-2xl font-semibold text-gray-900 mb-4">Access Denied</h2>
                 <p class="text-gray-600">You do not have permission to view this page.</p>
               </div>
             </CardBox>
           </div>
         </SectionMain>
    </LayoutAuthenticated>
</template>