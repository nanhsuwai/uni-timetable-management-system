<script setup>
import AsideMenuItem from "@/Components/AsideMenuItem.vue";
import { usePage } from "@inertiajs/vue3";
import { computed } from "@vue/reactivity";
import _ from 'lodash'
import { onMounted, ref } from "vue";

const props = defineProps({
  isDropdownList: Boolean,
  menu: {
    type: Array,
    required: true,
  },
});

const emit = defineEmits(["menu-click"]);

const menuClick = (event, item) => {
  emit("menu-click", event, item);
};



// onMounted( () => {

//   let permissions = usePage().props.auth.user.permissions;
//   let menus = props.menu;

//   menus.map(menu => {

//    let result = permissions.filter( permission => permission.code == menu.permission_code)
//    console.log('result', result)
//    if(result == false) {
//     menu.show = false
//    }
//   });
// })

</script>

<template>
  <ul>
    <AsideMenuItem
      v-for="(item, index) in menu"
      :key="index"
      :item="item"
      :is-dropdown-list="isDropdownList"
      @menu-click="menuClick"
    />
  </ul>
</template>
