<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue'
import { Head, router, ServerOptions } from '@inertiajs/vue3'
import EasyDataTable, { Item } from 'vue3-easy-data-table'
import { type BreadcrumbItem } from '@/types'
import 'vue3-easy-data-table/dist/style.css'
import Heading from '@/components/Heading.vue'
import { ref, computed, watch } from 'vue'
import Button from '@/components/ui/button/Button.vue'
import { Eye, Trash2, Plus, Pencil, Send, RotateCcw, Check, Download, Upload } from 'lucide-vue-next'
import axios from 'axios'
import Swal from 'sweetalert2'
import statusMappingSidangNol from '@/utils/statusMappingSidangNol'
import programStudiMapping from '@/utils/programStudiMapping'
import Modal from '@/components/ui/modal/Modal.vue'
import { useBaseUrl } from '@/utils/useBaseUrl'

const props = defineProps<{
    auth: any,
}>()

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Berkas Sidang Nol',
        href: '/berkas-sidang-nol',
    },
]

const headers = [
    { text: "Nomor Surat", value: "nomor_surat", width: 180, sortable: true },
    { text: "Mahasiswa | NIM", value: "mahasiswa_nim", sortable: true, width: 190 },
    { text: "Program Studi", value: "program_studi", sortable: true },
    { text: "Tanggal Dikirim", value: "tanggal_dikirim", sortable: true },
    { text: "Tanggal Selesai", value: "tanggal_selesai", sortable: true },
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

    const { data } = await axios.get(route('berkas-sidang-nol.index'), {
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

function onCreate() {
    router.get(route('berkas-sidang-nol.create'))
}

function onDetail(id: number) {
    router.get(route('berkas-sidang-nol.show', id))
}

function onEdit(id: number) {
    router.visit(route('berkas-sidang-nol.edit', id))
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
            router.delete(route('berkas-sidang-nol.destroy', id), {
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
                async onError() {
                    Swal.fire({
                        title: 'Terhapus!',
                        text: "Data telah dihapus!",
                        icon: 'success',
                        confirmButtonText: 'OK',
                        customClass: {
                            confirmButton: 'swal-confirm-button',
                        },
                    });
                }
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
        cancelButtonText: 'Batalkan',
        confirmButtonText: 'Kirim',
        customClass: {
            cancelButton: 'swal-cancel-button',
            confirmButton: 'swal-confirm-button',
            actions: 'swal-actions-button-group',
        },
        reverseButtons: true,
        buttonsStyling: false
    }).then(async (result) => {
        if (result.isConfirmed) {
            await axios.put(route('berkas-sidang-nol.kirim', { id })).then(
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
    router.get(route('berkas-sidang-nol.ajuan', id))
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
            await axios.put(route('berkas-sidang-nol.reset', { id })).then(
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

async function onDownloadSuratSidangNol(id: number, namaUser: string) {
    try {
        const response = await axios.get(
            route('berkas-sidang-nol.download-surat-sidang-nol', id),
            { responseType: 'blob' }
        )

        const blob = new Blob([response.data], { type: 'application/pdf' })
        const fileURL = URL.createObjectURL(blob)

        const link = document.createElement('a')
        link.href = fileURL
        link.download = `surat_sidang_nol_${namaUser.replace(/\s+/g, '_')}.pdf`
        document.body.appendChild(link) // ← penting di Safari
        link.click()
        document.body.removeChild(link)
        URL.revokeObjectURL(fileURL)

    } catch (error: any) {
        if (error.response?.status === 404) {
            Swal.fire({
                icon: 'warning',
                title: 'Surat sidang nol tidak tersedia',
                text: 'Belum ada surat sidang nol yang diunggah untuk berkas ini.',
                confirmButtonText: 'OK',
            })
        } else {
            Swal.fire({
                icon: 'error',
                title: 'Gagal mengunduh',
                text: 'Terjadi kesalahan saat mengunduh surat balasan.',
                confirmButtonText: 'Tutup',
            })
        }
    }
}



function formatTanggal(tanggal: string) {
    if (!tanggal) return '-';
    const date = new Date(tanggal);
    return date.toLocaleDateString('id-ID', {
        weekday: 'long', // Senin, Selasa, etc
        day: '2-digit',
        month: 'long',   // Januari, Februari, etc
        year: 'numeric'
    });
}

const showModal = ref(false)

const onOpenModal = () => (showModal.value = true)
const onCloseModal = () => (showModal.value = false)
const onExport = () => {
    const tahun = formExport.value.tahun 
    const status = formExport.value.status 
    const programStudi = formExport.value.program_studi 

    const query = new URLSearchParams({
        tahun,
        status,
        program_studi: programStudi
    }).toString()

    const exportUrl = `/berkas-sidang-nol/export?${query}`
    window.open(useBaseUrl(exportUrl), '_blank')
    onCloseModal()
}

const currentYear = new Date().getFullYear()
const tahunOptions = Array.from({ length: 5 }, (_, i) => currentYear - i)

const formExport = ref({
    tahun: 99,
    status: 99,
    program_studi: 99
})


</script>

<template>

    <Modal :show="showModal" @close="onCloseModal">
        <template #header>
            <h2 class="text-lg font-semibold">Export Excel</h2>
        </template>

        <div class="space-y-4">
            <div>
                <label class="block mb-1 text-sm font-medium">Tahun</label>
                <div class="relative">
                    <select v-model="formExport.tahun"
                        class="text-sm font-medium w-full rounded border border-gray-300 px-3 py-2 pr-10 focus:outline-none focus:ring-2 focus:ring-orange-500 appearance-none">
                        <option :value="99">Semua</option>
                        <option v-for="tahun in tahunOptions" :key="tahun" :value="tahun">{{ tahun }}</option>
                    </select>
                    <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-3 text-gray-500">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
                        </svg>
                    </div>
                </div>
            </div>

            <div>
                <label class="block mb-1 text-sm font-medium">Status</label>
                <div class="relative">
                    <select v-model="formExport.status"
                        class="text-sm font-medium w-full rounded border border-gray-300 px-3 py-2 pr-10 focus:outline-none focus:ring-2 focus:ring-orange-500 appearance-none">
                        <option value="99">Seluruh</option>
                        <option value="1">Dikirim</option>
                        <option value="2">Diterima</option>
                        <option value="3">Ditolak</option>
                    </select>
                    <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-3 text-gray-500">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
                        </svg>
                    </div>
                </div>
            </div>

            <div>
                <label class="block mb-1 text-sm font-medium">Program Studi</label>
                <div class="relative">
                    <select v-model="formExport.program_studi"
                        class="text-sm font-medium w-full rounded border border-gray-300 px-3 py-2 pr-10 focus:outline-none focus:ring-2 focus:ring-orange-500 appearance-none">
                        <option value="99">Seluruh</option>
                        <option v-for="(value, key) in programStudiMapping" :key="key" :value="value.value">
                            {{ value.label }}
                        </option>
                    </select>
                    <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-3 text-gray-500">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
                        </svg>
                    </div>
                </div>
            </div>
        </div>

        <template #footer>
            <div class="flex justify-end gap-2">
                <Button type="button" @click="onCloseModal"
                    class="mt-4 border border-orange-400 text-orange-500 hover:bg-orange-100 bg-white-500">
                    <span>Kembali</span>
                </Button>
                <Button type="button" @click="onExport"
                    class="mt-4 border border-green-400 text-white hover:bg-green-100 bg-green-500">
                    <span>Download</span>
                </Button>
            </div>
        </template>
    </Modal>


    <Head title="Users" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="p-4 space-y-4">
            <div class="container mx-auto flex items-center justify-between mb-4">
                <Heading title="Manajemen Surat Sidang Nol"
                    description="Daftar surat sidang nol yang telah terdaftar dalam sistem" class="!mb-0" />

                <div class="flex items-center gap-2">
                    <Button
                        v-if="props.auth.user.role_id === 6 || props.auth.user.role_id === 8"
                        class="rounded bg-green-500 px-4 py-2 text-white hover:bg-green-600 transition flex items-center gap-1"
                        @click="onOpenModal">
                        <Upload class="h-4 w-4" />
                        <span>Export</span>
                    </Button>

                    <Button
                        class="rounded bg-blue-500 px-4 py-2 text-white hover:bg-blue-600 transition flex items-center gap-1"
                        @click="onCreate">
                        <Plus class="h-4 w-4" />
                        <span>Tambah</span>
                    </Button>
                </div>
            </div>
            <div class="container mx-auto flex items-center justify-end gap-4 mb-4">
                <span class="text-sm font-medium whitespace-nowrap">Cari</span>
                <input v-model="search" @input="onSearch" type="text" placeholder="Cari nama"
                    class="w-50 h-8 text-sm rounded border border-gray-300 px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500" />
            </div>

            <EasyDataTable v-model:server-options="serverOptions" :server-items-length="serverItemsLength"
                :loading="loading" :headers="headers" :items="items" show-index>
                <template #loading>
                    <img src="https://i.pinimg.com/originals/94/fd/2b/94fd2bf50097ade743220761f41693d5.gif"
                        style="width: 100px; height: 80px;" />
                </template>

                <template #header-index>
                    No
                </template>

                <template #item-nomor_surat="{ nomor_surat }">
                    {{ nomor_surat }}
                </template>

                <template #item-mahasiswa_nim="{ user }">
                    {{ (user?.nama || '-') + ' | ' + (user?.nim || '-') }}
                </template>

                <template #item-program_studi="{ user }">
                    {{ programStudiMapping[user?.program_studi]?.label || '-' }}
                </template>

                <template #item-tanggal_dikirim="{ tanggal_dikirim }">
                    {{ tanggal_dikirim ? formatTanggal(tanggal_dikirim) : '-' }}
                </template>

                <template #item-tanggal_selesai="{ tanggal_selesai }">
                    {{ tanggal_selesai ? formatTanggal(tanggal_selesai) : '-' }}
                </template>

                <template #item-status="{ status }">
                    <div v-if="statusMappingSidangNol[status]"
                        :class="`inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-${statusMappingSidangNol[status].color}-100 text-${statusMappingSidangNol[status].color}-600`">
                        <component :is="statusMappingSidangNol[status].icon" :class="`w-3 h-3 mr-1`"
                            :color="`${statusMappingSidangNol[status].colorIcon}`" />
                        {{ statusMappingSidangNol[status].label }}
                    </div>
                </template>

                <template #item-id="{ status, id, user }">
                    <div class="flex items-center gap-2">
                        <!-- Tombol Detail -->
                        <Button
                            class="w-6 h-6 text-blue-500 hover:text-white hover:bg-blue-500 bg-white outline outline-1 outline-blue-500 p-1 rounded"
                            title="Detail" @click="onDetail(id)">
                            <Eye class="w-4 h-4" />
                        </Button>

                        <!-- Tombol Periksa Berkas -->
                        <Button v-if="props.auth.user.role_id !== 1 && status >= 1"
                            class="w-6 h-6 text-green-500 hover:text-white hover:bg-green-500 bg-white outline outline-1 outline-green-500 p-1 rounded"
                            title="Periksa Berkas" @click="onAjuan(id)">
                            <Check class="w-4 h-4" />
                        </Button>

                        <!-- Tombol Kirim Surat -->
                        <Button v-if="status === 0"
                            class="w-6 h-6 text-blue-500 hover:text-white hover:bg-blue-500 bg-white outline outline-1 outline-blue-500 p-1 rounded"
                            @click="onKirim(id)" title="Kirim Surat">
                            <Send class="w-4 h-4" />
                        </Button>

                        <!-- Tombol Reset Status -->
                        <Button v-if="status === 3"
                            class="w-6 h-6 text-blue-500 hover:text-white hover:bg-blue-500 bg-white outline outline-1 outline-blue-500 p-1 rounded"
                            @click="onReset(id)" title="Reset Status">
                            <RotateCcw class="w-4 h-4" />
                        </Button>

                        <!-- Tombol Edit -->
                        <Button
                            v-if="(props.auth.user.role_id === 1 && status === 0) || (props.auth.user.role_id !== 1)"
                            class="w-6 h-6 text-yellow-500 hover:text-white hover:bg-yellow-500 bg-white outline outline-1 outline-yellow-500 p-1 rounded"
                            @click="onEdit(id)" title="Edit Data">
                            <Pencil class="w-4 h-4" />
                        </Button>

                        <!-- Tombol Hapus -->
                        <Button
                            v-if="(props.auth.user.role_id === 1 && status === 0) || (props.auth.user.role_id !== 1)"
                            class="w-6 h-6 text-red-500 hover:text-white hover:bg-red-500 bg-white outline outline-1 outline-red-500 p-1 rounded"
                            @click="onDelete(id)" title="Hapus Data">
                            <Trash2 class="w-4 h-4" />
                        </Button>

                        <!-- Tombol Download Surat Balasan -->
                        <Button v-if="status === 2"
                            class="w-6 h-6 text-red-500 hover:text-white hover:bg-red-500 bg-white outline outline-1 outline-red-500 p-1 rounded"
                            @click="onDownloadSuratSidangNol(id, user?.nama)" title="Download Surat Balasan">
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
