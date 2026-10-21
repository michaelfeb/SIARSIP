export const sidangNolDocumentKeys = [
    'dokumen_hasil_studi',
    'dokumen_data_diri',
    'dokumen_pddikti_ukt',
    'dokumen_ruangbaca_laboratorium_pkkmb_skpi',
    'dokumen_office_toefl',
    'dokumen_tambahan',
] as const;

export type SidangNolDocumentKey = (typeof sidangNolDocumentKeys)[number];
export type SidangNolStatus = 0 | 1 | 2 | 3;

export interface SidangNolUser {
    id: number;
    nama: string;
    nim: string | null;
    program_studi: number | null;
    role_id?: number;
}

export interface SidangNolNote {
    id: number;
    pesan: string;
    user?: Pick<SidangNolUser, 'id' | 'nama'> | null;
}

export interface SidangNol {
    id: number;
    user_id: number;
    nomor_surat: string | null;
    status: SidangNolStatus;
    tanggal_dikirim: string | null;
    tanggal_selesai: string | null;
    surat_balasan: string | null;
    user?: SidangNolUser | null;
    notes?: SidangNolNote[];
    dokumen_hasil_studi: string | null;
    dokumen_data_diri: string | null;
    dokumen_pddikti_ukt: string | null;
    dokumen_ruangbaca_laboratorium_pkkmb_skpi: string | null;
    dokumen_office_toefl: string | null;
    dokumen_tambahan: string | null;
}

export interface SidangNolActor extends SidangNolUser {
    role_id: number;
}

export interface SidangNolAuth {
    user: SidangNolActor;
}

export interface Paginated<T> {
    data: T[];
    current_page: number;
    per_page: number;
    total: number;
    last_page: number;
}

export type SidangNolDocumentUrls = Partial<Record<SidangNolDocumentKey, string>>;

export interface SidangNolDocumentDefinition {
    key: SidangNolDocumentKey;
    label: string;
    required: boolean;
    instructions?: string[];
}

export interface SidangNolCapabilities {
    canCreate: boolean;
    canEdit: boolean;
    canDelete: boolean;
    canSubmit: boolean;
    canReset: boolean;
    canReview: boolean;
    canExport: boolean;
    canDownloadReply: boolean;
}
