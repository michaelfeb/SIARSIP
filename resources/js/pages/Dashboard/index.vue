<script setup lang="ts">
import BarChart from '@/components/ui/chart/BarChart.vue';
import PieChart from '@/components/ui/chart/PieChart.vue';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, router } from '@inertiajs/vue3';
import { CheckCircle, Eye, XCircle } from 'lucide-vue-next';
import { computed, ref } from 'vue';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dashboard',
        href: '/dashboard',
    },
];

const props = defineProps<{
    summary: {
        menunggu: number;
        diterima: number;
        ditolak: number;
    };
    berkasPerMonth: { bulan: string; total: number }[];
    prodiThisMonth: { label: string; total: number }[];
    selectedMonth: number;
    selectedYear: number;
}>();

const selectedYear = ref(props.selectedYear);
const selectedMonth = ref(props.selectedMonth);

function onDashboardFilterChange() {
    router.get(route('dashboard'), {
        year: selectedYear.value,
        month: selectedMonth.value,
    }, {
        preserveState: true,
        preserveScroll: true,
    });
}

const barChartData = computed(() => ({
    labels: props.berkasPerMonth.map((item) => item.bulan),
    datasets: [
        {
            label: 'Jumlah Berkas',
            data: props.berkasPerMonth.map((item) => item.total),
            backgroundColor: '#c2410c',
        },
    ],
}));

const pieChartData = computed(() => ({
    labels: props.prodiThisMonth.map((item) => item.label),
    datasets: [
        {
            label: 'Distribusi Prodi',
            data: props.prodiThisMonth.map((item) => item.total),
            backgroundColor: [
                '#f87171',
                '#fb923c',
                '#facc15',
                '#4ade80',
                '#60a5fa',
                '#a78bfa',
                '#f472b6',
                '#94a3b8',
            ],
        },
    ],
}));

const barChartOptions = {
    responsive: true,
    maintainAspectRatio: false,
    plugins: {
        legend: { display: false },
        title: { display: false },
    },
    scales: {
        y: {
            beginAtZero: true,
            ticks: {
                precision: 0,
            },
        },
    },
};

const pieChartOptions = {
    responsive: true,
    plugins: {
        legend: {
            position: 'bottom',
        },
    },
};
</script>

<template>
    <Head title="Dashboard" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="space-y-6 p-4">
            <div>
                <h1 class="text-2xl font-semibold text-gray-800">Berkas Sidang Nol</h1>
                <p class="mt-1 text-sm text-gray-500">Ringkasan permohonan Sidang Nol.</p>
            </div>

            <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
                <div class="flex items-center gap-4 rounded-xl border bg-white p-4 shadow">
                    <div class="rounded-xl bg-yellow-100 p-3">
                        <Eye class="size-6 text-yellow-500" />
                    </div>
                    <div>
                        <p class="text-sm text-gray-500">Menunggu</p>
                        <p class="text-xl font-semibold text-gray-800">{{ props.summary.menunggu.toLocaleString() }}</p>
                    </div>
                </div>

                <div class="flex items-center gap-4 rounded-xl border bg-white p-4 shadow">
                    <div class="rounded-xl bg-green-100 p-3">
                        <CheckCircle class="size-6 text-green-500" />
                    </div>
                    <div>
                        <p class="text-sm text-gray-500">Diterima</p>
                        <p class="text-xl font-semibold text-gray-800">{{ props.summary.diterima.toLocaleString() }}</p>
                    </div>
                </div>

                <div class="flex items-center gap-4 rounded-xl border bg-white p-4 shadow">
                    <div class="rounded-xl bg-red-100 p-3">
                        <XCircle class="size-6 text-red-500" />
                    </div>
                    <div>
                        <p class="text-sm text-gray-500">Ditolak</p>
                        <p class="text-xl font-semibold text-gray-800">{{ props.summary.ditolak.toLocaleString() }}</p>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 gap-4 lg:grid-cols-10">
                <section class="rounded-lg bg-white p-4 shadow lg:col-span-7">
                    <div class="mb-2 flex items-center justify-between gap-4">
                        <h2 class="font-semibold">Berkas Sidang Nol per Bulan</h2>
                        <select
                            v-model="selectedYear"
                            class="rounded-lg border border-gray-300 px-3 py-2 text-sm"
                            @change="onDashboardFilterChange"
                        >
                            <option v-for="year in [2026, 2025, 2024, 2023]" :key="year" :value="year">
                                {{ year }}
                            </option>
                        </select>
                    </div>
                    <div class="flex h-60 items-center justify-center">
                        <BarChart :chart-data="barChartData" :chart-options="barChartOptions" />
                    </div>
                </section>

                <section class="rounded-lg bg-white p-4 shadow lg:col-span-3">
                    <div class="mb-2 flex items-center justify-between gap-4">
                        <h2 class="font-semibold">Distribusi Program Studi</h2>
                        <select
                            v-model="selectedMonth"
                            class="rounded-lg border border-gray-300 px-3 py-2 text-sm"
                            @change="onDashboardFilterChange"
                        >
                            <option v-for="month in 12" :key="month" :value="month">
                                {{ new Date(0, month - 1).toLocaleString('id-ID', { month: 'long' }) }}
                            </option>
                        </select>
                    </div>
                    <div class="flex h-60 items-center justify-center">
                        <template v-if="pieChartData.datasets[0].data.length > 0">
                            <PieChart :chart-data="pieChartData" :chart-options="pieChartOptions" />
                        </template>
                        <p v-else class="text-sm text-gray-500">Data tidak tersedia untuk bulan ini.</p>
                    </div>
                </section>
            </div>
        </div>
    </AppLayout>
</template>
