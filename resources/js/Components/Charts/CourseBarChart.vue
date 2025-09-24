<script setup>
import { ref, watch, computed, onMounted } from "vue";
import {
  Chart,
  registerables
} from "chart.js";


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
    type: props.type || 'bar',
    data: props.data,
    options: {
      responsive: true,
      maintainAspectRatio: false,
      scales: {
        y: {
          display: true,
          beginAtZero: true,
          ticks: {
            precision: 0
          }
        },
        x: {
          display: true,
        },
      },
      plugins: {
        legend: {
          display: true,
          position: 'top',
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
