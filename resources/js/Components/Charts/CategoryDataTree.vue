<script setup>
import { onMounted } from "vue";
import anychart from 'anychart';

const props = defineProps({
  cat_items: {
    type: Object,
    default: {}
  }
})
onMounted(() => {

    anychart.onDocumentReady(() => {
          var data = [
          {
              children: props.cat_items.map(item => ({ name: item.label, value: item.count })),
          }
      ];

      const chart = anychart.treeMap(data, "as-tree");
      chart.container('chart-container').draw();
      chart.sort();

    });
});

</script>

<template>
  <div id="chart-container"></div>
</template>
