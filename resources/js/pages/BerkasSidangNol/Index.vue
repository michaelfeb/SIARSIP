<script setup lang="ts">
import Heading from '@/components/Heading.vue';
import Button from '@/components/ui/button/Button.vue';
import { Label } from '@/components/ui/label';
import Modal from '@/components/ui/modal/Modal.vue';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, router } from '@inertiajs/vue3';
import axios from 'axios';
import { Download, FileDown, Plus } from 'lucide-vue-next';
import Swal from 'sweetalert2';
import { computed, ref } from 'vue';
import DataTable from './DataTable.vue';
import { PROGRAM_STUDI_OPTIONS, sidangNolCapabilities } from './ts/constants';
import type {
    BerkasSidangNolDataTableFilters,
    BerkasSidangNolDataTableQuery,
    Paginated,
    SidangNol,
    SidangNolAuth,
} from './ts/types';

const props = defineProps<{
    auth: SidangNolAuth;
    berkas: Paginated<SidangNol>;
    filters: BerkasSidangNolDataTableFilters;
}>();

const breadcrumbs: BreadcrumbItem[] = [{ title: 'Berkas Sidang Nol', href: '/berkas-sidang-nol' }];
const isLoading = ref(false);
const showExportModal = ref(false);
const formExport = ref({ tahun: 99, status: 99, program_studi: 99 });

const canCreate = computed(() => sidangNolCapabilities(props.auth.user).canCreate);
const canExport = computed(() => sidangNolCapabilities(props.auth.user).canExport);
const currentYear = new Date().getFullYear();
const tahunOptions = Array.from({ length: 5 }, (_, index) => currentYear - index);

function currentQuery(page = props.berkas.current_page): BerkasSidangNolDataTableQuery {
    return {
        page,
        perPage: props.berkas.per_page,
        search: props.filters.search,
        status: props.filters.status,
    };
}

function visitIndex(query: BerkasSidangNolDataTableQuery): void {
    isLoading.value = true;

    router.get(
        route('berkas-sidang-nol.index'),
        {
            page: query.page,
            per_page: query.perPage,
            search: query.search || undefined,
            status: query.status ?? undefined,
        },
        {
            only: ['berkas', 'filters'],
            preserveScroll: true,
            preserveState: true,
            replace: true,
            onFinish: () => {
                isLoading.value = false;
            },
        },
    );
}

function onDataTableQueryChange(query: BerkasSidangNolDataTableQuery): void {
    visitIndex(query);
}

function reloadAfterMutation(): void {
    const page = props.berkas.data.length === 1 && props.berkas.current_page > 1
        ? props.berkas.current_page - 1
        : props.berkas.current_page;

    visitIndex(currentQuery(page));
}

function onCreate(): void {
    router.get(route('berkas-sidang-nol.create'));
}

function onOpenExportModal(): void {
    showExportModal.value = true;
}

function onCloseExportModal(): void {
    showExportModal.value = false;
}

function onDetail(id: number): void {
    router.get(route('berkas-sidang-nol.show', id));
}

function onEdit(id: number): void {
    router.get(route('berkas-sidang-nol.edit', id));
}

async function onDelete(berkas: SidangNol): Promise<void> {
    const result = await Swal.fire({
        icon: 'warning',
        title: 'Anda yakin?',
        text: 'Data yang terhapus tidak dapat dikembalikan!',
        showCancelButton: true,
        confirmButtonText: 'Hapus',
        cancelButtonText: 'Batalkan',
        reverseButtons: true,
    });

    if (!result.isConfirmed) {
        return;
    }

    router.delete(route('berkas-sidang-nol.destroy', berkas.id), {
        preserveScroll: true,
        onSuccess: async () => {
            await Swal.fire({ icon: 'success', title: 'Berhasil', text: 'Data telah dihapus.' });
            reloadAfterMutation();
        },
        onError: () => Swal.fire({ icon: 'error', title: 'Gagal', text: 'Data tidak dapat dihapus.' }),
    });
}

async function onSubmit(berkas: SidangNol): Promise<void> {
    const result = await Swal.fire({
        icon: 'warning',
        title: 'Kirim berkas?',
        text: 'Data yang terkirim tidak dapat diedit.',
        showCancelButton: true,
        confirmButtonText: 'Kirim',
        cancelButtonText: 'Batalkan',
        reverseButtons: true,
    });

    if (!result.isConfirmed) {
        return;
    }

    router.put(route('berkas-sidang-nol.kirim', berkas.id), {}, {
        preserveScroll: true,
        onSuccess: async () => {
            await Swal.fire({ icon: 'success', title: 'Berhasil', text: 'Data telah terkirim.' });
            reloadAfterMutation();
        },
        onError: () => Swal.fire({ icon: 'error', title: 'Gagal', text: 'Data tidak dapat dikirim.' }),
    });
}

async function onReset(berkas: SidangNol): Promise<void> {
    const result = await Swal.fire({
        icon: 'warning',
        title: 'Reset berkas?',
        text: 'Status berkas akan kembali menjadi draft.',
        showCancelButton: true,
        confirmButtonText: 'Reset',
        cancelButtonText: 'Batalkan',
        reverseButtons: true,
    });

    if (!result.isConfirmed) {
        return;
    }

    router.put(route('berkas-sidang-nol.reset', berkas.id), {}, {
        preserveScroll: true,
        onSuccess: async () => {
            await Swal.fire({ icon: 'success', title: 'Berhasil', text: 'Data telah direset.' });
            reloadAfterMutation();
        },
        onError: () => Swal.fire({ icon: 'error', title: 'Gagal', text: 'Data tidak dapat direset.' }),
    });
}

function onReview(id: number): void {
    router.get(route('berkas-sidang-nol.ajuan', id));
}

async function onDownloadReply(berkas: SidangNol): Promise<void> {
    try {
        const response = await axios.get(route('berkas-sidang-nol.download-surat-sidang-nol', berkas.id), {
            responseType: 'blob',
        });
        const url = URL.createObjectURL(new Blob([response.data], { type: 'application/pdf' }));
        const link = document.createElement('a');
        link.href = url;
        link.download = `surat_sidang_nol_${berkas.user?.nama?.replace(/\s+/g, '_') ?? berkas.id}.pdf`;
        document.body.appendChild(link);
        link.click();
        link.remove();
        URL.revokeObjectURL(url);
    } catch {
        await Swal.fire({ icon: 'error', title: 'Gagal mengunduh', text: 'Surat balasan tidak tersedia.' });
    }
}

function onExport(): void {
    window.open(route('berkas-sidang-nol.export', formExport.value), '_blank', 'noopener');
    onCloseExportModal();
}
</script>

<template>

    <Head title="Berkas Sidang Nol" />

    <Modal :show="showExportModal" @close="onCloseExportModal">
        <template #header>
            <h2 class="text-lg font-semibold">Export Excel</h2>
        </template>

        <div class="space-y-4">
            <div class="space-y-2">
                <Label for="export_tahun">Tahun</Label>
                <Select v-model="formExport.tahun">
                    <SelectTrigger id="export_tahun" class="w-full">
                        <SelectValue placeholder="Pilih tahun" />
                    </SelectTrigger>
                    <SelectContent>
                        <SelectItem :value="99">Semua</SelectItem>
                        <SelectItem v-for="tahun in tahunOptions" :key="tahun" :value="tahun">
                            {{ tahun }}
                        </SelectItem>
                    </SelectContent>
                </Select>
            </div>

            <div class="space-y-2">
                <Label for="export_status">Status</Label>
                <Select v-model="formExport.status">
                    <SelectTrigger id="export_status" class="w-full">
                        <SelectValue placeholder="Pilih status" />
                    </SelectTrigger>
                    <SelectContent>
                        <SelectItem :value="99">Seluruh</SelectItem>
                        <SelectItem :value="1">Dikirim</SelectItem>
                        <SelectItem :value="2">Diterima</SelectItem>
                        <SelectItem :value="3">Ditolak</SelectItem>
                    </SelectContent>
                </Select>
            </div>

            <div class="space-y-2">
                <Label for="export_program_studi">Program Studi</Label>
                <Select v-model="formExport.program_studi">
                    <SelectTrigger id="export_program_studi" class="w-full">
                        <SelectValue placeholder="Pilih program studi" />
                    </SelectTrigger>
                    <SelectContent>
                        <SelectItem :value="99">Seluruh</SelectItem>
                        <SelectItem v-for="programStudi in PROGRAM_STUDI_OPTIONS" :key="programStudi.value" :value="programStudi.value">
                            {{ programStudi.label }}
                        </SelectItem>
                    </SelectContent>
                </Select>
            </div>
        </div>

        <template #footer>
            <Button type="button" variant="outline" @click="onCloseExportModal">Kembali</Button>
            <Button type="button" @click="onExport">
                <Download class="size-4" /> Download
            </Button>
        </template>
    </Modal>

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="mx-auto w-full max-w-[1800px] space-y-7 p-4 sm:p-6">
            <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
                <Heading title="Permohonan Sidang Nol Mahasiswa"
                    description="Kelola permohonan surat sidang nol mahasiswa." class="!mb-0" />

                <div class="flex w-full flex-col gap-2 sm:w-auto sm:flex-row sm:flex-wrap sm:justify-end">
                    <Button v-if="canExport" variant="outline" class="w-full sm:w-auto" @click="onOpenExportModal">
                        <FileDown class="size-4" />
                        Export
                    </Button>
                    <Button v-if="canCreate" class="w-full sm:w-auto" @click="onCreate">
                        <Plus class="size-4" />
                        Tambah Berkas
                    </Button>
                </div>
            </div>

            <DataTable :auth="auth" :berkas="berkas" :filters="filters" :loading="isLoading"
                @query-change="onDataTableQueryChange" @view="onDetail" @edit="onEdit" @delete="onDelete"
                @submit="onSubmit" @reset="onReset" @review="onReview" @download-reply="onDownloadReply" />
        </div>
    </AppLayout>
</template>
