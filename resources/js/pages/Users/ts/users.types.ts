export interface UserRecord {
    id: number;
    nama: string;
    nim: string | null;
    email: string;
    role_id: number;
    program_studi: number | null;
    role?: { nama: string } | null;
}
export interface UserPage<T> {
    data: T[];
    current_page: number;
    per_page: number;
    total: number;
    last_page: number;
}
export interface UserFilters {
    audience: 'pegawai' | 'mahasiswa';
    search: string | null;
    per_page: number;
}
export interface UserQuery {
    audience: 'pegawai' | 'mahasiswa';
    search: string | null;
    page: number;
    perPage: number;
}
