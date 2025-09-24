<script setup>
import { ref, computed, onMounted } from "vue";
import { Link, usePage } from "@inertiajs/vue3";
import { useStyleStore } from "@/Stores/style.js";
import { mdiMinus, mdiPlus } from "@mdi/js";
import { getButtonColor } from "@/colors.js";
import BaseIcon from "@/Components/BaseIcon.vue";
import AsideMenuList from "@/Components/AsideMenuList.vue";
import _ from 'lodash'


// Add itemHref
const itemHref = computed(() =>
  props.item.route ? route(props.item.route) : props.item.href
);

// Add activeInactiveStyle
const activeInactiveStyle = computed(() =>
  props.item.route && route().current(props.item.route)
    ? styleStore.asideMenuItemActiveStyle
    : ""
);
const props = defineProps({
  item: {
    type: Object,
    required: true,
  },
  isDropdownList: Boolean,
});

const emit = defineEmits(["menu-click"]);

const styleStore = useStyleStore();

const hasColor = computed(() => props.item && props.item.color);

const asideMenuItemActiveStyle = computed(() =>
  hasColor.value ? "" : styleStore.asideMenuItemActiveStyle
);

const isDropdownActive = ref(false);

const componentClass = computed(() => [
  // props.item.show ? "hidden" : "",
  props.isDropdownList ? "py-3 px-0 text-sm pl-5 custom-dropdwon" : "py-3 px-2",
  hasColor.value
    ? getButtonColor(props.item.color, false, true)
    : `${styleStore.asideMenuItemStyle} dark:text-slate-300 dark:hover:text-white`,
]);

const hasDropdown = computed(() => !!props.item.menu);

const menuClick = (event) => {
  emit("menu-click", event, props.item);

  if (hasDropdown.value) {
    isDropdownActive.value = !isDropdownActive.value;
  }
};

</script>

<template>
  <li v-if="item.show">
    <component
      :is="item.route ? Link : 'a'"
      :href="itemHref"
      :target="item.target ?? null"
      class="flex cursor-pointer "
      :class="componentClass"
      @click="menuClick"
    >
      <BaseIcon
        v-if="item.icon"
        :path="item.icon"
        class="flex-none"
        :class="[{ 'pl-1': !hasDropdown }, activeInactiveStyle]"
        w="w-12"
        :size="20"
      />
      <span
        class="grow text-ellipsis line-clamp-2 text-xs pt-1 pl-3"
        :class="[{ 'pl-1 pt-1': !hasDropdown }, activeInactiveStyle]"
        >{{ item.label }}</span
      >
      <BaseIcon
        v-if="hasDropdown"
        :path="isDropdownActive ? mdiMinus : mdiPlus"
        class="flex-none"
        :class="[{ 'pl-5': !hasDropdown }, activeInactiveStyle]"
        w="w-12"
        :size="10"
      />
    </component>
    <AsideMenuList
      v-if="hasDropdown"
      :menu="item.menu"
      :class="[
        styleStore.asideMenuDropdownStyle,
        isDropdownActive ? 'block dark:bg-slate-800/50 pt-1' : 'hidden',
      ]"
      is-dropdown-list
    />
  </li>
</template>
