<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue'
import { Head, router, ServerOptions } from '@inertiajs/vue3'
import EasyDataTable, { Item } from 'vue3-easy-data-table'
import { type BreadcrumbItem } from '@/types'
import 'vue3-easy-data-table/dist/style.css'
import Heading from '@/components/Heading.vue'
import { ref, computed, watch } from 'vue'
import Button from '@/components/ui/button/Button.vue'
import { Eye, Pencil, Trash2, Plus, KeyRound } from 'lucide-vue-next'
import axios from 'axios'
import Swal from 'sweetalert2'
import VueToggles from "vue-toggles";

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Carousel',
        href: '/carousel',
    },
]

const headers = [
    { text: "Nama", value: "nama", },
    { text: "Tanggal dipublish", value: "tanggal_publish", },
    { text: "Status", value: "status", width: 240 },
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

    const { data } = await axios.get(route('carousel.index'), {
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
    router.get(route('carousel.create'))
}

function onEdit(id: number) {
    router.visit(route('carousel.edit', id))
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
            axios.delete(route('carousel.destroy', id))
                .then(response => {
                    Swal.fire({
                        title: 'Terhapus!',
                        text: response.data.message,
                        icon: 'success',
                        confirmButtonText: 'OK',
                        customClass: {
                            confirmButton: 'swal-confirm-button',
                        },
                    });
                    loadFromServer();
                })
                .catch(error => {
                    Swal.fire({
                        title: 'Gagal!',
                        text: error.response.data.message,
                        icon: 'error',
                        confirmButtonText: 'OK',
                        customClass: {
                            confirmButton: 'swal-confirm-button',
                        },
                    });
                });
        }
    });
}

const toggleStatus = async (id: number) => {
    try {
        await axios.put(route('carousel.toggle', id))
        await loadFromServer()
    } catch (error) {
        console.error('Gagal mengubah status', error)
    }
}

</script>

<template>

    <Head title="Carousel" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="p-4 space-y-4">
            <div class="container mx-auto flex items-center justify-between mb-4">
                <Heading title="Manajemen Carousel"
                    description="Daftar carousel yang telah terdaftar dalam sistem" class="!mb-0" />

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
                :loading="loading" :headers="headers" :items="items" show-index>
                <template #loading>
                    <img src="https://i.pinimg.com/originals/94/fd/2b/94fd2bf50097ade743220761f41693d5.gif"
                        style="width: 100px; height: 80px;" />
                </template>
                <template #header-index>
                    No
                </template>

                <template #item-nama="{ nama }">
                    {{ nama || '-' }}
                </template>

                <template #item-tanggal_publish="{ tanggal_publish }">
                    {{ tanggal_publish ? new Date(tanggal_publish).toLocaleDateString('id-ID', {
                        day: '2-digit', month:
                            'long', year: 'numeric' }) : '-' }}
                </template>

                <template #item-status="{ id, status }">
                    <VueToggles :value="status" :height="30" :width="70" checkedText="Aktif" uncheckedText="Off"
                        checkedBg="#22c55e" uncheckedBg="#d1d5db" @click="toggleStatus(id)" />
                </template>

                <template #item-id="{ id }">
                    <div class="flex items-center gap-2">
                        <Button
                            class="group w-6 h-6 bg-white text-yellow-500 outline outline-1 outline-yellow-500 p-1 rounded hover:bg-yellow-500 hover:outline-yellow-500"
                            title="Edit" @click="onEdit(id)">
                            <Pencil class="w-4 h-4 group-hover:text-white transition-colors duration-200" />
                        </Button>

                        <Button
                            class="group w-6 h-6 bg-white text-red-500 outline outline-1 outline-red-500 p-1 rounded hover:bg-red-500 hover:outline-red-500"
                            title="Hapus" @click="onDelete(id)">
                            <Trash2 class="w-4 h-4 group-hover:text-white transition-colors duration-200" />
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
