<script setup lang="ts">
import Heading from '@/components/Heading.vue';
import BarChart from '@/components/ui/chart/BarChart.vue';
import PieChart from '@/components/ui/chart/PieChart.vue';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, router } from '@inertiajs/vue3';
import { Eye, CheckCircle, XCircle, Clock, Inbox } from 'lucide-vue-next'
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
    persuratan: {
        summary: {
            masuk: number,
            proses: number,
            selesai: number,
            ditolak: number,
        },
        berkasPerMonth: { bulan: string, total: number }[],
        prodiThisMonth: { label: string, total: number }[],
    }
    persuratanMonth: number,
    persuratanYear: number,
    auth: any,
}>()

const roleId = props.auth?.user.role_id;

const activeDashboard = ref<'sidang' | 'persuratan'>(roleId === 9 ? 'persuratan' : 'sidang')

const selectedYear = ref(props.selectedYear)
const selectedMonth = ref(props.selectedMonth);

const selectedMonthPersurataan = ref(props.persuratanMonth)
const selectedYearPersurataan = ref(props.persuratanYear)

const reloadData = () => {
    router.get(route('dashboard'), {
        year: selectedYear.value,
        month: selectedMonth.value,
        persuratan_year: selectedYearPersurataan.value,
        persuratan_month: selectedMonthPersurataan.value,
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

const barChartDataPersuratan = computed(() => ({
    labels: props.persuratan.berkasPerMonth.map(item => item.bulan),
    datasets: [
        {
            label: 'Jumlah Berkas',
            data: props.persuratan.berkasPerMonth.map(item => item.total),
            backgroundColor: '#f97316',
        },
    ],
}));

const pieChartDataPersuratan = computed(() => ({
    labels: props.persuratan.prodiThisMonth.map(item => item.label),
    datasets: [
        {
            label: 'Distribusi Prodi',
            data: props.persuratan.prodiThisMonth.map(item => item.total),
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
                <div class="flex items-center gap-4">
                    <h1 class="text-2xl font-semibold text-gray-800">
                        {{ activeDashboard === 'sidang' ? 'Berkas Sidang Nol' : 'Berkas Persuratan' }}
                    </h1>

                    <span v-if="roleId !== 9"
                        class="cursor-pointer text-sm font-medium text-blue-600 hover:underline transition"
                        @click="activeDashboard = activeDashboard === 'sidang' ? 'persuratan' : 'sidang'">
                        {{ activeDashboard === 'sidang' ? 'Lihat Berkas Persuratan' : 'Lihat Berkas Sidang Nol' }}
                    </span>
                </div>
            </div>


            <!-- Statistik: 3 Kolom -->
            <div v-if="activeDashboard === 'sidang'">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div class="flex items-center gap-4 rounded-xl bg-white p-4 shadow border">
                        <div class="rounded-xl bg-yellow-100 p-3">
                            <Eye class="w-6 h-6 text-yellow-500" />
                        </div>
                        <div>
                            <p class="text-gray-500 text-sm">Menunggu</p>
                            <p class="text-xl font-semibold text-gray-800">{{ props.summary.menunggu.toLocaleString() }}
                            </p>
                        </div>
                    </div>

                    <div class="flex items-center gap-4 rounded-xl bg-white p-4 shadow border">
                        <div class="rounded-xl bg-green-100 p-3">
                            <CheckCircle class="w-6 h-6 text-green-500" />
                        </div>
                        <div>
                            <p class="text-gray-500 text-sm">Diterima</p>
                            <p class="text-xl font-semibold text-gray-800">{{ props.summary.diterima.toLocaleString() }}
                            </p>
                        </div>
                    </div>

                    <div class="flex items-center gap-4 rounded-xl bg-white p-4 shadow border">
                        <div class="rounded-xl bg-red-100 p-3">
                            <XCircle class="w-6 h-6 text-red-500" />
                        </div>
                        <div>
                            <p class="text-gray-500 text-sm">Ditolak</p>
                            <p class="text-xl font-semibold text-gray-800">{{ props.summary.ditolak.toLocaleString() }}
                            </p>
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
                                    <option v-for="year in [2025, 2024, 2023]" :key="year" :value="year">{{ year }}
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

            <div v-else>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div class="flex items-center gap-4 rounded-xl bg-white p-4 shadow border">
                        <div class="rounded-xl bg-yellow-100 p-3">
                            <Inbox class="w-6 h-6 text-yellow-500" />
                        </div>
                        <div>
                            <p class="text-gray-500 text-sm">Masuk</p>
                            <p class="text-xl font-semibold text-gray-800">{{
                                props.persuratan.summary.masuk.toLocaleString() }}</p>
                        </div>
                    </div>

                    <div class="flex items-center gap-4 rounded-xl bg-white p-4 shadow border">
                        <div class="rounded-xl bg-blue-100 p-3">
                            <Clock class="w-6 h-6 text-blue-500" />
                        </div>
                        <div>
                            <p class="text-gray-500 text-sm">Diproses</p>
                            <p class="text-xl font-semibold text-gray-800">{{
                                props.persuratan.summary.proses.toLocaleString() }}</p>
                        </div>
                    </div>

                    <div class="flex items-center gap-4 rounded-xl bg-white p-4 shadow border">
                        <div class="rounded-xl bg-green-100 p-3">
                            <CheckCircle class="w-6 h-6 text-green-500" />
                        </div>
                        <div>
                            <p class="text-gray-500 text-sm">Selesai</p>
                            <p class="text-xl font-semibold text-gray-800">{{
                                props.persuratan.summary.selesai.toLocaleString() }}</p>
                        </div>
                    </div>

                    <div class="flex items-center gap-4 rounded-xl bg-white p-4 shadow border">
                        <div class="rounded-xl bg-red-100 p-3">
                            <XCircle class="w-6 h-6 text-red-500" />
                        </div>
                        <div>
                            <p class="text-gray-500 text-sm">Ditolak</p>
                            <p class="text-xl font-semibold text-gray-800">{{
                                props.persuratan.summary.ditolak.toLocaleString() }}</p>
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-10 gap-4 mt-6">
                    <!-- Bar Chart Persuratan -->
                    <div class="lg:col-span-7 p-4 bg-white rounded shadow">
                        <div class="flex items-center justify-between mb-2">
                            <h2 class="font-semibold">Berkas Persuratan per Bulan</h2>
                            <div class="relative">
                                <select v-model="selectedYearPersurataan" @change="reloadData"
                                    class="text-sm font-medium w-full rounded border border-gray-300 px-3 py-2 pr-10 focus:outline-none focus:ring-2 focus:ring-orange-500 appearance-none">
                                    <option v-for="year in [2025, 2024, 2023]" :key="year" :value="year">{{ year }}
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
                            <BarChart :chartData="barChartDataPersuratan" :chartOptions="barChartOptions" />
                        </div>
                    </div>

                    <!-- Pie Chart Persuratan -->
                    <div class="lg:col-span-3 p-4 bg-white rounded shadow">
                        <div class="flex items-center justify-between mb-2 gap-5">
                            <h2 class="font-semibold">Distribusi Program Studi</h2>
                            <div class="relative">
                                <select v-model="selectedMonthPersurataan" @change="reloadData"
                                    class="text-sm font-medium w-full rounded border border-gray-300 px-3 py-2 pr-10 focus:outline-none focus:ring-2 focus:ring-orange-500 appearance-none">
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
                            <template v-if="pieChartDataPersuratan.datasets[0].data.length > 0">
                                <PieChart :chartData="pieChartDataPersuratan" :chartOptions="pieChartOptions" />
                            </template>
                            <template v-else>
                                <p class="text-gray-500 text-sm">Data tidak tersedia untuk bulan ini.</p>
                            </template>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
