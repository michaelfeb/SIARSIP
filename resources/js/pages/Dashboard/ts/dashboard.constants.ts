import type { DashboardLetterType } from './dashboard.types';

export const DASHBOARD_LETTER_TYPES: { value: DashboardLetterType; label: string }[] = [
    { value: 'all', label: 'Semua Jenis' },
    { value: 'sidang-nol', label: 'Berkas Sidang Nol' },
    { value: 'cuti-akademik', label: 'Cuti Akademik' },
];

export const DASHBOARD_MONTHS = Array.from({ length: 12 }, (_, index) => ({
    value: index + 1,
    label: new Intl.DateTimeFormat('id-ID', { month: 'long' }).format(new Date(2026, index, 1)),
}));

export const DASHBOARD_CHART_COLORS = ['#c2410c', '#ea580c', '#f97316', '#fb923c', '#9a3412', '#f59e0b', '#fdba74', '#fed7aa'];
