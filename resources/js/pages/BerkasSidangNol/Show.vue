<script setup lang="ts">
import Button from '@/components/ui/button/Button.vue';
import AppLayout from '@/layouts/AppLayout.vue';
import { BreadcrumbItem } from '@/types';
import { onMounted, reactive, ref } from 'vue';
import programStudiMapping from '@/utils/programStudiMapping'
import vueFilePond from 'vue-filepond'
import FilePondPluginPdfPreview from 'filepond-plugin-pdf-preview'
import Modal from '@/components/ui/modal/Modal.vue';


import 'filepond-plugin-pdf-preview/dist/filepond-plugin-pdf-preview.css'
import { router, useForm } from '@inertiajs/vue3';


const FilePond = vueFilePond(
    FilePondPluginPdfPreview,
)

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Berkas Sidang Nol',
        href: '/berkas-sidang-nol',
    },
    {
        title: 'Lihat',
        href: '/berkas-sidang-nol',
    },
]

const show = ref(false);
const props = defineProps<{
    auth: any,
    berkasSidangNol?: any,
    nomorSuratTerakhir: number,
}>()
const showModalDokumen = ref(false)

const form = useForm({
    nomor_surat: props.berkasSidangNol?.nomor_surat ?? props.nomorSuratTerakhir,
    status: props.berkasSidangNol?.status ?? 1,
    pegawai: '',
})


const filepondFiles = reactive({
    dokumen_hasil_studi: [],
    dokumen_data_diri: [],
    dokumen_pddikti_ukt: [],
    dokumen_ruangbaca_laboratorium_pkkmb_skpi: [],
    dokumen_office_toefl: [],
    dokumen_tambahan: [],
});

function toBack() {
    router.visit(route('berkas-sidang-nol.index'))
}

const textModalPreview = ref('');

const openFileInNewTab = (field: string) => {
    const path = props.berkasSidangNol?.[field];

    if (path) {
        const fileUrl = `${route('berkas-sidang-nol.download-upload')}?path=secure_storage/${path}`;
        window.open(fileUrl, '_blank', 'noopener');
    }
};

onMounted(() => {
    setTimeout(() => {
        show.value = true;
    }, 50);
});

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
</script>

<template>
    <Modal :show="showModalDokumen" @close="showModalDokumen = false">
        <template #header>
            <h2 class="text-lg font-semibold">Preview {{ textModalPreview }}</h2>
        </template>

        <!-- Konten utama -->
        <FilePond name="dokumen_hasil_studi"
            :files="filepondFiles.dokumen_hasil_studi ? [...filepondFiles.dokumen_hasil_studi] : []"
            :allow-multiple="false" :allow-revert="false" :allow-remove="false" :allow-process="false"
            :allow-replace="false" :allow-browse="false" :accepted-file-types="['application/pdf']"
            :pdf-preview-height="380" :disabled="true" />

        <template #footer>
            <Button @click="showModalDokumen = false"
                class="border border-orange-400 text-orange-500 hover:bg-orange-100 bg-white-500">
                Kembali
            </Button>
        </template>
    </Modal>

    <!-- Main -->
    <AppLayout :breadcrumbs="breadcrumbs">

        <Transition name="slide-up" mode="out-in">
            <div v-if="show" class="p-4 space-y-4">
                <h2 class="text-xl font-semibold">Ajuan Surat Sidang Nol</h2>
                <table class="w-full table-auto border border-gray-300 mb-0">
                    <tbody class="text-sm">
                        <tr class="border-b">
                            <td class="py-4 px-4 font-medium text-gray-600 border border-gray-200">Nomor Surat</td>
                            <td class="py-4 px-4 border border-gray-200">{{ props.berkasSidangNol.nomor_surat || '-' }}
                            </td>
                        </tr>
                        <tr class="border-b">
                            <td class="py-4 px-4 font-medium text-gray-600 border border-gray-200">Nama Mahasiswa</td>
                            <td class="py-4 px-4 border border-gray-200">{{ props.berkasSidangNol.user?.nama || '-' }}
                            </td>
                        </tr>
                        <tr class="border-b">
                            <td class="py-4 px-4 font-medium text-gray-600 border border-gray-200">NIM</td>
                            <td class="py-4 px-4 border border-gray-200">{{ props.berkasSidangNol.user?.nim || '-' }}
                            </td>
                        </tr>
                        <tr class="border-b">
                            <td class="py-4 px-4 font-medium text-gray-600 border border-gray-200">Program Studi</td>
                            <td class="py-4 px-4 border border-gray-200">
                                {{ programStudiMapping[props.berkasSidangNol.user?.program_studi]?.label || '-' }}
                            </td>
                        </tr>
                        <tr class="border-b">
                            <td class="py-4 px-4 font-medium text-gray-600 border border-gray-200">Tanggal Dikirim</td>
                            <td class="py-4 px-4 border border-gray-200">
                                {{ props.berkasSidangNol.tanggal_dikirim ?
                                    formatTanggal(props.berkasSidangNol.tanggal_dikirim) : '-' }}
                            </td>
                        </tr>
                        <tr class="border-b">
                            <td class="py-4 px-4 font-medium text-gray-600 border border-gray-200">Tanggal Selesai</td>
                            <td class="py-4 px-4 border border-gray-200">
                                {{ props.berkasSidangNol.tanggal_selesai ?
                                    formatTanggal(props.berkasSidangNol.tanggal_selesai) : '-' }}
                            </td>
                        </tr>
                        <tr class="border-b">
                            <td class="py-4 px-4 font-medium text-gray-600 border border-gray-200">Status</td>
                            <td class="py-4 px-4 border border-gray-200">
                                {{ programStudiMapping[props.berkasSidangNol.user.program_studi]?.label || 'Status tidak dikenal' }}
                            </td>
                        </tr>
                        <tr v-for="(label, fileField) in {
                            dokumen_hasil_studi: 'Dokumen Hasil Studi',
                            dokumen_data_diri: 'Dokumen Data Diri',
                            dokumen_pddikti_ukt: 'Dokumen PDDIKTI & UKT',
                            dokumen_ruangbaca_laboratorium_pkkmb_skpi: 'Dokumen Bebas Ruang Baca, Lab, PKKMB & SKPI',
                            dokumen_office_toefl: 'Dokumen Office & TOEFL',
                            dokumen_tambahan: 'Dokumen Tambahan (Opsional)'
                        }" :key="fileField" class="border-b">
                            <td class="py-4 px-4 font-medium text-gray-600 border border-gray-200">{{ label }}</td>
                            <td class="py-4 px-4 border border-gray-200">
                                <template v-if="props.berkasSidangNol[fileField]">
                                    <a href="javascript:void(0)" @click="openFileInNewTab(fileField)"
                                        class="text-blue-600 hover:underline">
                                        Lihat Dokumen
                                    </a>
                                </template>
                                <template v-else>-</template>
                            </td>
                        </tr>
                        <tr>
                            <td class="py-4 px-4 font-medium text-gray-600 border border-gray-200 align-top">Catatan
                            </td>
                            <td class="py-4 px-4 border border-gray-200">
                                <div v-if="props.berkasSidangNol.notes && props.berkasSidangNol.notes.length">
                                    <table class="text-sm w-full border border-gray-300">
                                        <thead>
                                            <tr class="bg-gray-100">
                                                <th class="border px-3 py-2 text-left">Oleh</th>
                                                <th class="border px-3 py-2 text-left">Catatan</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr v-for="note in props.berkasSidangNol.notes" :key="note.id">
                                                <td class="border px-3 py-2">{{ note.user?.nama || 'Tidak diketahui' }}
                                                </td>
                                                <td class="border px-3 py-2">{{ note.pesan }}</td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                                <div v-else>
                                    <table class="text-sm w-full border border-gray-300">
                                        <thead>
                                            <tr class="bg-gray-100">
                                                <th class="border px-3 py-2 text-left">Oleh</th>
                                                <th class="border px-3 py-2 text-left">Catatan</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr>
                                                <td class="border px-3 py-2"> - </td>
                                                <td class="border px-3 py-2"> - </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>

                <div class="w-full flex justify-end gap-2 mt-4 ">
                    <Button type="button" @click="toBack"
                        class="border border-orange-400 text-orange-500 hover:bg-orange-100 bg-white-500">
                        <span>Kembali</span>
                    </Button>
                </div>
            </div>
        </Transition>
    </AppLayout>
</template>