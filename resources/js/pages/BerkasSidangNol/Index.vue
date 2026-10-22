<script setup lang="ts">
import Heading from '@/components/Heading.vue';
import Button from '@/components/ui/button/Button.vue';
import Modal from '@/components/ui/modal/Modal.vue';
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
        <template #header><h2 class="text-lg font-semibold">Export Excel</h2></template>

        <div class="space-y-4">
            <label class="block text-sm font-medium">
                Tahun
                <select v-model="formExport.tahun" class="mt-1 w-full rounded border border-gray-300 px-3 py-2">
                    <option :value="99">Semua</option>
                    <option v-for="tahun in tahunOptions" :key="tahun" :value="tahun">{{ tahun }}</option>
                </select>
            </label>
            <label class="block text-sm font-medium">
                Status
                <select v-model="formExport.status" class="mt-1 w-full rounded border border-gray-300 px-3 py-2">
                    <option :value="99">Seluruh</option>
                    <option :value="1">Dikirim</option>
                    <option :value="2">Diterima</option>
                    <option :value="3">Ditolak</option>
                </select>
            </label>
            <label class="block text-sm font-medium">
                Program Studi
                <select v-model="formExport.program_studi" class="mt-1 w-full rounded border border-gray-300 px-3 py-2">
                    <option :value="99">Seluruh</option>
                    <option v-for="programStudi in PROGRAM_STUDI_OPTIONS" :key="programStudi.value" :value="programStudi.value">
                        {{ programStudi.label }}
                    </option>
                </select>
            </label>
        </div>

        <template #footer>
            <Button type="button" variant="outline" @click="onCloseExportModal">Kembali</Button>
            <Button type="button" class="bg-emerald-700 text-white hover:bg-emerald-800" @click="onExport"><Download class="size-4" /> Download</Button>
        </template>
    </Modal>

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="mx-auto w-full max-w-[1800px] space-y-7 p-4 lg:p-6">
            <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
                <Heading title="Permohonan Sidang Nol Mahasiswa" description="Kelola permohonan surat sidang nol mahasiswa." class="!mb-0" />

                <div class="flex flex-wrap items-center gap-2">
                    <Button v-if="canExport" variant="outline" class="rounded-full" @click="onOpenExportModal">
                        <FileDown class="size-4" />
                        Export
                    </Button>
                    <Button v-if="canCreate" class="rounded-full bg-emerald-800 text-white hover:bg-emerald-900" @click="onCreate">
                        <Plus class="size-4" />
                        Tambah Berkas
                    </Button>
                </div>
            </div>

            <DataTable
                :auth="auth"
                :berkas="berkas"
                :filters="filters"
                :loading="isLoading"
                @query-change="onDataTableQueryChange"
                @view="onDetail"
                @edit="onEdit"
                @delete="onDelete"
                @submit="onSubmit"
                @reset="onReset"
                @review="onReview"
                @download-reply="onDownloadReply"
            />
        </div>
    </AppLayout>
</template>
