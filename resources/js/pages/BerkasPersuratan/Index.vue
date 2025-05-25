<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue'
import { Head, router, ServerOptions } from '@inertiajs/vue3'
import EasyDataTable, { Item } from 'vue3-easy-data-table'
import { type BreadcrumbItem } from '@/types'
import 'vue3-easy-data-table/dist/style.css'
import Heading from '@/components/Heading.vue'
import { ref, computed, watch } from 'vue'
import Button from '@/components/ui/button/Button.vue'
import { Eye, Trash2, Plus, Pencil, Send, RotateCcw, Check, Download } from 'lucide-vue-next'
import axios from 'axios'
import Swal from 'sweetalert2'
import statusMapping from '@/utils/statusMapping'
import programStudiMapping from '@/utils/programStudiMapping'

const props = defineProps<{
    auth: any,
}>()

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Berkas Persuratan',
        href: '/berkas-persuratan',
    },
]

const headers = [
    { text: "Nomor Surat", value: "nomor_surat", width: 150, sortable: true },
    { text: "Mahasiswa | NIM", value: "mahasiswa_nim", width: 200, sortable: true },
    { text: "Keterangan", value: "keterangan", width: 250 },
    { text: "Program Studi", value: "program_studi", sortable: true },
    { text: "Jenis Surat", value: "jenis_surat.nama", sortable: true },
    { text: "Tanggal Dikirim", value: "tanggal_dikirim", sortable: true },
    { text: "Status", value: "status", sortable: true },
    { text: "Aksi", value: "id", sortable: false, width: 200 },
]


const items = ref<Item[]>([]);
const loading = ref(false);
const serverItemsLength = ref(0);
const serverOptions = ref<ServerOptions>({
    page: 1,
    rowsPerPage: 10,
});
const search = ref('')

const loadFromServer = async () => {
    loading.value = true

    const { data } = await axios.get(route('berkas-persuratan.index'), {
        params: {
            page: serverOptions.value.page,
            per_page: serverOptions.value.rowsPerPage,
            search: search.value,
        }
    })

    items.value = data.data
    serverItemsLength.value = data.total
    loading.value = false
}

loadFromServer();

watch([serverOptions, search], (value) => { loadFromServer(); }, { deep: true });

function onSearch() {
    serverOptions.value.page = 1
}

function onCreate() {
    router.get(route('berkas-persuratan.create'))
}

function onDetail(id: number) {
    router.get(route('berkas-persuratan.show', id))
}

function onEdit(id: number) {
    router.visit(route('berkas-persuratan.edit', id))
}

function onDelete(id: number) {

    Swal.fire({
        icon: 'warning',
        title: 'Anda yakin?',
        text: 'Data yang terhapus tidak dapat dikembalikan!',
        showCancelButton: true,
        confirmButtonText: 'Hapus',
        cancelButtonText: 'Batalkan',
        customClass: {
            confirmButton: 'swal-confirm-button',
            cancelButton: 'swal-cancel-button',
            actions: 'swal-actions-button-group',
        },
        reverseButtons: true,
        buttonsStyling: false
    }).then((result) => {
        if (result.isConfirmed) {
            router.delete(route('berkas-persuratan.destroy', id), {
                async onSuccess() {
                    await loadFromServer();
                    Swal.fire({
                        title: 'Terhapus!',
                        text: "Data telah dihapus!",
                        icon: 'success',
                        confirmButtonText: 'OK',
                        customClass: {
                            confirmButton: 'swal-confirm-button',
                        },
                    });
                },
            });
        }
    });
}

const onKirim = async (id: number) => {
    Swal.fire({
        icon: 'warning',
        title: 'Anda yakin?',
        text: 'Data yang terkirim tidak dapat diedit!',
        showCancelButton: true,
        confirmButtonText: 'Kirim',
        cancelButtonText: 'Batalkan',
        customClass: {
            confirmButton: 'swal-confirm-button',
            cancelButton: 'swal-cancel-button',
            actions: 'swal-actions-button-group',
        },
        reverseButtons: true,
        buttonsStyling: false
    }).then(async (result) => {
        if (result.isConfirmed) {
            await axios.post(route('berkas-persuratan.kirim-persuratan', { id })).then(
                async () => {
                    await Swal.fire({
                        title: 'Berhasil!',
                        text: "Data telah terkirim!",
                        icon: 'success',
                        confirmButtonText: 'OK',
                        customClass: {
                            confirmButton: 'swal-confirm-button',
                        },
                    }).then(async () => {
                        await loadFromServer()
                    });
                }
            )
        }
    });
}

function onAjuan(id: number) {
    router.get(route('berkas-persuratan.ajuan', id))
}

const onReset = async (id: number) => {
    Swal.fire({
        icon: 'warning',
        title: 'Anda yakin?',
        text: 'Data akan tereset!',
        showCancelButton: true,
        confirmButtonText: 'Reset',
        cancelButtonText: 'Batalkan',
        customClass: {
            confirmButton: 'swal-confirm-button',
            cancelButton: 'swal-cancel-button',
            actions: 'swal-actions-button-group',
        },
        reverseButtons: true,
        buttonsStyling: false
    }).then(async (result) => {
        if (result.isConfirmed) {
            await axios.put(route('berkas-persuratan.reset', { id })).then(
                async () => {
                    await Swal.fire({
                        title: 'Berhasil!',
                        text: "Data telah tereset!",
                        icon: 'success',
                        confirmButtonText: 'OK',
                        customClass: {
                            confirmButton: 'swal-confirm-button',
                        },
                    }).then(async () => {
                        await loadFromServer()
                    });
                }
            )
        }
    });
}

function formatTanggal(tanggal: string) {
    if (!tanggal) return '-';
    const date = new Date(tanggal);
    return date.toLocaleDateString('id-ID', {
        weekday: 'long',
        day: '2-digit',
        month: 'long',
        year: 'numeric'
    });
}

function showAjukanButton(roleId: number, status: number): boolean {
    const statusStr = status.toString().padStart(2, '0')
    const roleStatus = parseInt(statusStr[0])
    const stageStatus = parseInt(statusStr[1])

    if (roleId === 9) return false

    if (roleId === 2 && status === 71) return true

    if (roleId === 7 && status == 81) return true

    if (roleId === 7 && status == 71) return false

    if (roleId === 8) {
        if (status === 11) {
            return false
        }
        return stageStatus !== 3 && stageStatus !== 2
    } else if (status === 11) {
        return false
    }

    return roleId === roleStatus && stageStatus !== 3 && stageStatus !== 2
}


function showEditButton(roleId: number, status: number): boolean {
    const statusStr = status.toString().padStart(2, '0');
    const roleStatus = parseInt(statusStr[0]);
    const stageStatus = parseInt(statusStr[1]);

    if (roleId === 8) return true;

    if (roleId === 9 && roleStatus === 2) return true;;

    if (roleId === 1 && status === 11) return true;

    if (roleId === 7 && status == 71) return false;

    if ((roleId === 6 || roleId === 7) && roleStatus >= roleId && stageStatus !== 3) {
        return true;
    }

    return false;
}

function showResetButton(roleId: number, status: number): boolean {
    const statusStr = status.toString().padStart(2, '0');
    const stageStatus = parseInt(statusStr[1]);

    if (stageStatus !== 3) return false;

    if (roleId === 8) return true;

    if (roleId === 1) return true;

    if (roleId === 9) return true;

    return true;
}

function showKirimButton(roleId: number, status: number) {
    if (roleId === 8) return true;
    return roleId === 1 && status === 11
}

function showDeleteButton(roleId: number, status: number) {
    const statusStr = status.toString().padStart(2, '0');
    const stageStatus = parseInt(statusStr[1]);

    if (roleId === 8) return true;
    if (roleId === 9 && stageStatus === 3) return true;
    return roleId === 1 && status === 11
}

function showDownloadButton(roleId: number, status: number) {
    return status === 91
}

async function onDownloadSuratBalasan(id: number) {
    try {
        const response = await axios.get(route('berkas-persuratan.download-balasan', id), {
            responseType: 'blob'
        });

        const blob = new Blob([response.data]);
        const fileURL = URL.createObjectURL(blob);

        const link = document.createElement('a');
        link.href = fileURL;

        link.download = `surat_balasan_${id}.pdf`;
        link.click();
        URL.revokeObjectURL(fileURL);

    } catch (error: any) {
        if (error.response && error.response.status === 404) {
            Swal.fire({
                icon: 'warning',
                title: 'Surat balasan tidak tersedia',
                text: 'Belum ada surat balasan yang diunggah untuk berkas ini.',
                confirmButtonText: 'OK'
            });
        } else {
            Swal.fire({
                icon: 'error',
                title: 'Gagal mengunduh',
                text: 'Terjadi kesalahan saat mengunduh surat balasan.',
                confirmButtonText: 'Tutup'
            });
        }
    }
}



</script>

<template>

    <Head title="Users" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="p-4 space-y-4">
            <div class="container mx-auto flex items-center justify-between mb-4">
                <Heading title="Manajemen Berkas Persuratan"
                    description="Daftar berkas persuratan yang telah terdaftar dalam sistem" class="!mb-0" />

                <Button
                    class="rounded bg-blue-500 px-4 py-2 text-white hover:bg-blue-600 transition flex items-center gap-1"
                    @click="onCreate">
                    <Plus class="h-4 w-4" />
                    <span>Tambah</span>
                </Button>
            </div>
            <div class="container mx-auto flex items-center justify-end gap-4 mb-4">
                <span class="text-sm font-medium whitespace-nowrap">Cari</span>
                <input v-model="search" @input="onSearch" type="text" placeholder="Cari nama"
                    class="w-50 h-8 text-sm rounded border border-gray-300 px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500" />
            </div>

            <EasyDataTable v-model:server-options="serverOptions" :server-items-length="serverItemsLength"
                show-index-symbol="No" :loading="loading" :headers="headers" :items="items" show-index>
                <template #loading>
                    <img src="https://i.pinimg.com/originals/94/fd/2b/94fd2bf50097ade743220761f41693d5.gif"
                        style="width: 100px; height: 80px;" />
                </template>
                <template #item-nomor_surat="{ nomor_surat }">
                    {{ nomor_surat ?? '-' }}
                </template>
                <template #item-mahasiswa_nim="{ user }">
                    <span v-if="user">
                        {{ user.nama || '-' }} | {{ user.nim || '-' }}
                    </span>
                    <span v-else>-</span>
                </template>
                <template #item-keterangan="{ keterangan }">
                    <div v-html="keterangan.replace(/\n/g, '<br>')"></div>
                </template>

                <template #item-program_studi="{ program_studi }">
                    <span v-if="program_studi">
                        {{ programStudiMapping[program_studi].label || 'Program Studi tidak ditemukan' }}
                    </span>
                    <span v-else>-</span>
                </template>
                <template #item-status="{ status }">
                    <span v-if="statusMapping[status]"
                        class="inline-flex items-center whitespace-nowrap rounded-full px-2 py-1 text-xs font-medium"
                        :class="[
                            `bg-${statusMapping[status].color}-100`,
                            `text-${statusMapping[status].color}-600`
                        ]">
                        <component :is="statusMapping[status].icon" class="w-3 h-3 mr-1"
                            :class="`text-${statusMapping[status].colorIcon}`" />
                        {{ statusMapping[status].label }}
                    </span>
                    <span v-else class="text-xs text-gray-500">Status tidak dikenal</span>
                </template>

                <template #item-tanggal_dikirim="{ tanggal_dikirim }">
                    {{ formatTanggal(tanggal_dikirim) }}
                </template>
                <template #item-id="{ status, id }">
                    <div class="flex items-center gap-2">
                        <Button
                            class="w-6 h-6 text-blue-500 hover:text-white hover:bg-blue-500 bg-white outline outline-1 outline-blue-500 p-1 rounded"
                            title="Detail" @click="onDetail(id)">
                            <Eye class="w-4 h-4" />
                        </Button>

                        <Button v-if="showAjukanButton(props.auth.user.role_id, status)"
                            class="w-6 h-6 text-green-500 hover:text-white hover:bg-green-500 bg-white outline outline-1 outline-green-500 p-1 rounded"
                            title="Periksa Berkas" @click="onAjuan(id)">
                            <Check class="w-4 h-4" />
                        </Button>

                        <Button v-if="showKirimButton(props.auth.user.role_id, status)" @click="onKirim(id)"
                            class="w-6 h-6 text-blue-500 hover:text-white hover:bg-blue-500 bg-white outline outline-1 outline-blue-500 p-1 rounded">
                            <Send class="w-4 h-4" />
                        </Button>

                        <Button v-if="showResetButton(props.auth.user.role_id, status)" @click="onReset(id)"
                            class="w-6 h-6 text-blue-500 hover:text-white hover:bg-blue-500 bg-white outline outline-1 outline-blue-500 p-1 rounded">
                            <RotateCcw class="w-4 h-4" />
                        </Button>

                        <Button v-if="showEditButton(props.auth.user.role_id, status)" @click="onEdit(id)"
                            class="w-6 h-6 text-yellow-500 hover:text-white hover:bg-yellow-500 bg-white outline outline-1 outline-yellow-500 p-1 rounded">
                            <Pencil class="w-4 h-4" />
                        </Button>

                        <Button v-if="showDeleteButton(props.auth.user.role_id, status)" @click="onDelete(id)"
                            class="w-6 h-6 text-red-500 hover:text-white hover:bg-red-500 bg-white outline outline-1 outline-red-500 p-1 rounded">
                            <Trash2 class="w-4 h-4" />
                        </Button>

                        <Button v-if="showDownloadButton(props.auth.user.role_id, status)"
                            @click="onDownloadSuratBalasan(id)"
                            class="w-6 h-6 text-red-500 hover:text-white hover:bg-red-500 bg-white outline outline-1 outline-red-500 p-1 rounded">
                            <Download class="w-4 h-4" />
                        </Button>
                    </div>
                </template>
            </EasyDataTable>
        </div>
    </AppLayout>
</template>

<style>
.customize-header {
    display: flex;
    justify-items: center;
    align-items: center;
}
</style>
