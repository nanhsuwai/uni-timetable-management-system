<script setup>
import { ref, computed, watch } from "vue";
import { useForm, router } from '@inertiajs/vue3';
import { Head } from '@inertiajs/vue3';

const props = defineProps({
  email: String,
  token: String,
});

const form = useForm({
  email: props.email || '',
  token: props.token || '',
  password: '',
  password_confirmation: '',
});

const showPassword = ref(false);
const showConfirmPassword = ref(false);
const toastMessages = ref([]);

// Toast helper
const addToast = (type, message) => {
  const id = Date.now();
  toastMessages.value.push({ id, type, message });
  setTimeout(() => {
    toastMessages.value = toastMessages.value.filter(t => t.id !== id);
  }, 4000);
};

// Password strength computation
const passwordStrength = computed(() => {
  const pass = form.password;
  if (pass.length === 0) return { score: 0, label: '', color: '' };

  let score = 0;
  if (pass.length >= 8) score++;
  if (/[A-Z]/.test(pass)) score++;
  if (/[0-9]/.test(pass)) score++;
  if (/[^A-Za-z0-9]/.test(pass)) score++;

  let label = '';
  let color = '';
  switch (score) {
    case 0: case 1:
      label = 'Weak'; color = 'bg-red-500'; break;
    case 2:
      label = 'Fair'; color = 'bg-yellow-500'; break;
    case 3:
      label = 'Good'; color = 'bg-blue-500'; break;
    case 4:
      label = 'Strong'; color = 'bg-green-500'; break;
  }
  return { score, label, color };
});

// Validate password before submit
const validatePassword = () => {
  if (form.password.length < 8) {
    addToast('error', '❌ Password must be at least 8 characters!');
    return false;
  }
  if (form.password !== form.password_confirmation) {
    addToast('error', '❌ Password and Confirm Password do not match!');
    return false;
  }
  return true;
};

const submit = () => {
  if (!validatePassword()) return;

  form.post(route('password.store'), {
    onSuccess: () => {
      addToast('success', '✅ Password reset successful! Redirecting to login...');
      setTimeout(() => {
        router.visit(route('login'));
      }, 1500);
    },
    onError: (errors) => {
      if (errors.email) addToast('error', errors.email[0]);
      else if (errors.password) addToast('error', errors.password[0]);
      else addToast('error', '❌ Failed to reset password. Please try again.');
    }
  });
};
</script>


<template>

  <Head title="Reset Password" />



  <!-- Header -->
  <header class="bg-sky-600 border-b border-cyan-500 shadow-xl">
    <div class="mx-auto px-4 flex items-center py-4 space-x-4">
      <img src="/images/logo.png" alt="UCSh Logo" class="w-20 h-14 object-contain" />
      <div class="min-w-0">
        <h1 class="text-2xl font-extrabold text-white truncate">
          University Timetable Management System
        </h1>
        <p class="text-sm text-cyan-300 truncate">
          ကွန်ပျူတာတက္ကသိုလ်(ဟင်္သာတ)
        </p>
      </div>
    </div>
  </header>

  <!-- Toast Notifications -->
  <div class="fixed top-5 right-5 z-50 flex flex-col gap-2">
    <div v-for="toast in toastMessages" :key="toast.id" :class="[
      'px-4 py-2 rounded shadow text-white font-medium',
      toast.type === 'success' ? 'bg-green-500' : 'bg-red-500'
    ]">
      {{ toast.message }}
    </div>
  </div>
  <!-- Main Form -->
  <main class="min-h-screen flex items-center justify-center bg-gray-50">
    <div class="w-full max-w-md p-6 bg-white/90 dark:bg-gray-800/80 backdrop-blur-lg shadow-xl rounded-lg">
      <h2 class="text-2xl font-bold text-center text-gray-900 dark:text-white mb-4">
        Reset Your Password
      </h2>
      <p class="text-center text-gray-600 dark:text-gray-400 mb-6">
        Enter your email, new password, and confirm password.
      </p>

      <form @submit.prevent="submit">
        <!-- Hidden Token -->
        <input type="hidden" v-model="form.token" />

        <!-- Email -->
        <div class="mb-4">
          <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Email</label>
          <input v-model="form.email" type="email"
            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-teal-500"
            placeholder="Enter your email" required autocomplete="email" readonly />
        </div>

        <!-- Password -->
        <div class="mb-4">
          <div class="relative">
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Password</label>
          <input v-model="form.password" :type="showPassword ? 'text' : 'password'"
            class="w-full px-3 py-2 pr-10 border border-gray-300 rounded-md focus:ring-2 focus:ring-teal-500"
            placeholder="Enter new password" required autocomplete="new-password" />
          <button type="button" @click="showPassword = !showPassword"
            class="absolute right-2 top-11 transform -translate-y-1/2 text-gray-500">
            <span v-if="!showPassword">👁️</span>
            <span v-else>🙈</span>
          </button>

          </div>
          
          <!-- Password Strength Indicator -->
          <div v-if="form.password.length > 0" class="mt-1 h-2 w-full rounded bg-gray-200">
            <div class="h-2 rounded transition-all duration-300" :class="passwordStrength.color"
              :style="{ width: `${passwordStrength.score * 25}%` }"></div>
          </div>
          <p v-if="form.password.length > 0" class="text-sm mt-1 text-gray-600 dark:text-gray-300">
            Strength: <span :class="passwordStrength.color">{{ passwordStrength.label }}</span>
          </p>
        </div>

        <!-- Confirm Password -->
        <div class="mb-4 relative">
          <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Confirm Password</label>
          <input v-model="form.password_confirmation" :type="showConfirmPassword ? 'text' : 'password'"
            class="w-full px-3 py-2 pr-10 border border-gray-300 rounded-md focus:ring-2 focus:ring-teal-500"
            placeholder="Confirm new password" required autocomplete="new-password" />
          <button type="button" @click="showConfirmPassword = !showConfirmPassword"
            class="absolute right-2 top-11 transform -translate-y-1/2 text-gray-500">
            <span v-if="!showConfirmPassword">👁️</span>
            <span v-else>🙈</span>
          </button>
        </div>

        <!-- Submit -->
        <button type="submit"
          class="w-full bg-teal-600 hover:bg-teal-700 text-white py-2 rounded-md focus:ring-2 focus:ring-teal-500"
          :disabled="form.processing">
          <span v-if="form.processing">Resetting...</span>
          <span v-else>Reset Password</span>
        </button>

        <div class="mt-4 text-center">
          <button @click="$inertia.visit(route('login'))" class="text-blue-600 hover:text-blue-800 text-sm">
            ← Back to Login
          </button>
        </div>
      </form>
    </div>
  </main>

  <footer class="bg-sky-600 text-gray-50 text-center py-3 border-t border-gray-700">
    © 2025 University of Computer Studies, Hinthada. All rights reserved.
  </footer>
</template>
