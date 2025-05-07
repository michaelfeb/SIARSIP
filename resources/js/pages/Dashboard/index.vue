<script setup lang="ts">
import Heading from '@/components/Heading.vue';
import BarChart from '@/components/ui/chart/BarChart.vue';
import PieChart from '@/components/ui/chart/PieChart.vue';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, router } from '@inertiajs/vue3';
import { Eye, CheckCircle, XCircle } from 'lucide-vue-next'
import { computed, ref } from 'vue';


const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dashboard',
        href: '/dashboard',
    },
];

const props = defineProps<{
    summary: any,
    berkasPerMonth: { bulan: string, total: number }[],
    prodiThisMonth: { label: string, total: number }[],
    selectedMonth: number,
    selectedYear: number,
}>()

const selectedYear = ref(props.selectedYear)
const selectedMonth = ref(props.selectedMonth);

const reloadData = () => {
    router.get(route('dashboard'), {
        year: selectedYear.value,
        month: selectedMonth.value,
    }, {
        preserveState: true,
        preserveScroll: true,
    })
}


const barChartData = computed(() => ({
    labels: props.berkasPerMonth.map(item => item.bulan),
    datasets: [
        {
            label: 'Jumlah Berkas',
            data: props.berkasPerMonth.map(item => item.total),
            backgroundColor: '#3b82f6',
        },
    ],
}));


const pieChartData = computed(() => ({
    labels: props.prodiThisMonth.map(item => item.label),
    datasets: [
        {
            label: 'Distribusi Prodi',
            data: props.prodiThisMonth.map(item => item.total),
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
        }
    ]
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
            min: 0,
            max: 50,
            ticks: {
                stepSize: 10,
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
        }
    }
};
</script>

<template>

    <Head title="Dashboard" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="p-4 space-y-4">
            <div class="container mx-auto flex items-center justify-between mb-4">
                <Heading title="Dashboard Sidang Nol" class="!mb-0" />
            </div>

            <!-- Statistik: 3 Kolom -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div class="flex items-center gap-4 rounded-xl bg-white p-4 shadow border">
                    <div class="rounded-xl bg-yellow-100 p-3">
                        <Eye class="w-6 h-6 text-yellow-500" />
                    </div>
                    <div>
                        <p class="text-gray-500 text-sm">Menunggu</p>
                        <p class="text-xl font-semibold text-gray-800">{{ props.summary.menunggu.toLocaleString() }}</p>
                    </div>
                </div>

                <div class="flex items-center gap-4 rounded-xl bg-white p-4 shadow border">
                    <div class="rounded-xl bg-green-100 p-3">
                        <CheckCircle class="w-6 h-6 text-green-500" />
                    </div>
                    <div>
                        <p class="text-gray-500 text-sm">Diterima</p>
                        <p class="text-xl font-semibold text-gray-800">{{ props.summary.diterima.toLocaleString() }}</p>
                    </div>
                </div>

                <div class="flex items-center gap-4 rounded-xl bg-white p-4 shadow border">
                    <div class="rounded-xl bg-red-100 p-3">
                        <XCircle class="w-6 h-6 text-red-500" />
                    </div>
                    <div>
                        <p class="text-gray-500 text-sm">Ditolak</p>
                        <p class="text-xl font-semibold text-gray-800">{{ props.summary.ditolak.toLocaleString() }}</p>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-10 gap-4 mt-6">
                <div class="lg:col-span-7 p-4 bg-white rounded shadow">
                    <div class="flex items-center justify-between mb-2">
                        <h2 class="font-semibold">Berkas Sidang Nol per Bulan</h2>
                        <div class="relative">
                            <select v-model="selectedYear" @change="reloadData"
                                class="text-sm font-medium w-full rounded border border-gray-300 px-3 py-2 pr-10 focus:outline-none focus:ring-2 focus:ring-blue-500 appearance-none">
                                <option v-for="year in [2025, 2024, 2023]" :key="year" :value="year">{{ year }}</option>
                            </select>
                            <div
                                class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-3 text-gray-500">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
                                </svg>
                            </div>
                        </div>
                    </div>
                    <div class="h-60 flex items-center justify-center">
                        <BarChart :chartData="barChartData" :chartOptions="barChartOptions" />
                    </div>
                </div>

                <div class="lg:col-span-3 p-4 bg-white rounded shadow">
                    <div class="flex items-center justify-between mb-2 gap-5">
                        <h2 class="font-semibold">Distribusi Program Studi</h2>
                        <div class="relative">
                            <select v-model="selectedMonth" @change="reloadData"
                                class="text-sm font-medium w-full rounded border border-gray-300 px-3 py-2 pr-10 focus:outline-none focus:ring-2 focus:ring-blue-500 appearance-none">
                                <option v-for="m in 12" :key="m" :value="m">
                                    {{ new Date(0, m - 1).toLocaleString('default', { month: 'long' }) }}
                                </option>
                            </select>
                            <div
                                class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-3 text-gray-500">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
                                </svg>
                            </div>
                        </div>
                    </div>
                    <div class="h-60 flex items-center justify-center">
                        <template v-if="pieChartData.datasets[0].data.length > 0">
                            <PieChart :chartData="pieChartData" :chartOptions="pieChartOptions" />
                        </template>
                        <template v-else>
                            <p class="text-gray-500 text-sm">Data tidak tersedia untuk bulan ini.</p>
                        </template>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
