<script>
import { h, defineComponent } from "vue";

export default defineComponent({
  name: "BaseButtons",
  props: {
    noWrap: Boolean,
    type: {
      type: String,
      default: "justify-start",
    },
    classAddon: {
      type: String,
      default: "mr-3 last:mr-0 mb-3",
    },
    mb: {
      type: String,
      default: "-mb-3",
    },
  },
  render() {
    const hasSlot = this.$slots && this.$slots.default;

    const parentClass = [
      "flex",
      "items-center",
      this.type,
      this.noWrap ? "flex-nowrap" : "flex-wrap",
    ];

    if (this.mb) {
      parentClass.push(this.mb);
    }

    if (!hasSlot) {
      return h("div", { class: parentClass }, null);
    }

    const slotContent = this.$slots.default();

    // Handle different slot content structures
    const processSlotContent = (content) => {
      if (!content) return null;

      // If content is an array, process each element
      if (Array.isArray(content)) {
        return content.map((element) => {
          if (!element) return null;

          // Handle VNode objects
          if (element.tag || element.type) {
            return h(element, { class: [this.classAddon] });
          }

          // Handle text nodes or other content
          return element;
        });
      }

      // If content is a single VNode
      if (content.tag || content.type) {
        return h(content, { class: [this.classAddon] });
      }

      return content;
    };

    return h(
      "div",
      { class: parentClass },
      processSlotContent(slotContent)
    );
  },
});
</script>
