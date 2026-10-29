<script setup lang="ts">
import { formatThousandSeparatedNumber } from '@/utils/number';
import { ArcElement, Chart as ChartJS, Tooltip } from 'chart.js';
import { computed } from 'vue';
import { Doughnut } from 'vue-chartjs';
import { DASHBOARD_CHART_COLORS } from '../ts/dashboard.constants';
import type { DashboardProgramStudiDistribution } from '../ts/dashboard.types';

ChartJS.register(ArcElement, Tooltip);

const props = defineProps<{
    distribution: DashboardProgramStudiDistribution[];
    total: number;
}>();

const chartData = computed(() => ({
    labels: props.distribution.map((item) => item.label),
    datasets: [
        {
            data: props.distribution.map((item) => item.total),
            backgroundColor: props.distribution.map((_, index) => DASHBOARD_CHART_COLORS[index % DASHBOARD_CHART_COLORS.length]),
            borderWidth: 0,
            hoverOffset: 3,
        },
    ],
}));

const chartOptions = {
    responsive: true,
    maintainAspectRatio: false,
    cutout: '58%',
    plugins: {
        legend: { display: false },
        tooltip: {
            callbacks: {
                label: (context: { label: string; parsed: number }) => `${context.label}: ${formatThousandSeparatedNumber(context.parsed)}`,
            },
        },
    },
};
</script>

<template>
    <section class="border-border bg-card rounded-lg border p-5 shadow-sm">
        <h2 class="text-sm font-semibold text-[#c2410c]">Distribusi Per Prodi</h2>
        <p class="text-muted-foreground mt-1 text-xs">Surat berdasarkan program studi</p>

        <div v-if="distribution.length" class="mt-5 grid gap-6 md:grid-cols-[minmax(13rem,0.9fr)_minmax(0,1.1fr)] md:items-center">
            <div class="relative mx-auto h-52 w-full max-w-56">
                <Doughnut :data="chartData" :options="chartOptions" />
                <div class="pointer-events-none absolute inset-0 flex flex-col items-center justify-center">
                    <strong class="text-2xl font-bold tracking-tight">{{ formatThousandSeparatedNumber(total) }}</strong>
                    <span class="text-muted-foreground text-[10px]">Total Surat</span>
                </div>
            </div>

            <ul class="max-h-52 space-y-3 overflow-y-auto pr-2" aria-label="Distribusi surat per program studi">
                <li v-for="(item, index) in distribution" :key="item.label" class="text-muted-foreground flex items-center gap-2 text-xs">
                    <span
                        class="size-2.5 shrink-0 rounded-full"
                        :style="{ backgroundColor: DASHBOARD_CHART_COLORS[index % DASHBOARD_CHART_COLORS.length] }"
                    />
                    <span class="min-w-0 flex-1 truncate" :title="item.label">{{ item.label }}</span>
                    <span class="text-foreground font-medium">{{ formatThousandSeparatedNumber(item.total) }}</span>
                </li>
            </ul>
        </div>

        <div
            v-else
            class="border-border bg-muted/30 text-muted-foreground mt-5 flex h-52 items-center justify-center rounded-lg border border-dashed px-6 text-center text-sm"
            role="status"
        >
            Belum ada data surat untuk filter yang dipilih.
        </div>
    </section>
</template>
