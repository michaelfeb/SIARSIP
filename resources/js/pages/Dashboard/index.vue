<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, router } from '@inertiajs/vue3';
import { Archive, Clock3, FileText, OctagonAlert } from 'lucide-vue-next';
import { computed, ref, watch } from 'vue';
import DashboardDistribution from './components/DashboardDistribution.vue';
import DashboardFilters from './components/DashboardFilters.vue';
import DashboardSummaryCard from './components/DashboardSummaryCard.vue';
import type {
    DashboardFilterOptions,
    DashboardFilters as DashboardFiltersType,
    DashboardProgramStudiDistribution,
    DashboardSummary,
} from './ts/dashboard.types';

const breadcrumbs: BreadcrumbItem[] = [{ title: 'Dashboard', href: '/dashboard' }];

const props = defineProps<{
    filters: DashboardFiltersType;
    filterOptions: DashboardFilterOptions;
    summary: DashboardSummary;
    programStudiDistribution: DashboardProgramStudiDistribution[];
}>();

const selectedFilters = ref<DashboardFiltersType>({ ...props.filters });

watch(
    () => props.filters,
    (filters) => {
        selectedFilters.value = { ...filters };
    },
    { deep: true },
);

const summaryCards = computed(() => [
    {
        title: 'Total Surat',
        value: props.summary.total,
        description: 'Semua jenis surat',
        icon: FileText,
        tone: 'default' as const,
    },
    {
        title: 'Dalam Proses',
        value: props.summary.inProgress,
        description: 'Menunggu persetujuan',
        icon: Clock3,
        tone: 'progress' as const,
    },
    {
        title: 'Diarsipkan',
        value: props.summary.archived,
        description: 'Approval selesai',
        icon: Archive,
        tone: 'archived' as const,
    },
    {
        title: 'Ditolak',
        value: props.summary.rejected,
        description: 'Perlu revisi',
        icon: OctagonAlert,
        tone: 'rejected' as const,
    },
]);

function onFilterChange(filters: DashboardFiltersType): void {
    selectedFilters.value = filters;

    router.get(
        route('dashboard'),
        {
            letter_type: filters.letter_type === 'all' ? undefined : filters.letter_type,
            month: filters.month ?? undefined,
            year: filters.year ?? undefined,
        },
        {
            preserveScroll: true,
            preserveState: true,
            replace: true,
        },
    );
}
</script>

<template>
    <Head title="Dashboard" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <main class="space-y-8 p-4 sm:p-6">
            <header>
                <h1 class="text-foreground text-xl font-semibold tracking-tight">Dashboard</h1>
                <p class="text-muted-foreground mt-1 text-sm">Ringkasan data surat dan persetujuan</p>
            </header>

            <div class="grid gap-6 xl:grid-cols-2">
                <DashboardFilters :filters="selectedFilters" :years="filterOptions.years" @change="onFilterChange" />
                <DashboardDistribution :distribution="programStudiDistribution" :total="summary.total" />
            </div>

            <section class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4" aria-label="Ringkasan status surat">
                <DashboardSummaryCard v-for="card in summaryCards" :key="card.title" v-bind="card" />
            </section>
        </main>
    </AppLayout>
</template>
