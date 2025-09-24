<script setup>
import { ref, watch, computed, onMounted } from "vue";
import {
  Chart,
  registerables
} from "chart.js";
import ChartDataLabels from 'chartjs-plugin-datalabels';

const props = defineProps({
  data: {
    type: Object,
    required: true,
  },
  type: {
    type: String
  }
});
const root = ref(null);

let chart;

Chart.register(...registerables);

onMounted(() => {
  chart = new Chart(root.value, {
    type: props.type || 'pie',
    data: props.data,
    plugins: [ChartDataLabels],
    options: {
      responsive: true,
      maintainAspectRatio: false,
      plugins: {
        legend: {
          display: true,
          position: 'bottom'
        },
        datalabels: {
          color: "#ffffff",
          font: {
            weight: 'bold',
            size: 12
          },
          formatter: (value, context) => {
            const total = context.dataset.data.reduce((a, b) => a + b, 0);
            const percentage = ((value / total) * 100).toFixed(1) + '%';
            return percentage;
          }
        }
      },
    },
  });
});

const chartData = computed(() => props.data);

watch(chartData, (data) => {
  if (chart) {
    chart.data = data;
    chart.update();
  }
});
</script>

<template>
  <canvas ref="root" />
</template>
