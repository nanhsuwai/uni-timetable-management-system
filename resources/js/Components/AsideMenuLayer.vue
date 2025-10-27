<script setup>
import { mdiLogout, mdiClose } from "@mdi/js";
import { computed } from "vue";
import { useStyleStore } from "@/Stores/style.js";
import AsideMenuList from "@/Components/AsideMenuList.vue";
import AsideMenuItem from "@/Components/AsideMenuItem.vue";
import BaseIcon from "@/Components/BaseIcon.vue";

defineProps({
  menu: {
    type: Array,
    required: true,
  },
});

const emit = defineEmits(["menu-click", "aside-lg-close-click"]);

const styleStore = useStyleStore();

const logoutItem = computed(() => ({
  label: "Logout",
  icon: mdiLogout,
  color: "info",
  isLogout: true,
}));

const menuClick = (event, item) => {
  emit("menu-click", event, item);
};

const asideLgCloseClick = (event) => {
  emit("aside-lg-close-click", event);
};
</script>

<template>
  <aside id="aside"
    class="md:pl-2 lg:py-2 lg:pl-2 w-60 fixed flex z-40 top-0 h-screen transition-position overflow-hidden">
    <div :class="styleStore.asideStyle"
      class="lg:rounded-xl flex-1 flex flex-col overflow-hidden 
             bg-white dark:bg-gray-900 
             shadow-xl dark:shadow-2xl dark:shadow-gray-950/50 
             border-r border-gray-100 dark:border-gray-800/70">

      <div :class="styleStore.asideBrandStyle"
        class="flex flex-row h-16 items-center justify-between 
               border-b border-gray-100 dark:border-gray-800 
               bg-white dark:bg-gray-900 px-4">
        
        <div class="flex items-center space-x-3 text-lg font-bold text-cyan-700 dark:text-white transition-colors duration-200">
          <img src="/images/logo.png" alt="University of Computer Studies, Hinthada Logo"
            class="w-10 h-10 object-contain rounded-full shadow-md" />
          <div class="whitespace-nowrap">
            Timetable App
          </div>
        </div>
        
        <button class="hidden lg:inline-block xl:hidden p-2 text-cyan-800 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-800 transition-colors"
          @click.prevent="asideLgCloseClick">
          <BaseIcon :path="mdiClose" class="w-6 h-6" />
        </button>
      </div>

      <div :class="styleStore.darkMode
          ? 'aside-scrollbars-[slate]'
          : styleStore.asideScrollbarsStyle
        " class="flex-1 overflow-y-auto overflow-x-hidden p-2">
        <AsideMenuList :menu="menu" @menu-click="menuClick" />
      </div>

      <ul class="border-t border-gray-100 dark:border-gray-800">
        <AsideMenuItem :item="logoutItem" @menu-click="menuClick" />
      </ul>
    </div>
  </aside>
</template>
