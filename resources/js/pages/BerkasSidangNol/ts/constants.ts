import { CheckCircle, HelpCircle, Pencil, XCircle, type LucideIcon } from 'lucide-vue-next';
import type {
    SidangNol,
    SidangNolActor,
    SidangNolCapabilities,
    SidangNolDocumentDefinition,
    SidangNolStatus,
} from './types';

export const SIDANG_NOL_DOCUMENTS: readonly SidangNolDocumentDefinition[] = [
    {
        key: 'dokumen_hasil_studi',
        label: 'Dokumen Hasil Studi',
        required: true,
        instructions: [
            'Rekap hasil studi dari SIMARI yang disahkan ketua atau sekretaris program studi.',
            'Rekap mata kuliah recourse (jika ada).',
            'Form hapus mata kuliah (jika ada).',
        ],
    },
    {
        key: 'dokumen_data_diri',
        label: 'Dokumen Data Diri',
        required: true,
        instructions: ['Scan KTM.', 'Ijazah terakhir.', 'Scan KTP.'],
    },
    {
        key: 'dokumen_pddikti_ukt',
        label: 'Dokumen Tampilan PDDIKTI & Bukti Pembayaran UKT',
        required: true,
        instructions: ['Scan PDDIKTI.', 'Slip UKT.'],
    },
    {
        key: 'dokumen_ruangbaca_laboratorium_pkkmb_skpi',
        label: 'Dokumen Ruang Baca, Laboratorium, PKKMB, dan SKPI',
        required: true,
        instructions: [
            'Scan surat Ruang Baca.',
            'Scan surat Bebas Lab.',
            'Scan sertifikat PKKMB dengan tanda nama.',
            'Scan surat SKPI.',
        ],
    },
    {
        key: 'dokumen_office_toefl',
        label: 'Dokumen Sertifikat Office dan TOEFL',
        required: true,
        instructions: ['Scan sertifikat Office.', 'Scan sertifikat TOEFL yang diterbitkan UPT Bahasa ULM.'],
    },
    {
        key: 'dokumen_tambahan',
        label: 'Dokumen Tambahan dari Prodi',
        required: false,
    },
] as const;

export const PROGRAM_STUDI_LABELS: Record<number, string> = {
    1: 'Matematika',
    2: 'Kimia',
    3: 'Biologi',
    4: 'Fisika',
    5: 'Farmasi',
    6: 'Ilmu Komputer',
    7: 'Statistika',
    8: 'Profesi Apoteker',
};

export const PROGRAM_STUDI_OPTIONS = Object.entries(PROGRAM_STUDI_LABELS).map(([value, label]) => ({
    value: Number(value),
    label,
}));

interface SidangNolStatusPresentation {
    label: string;
    badgeClass: string;
    icon: LucideIcon;
}

export const SIDANG_NOL_STATUS: Record<SidangNolStatus, SidangNolStatusPresentation> = {
    0: { label: 'Draft', badgeClass: 'bg-gray-100 text-gray-600', icon: Pencil },
    1: { label: 'Menunggu', badgeClass: 'bg-blue-100 text-blue-600', icon: HelpCircle },
    2: { label: 'Diterima', badgeClass: 'bg-green-100 text-green-600', icon: CheckCircle },
    3: { label: 'Ditolak', badgeClass: 'bg-red-100 text-red-600', icon: XCircle },
};

export function formatSidangNolDate(value: string | null | undefined): string {
    if (!value) {
        return '-';
    }

    return new Intl.DateTimeFormat('id-ID', {
        weekday: 'long',
        day: '2-digit',
        month: 'long',
        year: 'numeric',
    }).format(new Date(value));
}

export function programStudiLabel(programStudi: number | null | undefined): string {
    return programStudi ? (PROGRAM_STUDI_LABELS[programStudi] ?? '-') : '-';
}

// This controls only presentation. The server remains the authorization authority.
export function sidangNolCapabilities(
    actor: SidangNolActor,
    berkas?: Pick<SidangNol, 'status'>,
): SidangNolCapabilities {
    const isStudent = actor.role_id === 1;
    const isStaff = actor.role_id === 6 || actor.role_id === 8;
    const status = berkas?.status;

    return {
        canCreate: isStudent || isStaff,
        canEdit: status === undefined ? isStudent || isStaff : (isStudent ? status === 0 : isStaff),
        canDelete: status === undefined ? false : (isStudent ? status === 0 : isStaff),
        canSubmit: status === 0,
        canReset: status === 3,
        canReview: isStaff && typeof status === 'number' && status >= 1,
        canExport: isStaff,
        canDownloadReply: status === 2,
    };
}
