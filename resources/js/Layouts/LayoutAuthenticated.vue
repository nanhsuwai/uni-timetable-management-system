<script setup>
import { mdiForwardburger, mdiBackburger, mdiMenu } from "@mdi/js";
import { onMounted, ref } from "vue";
import { router, usePage } from "@inertiajs/vue3";
// import { useRouter } from "vue-router";
import menuAside from "@/menuAside.js";
import menuNavBar from "@/menuNavBar.js";
import { useMainStore } from "@/Stores/main.js";
import { useStyleStore } from "@/Stores/style.js";
import BaseIcon from "@/Components/BaseIcon.vue";
import FormControl from "@/Components/FormControl.vue";
import NavBar from "@/Components/NavBar.vue";
import NavBarItemPlain from "@/Components/NavBarItemPlain.vue";
import AsideMenu from "@/Components/AsideMenu.vue";
import FooterBar from "@/Components/FooterBar.vue";
import ToastList from "@/Components/ToastList.vue";

useMainStore().setUser({
  name: "Admin user",
  email: "admin@gov.com",
  avatar:
    "https://avatars.dicebear.com/api/avataaars/example.svg?options[top][]=shortHair&options[accessoriesChance]=93",
});
const layoutAsidePadding = "xl:pl-60";
const styleStore = useStyleStore();

// const router = useRouter();

const isAsideMobileExpanded = ref(false);
const isAsideLgActive = ref(false);

router.on("navigate", () => {
  isAsideMobileExpanded.value = false;
  isAsideLgActive.value = false;
});

// router.beforeEach(() => {
//   isAsideMobileExpanded.value = false;
//   isAsideLgActive.value = false;
// });

const menuClick = (event, item) => {
  if (item.isToggleLightDark) {
    styleStore.setDarkMode();
  }

  if (item.isLogout) {
    // Add:
    router.post(route("logout"));
  }
};
let menuData = ref();

onMounted(() => {
  let permissions = usePage().props.auth.user.permissions;
  let menus = menuAside;

  menus.map((menu) => {
    let result = permissions.filter(
      (permission) => permission.code == menu.permission_code
    );
    if (result == false && usePage().props.auth.user.user_type != "admin") {
      menu.show = false;
    }
  });

  menuData.value = menus;
});
</script>

<template>
  <ToastList />
  <div
    :class="{
      dark: styleStore.darkMode,
      'overflow-hidden lg:overflow-visible': isAsideMobileExpanded,
    }"
  >
    <div
      :class="[
        layoutAsidePadding,
        { 'ml-60 lg:ml-0': isAsideMobileExpanded },
      ]"
      class="pt-14 min-h-screen w-screen transition-position lg:w-auto bg-gray-200 dark:bg-slate-800 dark:text-slate-100"
    >
      <NavBar
        :menu="menuNavBar"
        :class="[
          layoutAsidePadding,
          { 'ml-60 lg:ml-0': isAsideMobileExpanded },
        ]"
        @menu-click="menuClick"
      >
        <NavBarItemPlain
          display="flex lg:hidden"
          @click.prevent="isAsideMobileExpanded = !isAsideMobileExpanded"
        >
          <BaseIcon
            :path="isAsideMobileExpanded ? mdiBackburger : mdiForwardburger"
            size="24"
          />
        </NavBarItemPlain>
        <NavBarItemPlain
          display="hidden lg:flex xl:hidden"
          @click.prevent="isAsideLgActive = true"
        >
          <BaseIcon :path="mdiMenu" size="24" />
        </NavBarItemPlain>
        <NavBarItemPlain use-margin>
          <!-- <FormControl
            placeholder="Search (ctrl+k)"
            ctrl-k-focus
            transparent
            borderless
          /> -->
        </NavBarItemPlain>
      </NavBar>
      <AsideMenu
        :is-aside-mobile-expanded="isAsideMobileExpanded"
        :is-aside-lg-active="isAsideLgActive"
        :menu="menuData"
        @menu-click="menuClick"
        @aside-lg-close-click="isAsideLgActive = false"
      />
      <slot />
  
    </div>
  </div>
</template>
