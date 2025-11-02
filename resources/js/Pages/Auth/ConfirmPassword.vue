<script setup>
import { useForm, Head } from '@inertiajs/vue3'
import { ref } from 'vue'

const form = useForm({
  password: ''
})

const passwordInput = ref(null)

const submit = () => {
  form.post(route('password.store'), {
    onFinish: () => {
      form.reset()

      passwordInput.value?.focus()
    }
  })
}
</script>

<template>
  <Head title="Confirm Password" />

  <!-- Header -->
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
      <div class="absolute inset-0 bg-transparent"></div>
    </div>
    <div class="relative z-40 max-w-7xl mx-auto px-3 sm:px-4 lg:px-6 xl:px-8 py-6 sm:py-8 lg:py-12">
      <div class="flex items-center justify-center min-h-[60vh]">
        <div class="w-full max-w-md">
          <div class="bg-white/90 dark:bg-gray-800/80 backdrop-blur-lg shadow-xl rounded-lg p-6">
            <div class="text-center mb-6">
              <h2 class="text-2xl font-bold text-gray-900 dark:text-white">Confirm Password</h2>
              <p class="text-gray-600 dark:text-gray-400 mt-2">
                This is a secure area of the application. Please confirm your password before continuing.
              </p>
            </div>

            <form @submit.prevent="submit">
              <div class="mb-4">
                <label for="password" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                  Password
                </label>
                <div class="relative">
                  <input
                    ref="passwordInput"
                    v-model="form.password"
                    type="password"
                    id="password"
                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-teal-500 focus:border-teal-500 transition-colors"
                    placeholder="Enter your password"
                    required
                    autocomplete="current-password"
                  />
                  <svg class="absolute right-3 top-2.5 h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                  </svg>
                </div>
              </div>

              <div class="flex items-center justify-between">
                <button
                  type="submit"
                  class="w-full bg-teal-600 hover:bg-teal-700 text-white font-medium py-2 px-4 rounded-md focus:outline-none focus:ring-2 focus:ring-teal-500 transition-colors"
                  :disabled="form.processing"
                >
                  <span v-if="form.processing">Confirming...</span>
                  <span v-else>Confirm</span>
                </button>
              </div>
            </form>
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
