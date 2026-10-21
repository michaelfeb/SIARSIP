<script setup lang="ts">
import Heading from '@/components/Heading.vue';
import Button from '@/components/ui/button/Button.vue';
import Modal from '@/components/ui/modal/Modal.vue';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, router, usePage } from '@inertiajs/vue3';
import axios from 'axios';
// @ts-expect-error The package type definitions omit its Vue component default export.
import EasyDataTable from 'vue3-easy-data-table';
import { Check, Download, Eye, Pencil, Plus, RotateCcw, Send, Trash2, Upload } from 'lucide-vue-next';
import Swal from 'sweetalert2';
import { computed, onBeforeUnmount, ref, watch } from 'vue';
import {
    PROGRAM_STUDI_OPTIONS,
    SIDANG_NOL_STATUS,
    formatSidangNolDate,
    programStudiLabel,
    sidangNolCapabilities,
} from './ts/constants';
import type { Paginated, SidangNol, SidangNolAuth, SidangNolStatus } from './ts/types';

interface DataTableOptions {
    page: number;
    rowsPerPage: number;
}

const props = defineProps<{
    auth: SidangNolAuth;
    berkas: Paginated<SidangNol>;
}>();

const breadcrumbs: BreadcrumbItem[] = [{ title: 'Berkas Sidang Nol', href: '/berkas-sidang-nol' }];

const headers = [
    { text: 'Nomor Surat', value: 'nomor_surat', width: 180, sortable: true },
    { text: 'Mahasiswa | NIM', value: 'mahasiswa_nim', sortable: true, width: 190 },
    { text: 'Program Studi', value: 'program_studi', sortable: true },
    { text: 'Tanggal Dikirim', value: 'tanggal_dikirim', sortable: true },
    { text: 'Tanggal Selesai', value: 'tanggal_selesai', sortable: true },
    { text: 'Status', value: 'status', sortable: true },
    { text: 'Aksi', value: 'id', sortable: false, width: 200 },
];

const page = usePage();
const initialSearch = new URLSearchParams(page.url.split('?')[1] ?? '').get('search') ?? '';
const search = ref(initialSearch);
const isLoading = ref(false);
const showExportModal = ref(false);
const serverOptions = ref<DataTableOptions>({
    page: props.berkas.current_page,
    rowsPerPage: props.berkas.per_page,
});
const formExport = ref({ tahun: 99, status: 99, program_studi: 99 });

let searchTimer: ReturnType<typeof setTimeout> | undefined;

const items = computed(() => props.berkas.data);
const canCreate = computed(() => sidangNolCapabilities(props.auth.user).canCreate);
const canExport = computed(() => sidangNolCapabilities(props.auth.user).canExport);
const currentYear = new Date().getFullYear();
const tahunOptions = Array.from({ length: 5 }, (_, index) => currentYear - index);

function visitIndex(targetPage = serverOptions.value.page) {
    isLoading.value = true;

    router.get(
        route('berkas-sidang-nol.index'),
        {
            page: targetPage,
            per_page: serverOptions.value.rowsPerPage,
            search: search.value || undefined,
        },
        {
            only: ['berkas'],
            preserveScroll: true,
            preserveState: true,
            replace: true,
            onFinish: () => {
                isLoading.value = false;
            },
        },
    );
}

watch(
    () => [props.berkas.current_page, props.berkas.per_page],
    ([currentPage, perPage]) => {
        if (serverOptions.value.page !== currentPage || serverOptions.value.rowsPerPage !== perPage) {
            serverOptions.value = { page: currentPage, rowsPerPage: perPage };
        }
    },
);

watch(
    () => [serverOptions.value.page, serverOptions.value.rowsPerPage],
    ([targetPage, rowsPerPage]) => {
        if (targetPage !== props.berkas.current_page || rowsPerPage !== props.berkas.per_page) {
            visitIndex(targetPage);
        }
    },
);

watch(search, () => {
    if (searchTimer) {
        clearTimeout(searchTimer);
    }

    searchTimer = setTimeout(() => {
        if (serverOptions.value.page !== 1) {
            serverOptions.value = { ...serverOptions.value, page: 1 };
            return;
        }

        visitIndex(1);
    }, 300);
});

onBeforeUnmount(() => {
    if (searchTimer) {
        clearTimeout(searchTimer);
    }
});

function onCreate() {
    router.get(route('berkas-sidang-nol.create'));
}

function onDetail(id: number) {
    router.get(route('berkas-sidang-nol.show', id));
}

function onEdit(id: number) {
    router.get(route('berkas-sidang-nol.edit', id));
}

function reloadAfterMutation() {
    visitIndex();
}

async function onDelete(berkas: SidangNol) {
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

async function onSubmit(berkas: SidangNol) {
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

async function onReset(berkas: SidangNol) {
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

function onReview(id: number) {
    router.get(route('berkas-sidang-nol.ajuan', id));
}

async function onDownloadReply(berkas: SidangNol) {
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

function onExport() {
    window.open(route('berkas-sidang-nol.export', formExport.value), '_blank', 'noopener');
    showExportModal.value = false;
}

function statusPresentation(status: number) {
    return SIDANG_NOL_STATUS[status as SidangNolStatus] ?? SIDANG_NOL_STATUS[0];
}
</script>

<template>
    <Head title="Berkas Sidang Nol" />

    <Modal :show="showExportModal" @close="showExportModal = false">
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
            <Button type="button" class="border border-orange-400 bg-white text-orange-500 hover:bg-orange-100" @click="showExportModal = false">Kembali</Button>
            <Button type="button" class="bg-green-600 text-white hover:bg-green-700" @click="onExport">Download</Button>
        </template>
    </Modal>

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="space-y-4 p-4">
            <div class="container mx-auto flex items-center justify-between gap-4">
                <Heading title="Manajemen Surat Sidang Nol" description="Daftar surat sidang nol yang telah terdaftar dalam sistem" class="!mb-0" />
                <div class="flex items-center gap-2">
                    <Button v-if="canExport" class="flex items-center gap-1 bg-green-500 text-white hover:bg-green-600" @click="showExportModal = true">
                        <Upload class="h-4 w-4" /> Export
                    </Button>
                    <Button v-if="canCreate" class="flex items-center gap-1 bg-blue-500 text-white hover:bg-blue-600" @click="onCreate">
                        <Plus class="h-4 w-4" /> Tambah
                    </Button>
                </div>
            </div>

            <div class="container mx-auto flex items-center justify-end gap-3">
                <label for="sidang-nol-search" class="text-sm font-medium">Cari</label>
                <input id="sidang-nol-search" v-model="search" type="search" placeholder="Nama, NIM, atau nomor surat" class="h-9 w-64 rounded border border-gray-300 px-3 text-sm" />
            </div>

            <EasyDataTable
                v-model:server-options="serverOptions"
                :server-items-length="berkas.total"
                :loading="isLoading"
                :headers="headers"
                :items="items"
                show-index
            >
                <template #header-index>No</template>
                <template #item-mahasiswa_nim="{ user }">{{ `${user?.nama ?? '-'} | ${user?.nim ?? '-'}` }}</template>
                <template #item-program_studi="{ user }">{{ programStudiLabel(user?.program_studi) }}</template>
                <template #item-tanggal_dikirim="{ tanggal_dikirim }">{{ formatSidangNolDate(tanggal_dikirim) }}</template>
                <template #item-tanggal_selesai="{ tanggal_selesai }">{{ formatSidangNolDate(tanggal_selesai) }}</template>
                <template #item-status="{ status }">
                    <span :class="['inline-flex items-center rounded-full px-3 py-1 text-xs font-medium', statusPresentation(status).badgeClass]">
                        <component :is="statusPresentation(status).icon" class="mr-1 h-3 w-3" />
                        {{ statusPresentation(status).label }}
                    </span>
                </template>
                <template #item-id="{ id, status, user, ...berkas }">
                    <div class="flex items-center gap-2">
                        <Button class="h-7 w-7 border border-blue-500 bg-white p-1 text-blue-500 hover:bg-blue-500 hover:text-white" title="Detail" @click="onDetail(id)"><Eye class="h-4 w-4" /></Button>
                        <Button v-if="sidangNolCapabilities(auth.user, { status }).canReview" class="h-7 w-7 border border-green-500 bg-white p-1 text-green-500 hover:bg-green-500 hover:text-white" title="Periksa berkas" @click="onReview(id)"><Check class="h-4 w-4" /></Button>
                        <Button v-if="sidangNolCapabilities(auth.user, { status }).canSubmit" class="h-7 w-7 border border-blue-500 bg-white p-1 text-blue-500 hover:bg-blue-500 hover:text-white" title="Kirim" @click="onSubmit({ id, status, user, ...berkas } as SidangNol)"><Send class="h-4 w-4" /></Button>
                        <Button v-if="sidangNolCapabilities(auth.user, { status }).canReset" class="h-7 w-7 border border-blue-500 bg-white p-1 text-blue-500 hover:bg-blue-500 hover:text-white" title="Reset" @click="onReset({ id, status, user, ...berkas } as SidangNol)"><RotateCcw class="h-4 w-4" /></Button>
                        <Button v-if="sidangNolCapabilities(auth.user, { status }).canEdit" class="h-7 w-7 border border-yellow-500 bg-white p-1 text-yellow-500 hover:bg-yellow-500 hover:text-white" title="Ubah" @click="onEdit(id)"><Pencil class="h-4 w-4" /></Button>
                        <Button v-if="sidangNolCapabilities(auth.user, { status }).canDelete" class="h-7 w-7 border border-red-500 bg-white p-1 text-red-500 hover:bg-red-500 hover:text-white" title="Hapus" @click="onDelete({ id, status, user, ...berkas } as SidangNol)"><Trash2 class="h-4 w-4" /></Button>
                        <Button v-if="sidangNolCapabilities(auth.user, { status }).canDownloadReply" class="h-7 w-7 border border-red-500 bg-white p-1 text-red-500 hover:bg-red-500 hover:text-white" title="Download surat balasan" @click="onDownloadReply({ id, status, user, ...berkas } as SidangNol)"><Download class="h-4 w-4" /></Button>
                    </div>
                </template>
            </EasyDataTable>
        </div>
    </AppLayout>
</template>
