<script setup lang="ts">
import { Label } from '@/components/ui/label';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { DASHBOARD_LETTER_TYPES, DASHBOARD_MONTHS } from '../ts/dashboard.constants';
import type { DashboardFilters } from '../ts/dashboard.types';

const props = defineProps<{
    filters: DashboardFilters;
    years: number[];
}>();

const emit = defineEmits<{
    change: [filters: DashboardFilters];
}>();

function onLetterTypeChange(value: unknown): void {
    if (typeof value !== 'string') return;

    emit('change', { ...props.filters, letter_type: value as DashboardFilters['letter_type'] });
}

function onMonthChange(value: unknown): void {
    if (typeof value !== 'string') return;

    emit('change', { ...props.filters, month: value === 'all' ? null : Number(value) });
}

function onYearChange(value: unknown): void {
    if (typeof value !== 'string') return;

    emit('change', { ...props.filters, year: value === 'all' ? null : Number(value) });
}
</script>

<template>
    <section class="border-border bg-card overflow-hidden rounded-lg border shadow-sm">
        <div class="border-border border-b px-5 py-4">
            <h2 class="text-sm font-semibold text-[#c2410c]">Filter</h2>
        </div>

        <div class="space-y-4 p-5">
            <div class="space-y-2">
                <Label for="dashboard-letter-type" class="text-muted-foreground text-[10px] font-semibold tracking-wide uppercase">
                    Jenis Surat
                </Label>
                <Select :model-value="filters.letter_type" @update:model-value="onLetterTypeChange">
                    <SelectTrigger id="dashboard-letter-type" class="bg-background h-10">
                        <SelectValue placeholder="Semua Jenis" />
                    </SelectTrigger>
                    <SelectContent>
                        <SelectItem v-for="option in DASHBOARD_LETTER_TYPES" :key="option.value" :value="option.value">
                            {{ option.label }}
                        </SelectItem>
                    </SelectContent>
                </Select>
            </div>

            <div class="space-y-2">
                <Label for="dashboard-month" class="text-muted-foreground text-[10px] font-semibold tracking-wide uppercase"> Bulan </Label>
                <Select :model-value="filters.month === null ? 'all' : String(filters.month)" @update:model-value="onMonthChange">
                    <SelectTrigger id="dashboard-month" class="bg-background h-10">
                        <SelectValue placeholder="Semua Bulan" />
                    </SelectTrigger>
                    <SelectContent>
                        <SelectItem value="all">Semua Bulan</SelectItem>
                        <SelectItem v-for="option in DASHBOARD_MONTHS" :key="option.value" :value="String(option.value)">
                            {{ option.label }}
                        </SelectItem>
                    </SelectContent>
                </Select>
            </div>

            <div class="space-y-2">
                <Label for="dashboard-year" class="text-muted-foreground text-[10px] font-semibold tracking-wide uppercase"> Tahun </Label>
                <Select :model-value="filters.year === null ? 'all' : String(filters.year)" @update:model-value="onYearChange">
                    <SelectTrigger id="dashboard-year" class="bg-background h-10">
                        <SelectValue placeholder="Semua Tahun" />
                    </SelectTrigger>
                    <SelectContent>
                        <SelectItem value="all">Semua Tahun</SelectItem>
                        <SelectItem v-for="year in years" :key="year" :value="String(year)">
                            {{ year }}
                        </SelectItem>
                    </SelectContent>
                </Select>
            </div>
        </div>
    </section>
</template>
