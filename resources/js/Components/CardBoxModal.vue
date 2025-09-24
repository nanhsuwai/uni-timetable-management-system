<script setup>
import { computed } from "vue";
import { mdiClose } from "@mdi/js";
import BaseButton from "@/Components/BaseButton.vue";
import BaseButtons from "@/Components/BaseButtons.vue";
import CardBox from "@/Components/CardBox.vue";
import OverlayLayer from "@/Components/OverlayLayer.vue";
import CardBoxComponentTitle from "@/Components/CardBoxComponentTitle.vue";

const props = defineProps({
  title: {
    type: String,
    required: true,
  },
  button: {
    type: String,
    default: "info",
  },
  buttonLabel: {
    type: String,
    default: "",
  },
  hasCancel: Boolean,
  // hasExit: Boolean,
  hasExit: {
    type: Boolean,
    default: true
  },
  modelValue: {
    type: [String, Number, Boolean],
    default: null,
  },
  width: String,
});

const emit = defineEmits(["update:modelValue", "cancel", "confirm"]);

const value = computed({
  get: () => props.modelValue,
  set: (value) => emit("update:modelValue", value),
});

const confirmCancel = (mode) => {
  value.value = false;
  emit(mode);
};

const confirm = () => confirmCancel("confirm");

const cancel = () => confirmCancel("cancel");

window.addEventListener("keydown", (e) => {
  if (e.key === "Escape" && value.value) {
    cancel();
  }
});
</script>

<template>
  <!-- w-10/12 md:w-5/5 lg:w-4/5 xl:w-10/12 -->
  <OverlayLayer v-show="value" @overlay-click="cancel">
    <CardBox
      v-show="value"
      class="shadow-lg h-auto w-auto z-50 overflow-y-auto"
      :class="width"
      is-modal
    >
      <CardBoxComponentTitle :title="title">
        <BaseButton
          v-if=true
          :icon="mdiClose"
          color="whiteDark"
          small
          rounded-full
          @click.prevent="cancel"
        />
      </CardBoxComponentTitle>

      <div class="space-y-3">
        <slot />
      </div>

      <template #footer>
        <BaseButtons>
          <BaseButton
          v-if="buttonLabel"
          :label="buttonLabel" :color="button" @click="confirm" />
          <BaseButton
            v-if="hasCancel"
            label="Cancel"
            :color="button"
            outline
            @click="cancel"
          />
        </BaseButtons>
      </template>
    </CardBox>
  </OverlayLayer>
</template>
