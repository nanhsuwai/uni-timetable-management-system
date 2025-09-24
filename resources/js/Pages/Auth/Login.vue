<script setup>
import { useForm, Head, Link, router } from '@inertiajs/vue3'
import { computed, ref, onMounted } from 'vue'
import { mdiAccount, mdiAsterisk, mdiEye, mdiEyeOff } from '@mdi/js'
import SectionFullScreen from '@/Components/SectionFullScreen.vue'
import CardBox from '@/Components/CardBox.vue'
import FormField from '@/Components/FormField.vue'
import FormControl from '@/Components/FormControl.vue'
import BaseDivider from '@/Components/BaseDivider.vue'
import BaseButton from '@/Components/BaseButton.vue'
import BaseButtons from '@/Components/BaseButtons.vue'
import FormValidationErrors from '@/Components/FormValidationErrors.vue'

const props = defineProps({
  canResetPassword: Boolean,
  status: {
    type: String,
    default: null
  }
})

const form = useForm({
  email: '',
  password: '',
  remember: false
})

// Animation states
const isLoading = ref(true)
const showForm = ref(false)
const animateCard = ref(false)

onMounted(() => {
  setTimeout(() => {
    isLoading.value = false
    showForm.value = true
    setTimeout(() => {
      animateCard.value = true
    }, 300)
  }, 600)
})

const showPassword = ref(false)

const togglePasswordVisibility = () => {
  showPassword.value = !showPassword.value
}

const submit = () => {
  form.post(route('login'), {
    onFinish: () => form.reset('password'),
    onSuccess: () => {
      // Navigate to Welcome page after successful login
      router.visit('/')
    },
  })
}
</script>

<template>
    <Head title="Login - University of Computer Studies, Hinthada" />

    <!-- Loading Screen fff-->
    <div v-if="isLoading" class="fixed inset-0 flex items-center justify-center z-50">
        <div class="text-center">
            <div class="inline-flex items-center justify-center p-4  rounded-full mb-4 animate-pulse">
                <svg class="w-12 h-12 text-white animate-spin" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                </svg>
            </div>
            <h2 class="text-2xl font-bold text-white mb-2">Welcome back!</h2>
            <p class="text-teal-100">Please wait while we prepare the login form</p>
        </div>
    </div>

    <!-- <SectionFullScreen v-slot="{ cardClass }" bg="teal"> -->
        <div class="min-h-screen flex items-center justify-center py-6 sm:py-12 px-4 sm:px-6 lg:px-8">
            <div class="w-full max-w-md space-y-8">
                <!-- Header -->
                <div class="text-center" :class="{
                    'opacity-100 transform translate-y-0': showForm,
                    'opacity-0 transform -translate-y-4': !showForm
                }" style="transition: all 0.6s ease-out;">
                    <div class="inline-flex items-center justify-center p-3 bg-white  shadow-lg">
                        <img
                            src="/images/logo.png"
                            alt="University of Computer Studies, Hinthada Logo"
                            class="w-120 h-14 object-contain"
                        />
                    </div>

                    <!-- <p class="text-sm text-teal-600 mt-1">Timetable Management System</p> -->
                </div>

                <!-- Login Form -->
                <CardBox
                  
                    class="shadow-2xl border-0"
                    is-form
                    @submit.prevent="submit"
                >
                    <FormValidationErrors class="mb-4" />

                    <!-- Status Message -->
                    <div v-if="status" class="mb-4 p-4 bg-green-50 border border-green-200 rounded-lg">
                        <div class="flex">
                            <svg class="w-5 h-5 text-green-400 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                            </svg>
                            <p class="text-sm text-green-700">{{ status }}</p>
                        </div>
                    </div>

                    <!-- Email Field -->
                    <FormField label="Email Address" label-for="email" help="Enter your registered email">
                        <FormControl
                            v-model="form.email"
                            :icon="mdiAccount"
                            id="email"
                            autocomplete="email"
                            type="email"
                            placeholder="Enter your email address"
                            required
                            class="transition-all duration-300 focus:ring-2 focus:ring-teal-500 focus:border-teal-500"
                        />
                    </FormField>

                    <!-- Password Field -->
                    <FormField label="Password" label-for="password" help="Enter your password">
                        <div class="relative">
                            <FormControl
                                v-model="form.password"
                                :icon="mdiAsterisk"
                                :type="showPassword ? 'text' : 'password'"
                                id="password"
                                autocomplete="current-password"
                                placeholder="Enter your password"
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

                    <!-- Remember Me -->
                    <div class="flex items-center justify-between">
                        <label class="flex items-center space-x-3 text-sm">
                            <input
                                v-model="form.remember"
                                type="checkbox"
                                class="h-4 w-4 text-teal-600 border-gray-300 rounded focus:ring-teal-500 focus:ring-2"
                            />
                            <span class="text-gray-700">Remember me</span>
                        </label>
                        <div v-if="canResetPassword" class="text-sm">
                            <a href="#" @click.prevent="$inertia.visit(route('password.request'))" class="text-teal-600 hover:text-teal-500 font-medium transition-colors duration-300">
                                Forgot password?
                            </a>
                        </div>
                    </div>

                    <BaseDivider class="my-6" />

                    <!-- Action Buttons -->
                    <BaseButtons class="flex-col sm:flex-row gap-3">
                        <BaseButton
                            type="submit"
                            color="success"
                            label="Sign In"
                            :class="[
                                'w-full transition-all duration-300 transform hover:scale-105',
                                { 'opacity-50 cursor-not-allowed': form.processing }
                            ]"
                            :disabled="form.processing"
                        >
                            <svg v-if="form.processing" class="w-5 h-5 mr-2 animate-spin" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                            </svg>
                            <svg v-else class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"></path>
                            </svg>
                            {{ form.processing ? 'Signing In...' : 'Sign In' }}
                        </BaseButton>
                        
                    </BaseButtons>
                </CardBox>

               
            </div>
        </div>
    <!-- </SectionFullScreen> -->
</template>
