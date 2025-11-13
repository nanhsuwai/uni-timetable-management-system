<script setup>
import { useForm, usePage, Head } from "@inertiajs/vue3";
import { computed, ref, onMounted } from "vue";
import { mdiAccount, mdiEmail, mdiPhone, mdiSchool } from "@mdi/js";
import SectionFullScreen from "@/Components/SectionFullScreen.vue";
import CardBox from "@/Components/CardBox.vue";
import FormField from "@/Components/FormField.vue";
import FormControl from "@/Components/FormControl.vue";
import BaseDivider from "@/Components/BaseDivider.vue";
import BaseButton from "@/Components/BaseButton.vue";
import BaseButtons from "@/Components/BaseButtons.vue";
import FormValidationErrors from "@/Components/FormValidationErrors.vue";
import toast from "@/Stores/toast";
import LayoutGuest from "@/Layouts/LayoutGuest.vue";
import ToastList from "../../Components/ToastList.vue";

// Department enum options
const DepartmentOption = {
    ITSM: "Department of Information Technology Supporting and Maintenance",
    FCST: "Faculty of Computer System and Technology",
    FCS: "Faculty of Computer Science",
    IS: "Faculty of Information Science",
    Physics: "Natural Science Department",
    Mathematics: "Faculty of Computing",
    English: "Language Department(English)",
    Myanmar: "Language Department(Myanmar)",
};

const form = useForm({
    name: "",
    email: "",
    phone: "",
    department: "",
});

// Animation states
const isLoading = ref(true);
const showForm = ref(false);
const animateCard = ref(false);

onMounted(() => {
    setTimeout(() => {
        isLoading.value = false;
        showForm.value = true;
        setTimeout(() => {
            animateCard.value = true;
        }, 300);
    }, 800);
});

const submit = () => {
    form.post(route("teacher.register"), {
        onSuccess: () => {
            // Reset form after successful submission
            form.reset();
            toast.add({
                type: "success",
                message: "Your teacher registration form has been submitted successfully. You will be notified once it is reviewed by an administrator.",
            });
            showForm.value = false;
            animateCard.value = false;

        },
        onError: () => {
            toast.add({
                type: "error",
                message: "There were errors with your submission. Please check the form and try again.",
            });
            // Handle error state if needed
            console.error("Registration failed:", form.errors);
        },


    });
};

// Department options
const departmentOptions = computed(() => {
    return Object.values(DepartmentOption).map(dept => ({
        value: dept,
        label: dept
    }));
});
</script>

<template>
    <ToastList />

    <Head title="Teacher Registration - University of Computer Studies, Hinthada" />

    <!-- Loading Screen -->
    <div v-if="isLoading" class="fixed inset-0 from-teal-500 to-teal-700 flex items-center justify-center z-50">
        <div class="text-center">
            <div class="inline-flex items-center justify-center p-4 bg-white/10 rounded-full mb-4 animate-pulse">
                <svg class="w-12 h-12 text-white animate-spin" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15">
                    </path>
                </svg>
            </div>
            <h2 class="text-2xl font-bold text-white mb-2">Setting up Teacher Registration</h2>
            <p class="text-teal-100">Please wait while we prepare the registration form</p>
        </div>
    </div>

    <SectionFullScreen v-slot="{ cardClass }" bg="teal">
        <div class="min-h-screen flex items-center justify-center py-4 sm:py-6 lg:py-12 px-3 sm:px-4 lg:px-8">
            <div class="w-full max-w-sm sm:max-w-md lg:max-w-lg space-y-6 sm:space-y-8">
                <!-- Header -->
                <div class="text-center" :class="{
                    'opacity-100 transform translate-y-0': showForm,
                    'opacity-0 transform -translate-y-4': !showForm
                }" style="transition: all 0.6s ease-out;">
                    <div class="inline-flex items-center justify-center p-3 bg-white rounded-full mb-4 shadow-lg">
                        <img src="/images/logo.png" alt="University of Computer Studies, Hinthada Logo"
                            class="w-12 h-12 object-contain" />
                    </div>
                    <h2 class="text-xl sm:text-2xl lg:text-3xl font-bold text-gray-900 mb-2">Teacher Registration</h2>
                    <p class="text-sm sm:text-base text-gray-600">Apply to join University of Computer Studies, Hinthada
                    </p>
                    <p class="text-xs sm:text-sm text-teal-600 mt-1">Timetable Management System</p>
                    <div class="mt-4 p-3 bg-yellow-50 border border-yellow-200 rounded-lg">
                        <p class="text-sm text-yellow-800">
                            <strong>Note:</strong> Your application will be reviewed by an administrator before
                            approval.
                        </p>
                    </div>
                </div>

                <!-- Registration Form -->
                <CardBox class="shadow-2xl border-0" is-form @submit.prevent="submit">
                    <FormValidationErrors class="mb-4" />

                    <!-- Name Field -->
                    <FormField label="Full Name" label-for="name"
                        help="Enter your full name as it appears on official documents">
                        <FormControl v-model="form.name" id="name" :icon="mdiAccount" autocomplete="name" type="text"
                            placeholder="Enter your full name" required
                            class="transition-all duration-300 focus:ring-2 focus:ring-teal-500 focus:border-teal-500" />
                    </FormField>

                    <!-- Email Field -->
                    <FormField label="Email Address" label-for="email"
                        help="We'll send you important updates about your application">
                        <FormControl v-model="form.email" id="email" :icon="mdiEmail" autocomplete="email" type="email"
                            placeholder="Enter your email address" required
                            class="transition-all duration-300 focus:ring-2 focus:ring-teal-500 focus:border-teal-500" />
                    </FormField>

                    <!-- Phone Field -->
                    <FormField label="Phone Number" label-for="phone" help="Enter your contact phone number">
                        <FormControl v-model="form.phone" id="phone" :icon="mdiPhone" autocomplete="tel" type="tel"
                            placeholder="Enter your phone number" required
                            class="transition-all duration-300 focus:ring-2 focus:ring-teal-500 focus:border-teal-500" />
                    </FormField>

                    <!-- Department Field -->
                    <FormField label="Department" label-for="department" help="Select your primary department">
                        <select v-model="form.department" id="department"
                            class="w-full h-9 sm:h-10 lg:h-11 px-3 text-xs sm:text-sm border-gray-300 rounded-md focus:ring-2 focus:ring-teal-500 focus:border-teal-500 transition-colors"
                            required>
                            <option value="">Select Department</option>
                            <option v-for="dept in departmentOptions" :key="dept.value" :value="dept.value">
                                {{ dept.label }}
                            </option>
                        </select>
                    </FormField>





                    <BaseDivider class="my-6" />

                    <!-- Action Buttons -->
                    <BaseButtons class="flex-col sm:flex-row gap-2 sm:gap-3">
                        <BaseButton type="submit" color="success" label="Submit Application" :class="[
                            'w-full text-xs sm:text-sm px-3 sm:px-4 py-2 sm:py-3 transition-all duration-300 transform hover:scale-105',
                            { 'opacity-50 cursor-not-allowed': form.processing }
                        ]" :disabled="form.processing">
                            <svg v-if="form.processing" class="w-4 h-4 sm:w-5 sm:h-5 mr-1 sm:mr-2 animate-spin"
                                fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15">
                                </path>
                            </svg>
                            <svg v-else class="w-4 h-4 sm:w-5 sm:h-5 mr-1 sm:mr-2" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                           <!--  <span class="hidden sm:inline">{{ form.processing ? 'Submitting Application...' : 'Submit
                                Application' }}</span> -->
                            <span class="sm:hidden">{{ form.processing ? 'Submitting...' : 'Submit' }}</span>
                        </BaseButton>
                        <BaseButton route-name="welcome" color="info" outline label="Back to Home"
                            class="w-full text-xs sm:text-sm px-3 sm:px-4 py-2 sm:py-3 transition-all duration-300 transform hover:scale-105" />
                    </BaseButtons>
                </CardBox>


            </div>
        </div>
    </SectionFullScreen>
</template>
