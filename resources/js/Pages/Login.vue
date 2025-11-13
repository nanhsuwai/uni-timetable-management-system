<script setup>
import { Head } from '@inertiajs/vue3';
import Login from '@/Pages/Auth/Login.vue';
</script>

<template>

    <Head title="University Timetable Management System" />
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
    <div class="relative min-h-screen flex flex-col">
        <!-- Header -->
        <header class="bg-sky-700 border-b border-cyan-500 shadow-xl transition-all duration-300 z-20">
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
        <main class="relative flex-1 flex items-center justify-center">
            <!-- Background Image -->
            <div class="absolute inset-0">
                <img src="/images/background2.jpg" alt="Background" class="w-full h-full object-cover" />
                <div class="absolute inset-0 bg-black/30"></div> <!-- Optional overlay for readability -->
            </div>

            <!-- Login Form -->
            <div class="relative z-10 w-full max-w-md">
                <Login />
            </div>
        </main>

        <!-- Footer -->
        <footer class="bg-sky-800 text-gray-50 border-t border-gray-700 z-20">
            <div class="text-center text-sm text-gray-50 py-3">
                © 2025 University of Computer Studies, Hinthada. All rights reserved.
            </div>
        </footer>
    </div>
</template>
