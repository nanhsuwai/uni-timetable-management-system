<script setup>
import { useForm, usePage, Head } from "@inertiajs/vue3";
import { computed, ref, onMounted } from "vue";
import { mdiAccount, mdiEmail, mdiFormTextboxPassword, mdiEye, mdiEyeOff } from "@mdi/js";
import SectionFullScreen from "@/Components/SectionFullScreen.vue";
import CardBox from "@/Components/CardBox.vue";
import FormField from "@/Components/FormField.vue";
import FormControl from "@/Components/FormControl.vue";
import BaseDivider from "@/Components/BaseDivider.vue";
import BaseButton from "@/Components/BaseButton.vue";
import BaseButtons from "@/Components/BaseButtons.vue";
import FormValidationErrors from "@/Components/FormValidationErrors.vue";

const form = useForm({
  name: "",
  username: "",
  email: "",
  password: "",
  password_confirmation: "",
  terms: false,
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

const hasTermsAndPrivacyPolicyFeature = computed(
  () => usePage().props.jetstream?.hasTermsAndPrivacyPolicyFeature
);

const showPassword = ref(false);
const showConfirmPassword = ref(false);

const togglePasswordVisibility = () => {
  showPassword.value = !showPassword.value;
};

const toggleConfirmPasswordVisibility = () => {
  showConfirmPassword.value = !showConfirmPassword.value;
};

const submit = () => {
  form.post(route("register"), {
    onFinish: () => form.reset("password", "password_confirmation"),
  });
};
</script>

<template>
    <Head title="Register - University of Computer Studies, Hinthada" />

    <!-- Loading Screen -->
    <div v-if="isLoading" class="fixed inset-0 bg-gradient-to-br from-teal-500 to-teal-700 flex items-center justify-center z-50">
        <div class="text-center">
            <div class="inline-flex items-center justify-center p-4 bg-white/10 rounded-full mb-4 animate-pulse">
                <svg class="w-12 h-12 text-white animate-spin" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                </svg>
            </div>
            <h2 class="text-2xl font-bold text-white mb-2">Setting up your account...</h2>
            <p class="text-teal-100">Please wait while we prepare the registration form</p>
        </div>
    </div>

    <SectionFullScreen v-slot="{ cardClass }" bg="teal">
        <div class="min-h-screen flex items-center justify-center py-6 sm:py-12 px-4 sm:px-6 lg:px-8">
            <div class="w-full max-w-md space-y-8">
                <!-- Header -->
                <div class="text-center" :class="{
                    'opacity-100 transform translate-y-0': showForm,
                    'opacity-0 transform -translate-y-4': !showForm
                }" style="transition: all 0.6s ease-out;">
                    <div class="inline-flex items-center justify-center p-3 bg-white rounded-full mb-4 shadow-lg">
                        <img
                            src="/images/logo.png"
                            alt="University of Computer Studies, Hinthada Logo"
                            class="w-12 h-12 object-contain"
                        />
                    </div>
                    <h2 class="text-2xl sm:text-3xl font-bold text-gray-900 mb-2">Create Account</h2>
                    <p class="text-gray-600">Join University of Computer Studies, Hinthada</p>
                    <p class="text-sm text-teal-600 mt-1">Timetable Management System</p>
                </div>

                <!-- Registration Form -->
                <CardBox
                   
                    class="shadow-2xl border-0"
                    is-form
                    @submit.prevent="submit"
                >
                    <FormValidationErrors class="mb-4" />

                    <!-- Username Field -->
                    <FormField label="Username" label-for="username" help="Choose a unique username">
                        <FormControl
                            v-model="form.username"
                            id="username"
                            :icon="mdiAccount"
                            autocomplete="username"
                            type="text"
                            placeholder="Enter your username"
                            required
                            class="transition-all duration-300 focus:ring-2 focus:ring-teal-500 focus:border-teal-500"
                        />
                    </FormField>

                    <!-- Name Field -->
                    <FormField label="Full Name" label-for="name" help="Enter your full name">
                        <FormControl
                            v-model="form.name"
                            id="name"
                            :icon="mdiAccount"
                            autocomplete="name"
                            type="text"
                            placeholder="Enter your full name"
                            required
                            class="transition-all duration-300 focus:ring-2 focus:ring-teal-500 focus:border-teal-500"
                        />
                    </FormField>

                    <!-- Email Field -->
                    <FormField label="Email Address" label-for="email" help="We'll send you important updates">
                        <FormControl
                            v-model="form.email"
                            id="email"
                            :icon="mdiEmail"
                            autocomplete="email"
                            type="email"
                            placeholder="Enter your email address"
                            required
                            class="transition-all duration-300 focus:ring-2 focus:ring-teal-500 focus:border-teal-500"
                        />
                    </FormField>

                    <!-- Password Field -->
                    <FormField label="Password" label-for="password" help="Minimum 8 characters">
                        <div class="relative">
                            <FormControl
                                v-model="form.password"
                                id="password"
                                :icon="mdiFormTextboxPassword"
                                :type="showPassword ? 'text' : 'password'"
                                autocomplete="new-password"
                                placeholder="Create a strong password"
                                required
                                class="pr-12 transition-all duration-300 focus:ring-2 focus:ring-teal-500 focus:border-teal-500"
                            />
                            <button
                                type="button"
                                @click="togglePasswordVisibility"
                                class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-400 hover:text-teal-600 transition-colors duration-300"
                            >
                                <svg class="w-5 h-5" :class="showPassword ? 'hidden' : 'block'" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                </svg>
                                <svg class="w-5 h-5" :class="showPassword ? 'block' : 'hidden'" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.878 9.878L3 3m6.878 6.878L21 21" />
                                </svg>
                            </button>
                        </div>
                    </FormField>

                    <!-- Confirm Password Field -->
                    <FormField label="Confirm Password" label-for="password_confirmation" help="Repeat your password">
                        <div class="relative">
                            <FormControl
                                v-model="form.password_confirmation"
                                id="password_confirmation"
                                :icon="mdiFormTextboxPassword"
                                :type="showConfirmPassword ? 'text' : 'password'"
                                autocomplete="new-password"
                                placeholder="Confirm your password"
                                required
                                class="pr-12 transition-all duration-300 focus:ring-2 focus:ring-teal-500 focus:border-teal-500"
                            />
                            <button
                                type="button"
                                @click="toggleConfirmPasswordVisibility"
                                class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-400 hover:text-teal-600 transition-colors duration-300"
                            >
                                <svg class="w-5 h-5" :class="showConfirmPassword ? 'hidden' : 'block'" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                </svg>
                                <svg class="w-5 h-5" :class="showConfirmPassword ? 'block' : 'hidden'" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.878 9.878L3 3m6.878 6.878L21 21" />
                                </svg>
                            </button>
                        </div>
                    </FormField>

                    <!-- Terms and Conditions -->
                    <div v-if="hasTermsAndPrivacyPolicyFeature" class="mt-4">
                        <label class="flex items-start space-x-3 text-sm">
                            <input
                                v-model="form.terms"
                                type="checkbox"
                                class="mt-1 h-4 w-4 text-teal-600 border-gray-300 rounded focus:ring-teal-500 focus:ring-2"
                            />
                            <span class="text-gray-700">
                                I agree to the
                                <a href="#" class="text-teal-600 hover:text-teal-500 font-medium transition-colors duration-300">Terms of Service</a>
                                and
                                <a href="#" class="text-teal-600 hover:text-teal-500 font-medium transition-colors duration-300">Privacy Policy</a>
                            </span>
                        </label>
                    </div>

                    <BaseDivider class="my-6" />

                    <!-- Action Buttons -->
                    <BaseButtons class="flex-col sm:flex-row gap-3">
                        <BaseButton
                            type="submit"
                            color="success"
                            label="Create Account"
                            :class="[
                                'w-full transition-all duration-300 transform hover:scale-105',
                                { 'opacity-50 cursor-not-allowed': form.processing }
                            ]"
                            :disabled="form.processing || !form.terms"
                        >
                            <svg v-if="form.processing" class="w-5 h-5 mr-2 animate-spin" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                            </svg>
                            <svg v-else class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"></path>
                            </svg>
                            {{ form.processing ? 'Creating Account...' : 'Create Account' }}
                        </BaseButton>
                        <BaseButton
                            route-name="login"
                            color="info"
                            outline
                            label="Already have an account?"
                            class="w-full transition-all duration-300 transform hover:scale-105"
                        />
                    </BaseButtons>
                </CardBox>

                <!-- Footer -->
                <div class="text-center text-sm text-gray-600">
                    <p>© 2024 University of Computer Studies, Hinthada</p>
                    <p class="mt-1">All rights reserved</p>
                </div>
            </div>
        </div>
    </SectionFullScreen>
</template>
