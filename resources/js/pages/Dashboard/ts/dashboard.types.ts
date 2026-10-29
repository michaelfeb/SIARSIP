export type DashboardLetterType = 'all' | 'sidang-nol' | 'cuti-akademik';

export interface DashboardFilters {
    letter_type: DashboardLetterType;
    month: number | null;
    year: number | null;
}

export interface DashboardSummary {
    total: number;
    inProgress: number;
    archived: number;
    rejected: number;
}

export interface DashboardProgramStudiDistribution {
    label: string;
    total: number;
}

export interface DashboardFilterOptions {
    years: number[];
}
