<script setup lang="ts">
import {
  Chart as ChartJS,
  ArcElement,
  Tooltip,
  Legend,
  Title
} from 'chart.js'
import { Pie } from 'vue-chartjs'

ChartJS.register(
  ArcElement,
  Tooltip,
  Legend,
  Title
)

defineProps({
  chartData: Object,
  chartOptions: Object
})
</script>

<template>
  <Pie :data="chartData" :options="chartOptions" />
</template>