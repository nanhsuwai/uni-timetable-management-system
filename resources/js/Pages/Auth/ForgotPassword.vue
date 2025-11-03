<script setup>
import { useForm, Head } from '@inertiajs/vue3'
import { mdiEmail } from '@mdi/js'

defineProps({
  status: {
    type: String,
    default: null
  }
})

const form = useForm({
  email: ''
})

const submit = () => {
  form.post(route('password.email'))
}
const switchToWelcome = () => switchTab('welcome');
</script>

<template>
  <Head title="Forgot Password" />

  <!-- Header -->
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
      <div class="absolute inset-0 bg-transparent"></div>
    </div>
    <div class="relative z-40 max-w-7xl mx-auto px-3 sm:px-4 lg:px-6 xl:px-8 py-6 sm:py-8 lg:py-12">
      <div class="flex items-center justify-center min-h-[60vh]">
        <div class="w-full max-w-md">
          <div class="bg-white/90 dark:bg-gray-800/80 backdrop-blur-lg shadow-xl rounded-lg p-6">
            <div class="text-center mb-6">
              <h2 class="text-2xl font-bold text-gray-900 dark:text-white">Forgot Password</h2>
              <p class="text-gray-600 dark:text-gray-400 mt-2">
                Forgot your password? No problem. Just let us know your email address and we will email you a password reset link that will allow you to choose a new one.
              </p>
            </div>

            <form @submit.prevent="submit">
              <div class="mb-4">
                <label for="email" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                  Email
                </label>
                <div class="relative">
                  <input
                    v-model="form.email"
                    type="email"
                    id="email"
                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-teal-500 focus:border-teal-500 transition-colors"
                    placeholder="Enter your email"
                    required
                  />
                  <svg class="absolute right-3 top-2.5 h-5 w-5 text-gray-400" fill="currentColor" viewBox="0 0 24 24">
                    <path d="M20 4H4c-1.1 0-1.99.9-1.99 2L2 18c0 1.1.9 2 2 2h16c1.1 0 2-.9 2-2V6c0-1.1-.9-2-2-2zm0 4l-8 5-8-5V6l8 5 8-5v2z"/>
                  </svg>
                </div>
              </div>

              <div v-if="status" class="mb-4 p-3 bg-green-100 border border-green-400 text-green-700 rounded-md">
                {{ status }}
              </div>

              <div class="flex items-center justify-between">
                <button
                  type="submit"
                  class="w-full bg-teal-600 hover:bg-teal-700 text-white font-medium py-2 px-4 rounded-md focus:outline-none focus:ring-2 focus:ring-teal-500 transition-colors"
                  :disabled="form.processing"
                >
                  <span v-if="form.processing">Sending...</span>
                  <span v-else>Email Password Reset Link</span>
                </button>
              </div>

             <!--  <div class="mb-4 text-center">
                        <button @click="switchToWelcome"
                            class="text-blue-600 hover:text-blue-800 text-sm font-medium transition-all duration-300 hover:bg-blue-50 px-3 py-2 rounded-md transform hover:scale-105">
                            ← Back to Home
                        </button>
                    </div> -->
              <div class="mt-4 text-center">
                <button @click="$inertia.visit(route('login'))" class="text-blue-600 hover:text-blue-800 text-sm font-medium transition-all duration-300 hover:bg-blue-50 px-3 py-2 rounded-md transform hover:scale-105">
                  ← Back to Login
                </button>
              </div>
            </form>
          </div>
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
