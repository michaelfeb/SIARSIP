export const CAROUSEL_IMAGE_ACCEPTED_TYPES = ['image/png', 'image/jpeg'] as const;
export const CAROUSEL_IMAGE_MAX_SIZE_LABEL = '5 MB';

export const CAROUSEL_STATUS = {
    active: { label: 'Aktif', className: 'border-emerald-200 bg-emerald-50 text-emerald-700 hover:bg-emerald-100' },
    inactive: { label: 'Tidak Aktif', className: 'border-slate-200 bg-slate-50 text-slate-700 hover:bg-slate-100' },
} as const;
