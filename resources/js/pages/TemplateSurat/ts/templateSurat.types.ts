export interface TemplateSurat {
    id: number;
    nama: string;
    deskripsi: string | null;
    status: boolean;
    dokumen_path: string | null;
    tanggal_publish: string | null;
}

export interface TemplateSuratActor {
    id: number;
    role_id: number;
}

export interface TemplateSuratPaginated<T> {
    data: T[];
    current_page: number;
    per_page: number;
    total: number;
    last_page: number;
}

export interface TemplateSuratFilters {
    search: string | null;
    per_page: number;
}

export interface TemplateSuratTableQuery {
    page: number;
    perPage: number;
    search: string | null;
}

export interface TemplateSuratFormData {
    nama: string;
    deskripsi: string;
    status: boolean;
    dokumen_path: File | null;
    tanggal_publish: string;
}
