<script setup>
import { ref, computed, onMounted } from "vue";
import { Link, usePage } from "@inertiajs/vue3";
import { useStyleStore } from "@/Stores/style.js";
// Changed mdiMinus/mdiPlus to mdiChevronDown for a modern dropdown look
import { mdiChevronDown, mdiChevronUp } from "@mdi/js"; 
import { getButtonColor } from "@/colors.js";
import BaseIcon from "@/Components/BaseIcon.vue";
import AsideMenuList from "@/Components/AsideMenuList.vue";
import _ from 'lodash'


const props = defineProps({
  item: {
    type: Object,
    required: true,
  },
  isDropdownList: Boolean,
});

const emit = defineEmits(["menu-click"]);

const styleStore = useStyleStore();

// --- Computed Properties ---

const itemHref = computed(() =>
  props.item.route ? route(props.item.route) : props.item.href
);

// Define active state for styling (Primary Color: Cyan)
const isActive = computed(() =>
  props.item.route && route().current(props.item.route)
);

const asideMenuItemActiveStyle = computed(() =>
  isActive.value 
    ? "font-semibold text-cyan-600 dark:text-cyan-400 bg-cyan-50 dark:bg-gray-800/50" // Active background and text color
    : "text-gray-600 dark:text-gray-300 hover:text-gray-900 dark:hover:text-white hover:bg-gray-50 dark:hover:bg-gray-800"
);

const hasColor = computed(() => props.item && props.item.color);

const isDropdownActive = ref(false);
const hasDropdown = computed(() => !!props.item.menu);

// Component class for the main menu item link
const componentClass = computed(() => [
  // Base padding and layout
  "flex items-center w-full transition-colors duration-200 ease-in-out",
  // Dropdown list styling (sub-menu items)
  props.isDropdownList 
    ? "pl-9 py-2 text-sm" // Increased padding for sub-items to create hierarchy
    : "pl-4 pr-3 py-2.5 rounded-lg my-0.5 mx-2", // Primary menu item styling
  // Apply specific color if defined in item (e.g., for logout)
  hasColor.value
    ? getButtonColor(props.item.color, false, true)
    : asideMenuItemActiveStyle.value, // Apply active/inactive styles
]);

// Class for the icon container
const iconClass = computed(() => [
  "flex-none",
  props.isDropdownList ? "w-6" : "w-6", // Consistent width
  isActive.value ? "text-cyan-600 dark:text-cyan-400" : "text-gray-400 dark:text-gray-500", // Icon color changes based on state
  "transition-colors duration-200"
]);

// Class for the text/label
const labelClass = computed(() => [
  "grow text-ellipsis whitespace-nowrap",
  "text-sm", // Standardized font size
  isActive.value ? "font-semibold" : "font-medium",
]);

// --- Methods ---

const menuClick = (event) => {
  emit("menu-click", event, props.item);

  if (hasDropdown.value) {
    isDropdownActive.value = !isDropdownActive.value;
  }
};

</script>

<template>
  <li v-if="item.show !== false" :class="{ 'is-active': isActive }">
    <component
      :is="item.route ? Link : 'a'"
      :href="itemHref"
      :target="item.target ?? null"
      :class="componentClass"
      @click="menuClick"
    >
      <BaseIcon
        v-if="item.icon"
        :path="item.icon"
        :class="iconClass"
        w="w-6"
        :size="18"
      />
      
      <span :class="labelClass" class="ml-3">{{ item.label }}</span>

      <BaseIcon
        v-if="hasDropdown"
        :path="isDropdownActive ? mdiChevronUp : mdiChevronDown"
        class="flex-none text-gray-400 dark:text-gray-500 transition-transform duration-300"
        w="w-6"
        :size="16"
      />
    </component>
    
    <AsideMenuList
      v-if="hasDropdown"
      :menu="item.menu"
      :class="[
        styleStore.asideMenuDropdownStyle,
        isDropdownActive ? 'block max-h-screen opacity-100' : 'hidden max-h-0 opacity-0',
        'overflow-hidden transition-[max-height,opacity] duration-300 ease-in-out',
        'border-l-2 border-cyan-100 dark:border-gray-800' // Visual hierarchy marker
      ]"
      is-dropdown-list
    />
  </li>
</template>