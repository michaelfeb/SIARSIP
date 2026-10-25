import type { CutiAkademikStatus } from './cutiAkademik.types';

export const CUTI_AKADEMIK_STATUS: Record<CutiAkademikStatus, { label: string; className: string }> = {
    DRAFT: { label: 'Draft', className: 'bg-slate-100 text-slate-700' },
    IN_PROGRESS: { label: 'Dalam Proses', className: 'bg-blue-100 text-blue-700' },
    REJECTED: { label: 'Ditolak', className: 'bg-rose-100 text-rose-700' },
    ARCHIVED: { label: 'Selesai', className: 'bg-emerald-100 text-emerald-700' },
};

export const CUTI_AKADEMIK_SEMESTERS = Array.from({ length: 14 }, (_, index) => String(index + 1));
