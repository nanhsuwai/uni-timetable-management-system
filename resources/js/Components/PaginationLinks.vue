<script setup>
    import { Link } from '@inertiajs/vue3';
    defineProps({links: Array})
</script>

<template>
    <div class="flex justify-center items-center py-6 text-xs">
        <template v-for="(link, index) in links">
          <span v-if="link.url === null" v-html="link.label" :class="link.url ? '': 'text-gray-500 px-2'" :key="index"></span>
            <Link
                preserve-scroll
                v-else
                :key="link.label"
                :href="link.url"
                v-html="link.label"
                class="px-4 py-1 border"
                :class="{'test-gray-500':! link.url, 'font-bold bg-gray-700 text-white' : link.active}"

            />

        </template>
    </div>
</template>
