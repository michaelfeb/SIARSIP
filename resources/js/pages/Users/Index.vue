<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue'
import { Head, router, usePage } from '@inertiajs/vue3'
import EasyDataTable from 'vue3-easy-data-table'
import { type BreadcrumbItem } from '@/types'
import 'vue3-easy-data-table/dist/style.css'
import Heading from '@/components/Heading.vue'
import { ref } from 'vue'
import Button from '@/components/ui/button/Button.vue'
import { Eye, Pencil, Trash2, Plus } from 'lucide-vue-next'


const props = defineProps<{
    users: {
        data: Array<{ id: number; nama: string; email: string }>
        current_page: number
        last_page: number
        per_page: number
        total: number
    }
}>()

const headers = [
    { text: "Nama", value: "nama" },
    { text: "Email", value: "email" },
    { text: "Aksi", value: "aksi", sortable: false },
]


const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Users',
        href: '/users',
    },
]

const search = ref('')

function onSearch() {
    router.get(route('users.index'), { search: search.value }, {
        preserveScroll: true,
        preserveState: true,
    })
}

function onPageChange(page: number) {
    router.get(route('users.index'), { page }, {
        preserveScroll: true,
        preserveState: true,
    })
}

const customPaginationInfo = (firstItem: number, lastItem: number, total: number): string => {
  return `Menampilkan ${firstItem} - ${lastItem} dari total ${total} data`
}

function toCreate() {
  router.get(route('users.create'))
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
                    @click="toCreate">
                    <Plus class="h-4 w-4" />
                    <span>Tambah</span>
                </Button>
            </div>

            <div class="container mx-auto flex items-center justify-end gap-4 mb-4">
                <span class="text-sm font-medium whitespace-nowrap">Cari</span>
                <input v-model="search" @input="onSearch" type="text" placeholder="Cari nama atau email"
                    class="w-50 h-8 text-sm rounded border border-gray-300 px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500" />
            </div>
            <EasyDataTable :headers="headers" :items="props.users.data" :server-items-length="props.users.total"
                :rows-per-page="props.users.per_page" :page="props.users.current_page" :loading="false" show-index
                :pagination-info="customPaginationInfo" rows-per-page-message="Baris data" @update:page="onPageChange">
                <template #loading>
                    <img src="https://i.pinimg.com/originals/94/fd/2b/94fd2bf50097ade743220761f41693d5.gif"
                        style="width: 100px; height: 80px;" />
                </template>
                <template #header-index>
                    No
                </template>
                <template #item-aksi="{ item }">
                    <div class="flex items-center gap-2">
                        <Button
                            class="group w-6 h-6 bg-white text-blue-500 outline outline-1 outline-blue-500 p-1 rounded hover:bg-blue-500 hover:outline-blue-500"
                            title="Detail" @click="onDetail(item)">
                            <Eye class="w-4 h-4 group-hover:text-white transition-colors duration-200" />
                        </Button>

                        <!-- Button Edit -->
                        <Button
                            class="group w-6 h-6 bg-white text-yellow-500 outline outline-1 outline-yellow-500 p-1 rounded hover:bg-yellow-500 hover:outline-yellow-500"
                            title="Edit" @click="onEdit(item)">
                            <Pencil class="w-4 h-4 group-hover:text-white transition-colors duration-200" />
                        </Button>

                        <!-- Button Hapus -->
                        <Button
                            class="group w-6 h-6 bg-white text-red-500 outline outline-1 outline-red-500 p-1 rounded hover:bg-red-500 hover:outline-red-500"
                            title="Hapus" @click="onDelete(item)">
                            <Trash2 class="w-4 h-4 group-hover:text-white transition-colors duration-200" />
                        </Button>
                    </div>
                </template>
            </EasyDataTable>
        </div>
    </AppLayout>
</template>
