export type CutiAkademikStatus = 'DRAFT' | 'IN_PROGRESS' | 'REJECTED' | 'ARCHIVED';

export interface CutiAkademikActor {
    id: number;
    nama: string;
    nim?: string | null;
    program_studi?: number | null;
    nomor_telpon?: string | null;
    role_id: number;
}

export interface CutiAkademikAttachment {
    id: number;
    original_name: string;
    mime: string;
    size_bytes: number;
}

export interface CutiAkademikRoute {
    id: number;
    from_stage: string | null;
    to_stage: string;
    note: string | null;
    created_at: string;
    decider?: { nama: string } | null;
}

export interface CutiAkademikLetter {
    id: string;
    applicant_id: number | null;
    applicant_name: string;
    applicant_id_number: string;
    program_studi: string | null;
    purpose: string | null;
    status: CutiAkademikStatus;
    current_stage: string | null;
    letter_number: string | null;
    issued_at: string | null;
    submitted_at: string | null;
    archived_at: string | null;
    notes_snapshot: string | null;
    meta: Record<string, string | number | null>;
    attachments?: CutiAkademikAttachment[];
    routes?: CutiAkademikRoute[];
}

export interface CutiAkademikWorkflowContext {
    approvalChain: string[];
}

export interface CutiAkademikPaginated<T> {
    data: T[];
    current_page: number;
    per_page: number;
    total: number;
}

export interface CutiAkademikFilters {
    search: string | null;
    status: CutiAkademikStatus | null;
    per_page: number;
}

export interface CutiAkademikTableQuery {
    page: number;
    perPage: number;
    search: string | null;
    status: CutiAkademikStatus | null;
}

export interface CutiAkademikFormContext {
    notesMarkdown: string | null;
    notesHtml: string;
    approvalChain: string[];
}
