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

const headers = [
    { text: "Nama", value: "nama", width: 480 },
    { text: "Email", value: "email", width: 240 },
    { text: "Jabatan", value: "role", width: 160 },
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

    const { data } = await axios.get(route('users.index'), {
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


const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Pengguna',
        href: '/users',
    },
]

function onSearch() {
    serverOptions.value.page = 1 // Reset ke halaman 1 saat search
}

function onCreate() {
    router.get(route('users.create'))
}

function onDetail(id: number) {
    router.visit(route('users.show', id))
}

function onEdit(id: number) {
    router.visit(route('users.edit', id))
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
        buttonsStyling: false
    }).then((result) => {
        if (result.isConfirmed) {
            router.delete(route('users.destroy', id), {
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

</script>

<template>

    <Head title="Users" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="p-4 space-y-4">
            <div class="container mx-auto flex items-center justify-between mb-4">
                <Heading title="Manajemen Pengguna" description="Daftar pengguna yang telah terdaftar dalam sistem"
                    class="!mb-0" />
                <Button
                    class="rounded bg-blue-500 px-4 py-2 text-white hover:bg-blue-600 transition flex items-center gap-1"
                    @click="onCreate">
                    <Plus class="h-4 w-4" />
                    <span>Tambah</span>
                </Button>
            </div>

            <div class="container mx-auto flex items-center justify-end gap-4 mb-4">
                <span class="text-sm font-medium whitespace-nowrap">Cari</span>
                <input v-model="search" @input="onSearch" type="text" placeholder="Cari nama atau email"
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

                <template #item-email="{ email }">
                    {{ email || '-' }}
                </template>

                <template #item-role="{ role }">
                    {{ role?.nama || '-' }}
                </template>
                <template #item-id="{ id }">
                    <div class="flex items-center gap-2">
                        <Button
                            class="w-6 h-6 text-blue-500 hover:text-white hover:bg-blue-500 bg-white outline outline-1 outline-blue-500 p-1 rounded"
                            title="Detail" @click="onDetail(id)">
                            <Eye class="w-4 h-4" />
                        </Button>

                        <Button
                            class="group w-6 h-6 bg-white text-yellow-500 outline outline-1 outline-yellow-500 p-1 rounded hover:bg-yellow-500 hover:outline-yellow-500"
                            title="Edit" @click="onEdit(id)">
                            <Pencil class="w-4 h-4 group-hover:text-white transition-colors duration-200" />
                        </Button>

                        <!-- <Button
                            class="group w-6 h-6 bg-white text-orange-500 outline outline-1 outline-orange-500 p-1 rounded hover:bg-orange-500 hover:outline-orange-500"
                            title="Reset Password" @click="onResetPassword(id)">
                            <KeyRound class="w-4 h-4 group-hover:text-white transition-colors duration-200" />
                        </Button> -->

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
