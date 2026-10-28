export const TEMPLATE_SURAT_DOCUMENT_ACCEPTED_TYPES = [
    'application/msword',
    'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
] as const;

export const TEMPLATE_SURAT_DOCUMENT_MAX_SIZE_LABEL = '2 MB';

export const TEMPLATE_SURAT_STATUS = {
    active: { label: 'Aktif', badgeClass: 'bg-emerald-100 text-emerald-700' },
    inactive: { label: 'Tidak Aktif', badgeClass: 'bg-muted text-muted-foreground' },
} as const;
