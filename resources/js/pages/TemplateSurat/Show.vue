<script setup lang="ts">
import Button from '@/components/ui/button/Button.vue';
import AppLayout from '@/layouts/AppLayout.vue';
import { BreadcrumbItem } from '@/types';
import { usePage } from '@inertiajs/vue3'
import { router } from '@inertiajs/vue3'
import { onMounted, ref } from 'vue';

const props = defineProps<{
    templateSurat: any,
}>()
const show = ref(false)

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Template Surat',
        href: '/template-surat',
    },
    {
        title: 'Detail',
        href: '/template-surat',
    },
]

function toBack() {
    router.visit(route('template-surat.index'))
}

onMounted(() => {
    setTimeout(() => {
        show.value = true
    }, 50)
})
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <Transition name="slide-up" mode="out-in">
            <div v-if="show" class="p-4 space-y-4">
                <h2 class="text-xl font-semibold">Detail User</h2>
                <table class="w-full table-auto border border-gray-300 mb-0">
                    <tbody class="text-sm">
                        <tr class="border-b">
                            <td class="py-4 px-4 font-medium text-gray-600 border border-gray-200">Nama Template</td>
                            <td class="py-4 px-4 border border-gray-200">{{ props.templateSurat.nama || '-' }}</td>
                        </tr>
                        <tr class="border-b">
                            <td class="py-4 px-4 font-medium text-gray-600 border border-gray-200">Jenis Surat</td>
                            <td class="py-4 px-4 border border-gray-200">{{ props.templateSurat.jenis_surat?.nama || '-' }}</td>
                        </tr>
                        <tr class="border-b">
                            <td class="py-4 px-4 font-medium text-gray-600 border border-gray-200">Deskripsi</td>
                            <td class="py-4 px-4 border border-gray-200">{{ props.templateSurat.deskripsi || '-' }}</td>
                        </tr>
                        <tr class="border-b">
                            <td class="py-4 px-4 font-medium text-gray-600 border border-gray-200">Status</td>
                            <td class="py-4 px-4 border border-gray-200">
                                <span :class="props.templateSurat.status ? 'text-green-600' : 'text-red-600'">
                                    {{ props.templateSurat.status ? 'Aktif' : 'Tidak Aktif' }}
                                </span>
                            </td>
                        </tr>
                        <tr class="border-b">
                            <td class="py-4 px-4 font-medium text-gray-600 border border-gray-200">Tanggal Publish</td>
                            <td class="py-4 px-4 border border-gray-200">{{ props.templateSurat.tanggal_publish || '-' }}</td>
                        </tr>
                        <tr>
                            <td class="py-4 px-4 font-medium text-gray-600 border border-gray-200">Dokumen</td>
                            <td class="py-4 px-4 border border-gray-200">
                                <a v-if="props.templateSurat.dokumen_path"
                                    :href="`/storage/${props.templateSurat.dokumen_path}`" target="_blank"
                                    class="text-blue-600 hover:underline">
                                    Lihat Dokumen
                                </a>
                                <span v-else>-</span>
                            </td>
                        </tr>
                    </tbody>
                </table>
                <div class="w-full flex justify-end">
                    <Button type="button" @click="toBack"
                        class="mt-4 border border-orange-400 text-orange-500 hover:bg-orange-100 bg-white-500">
                        <span>Kembali</span>
                    </Button>
                </div>
            </div>
        </Transition>
    </AppLayout>
</template>
