<script setup>
import { Head } from '@inertiajs/vue3';
import { ref, computed, watch, onMounted } from 'vue';
import { router } from '@inertiajs/vue3';
import Login from '@/Pages/Auth/Login.vue';
import Register from '@/Pages/Auth/Register.vue';
import SectionTitleLineWithButton from '@/Components/SectionTitleLineWithButton.vue';
import CardBox from '@/Components/CardBox.vue';
import InputLabel from '@/Components/InputLabel.vue';
import BaseButton from '@/Components/BaseButton.vue';
import { mdiTable, mdiGrid } from '@mdi/js';

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
const switchToWelcome = () => switchTab('welcome');
const switchToTimetable = () => switchTab('timetable');

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

// Time slots for the grid - using dynamic data from backend and filtering by academic year
const timeSlots = computed(() => {
     let slots = Array.isArray(props.timeSlots) ? props.timeSlots : [];
    // Filter by academic year if selected
    if (filterYear.value) {
        slots = slots.filter(slot => slot.academic_year_id == filterYear.value);
    }
console.log("Filtered Time Slots:", props.timeSlots);
    return slots.map(slot => ({
        start: slot.start_time,
        end: slot.end_time,
        label: `${slot.start_time} - ${slot.end_time}`,
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

// Check if a time slot is lunch
const isLunch = (timeSlot) => {
    return timeSlot.is_lunch_period === 1;
};

// Get entry for specific day and time
const getEntry = (day, timeSlot) => {
    return organizedEntries.value[day]?.[timeSlot.start] || null;
};

// Get subject name with code
const getSubjectDisplay = (entry) => {
    if (!entry || !entry.subject) return "";
    return `${entry.subject.name}`;
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
</script>

<template>

    <Head title="Welcome to University of Computer Studies, Hinthada Timetable System" />

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

    <header class="backdrop-blur-md bg-teal-500/80 border-b border-teal-600 transition-all duration-300">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-6">
            <div class="flex items-center py-3 sm:py-4 space-x-3 sm:space-x-4">

                <!-- Logo -->
                <div class="p-1 sm:p-2 bg-white/90 rounded-lg shadow-sm hover:shadow-md transition-shadow duration-300">
                    <img src="/images/logo.png" alt="UCSh Logo" class="w-20 h-14 object-contain sm:w-24 sm:h-16" />
                </div>

                <!-- Text -->
                <div class="min-w-0">
                    <h1 class="text-lg sm:text-xl lg:text-2xl font-bold text-white truncate">
                        University of Computer Studies, Hinthada
                    </h1>
                    <p class="text-sm sm:text-base text-teal-100 truncate">
                        ကွန်ပျူတာတက္ကသိုလ်(ဟင်္သာတ)
                    </p>
                    <p class="text-xs sm:text-sm text-teal-200 truncate">
                        Timetable Information System
                    </p>
                </div>

            </div>
        </div>
    </header>


    <!-- Main Content -->
    <main class="min-h-screen relative">
        <div class="absolute inset-0 bg-transparent">
            <img src="/images/background2.jpg" alt="Background" class="w-full h-full object-cover opacity-50" />
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


                    <!-- Welcome Content -->
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 lg:gap-12 items-start mb-12 lg:mb-16" :class="{
                        'opacity-100 transform translate-y-0': animateWelcome,
                        'opacity-0 transform translate-y-8': !animateWelcome
                    }" style="transition: all 0.8s ease-out 0.2s;">
                        <!-- Left Column - Welcome Content -->
                        <div class="text-center lg:text-left order-2 lg:order-1">
                            <!-- University Branding -->
                            <div class="mb-6 lg:mb-8">
                                <h1 class="text-3xl sm:text-4xl lg:text-5xl font-bold text-gray-900 mb-3 sm:mb-4">
                                    Welcome to</h1>
                                <h2
                                    class="text-2xl sm:text-3xl lg:text-4xl font-bold text-teal-600 mb-3 sm:mb-4 leading-tight">
                                    University of Computer Studies, Hinthada</h2>
                                <p class="text-lg sm:text-xl text-gray-600 mb-4 sm:mb-6">Advanced Timetable Management
                                    System</p>
                                <p class="text-base sm:text-lg text-gray-700 leading-relaxed px-2 sm:px-0">
                                    Streamline your academic scheduling with our comprehensive system designed
                                    specifically
                                    for University of Computer Studies, Hinthada.
                                    Manage timetables, resources, and schedules efficiently with real-time updates and
                                    intelligent conflict resolution.
                                </p>
                            </div>

                            <!-- Feature Highlights -->
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

                            <!-- Call to Action -->

                        </div>

                        <!-- Right Column - System Features -->
                        <div class="lg:pl-4 xl:pl-8 order-1 lg:order-2" :class="{
                            'opacity-100 transform translate-x-0': animateFeatures,
                            'opacity-0 transform translate-x-8': !animateFeatures
                        }" style="transition: all 0.8s ease-out 0.4s;">
                            <div
                                class="bg-white rounded-lg shadow-lg p-4 sm:p-6 hover:shadow-xl transition-shadow duration-300">
                                <h3 class="text-lg sm:text-xl font-semibold text-gray-900 mb-3 sm:mb-4">System Features
                                </h3>
                                <ul class="space-y-2 sm:space-y-3">
                                    <li
                                        class="flex items-center p-2 sm:p-3 rounded-lg hover:bg-green-50 transition-colors duration-300">
                                        <svg class="w-4 h-4 sm:w-5 sm:h-5 text-green-500 mr-2 sm:mr-3 flex-shrink-0"
                                            fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd"
                                                d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                                                clip-rule="evenodd" />
                                        </svg>
                                        <span class="text-sm sm:text-base text-gray-700">Automated Scheduling</span>
                                    </li>
                                    <li
                                        class="flex items-center p-2 sm:p-3 rounded-lg hover:bg-teal-50 transition-colors duration-300">
                                        <svg class="w-4 h-4 sm:w-5 sm:h-5 text-teal-500 mr-2 sm:mr-3 flex-shrink-0"
                                            fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd"
                                                d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                                                clip-rule="evenodd" />
                                        </svg>
                                        <span class="text-sm sm:text-base text-gray-700">Conflict Resolution</span>
                                    </li>
                                    <li
                                        class="flex items-center p-2 sm:p-3 rounded-lg hover:bg-purple-50 transition-colors duration-300">
                                        <svg class="w-4 h-4 sm:w-5 sm:h-5 text-purple-500 mr-2 sm:mr-3 flex-shrink-0"
                                            fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd"
                                                d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                                                clip-rule="evenodd" />
                                        </svg>
                                        <span class="text-sm sm:text-base text-gray-700">Resource Management</span>
                                    </li>
                                </ul>
                            </div>
                            <div
                                class="flex flex-col sm:flex-row gap-4 justify-center lg:justify-start px-2 mt-8 sm:px-0">
                                <!-- View Timetable Button -->
                                <button @click="switchToTimetable" class="inline-flex items-center justify-center px-6 sm:px-8 py-3 border border-transparent 
           text-sm sm:text-base font-medium rounded-xl text-white 
           bg-gradient-to-r from-teal-600 to-teal-700 
           hover:from-teal-700 hover:to-teal-800 
           focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-teal-500 
           transition-all duration-300 transform hover:scale-105 
           shadow-lg hover:shadow-xl backdrop-blur-md">
                                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 
         00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                    </svg>

                                    View Timetable
                                </button>

                                <!-- Admin Sign In Button -->
                                <button @click="switchToLogin" class="inline-flex items-center justify-center px-6 sm:px-8 py-3 
           border border-teal-600 text-sm sm:text-base font-medium 
           rounded-xl text-teal-700 bg-white/80 
           hover:bg-teal-50 
           focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-teal-500 
           transition-all duration-300 transform hover:scale-105 
           shadow-md hover:shadow-lg backdrop-blur-md">
                                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1" />
                                    </svg>
                                    Admin Sign In
                                </button>
                            </div>

                        </div>
                    </div>


                </div>
                <div v-else-if="activeTab === 'timetable'">
                    <div>
                        <div class="mb-4 text-center">
                            <button @click="switchToWelcome"
                                class="text-blue-600 hover:text-blue-800 text-sm font-medium transition-all duration-300 hover:bg-blue-50 px-3 py-2 rounded-md transform hover:scale-105">
                                ← Back to Home
                            </button>
                        </div>

                        <div class="py-6">
                            <!-- Filters -->
                            <div
                                class="mb-6 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-5 gap-3 sm:gap-4 bg-white/30 dark:bg-gray-800/70 backdrop-blur-md p-4 rounded-lg shadow-md">
                                <!-- Academic Year -->
                                <div class="sm:col-span-1">
                                    <InputLabel value="Academic Year" class="text-sm font-medium text-gray-700 mb-1" />
                                    <select v-model="filterYear"
                                        class="w-full h-10 sm:h-11 px-3 text-sm border-gray-300 rounded-md focus:ring-2 focus:ring-teal-500 focus:border-teal-500 transition-colors">
                                        <option value="">All Years</option>
                                        <option v-for="y in props.academicYears" :key="y.id" :value="y.id">
                                            {{ y.name }}
                                        </option>
                                    </select>
                                </div>
                                <!-- Semester -->
                                <div class="sm:col-span-1">
                                    <InputLabel value="Semester" class="text-sm font-medium text-gray-700 mb-1" />
                                    <select v-model="filterSemester"
                                        class="w-full h-10 sm:h-11 px-3 text-sm border-gray-300 rounded-md focus:ring-2 focus:ring-teal-500 focus:border-teal-500 transition-colors">
                                        <option value="">All Semesters</option>
                                        <option v-for="s in filteredSemesters" :key="s.id" :value="s.id">{{ s.name }}
                                        </option>
                                    </select>
                                </div>
                                <!-- Program -->
                                <div class="sm:col-span-1">
                                    <InputLabel value="Program" class="text-sm font-medium text-gray-700 mb-1" />
                                    <select v-model="filterProgram"
                                        class="w-full h-10 sm:h-11 px-3 text-sm border-gray-300 rounded-md focus:ring-2 focus:ring-teal-500 focus:border-teal-500 transition-colors">
                                        <option value="">All Programs</option>
                                        <option v-for="p in filteredPrograms" :key="p.id" :value="p.id">
                                            {{ p.name }}
                                        </option>
                                    </select>
                                </div>
                                <!-- Level -->
                                <div class="sm:col-span-1">
                                    <InputLabel value="Level" class="text-sm font-medium text-gray-700 mb-1" />
                                    <select v-model="filterLevel"
                                        class="w-full h-10 sm:h-11 px-3 text-sm border-gray-300 rounded-md focus:ring-2 focus:ring-teal-500 focus:border-teal-500 transition-colors">
                                        <option value="">All Levels</option>
                                        <option v-for="l in filteredLevels" :key="l.id" :value="l.id">{{ l.name }}
                                        </option>
                                    </select>
                                </div>
                                <!-- Section -->
                                <div class="sm:col-span-1">
                                    <InputLabel value="Section" class="text-sm font-medium text-gray-700 mb-1" />
                                    <select v-model="filterSection"
                                        class="w-full h-10 sm:h-11 px-3 text-sm border-gray-300 rounded-md focus:ring-2 focus:ring-teal-500 focus:border-teal-500 transition-colors">
                                        <option value="">All Sections</option>
                                        <option v-for="s in filteredSections" :key="s.id" :value="s.id">
                                            {{ s.name }}
                                        </option>
                                    </select>
                                </div>
                                
                            </div>

                            <!-- Timetable Grid -->
                            <CardBox
                                class="overflow-hidden bg-white/90 dark:bg-gray-800/80 backdrop-blur-lg shadow-xl rounded-lg">
                                <div
                                    class="overflow-x-auto scrollbar-thin scrollbar-thumb-gray-300 scrollbar-track-gray-100">
                                    <!-- your <table> content remains the same -->
                                    <table class="w-full border-collapse min-w-full">
                                        <!-- Header with time slots -->
                                        <thead>
                                            <tr>
                                                <th
                                                    class="border border-gray-300 p-2 sm:p-3 bg-gray-50 font-semibold text-xs sm:text-sm w-20 sm:w-24 sticky left-0 z-10">
                                                    Day
                                                </th>
                                                <th v-for="slot in uniqueTimeSlots" :key="slot.start"
                                                    class="border border-gray-300 p-2 sm:p-3 bg-gray-50 font-semibold text-xs sm:text-sm text-center min-w-24 sm:min-w-32 whitespace-nowrap"
                                                    :class="{ 'bg-orange-100': isLunch(slot) }">
                                                    <div class="truncate">{{ slot.label }}</div>
                                                    <div v-if="isLunch(slot)"
                                                        class="text-xs text-orange-700 font-normal">
                                                        Lunch
                                                    </div>
                                                </th>
                                            </tr>
                                        </thead>

                                        <!-- Body with days and entries -->
                                        <tbody>
                                            <tr v-for="day in days" :key="day.key">
                                                <td
                                                    class="border border-gray-300 p-2 sm:p-3 bg-gray-50 font-semibold text-center text-xs sm:text-sm sticky left-0 z-10">
                                                    {{ day.label }}
                                                </td>
                                                <td v-for="slot in uniqueTimeSlots" :key="`${day.key}-${slot.start}`"
                                                    class="border border-gray-300 p-2 sm:p-3 text-center min-h-16 sm:min-h-20 align-top"
                                                    :class="{ 'bg-orange-50': isLunch(slot) }">
                                                    <div v-if="getEntry(day.key, slot)" class="text-xs leading-tight">
                                                        <div class="font-semibold text-teal-800 mb-1 truncate">
                                                            {{ getSubjectDisplay(getEntry(day.key, slot)) }}
                                                        </div>
                                                        <div class="text-gray-600 mb-1 text-xs" :class="{
                                                            'text-xs':
                                                                getEntry(day.key, slot).teachers &&
                                                                getEntry(day.key, slot).teachers.length > 1,
                                                            'text-xs':
                                                                getEntry(day.key, slot).teachers &&
                                                                getEntry(day.key, slot).teachers.length === 1
                                                        }">
                                                            <span v-if="
                                                                getEntry(day.key, slot).teachers &&
                                                                getEntry(day.key, slot).teachers.length > 0
                                                            ">
                                                                <span
                                                                    v-if="getEntry(day.key, slot).teachers.length === 1"
                                                                    class="truncate block">
                                                                    {{ getEntry(day.key, slot).teachers[0].name }}
                                                                </span>
                                                                <span v-else class="truncate block">
                                                                    {{ getEntry(day.key, slot).teachers.length }}
                                                                    teachers
                                                                </span>
                                                            </span>
                                                            <span v-else class="text-gray-400">No teacher</span>
                                                        </div>
                                                        <div class="text-gray-500 text-xs truncate">
                                                            {{ getClassroomDisplay(getEntry(day.key, slot)) }}
                                                        </div>
                                                    </div>
                                                    <div v-else-if="!isLunch(slot)"
                                                        class="text-gray-400 text-xs italic">
                                                        No class
                                                    </div>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </CardBox>

                            <!-- Legend -->
                            <div class="mt-4 p-3 sm:p-4 bg-white/80 dark:bg-gray-800/70 backdrop-blur-md rounded-lg">
                                <div class="flex flex-wrap gap-3 sm:gap-4 text-xs sm:text-sm">
                                    <div class="flex items-center">
                                        <div
                                            class="w-3 h-3 sm:w-4 sm:h-4 bg-orange-100 border border-gray-300 mr-2 rounded-sm">
                                        </div>
                                        <span class="text-gray-700">Lunch Period</span>
                                    </div>
                                    <div class="flex items-center">
                                        <div
                                            class="w-3 h-3 sm:w-4 sm:h-4 bg-teal-50 border border-gray-300 mr-2 rounded-sm">
                                        </div>
                                        <span class="text-gray-700">Regular Class</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- Login Tab -->
                <div v-else-if="activeTab === 'login'">

                    <Login />
                    <div class="mb-4 text-center">
                        <button @click="switchToWelcome"
                            class="text-blue-600 hover:text-blue-800 text-sm font-medium transition-all duration-300 hover:bg-blue-50 px-3 py-2 rounded-md transform hover:scale-105">
                            ← Back to Home
                        </button>
                    </div>
                </div>

                <!-- Register Tab -->
                <!-- <div v-else-if="activeTab === 'register'">
                <div class="mb-4 text-center">
                    <button @click="switchToWelcome" class="text-blue-600 hover:text-blue-800 text-sm font-medium transition-all duration-300 hover:bg-blue-50 px-3 py-2 rounded-md transform hover:scale-105">
                        ← Back to Home
                    </button>
                </div>
                <Register />
            </div> -->
            </div>
        </div>
    </main>

    <!-- Footer -->
    <footer class="bg-teal-500 border-t border-teal-600">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6 sm:py-8">
            <div class="text-center">
                <div class="flex flex-col sm:flex-row items-center justify-center mb-3 sm:mb-4">
                    <div class="inline-flex items-center justify-center p-2 bg-white rounded-lg mb-2 sm:mb-0">
                        <img src="/images/logo.png" alt="University of Computer Studies, Hinthada Logo"
                            class="w-6 h-6 sm:w-8 sm:h-8 object-contain" />
                    </div>
                    <span class="ml-0 sm:ml-3 text-lg sm:text-xl font-semibold text-white text-center">University of
                        Computer Studies, Hinthada</span>
                </div>
                <p class="text-teal-100 text-xs sm:text-sm px-4 sm:px-0">
                    © 2024 University of Computer Studies, Hinthada. All rights reserved. | Timetable Management System
                </p>
            </div>
        </div>
    </footer>
</template>
