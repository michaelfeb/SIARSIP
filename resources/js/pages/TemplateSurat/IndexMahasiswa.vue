<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue'
import { Head, router, ServerOptions } from '@inertiajs/vue3'
import EasyDataTable, { Item } from 'vue3-easy-data-table'
import { type BreadcrumbItem } from '@/types'
import 'vue3-easy-data-table/dist/style.css'
import Heading from '@/components/Heading.vue'
import { ref, computed, watch } from 'vue'
import Button from '@/components/ui/button/Button.vue'
import { Eye, Pencil, Trash2, Plus, Download } from 'lucide-vue-next'
import axios from 'axios'
import Swal from 'sweetalert2'

const headers = [
    { text: "Nama", value: "nama" },
    { text: "Jenis Surat", value: "jenis_surat" },
    { text: "Deskripsi", value: "deskripsi" },
    { text: "Tanggal terbit", value: "tanggal_publish" },
    { text: "Aksi", value: "id", sortable: false },
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

    const { data } = await axios.get(route('template-surat.index'), {
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
        title: 'Template Surat',
        href: '/template-surat',
    },
]

function onSearch() {
    serverOptions.value.page = 1 // Reset ke halaman 1 saat search
}

function onDetail(id: number) {
    router.visit(route('template-surat.show', id))
}

function onDownload(id: number) {
    window.open(route('template-surat.download', id), '_blank');
}

</script>

<template>

    <Head title="Template Surat" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="p-4 space-y-4">
            <div class="container mx-auto flex items-center justify-between mb-4">
                <Heading title="Template Surat" description="Daftar template surat yang telah terdaftar dalam sistem"
                    class="!mb-0" />
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

                <template #item-jenis_surat="{ jenis_surat }">
                    {{ jenis_surat.nama || '-' }}
                </template>

                <template #item-status="{ status }">
                    <span :class="[
                        'text-xs inline-flex items-center gap-1 px-3 py-1 rounded-full font-medium',
                        status == 1 ? 'bg-green-100 text-green-600' : 'bg-gray-200 text-gray-600'
                    ]">
                        {{ status == 1 ? 'Aktif' : 'Tidak Aktif' }}
                    </span>
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
                            class="w-6 h-6 text-orange-500 hover:text-white hover:bg-orange-500 bg-white outline outline-1 outline-orange-500 p-1 rounded"
                            title="Download" @click="onDownload(id)">
                            <Download class="w-4 h-4"/>
                        </Button>
                    </div>
                </template>
            </EasyDataTable>
        </div>
    </AppLayout>
</template>
