<script setup>
import { mdiCog } from "@mdi/js";
import { useSlots, computed } from "vue";
import BaseIcon from "@/Components/BaseIcon.vue";
import BaseButton from "@/Components/BaseButton.vue";
import IconRounded from "@/Components/IconRounded.vue";

defineProps({
  icon: {
    type: String,
    default: null,
  },
  title: {
    type: String,
    required: true,
  },
  total: {
    type: Number,
    default: 0
  },
  main: true,
});

const hasSlot = computed(() => useSlots().default);
</script>

<template>
  <section
    :class="{ 'pt-6': !main }"
    class="mb-6 flex items-center justify-between"
  >
    <div class="flex items-center justify-start">
      <IconRounded
        v-if="icon && main"
        :icon="icon"
        color="light"
        class="mr-3"
        bg
      />
      <BaseIcon v-else-if="icon" :path="icon" class="mr-2" size="20" />
      <h1 :class="main == true ? 'text-xl' : 'text-lg'" class="semi-bold" >
        {{ title }}
      </h1>
    </div>
    <slot v-if="hasSlot" />
    <!-- <BaseButton v-else :icon="mdiCog" color="whiteDark" /> -->
  </section>
</template>
