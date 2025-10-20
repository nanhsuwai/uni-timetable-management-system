<script setup>
import { Head } from '@inertiajs/vue3';
import { ref, computed, watch, onMounted } from 'vue';
/* import { router } from '@inertiajs/vue3'; */
import Login from '@/Pages/Auth/Login.vue';
import Register from '@/Pages/Auth/Register.vue';
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

    <header class="bg-teal-900 border-b border-cyan-500 shadow-xl transition-all duration-300">
        <div class="max-w-7xl mx-auto px-2 sm:px-4 lg:px-4">
            <div class="flex items-center py-3 sm:py-4 space-x-3 sm:space-x-4">


                <img src="/images/logo.png" alt="UCSh Logo" class="w-20 h-14 object-contain sm:w-20 sm:h-13" />


                <div class="min-w-0">
                    <h1 class="text-lg sm:text-xl lg:text-2xl font-extrabold text-white truncate">
                        University of Computer Studies, Hinthada
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
                                <!-- Right Column - System Features -->
                                <div class="lg:pl-4 xl:pl-8 order-1 lg:order-2" :class="{
                                    'opacity-100 transform translate-x-0': animateFeatures,
                                    'opacity-0 transform translate-x-8': !animateFeatures
                                }" style="transition: all 0.8s ease-out 0.4s;">

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
                                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor"
                                                viewBox="0 0 24 24">
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
                                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor"
                                                viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1" />
                                            </svg>
                                            Admin Sign In
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
                    <div>
                        <div class="mb-4 text-center">
                            <button @click="switchToWelcome"
                                class="text-blue-600 hover:text-blue-800 text-sm font-medium transition-all duration-300 hover:bg-blue-50 px-3 py-2 rounded-md transform hover:scale-105">
                                ← Back to Home
                            </button>
                        </div>

                        <div class="py-6">
                            <!-- Filters -->
                            <div class="mb-6 grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                                <div>
                                    <InputLabel value="Academic Year" />
                                    <select v-model="filterYear" class="w-full border-gray-300 rounded">
                                        <option value="">All Years</option>
                                        <option v-for="y in props.academicYears" :key="y.id" :value="y.id">{{ y.name }}
                                        </option>
                                    </select>
                                </div>
                                <div>
                                    <InputLabel value="Program" />
                                    <select v-model="filterProgram" class="w-full border-gray-300 rounded">
                                        <option value="">All Programs</option>
                                        <option v-for="p in filteredPrograms" :key="p.id" :value="p.id">{{ p.name }}
                                        </option>
                                    </select>
                                </div>
                                <div>
                                    <InputLabel value="Level" />
                                    <select v-model="filterLevel" class="w-full border-gray-300 rounded">
                                        <option value="">All Levels</option>
                                        <option v-for="l in filteredLevels" :key="l.id" :value="l.id">{{ l.name }}
                                        </option>
                                    </select>
                                </div>
                                <div>
                                    <InputLabel value="Section" />
                                    <select v-model="filterSection" class="w-full border-gray-300 rounded">
                                        <option value="">All Sections</option>
                                        <option v-for="s in filteredSections" :key="s.id" :value="s.id">{{ s.name }}
                                        </option>
                                    </select>
                                </div>
                                <div>
                                    <InputLabel value="Semester" />
                                    <select v-model="filterSemester" class="w-full border-gray-300 rounded">
                                        <option value="">All Semesters</option>
                                        <option v-for="s in filteredSemesters" :key="s.id" :value="s.id">{{ s.name }}
                                        </option>
                                    </select>
                                </div>
                            </div>

                            <!-- Timetable Grid -->
                           <CardBox v-if="allSelectionsComplete">
        <div class="overflow-x-auto">
          <table class="w-full border-collapse">
            <thead>
              <tr>
                <th :colspan="1 + uniqueTimeSlots.length"
                  class="text-center font-bold text-lg bg-gray-100 p-4 border border-gray-300 text-teal-600">
                  University of Computer Studies, Hinthada
                </th>
              </tr>
              <tr>
                <th :colspan="1 + uniqueTimeSlots.length"
                  class="text-center text-sm bg-gray-50 p-2 border border-gray-300 text-teal-600">
                  <span v-if="selectedYear" class="font-bold text-base">
                    Academic Year: {{ selectedYear.name }}
                  </span>
                  <span v-if="selectedSemester" class="ml-4">
                    ( {{ selectedSemester.name }})
                  </span>
                </th>
              </tr>
              <tr>
                <th :colspan="1 + uniqueTimeSlots.length"
                  class="text-center text-sm bg-gray-50 p-2 border border-gray-300 text-teal-600">
                  Timetable For -
                  <span v-if="selectedLevel"> {{ selectedLevel.name }} </span>
                  (<span v-if="selectedProgram">{{ selectedProgram.name }} </span>)
                  <span v-if="selectedSection && selectedSection.name"> Section:
                    {{ selectedSection.name }}
                  </span>
                  <span v-else>
                  </span>
                  <span class="block text-right" v-if="selectedSection && selectedSection?.classroom">Classroom:
                    {{ selectedSection.classroom.room_no }} </span>
                </th>
              </tr>
              <tr>
                <th class="border border-gray-300 p-3 bg-gray-50 font-semibold text-sm w-24">Day</th>
                <th v-for="slot in uniqueTimeSlots" :key="slot.start"
                  class="border border-gray-300 p-3 bg-gray-50 font-semibold text-sm text-center min-w-32" :class="{
                    'bg-orange-100': isLunch(slot)
                  }">
                  {{ slot.label }}
                  <div v-if="isLunch(slot)" class="text-xs text-orange-700 font-normal">Lunch</div>
                </th>
              </tr>
            </thead>

            <tbody>
              <tr v-for="day in days" :key="day.key">
                <td class="border border-gray-300 p-3 bg-gray-50 font-semibold text-center">
                  {{ day.label }}
                </td>
                <td v-for="slot in uniqueTimeSlots" :key="`${day.key}-${slot.start}`"
                  class="border border-gray-300 p-2 text-center min-h-20 align-top" :class="{
                    'bg-orange-50': isLunch(slot)
                  }">
                  <div v-if="getEntry(day.key, slot)" class="text-sm">
                    <div class="font-semibold text-teal-700 mb-1">
                      {{ getSubjectDisplay(getEntry(day.key, slot)) }}
                    </div>
                  </div>
                  <div v-else-if="!isLunch(slot)" class="text-gray-400 text-xs italic">
                    No subject
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </CardBox>
                <CardBox v-if="allSelectionsComplete && uniqueSubjects.length > 0" class="mt-4">
        <h4 class="font-semibold mb-2"> <span
            v-if="selectedSection && selectedSection?.section_head_teacher">သင်တန်းမှူး - {{
              selectedSection.section_head_teacher.name }}</span>
        </h4>

        <div class="overflow-x-auto">
          <table class="w-full border-collapse border border-gray-300">
            <thead>
              <tr>
                <th class="border border-gray-300 p-2 bg-gray-50 font-semibold w-24">Code</th>
                <th class="border border-x border-gray-300 p-2 bg-gray-50 font-semibold w-auto">Subject Name</th>
                <th class="border border-gray-300 p-2 bg-gray-50 font-semibold w-64">Teacher Name</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="subject in uniqueSubjects" :key="subject.id">
                <td class="border border-gray-300 p-2 text-sm">{{ subject.code }}</td>
                <td class="border border-x border-gray-300 p-2 text-sm">{{ subject.name }}</td>
                <td class="border border-gray-300 p-2 text-sm text-gray-700 font-medium">
                  {{ subject.teacherNames }}
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </CardBox>        
                           
                           
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
            </div>
        </div>
    </main>

    <!-- Footer -->
    <footer class="bg-gray-800 text-gray-300 border-t border-gray-700">
        <div class="text-center text-sm text-gray-500 py-3">
            © 2025 University of Computer Studies, Hinthada. All rights reserved.
        </div>
    </footer>
</template>
