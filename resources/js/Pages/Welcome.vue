<script setup>
import { Head } from '@inertiajs/vue3';
import { ref, computed, watch, onMounted } from 'vue';
/* import { router } from '@inertiajs/vue3'; */
import Login from '@/Pages/Auth/Login.vue';
import Register from '@/Pages/Auth/Register.vue';
import TeacherRegister from '@/Pages/Auth/TeacherRegister.vue';
import SectionTitleLineWithButton from '@/Components/SectionTitleLineWithButton.vue';
import CardBox from '@/Components/CardBox.vue';
import InputLabel from '@/Components/InputLabel.vue';
import BaseButton from '@/Components/BaseButton.vue';
/* import { mdiTable, mdiGrid } from '@mdi/js'; */


import { useForm, router } from "@inertiajs/vue3";
import toast from "@/Stores/toast";
import LayoutAuthenticated from "@/Layouts/LayoutAuthenticated.vue";
import TextInput from "@/Components/TextInput.vue";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import SecondaryButton from "@/Components/SecondaryButton.vue";
import { mdiTable, mdiGrid, mdiShapePlus, mdiHeart, mdiHeartOutline } from "@mdi/js";
// Tab state management
const activeTab = ref('welcome');

// Animation states
const isLoading = ref(true);
const showContent = ref(false);
const animateWelcome = ref(false);
const animateFeatures = ref(false);

// Mobile menu state
const mobileMenuOpen = ref(false);

// Animation functions
const triggerWelcomeAnimation = () => {
    setTimeout(() => {
        animateWelcome.value = true;
    }, 300);
};

const triggerFeaturesAnimation = () => {
    setTimeout(() => {
        animateFeatures.value = true;
    }, 800);
};

// Initialize animations on mount
onMounted(() => {
    setTimeout(() => {
        isLoading.value = false;
        showContent.value = true;
        triggerWelcomeAnimation();
        triggerFeaturesAnimation();
    }, 500);
});

// Smooth tab switching with animation
const switchTab = (tabName) => {
    showContent.value = false;
    setTimeout(() => {
        activeTab.value = tabName;
        setTimeout(() => {
            showContent.value = true;
        }, 150);
    }, 150);
};

// Tab switching functions with animation
const switchToLogin = () => switchTab('login');
const switchToRegister = () => switchTab('register');
const switchToTeacherRegister = () => switchTab('teacher-register');
const switchToWelcome = () => switchTab('welcome');
const switchToTimetable = () => switchTab('timetable');
const switchToSignUp = () => switchTab('signup');

// Props from Laravel
const props = defineProps({
    entries: Array,
    academicYears: Array,
    programs: Array,
    levels: Array,
    sections: Array,
    subjects: Array,
    teachers: Array,
    semesters: Array,
    timeSlots: Array,
    filters: Object,
});

// Filters
const filterYear = ref(props.filters.filterYear || "");
const filterSemester = ref(props.filters.filterSemester || "");
const filterProgram = ref(props.filters.filterProgram || "");
const filterLevel = ref(props.filters.filterLevel || "");
const filterSection = ref(props.filters.filterSection || "");

// Update when filters change

watch(
    [filterYear, filterSemester, filterProgram, filterLevel, filterSection],
    ([newYear, newSemester, newProgram, newLevel, newSection]) => {
        router.get(
            route("welcome"),
            {
                filterYear: newYear,
                filterSemester: newSemester,
                filterProgram: newProgram,
                filterLevel: newLevel,
                filterSection: newSection,
            },
            { preserveState: true, replace: true, preserveScroll: true, replace: false, }
        );
    }
);

// Computed for dependent selects
const filteredSemesters = computed(() => {
    if (!filterYear.value) return [];
    return props.semesters.filter(s => s.academic_year_id == filterYear.value);
});

const filteredPrograms = computed(() => {
    const programs = Array.isArray(props.programs) ? props.programs : [];
    if (!filterYear.value) return programs;
    return programs.filter(p => p.academic_year_id == filterYear.value);
});

const filteredLevels = computed(() => {
    if (!filterProgram.value) return [];
    return props.levels.filter(l => l.program_id == filterProgram.value);
});

const filteredSections = computed(() => {
    if (!filterLevel.value) return [];
    return props.sections.filter(s => s.level_id == filterLevel.value);
});
// Check if all required selections are complete
const allSelectionsComplete = computed(() => {
    return filterYear.value &&
        filterSemester.value &&
        filterProgram.value &&
        filterLevel.value &&
        filterSection.value;
});

// Computed for selected items to display in header
const selectedYear = computed(() => props.academicYears.find(y => y.id == filterYear.value)); // ADDED: Selected Academic Year
const selectedSemester = computed(() => props.semesters.find(s => s.id == filterSemester.value));
const selectedProgram = computed(() => props.programs.find(p => p.id == filterProgram.value));
const selectedLevel = computed(() => props.levels.find(l => l.id == filterLevel.value));
const selectedSection = computed(() => props.sections.find(s => s.id == filterSection.value));


// Time slots for the grid - using dynamic data from backend and filtering by academic year
const timeSlots = computed(() => {
    let slots = Array.isArray(props.timeSlots) ? props.timeSlots : [];

    // Filter by academic year if selected
    if (filterYear.value) {
        slots = slots.filter(slot => slot.academic_year_id == filterYear.value);
    }

    const to12Hour = (time) => {
        if (!time) return '';
        const date = new Date(`1970-01-01T${time}`);
        let hours = date.getHours();
        let minutes = date.getMinutes();
        const ampm = hours >= 12 ? 'PM' : 'AM';
        hours = hours % 12 || 12;
        minutes = minutes.toString().padStart(2, '0');
        return `${hours}:${minutes} ${ampm}`;
    };

    return slots.map(slot => ({
        start: slot.start_time,
        end: slot.end_time,
        label: `${to12Hour(slot.start_time)} - ${to12Hour(slot.end_time)}`,
        name: slot.name,
        day_of_week: slot.day_of_week,
        academic_year_id: slot.academic_year_id,
        is_lunch_period: slot.is_lunch_period,
    }));
});


// Days of the week
const days = [
    { key: "monday", label: "Monday" },
    { key: "tuesday", label: "Tuesday" },
    { key: "wednesday", label: "Wednesday" },
    { key: "thursday", label: "Thursday" },
    { key: "friday", label: "Friday" },
];

// Get unique time slots for header (to avoid duplicates)
const uniqueTimeSlots = computed(() => {
    const unique = [];
    const seen = new Set();

    timeSlots.value.forEach(slot => {
        const key = `${slot.start}-${slot.end}`;
        if (!seen.has(key)) {
            seen.add(key);
            unique.push(slot);
        }
    });

    return unique.sort((a, b) => a.start.localeCompare(b.start));
});

// Organize entries by day and time slot
const organizedEntries = computed(() => {
    const grid = {};

    // Initialize grid with all time slots for all days
    days.forEach(day => {
        grid[day.key] = {};
        uniqueTimeSlots.value.forEach(slot => {
            grid[day.key][slot.start] = null;
        });
    });

    // Fill grid with entries - match by day and time
    props.entries.forEach(entry => {
        if (entry.time_slot) {
            const day = entry.time_slot.day_of_week;
            const time = entry.time_slot.start_time;

            if (grid[day] && grid[day][time] !== undefined) {
                grid[day][time] = entry;
            }
        }
    });

    return grid;
});



const uniqueSubjects = computed(() => {
    const subjectsMap = new Map();

    props.entries.forEach(entry => {
        if (entry.subject) {
            const subjectId = entry.subject.id;

            // Initialize subject entry in map if it doesn't exist
            if (!subjectsMap.has(subjectId)) {
                subjectsMap.set(subjectId, {
                    id: subjectId,
                    code: entry.subject.code,
                    name: entry.subject.name,
                    teachers: new Set(), // Use a Set to store unique teacher names
                });
            }

            // Add teachers from the current entry to the subject's teacher list
            if (entry.teachers && entry.teachers.length > 0) {
                const subjectEntry = subjectsMap.get(subjectId);
                entry.teachers.forEach(teacher => {
                    subjectEntry.teachers.add(teacher.name);
                });
            }
        }
    });
    // Convert map values to an array, and convert teacher sets to comma-separated strings
    return Array.from(subjectsMap.values())
        .map(subject => ({
            ...subject,
            teacherNames: Array.from(subject.teachers).join(' ၊ ') || 'N/A',
        }))
        .sort((a, b) => a.code.localeCompare(b.code));
});

// Check if a time slot is lunch
const isLunch = (timeSlot) => {
    return timeSlot.is_lunch_period === 1;
};

// Get entry for specific day and time
const getEntry = (day, timeSlot) => {
    return organizedEntries.value[day]?.[timeSlot.start] || null;
};
// Get subject code - Kept as is
const getSubjectDisplay = (entry) => {
    if (!entry || !entry.subject) return "";
    return `${entry.subject.code}`;
};

// Get teacher names (multiple teachers)
const getTeacherDisplay = (entry) => {
    if (!entry || !entry.teachers || entry.teachers.length === 0) return "";
    if (entry.teachers.length === 1) return entry.teachers[0].name;
    return entry.teachers.map(t => t.name).join(", ");
};

// Get classroom
const getClassroomDisplay = (entry) => {
    if (!entry || !entry.classroom) return "";
    return entry.classroom.room_no;
};
// Download PDF - Kept as is
const downloadPDF = () => {
    const url = route('generate', {
        filterYear: filterYear.value,
        filterSemester: filterSemester.value,
        filterProgram: filterProgram.value,
        filterLevel: filterLevel.value,
        filterSection: filterSection.value,
    });
    window.open(url, '_blank');
};
const exportExcel = () => {
    const url = route('export', {
        filterYear: filterYear.value,
        filterSemester: filterSemester.value,
        filterProgram: filterProgram.value,
        filterLevel: filterLevel.value,
        filterSection: filterSection.value,
    });
    window.open(url, '_blank');
    //window.open(route('export') + '?' + params.toString(), '_blank');
};
</script>

<template>

    <Head title="University Timetable Management System" />

    <!-- Loading Screen -->
    <div v-if="isLoading"
        class="fixed inset-0 bg-gradient-to-br from-teal-500 to-teal-700 flex items-center justify-center z-50">
        <div class="text-center">
            <div class="inline-flex items-center justify-center p-4 bg-white/10 rounded-full mb-4 animate-pulse">
                <svg class="w-12 h-12 text-white animate-spin" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15">
                    </path>
                </svg>
            </div>
            <h2 class="text-2xl font-bold text-white mb-2">Loading Timetable System</h2>
            <p class="text-teal-100">Please wait while we prepare your content...</p>
        </div>
    </div>

    <header class="bg-sky-700 border-b border-cyan-500 shadow-xl transition-all duration-300">
        <div class="mx-auto px-1 sm:px-4 lg:px-4">
            <div class="flex items-center py-3 sm:py-4 space-x-2 sm:space-x-4">


                <img src="/images/logo.png" alt="UCSh Logo" class="w-20 h-14 object-contain sm:w-20 sm:h-13" />


                <div class="min-w-0">
                    <h1 class="text-lg sm:text-xl lg:text-2xl font-extrabold text-white truncate">
                        University Timetable Management System
                    </h1>
                    <p class="text-sm sm:text-base text-cyan-300 truncate">
                        ကွန်ပျူတာတက္ကသိုလ်(ဟင်္သာတ)
                    </p>
                </div>

            </div>
        </div>
    </header>


    <!-- Main Content -->
    <main class="min-h-screen relative">
        <div class="absolute inset-0 bg-transparent">
            <img src="/images/background2.jpg" alt="Background" class="w-full h-full object-cover filter" />
            <!-- Optional overlay gradient for readability -->
            <div class="absolute inset-0 bg-transparent"></div>

        </div>
        <div class="relative z-40 max-w-7xl mx-auto px-3 sm:px-4 lg:px-6 xl:px-8 py-6 sm:py-8 lg:py-12">
            <!-- Content Transition Wrapper -->
            <div v-if="showContent" class="transition-all duration-500 ease-in-out" :class="{
                'opacity-100 transform translate-y-0': showContent,
                'opacity-0 transform translate-y-4': !showContent
            }">
                <!-- Timetable Grid Section -->


                <div v-if="activeTab === 'welcome'">
                    <!-- Back to Timetable Button -->
                    <div class="space-y-3 sm:space-y-4 mb-6 sm:mb-8 px-2 sm:px-0" :class="{
                        'opacity-100 transform translate-y-0': animateFeatures,
                        'opacity-0 transform translate-y-4': !animateFeatures
                    }" style="transition: all 0.6s ease-out 0.6s;">
                        <div
                            class="flex items-center space-x-3 p-3 rounded-lg hover:bg-gray-50 transition-colors duration-300">
                            <div class="flex-shrink-0">
                                <svg class="w-5 h-5 sm:w-6 sm:h-6 text-green-500" fill="currentColor"
                                    viewBox="0 0 24 24">
                                    <path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </div>
                            <span class="text-sm sm:text-base text-gray-700">Efficient Academic
                                Scheduling</span>
                        </div>
                        <div
                            class="flex items-center space-x-3 p-3 rounded-lg hover:bg-gray-50 transition-colors duration-300">
                            <div class="flex-shrink-0">
                                <svg class="w-5 h-5 sm:w-6 sm:h-6 text-teal-500" fill="currentColor"
                                    viewBox="0 0 24 24">
                                    <path
                                        d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                                </svg>
                            </div>
                            <span class="text-sm sm:text-base text-gray-700">Smart Resource Management</span>
                        </div>
                        <div
                            class="flex items-center space-x-3 p-3 rounded-lg hover:bg-gray-50 transition-colors duration-300">
                            <div class="flex-shrink-0">
                                <svg class="w-5 h-5 sm:w-6 sm:h-6 text-purple-500" fill="currentColor"
                                    viewBox="0 0 24 24">
                                    <path d="M13 10V3L4 14h7v7l9-11h-7z" />
                                </svg>
                            </div>
                            <span class="text-sm sm:text-base text-gray-700">Real-time Updates</span>
                        </div>
                    </div>

                    <!-- Welcome Content -->
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 lg:gap-12 items-start mb-12 lg:mb-16" :class="{
                        'opacity-100 transform translate-y-0': animateWelcome,
                        'opacity-0 transform translate-y-8': !animateWelcome
                    }" style="transition: all 0.8s ease-out 0.2s;">
                        <!-- Left Column - Welcome Content -->
                        <div class="text-center lg:text-left order-2 lg:order-1">
                            <!-- University Branding -->
                            <div class="mb-6 lg:mb-8 justify-items-center">


                                <p class="text-lg sm:text-xl text-gray-100 mb-4 sm:mb-6">University Timetable Management
                                    System</p>
                                <p class="text-base sm:text-lg text-gray-100 leading-relaxed px-2 sm:px-0">
                                    Streamline your academic scheduling with our comprehensive system designed
                                    specifically
                                    for University of Computer Studies, Hinthada.
                                    Manage timetables, resources, and schedules efficiently with real-time updates and
                                    intelligent conflict resolution.
                                </p>

                                <div class="lg:pl-4 xl:pl-8 order-1 lg:order-2" :class="{
                                    'opacity-100 transform translate-x-0': animateFeatures,
                                    'opacity-0 transform translate-x-8': !animateFeatures
                                }" style="transition: all 0.8s ease-out 0.4s;">

                                    <div
                                        class="flex flex-col sm:flex-row gap-2 sm:gap-4 justify-center lg:justify-start px-2 sm:px-0">
                                        <!-- View Timetable Button -->
                                        <button @click="switchToTimetable" class="inline-flex items-center justify-center px-6 sm:px-8 py-3 border border-transparent 
           text-sm sm:text-base font-medium rounded-xl text-white 
           bg-gradient-to-r from-teal-600 to-teal-700 
           hover:from-teal-700 hover:to-teal-800 
           focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-teal-500 
           transition-all duration-300 transform hover:scale-105 
           shadow-lg hover:shadow-xl backdrop-blur-md">
                                            <svg class="w-4 h-4 sm:w-5 sm:h-5 mr-2" fill="none" stroke="currentColor"
                                                viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0
         00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                            </svg>

                                            View Timetable
                                        </button>

                                        <!-- Teacher Register Button -->
                                        <button @click="switchToTeacherRegister" class="w-full sm:w-auto inline-flex items-center justify-center px-4 sm:px-6 lg:px-8 py-2 sm:py-3
           border border-purple-600 text-xs sm:text-sm lg:text-base font-medium
           rounded-xl text-purple-700 bg-white/80
           hover:bg-purple-50
           focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-purple-500
           transition-all duration-300 transform hover:scale-105
           shadow-md hover:shadow-lg backdrop-blur-md">
                                            <svg class="w-4 h-4 sm:w-5 sm:h-5 mr-2" fill="none" stroke="currentColor"
                                                viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.746 0 3.332.477 4.5 1.253v13C19.832 18.477 18.246 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                                            </svg>
                                            Join as Teacher
                                        </button>

                                        <!-- Admin Sign In Button -->
                                        <button @click="switchToLogin" class="inline-flex items-center justify-center px-6 sm:px-8 py-3 
           border border-teal-600 text-sm sm:text-base font-medium 
           rounded-xl text-teal-700 bg-white/80 
           hover:bg-teal-50 
           focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-teal-500 
           transition-all duration-300 transform hover:scale-105 
           shadow-md hover:shadow-lg backdrop-blur-md">
                                            <svg class="w-4 h-4 sm:w-5 sm:h-5 mr-2" fill="none" stroke="currentColor"
                                                viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M13 8l4 4m0 0l-4 4m4-4H3m5-4v-1a3 3 0 013-3h7a3 3 0 013 3v10a3 3 0 01-3 3h-7a3 3 0 01-3-3v-1" />
                                            </svg>

                                            Login to your account
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <!-- Feature Highlights -->


                            <!-- Call to Action -->

                        </div>


                    </div>


                </div>
                <div v-else-if="activeTab === 'timetable'">
                    <div class="w-full">

                        <div class="mb-8 text-left">
                            <button @click="switchToWelcome"
                                class="inline-flex items-center text-white text-base font-medium transition-all duration-300 px-4 py-2 rounded-xl bg-white/10 hover:bg-white/20 backdrop-blur-md border border-white/20 shadow-lg shadow-black/20">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                                </svg>
                                Back to Home
                            </button>
                        </div>

                        <div class="bg-white rounded-xl p-6 lg:p-10 shadow-2xl shadow-black/40 border border-gray-100">

                            <div class="mb-8 grid grid-cols-2 md:grid-cols-3 lg:grid-cols-5 gap-4">
                                <div class="space-y-1">
                                    <InputLabel value="Academic Year" class="text-teal-700 font-bold text-sm" />
                                    <select v-model="filterYear"
                                        class="w-full border-gray-300 rounded-lg text-sm h-10 focus:border-sky-500 focus:ring-sky-500 transition duration-150">
                                        <option value="">All Years</option>
                                        <option v-for="y in props.academicYears" :key="y.id" :value="y.id">{{ y.name }}
                                        </option>
                                    </select>
                                </div>
                                <div class="space-y-1">
                                    <InputLabel value="Program" class="text-teal-700 font-bold text-sm" />
                                    <select v-model="filterProgram"
                                        class="w-full border-gray-300 rounded-lg text-sm h-10 focus:border-sky-500 focus:ring-sky-500 transition duration-150">
                                        <option value="">All Programs</option>
                                        <option v-for="p in filteredPrograms" :key="p.id" :value="p.id">{{ p.name }}
                                        </option>
                                    </select>
                                </div>
                                <div class="space-y-1">
                                    <InputLabel value="Level" class="text-teal-700 font-bold text-sm" />
                                    <select v-model="filterLevel"
                                        class="w-full border-gray-300 rounded-lg text-sm h-10 focus:border-sky-500 focus:ring-sky-500 transition duration-150">
                                        <option value="">All Levels</option>
                                        <option v-for="l in filteredLevels" :key="l.id" :value="l.id">{{ l.name }}
                                        </option>
                                    </select>
                                </div>
                                <div class="space-y-1">
                                    <InputLabel value="Section" class="text-teal-700 font-bold text-sm" />
                                    <select v-model="filterSection"
                                        class="w-full border-gray-300 rounded-lg text-sm h-10 focus:border-sky-500 focus:ring-sky-500 transition duration-150">
                                        <option value="">All Sections</option>
                                        <option v-for="s in filteredSections" :key="s.id" :value="s.id">{{ s.name }}
                                        </option>
                                    </select>
                                </div>
                                <div class="space-y-1">
                                    <InputLabel value="Semester" class="text-teal-700 font-bold text-sm" />
                                    <select v-model="filterSemester"
                                        class="w-full border-gray-300 rounded-lg text-sm h-10 focus:border-sky-500 focus:ring-sky-500 transition duration-150">
                                        <option value="">All Semesters</option>
                                        <option v-for="s in filteredSemesters" :key="s.id" :value="s.id">{{ s.name }}
                                        </option>
                                    </select>
                                </div>
                            </div>

                            <div v-if="!allSelectionsComplete"
                                class="mb-8 p-5 bg-sky-50 border-l-4 border-sky-500 rounded-lg">
                                <div class="flex items-center">
                                    <div class="flex-shrink-0 text-sky-500">
                                        <svg class="h-6 w-6" viewBox="0 0 20 20" fill="currentColor">
                                            <path fill-rule="evenodd"
                                                d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z"
                                                clip-rule="evenodd" />
                                        </svg>
                                    </div>
                                    <div class="ml-4">
                                        <h3 class="text-lg font-semibold text-sky-800">
                                            Complete Your Selection
                                        </h3>
                                        <div class="mt-1 text-sm text-sky-700">
                                            <p>To view the class timetable, please select an option for **all five**
                                                filters above.</p>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <SectionTitleLineWithButton v-if="allSelectionsComplete">
                                <div class="flex space-x-4">
                                    <BaseButton @click="downloadPDF" color="info" icon="mdi-download"
                                        label="Download PDF" class="shadow-md hover:shadow-lg" />
                                    <BaseButton @click="exportExcel" color="success" label="Export Excel"
                                        class="shadow-md hover:shadow-lg" />
                                </div>
                            </SectionTitleLineWithButton>

                            <CardBox v-if="allSelectionsComplete"
                                class="p-0 border border-gray-200 mt-6 rounded-xl overflow-hidden">

                                <div class="overflow-x-auto">
                                    <table class="hidden md:table w-full border-collapse min-w-[600px] md:min-w-full">
                                        <thead>
                                            <tr>
                                                <th :colspan="1 + uniqueTimeSlots.length"
                                                    class="text-center font-extrabold text-2xl bg-teal-600 text-white p-4">
                                                    University of Computer Studies, Hinthada
                                                </th>
                                            </tr>
                                            <tr>
                                                <th :colspan="1 + uniqueTimeSlots.length"
                                                    class="text-center text-sm bg-gray-100 p-3 border-b border-gray-300 text-gray-700">
                                                    <span v-if="selectedYear" class="font-bold text-base text-teal-700">
                                                        Academic Year: {{ selectedYear.name }}
                                                    </span>
                                                    <span v-if="selectedSemester" class="ml-2 font-bold text-teal-700">
                                                        ({{ selectedSemester.name }})
                                                    </span>
                                                    <span class="block mt-1 text-base text-gray-600">
                                                        Timetable For: <span v-if="selectedLevel"
                                                            class="font-extrabold text-gray-800">{{ selectedLevel.name
                                                            }}</span>
                                                        (<span v-if="selectedProgram">{{ selectedProgram.name }}</span>)
                                                        <span v-if="selectedSection && selectedSection.name"
                                                            class="ml-2">
                                                            Section: **{{ selectedSection.name }}**
                                                        </span>
                                                    </span>
                                                    <span class="block text-right pr-4 text-xs text-gray-500"
                                                        v-if="selectedSection && selectedSection?.classroom">
                                                        Classroom: {{ selectedSection.classroom.room_no }}
                                                    </span>
                                                </th>
                                            </tr>
                                            <tr>
                                                <th
                                                    class="border border-gray-300 p-3 bg-teal-100 font-extrabold text-base text-teal-800 w-28">
                                                    Day\Time
                                                </th>
                                                <th v-for="slot in uniqueTimeSlots" :key="slot.start"
                                                    class="border border-gray-300 p-3 bg-teal-100 font-bold text-sm text-center"
                                                    :class="{
                                                        'bg-orange-100 text-orange-800': isLunch(slot)
                                                    }">
                                                    {{ slot.label }}
                                                    <div v-if="isLunch(slot)"
                                                        class="text-xs text-orange-700 font-semibold">
                                                        Lunch Break
                                                    </div>
                                                </th>
                                            </tr>
                                        </thead>

                                        <tbody>
                                            <tr v-for="day in days" :key="day.key" class="transition-colors">
                                                <td
                                                    class="border border-gray-300 p-3 bg-gray-50 text-base font-extrabold text-center text-teal-800">
                                                    {{ day.label }}
                                                </td>
                                                <td v-for="slot in uniqueTimeSlots" :key="`${day.key}-${slot.start}`"
                                                    class="border border-gray-300 p-2 text-center align-middle" :class="{
                                                        'bg-orange-50': isLunch(slot),
                                                        'bg-white hover:bg-teal-50/50 transition-colors': !isLunch(slot)
                                                    }">
                                                    <div v-if="getEntry(day.key, slot)" class="text-sm">
                                                        <div
                                                            class="text-sm font-extrabold text-teal-700 mb-1 leading-tight">
                                                            {{ getSubjectDisplay(getEntry(day.key, slot)) }}
                                                        </div>
                                                    </div>
                                                    <div v-else-if="!isLunch(slot)"
                                                        class="text-cyan-700 text-sm italic p-1">
                                                        Lab/Library/<br>အားကစား/ဂီတ
                                                    </div>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>

                                <div class="md:hidden space-y-6 p-4">
                                    <div v-for="day in days" :key="day.key"
                                        class="bg-white shadow-lg rounded-xl overflow-hidden border border-gray-200">
                                        <div class="bg-teal-600 text-white text-center py-3 font-extrabold text-lg">
                                            {{ day.label }}
                                        </div>

                                        <div v-for="slot in uniqueTimeSlots" :key="`${day.key}-${slot.start}`"
                                            class="p-4 border-b border-gray-200 flex items-center justify-between transition-colors"
                                            :class="{ 'bg-orange-50': isLunch(slot) }">

                                            <div class="font-medium text-gray-700 w-1/4 flex-shrink-0 pr-2">
                                                
                                                <span class="text-sm font-semibold"> {{ slot.name || slot.id }} {{ slot.label }}</span>
                                            </div>

                                            <div class="text-right w-3/4">
                                                <span v-if="isLunch(slot)"
                                                    class="text-base font-bold text-orange-700 block">
                                                    Lunch Break
                                                </span>
                                                <span v-else-if="getEntry(day.key, slot)"
                                                    class="text-lg font-extrabold text-teal-700 block leading-tight">
                                                    {{ getSubjectDisplay(getEntry(day.key, slot)) }}
                                                   
                                                </span>
                                                <span v-else class="italic text-sm block">
                                                     Library/Lab<br>အားကစား/ဂီတ 
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </CardBox>

                            <CardBox v-if="allSelectionsComplete && uniqueSubjects.length > 0"
                                class="mt-8 p-6 border border-gray-200 shadow-lg rounded-xl">
                                <h4 class="font-extrabold text-xl text-teal-800 mb-4 border-b-2 border-teal-100 pb-2"
                                    v-if="selectedSection && selectedSection?.section_head_teacher">
                                    သင်တန်းမှူး - {{ selectedSection.section_head_teacher.name }}
                                </h4>

                                <div class="overflow-x-auto">
                                    <table class="hidden md:table w-full border-collapse border border-gray-300">
                                        <thead>
                                            <tr>
                                                <th
                                                    class="border border-gray-300 p-3 bg-gray-50 font-bold text-sm w-24">
                                                    Code</th>
                                                <th
                                                    class="border border-x border-gray-300 p-3 bg-gray-50 font-bold text-sm w-auto">
                                                    Subject Name</th>
                                                <th
                                                    class="border border-gray-300 p-3 bg-gray-50 font-bold text-sm w-64">
                                                    Teacher Name(s)</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr v-for="subject in uniqueSubjects" :key="subject.id"
                                                class="hover:bg-teal-50 transition-colors">
                                                <td
                                                    class="border border-gray-300 p-3 text-sm text-gray-600 font-mono text-center">
                                                    {{ subject.code }}</td>
                                                <td
                                                    class="border border-x border-gray-300 p-3 text-sm text-gray-800 font-semibold">
                                                    {{ subject.name }}</td>
                                                <td class="border border-gray-300 p-3 text-sm text-gray-700">{{
                                                    subject.teacherNames }}</td>
                                            </tr>
                                        </tbody>
                                    </table>

                                    <div class="md:hidden space-y-4">
                                        <div v-for="subject in uniqueSubjects" :key="subject.id"
                                            class="border border-gray-200 rounded-lg shadow-sm p-4 bg-white hover:bg-teal-50 transition-colors">
                                            <div class="text-base font-extrabold text-teal-700 mb-1 leading-tight">
                                                <span class="font-mono text-sm text-gray-500 mr-2">{{ subject.code
                                                }}</span> {{ subject.name }}
                                            </div>
                                            <div class="text-sm text-gray-600 mt-2">
                                                <span class="font-bold text-gray-800">Teacher:</span>
                                                {{ subject.teacherNames }}
                                            </div>
                                        </div>
                                    </div>
                                </div>

                            </CardBox>
                        </div>
                    </div>
                </div>
                <div v-if="activeTab === 'teacher-register'">
                    <TeacherRegister />
                </div>
                <!-- Login Tab -->
                <div v-else-if="activeTab === 'login'">
                    <Login />
                    <!-- <div class="mb-4 text-center">
                        <button @click="switchToWelcome"
                            class="text-blue-600 hover:text-blue-800 text-sm font-medium transition-all duration-300 hover:bg-blue-50 px-3 py-2 rounded-md transform hover:scale-105">
                            ← Back to Home
                        </button>
                    </div> -->
                </div>

               
            </div>
        </div>
    </main>

    <!-- Footer -->
    <footer class="bg-sky-800 text-gray-50 border-t border-gray-700">
        <div class="text-center text-sm text-gray-50 py-3">
            © 2025 University of Computer Studies, Hinthada. All rights reserved.
        </div>
    </footer>
</template>
