export interface CarouselRecord {
    id: number;
    nama: string;
    gambar: string;
    status: boolean;
    tanggal_publish: string;
}

export interface CarouselActor {
    id: number;
    role_id: number;
}

export interface CarouselPaginated<T> {
    data: T[];
    current_page: number;
    per_page: number;
    total: number;
    last_page: number;
}

export interface CarouselFilters {
    search: string | null;
    per_page: number;
}

export interface CarouselTableQuery {
    page: number;
    perPage: number;
    search: string | null;
}

export interface CarouselFormData {
    nama: string;
    gambar: File | null;
    status: boolean;
    tanggal_publish: string;
}
