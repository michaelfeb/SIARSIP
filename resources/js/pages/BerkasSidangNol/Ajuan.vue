<script setup lang="ts">
import Button from '@/components/ui/button/Button.vue';
import AppLayout from '@/layouts/AppLayout.vue';
import InputError from '@/components/InputError.vue'
import { BreadcrumbItem } from '@/types';
import { onMounted, reactive, ref } from 'vue';
import programStudiMapping from '@/utils/programStudiMapping'
import vueFilePond from 'vue-filepond'
import FilePondPluginPdfPreview from 'filepond-plugin-pdf-preview'
import Modal from '@/components/ui/modal/Modal.vue';
import Swal from 'sweetalert2';
import daftarPenandatangan from '@/utils/penandatangan';


import 'filepond-plugin-pdf-preview/dist/filepond-plugin-pdf-preview.css'
import { router, useForm } from '@inertiajs/vue3';
import { onlyAllowNumbers } from '@/utils/inputValidatior';


const FilePond = vueFilePond(
    FilePondPluginPdfPreview,
)

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Berkas Sidang Nol',
        href: '/berkas-sidang-nol',
    },
    {
        title: 'Ajuan',
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
const showModalKeputusan = ref(false)
const today = new Date()
const currentYear = today.getFullYear()

const form = useForm({
    nomor_surat: props.berkasSidangNol?.nomor_surat ? props.berkasSidangNol?.nomor_surat.split('SN-')[1]?.split('/')[0] : (props.nomorSuratTerakhir + 1),
    status: props.berkasSidangNol?.status ?? 1,
    note: '',
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

const textModalPreview = ref('');

const onKeputusan = () => {
    showModalKeputusan.value = true;
};

function toBack() {
    router.visit(route('berkas-sidang-nol.index'))
}

const openModalWithFile = (field) => {
    showModalDokumen.value = true;

    filepondFiles[field] = [];

    if (props.berkasSidangNol[field]) {
        filepondFiles[field] = [
            {
                source: `${route('berkas-sidang-nol.download-upload')}?path=secure_storage/${props.berkasSidangNol[field]}`,
                options: {
                    type: 'remote',
                }
            }
        ];
    }
};

const terimaKeputusan = () => {
    form.status = 2
    submit()
}

const tolakKeputusan = () => {
    form.status = 3
    submit()
}

const submit = async () => {
    const url = route('berkas-sidang-nol.keputusan', props.berkasSidangNol.id)
    await form.submit('post', url, {
        forceFormData: true,
        method: 'post',
        onSuccess: () => {
            Swal.fire({
                icon: 'success',
                title: 'Berhasil!',
                text: 'Keputusan telah ditambahkan',
                confirmButtonText: 'OK',
                customClass: {
                    confirmButton: 'swal-confirm-button',
                },
            }).then(() => {
                showModalKeputusan.value = false;
                router.visit(route('berkas-sidang-nol.index'))
            })
        },
        onError: () => {
            Swal.fire({
                icon: 'error',
                title: 'Gagal!',
                text: 'Gagal memperbarui data!',
                confirmButtonText: 'OK',
                customClass: {
                    confirmButton: 'swal-confirm-button',
                },
            })
        }
    })
}

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

const fileUrl = (field: any) => {
    if (!props.berkasSidangNol[field]) return '#';
    return `${route('berkas-sidang-nol.download-upload')}?path=secure_storage/${props.berkasSidangNol[field]}`;
};


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

    <!-- On Keputusan -->
    <Modal :show="showModalKeputusan" @close="showModalKeputusan = false">
        <template #header>
            <h2 class="text-lg font-semibold">Tambah Keputusan</h2>
        </template>

        <div v-if="Object.keys(form.errors).length > 0" class="mb-4 rounded bg-red-100 p-4 text-red-600">
            <ul class="list-disc pl-5 space-y-1 text-sm">
                <li v-for="(error, key) in form.errors" :key="key">{{ error }}</li>
            </ul>
        </div>

        <form @submit.prevent="submit" class="space-y-6">
            <div class="space-y-2">
                <label for="nomor_surat" class="block text-sm font-medium text-gray-700">Nomor Surat<span
                        class="text-red-500"> *</span></label>
                <div class="mt-1 flex rounded-md shadow-sm border border-gray-300 overflow-hidden">
                    <span
                        class="inline-flex items-center px-3 bg-gray-100 text-gray-600 text-sm border-r border-gray-300">
                        SN-
                    </span>
                    <input v-model="form.nomor_surat" type="text" id="nomor_surat" @keypress="onlyAllowNumbers"
                        class="flex-1 block w-full text-sm px-3 py-2 focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                        placeholder="Nomor" />
                    <span
                        class="inline-flex items-center px-3 bg-gray-100 text-gray-600 text-sm border-l border-gray-300">
                        /UN8.1.28/DV.01/{{ currentYear }}
                    </span>
                </div>
                <InputError :message="form.errors.nomor_surat" />

                <label for="note" class="block text-sm font-medium text-gray-700">Catatan</label>
                <textarea v-model="form.note" placeholder="Opsional. Tulis catatan, saran atau revisi di sini"
                    class="w-full border border-gray-300 rounded px-3 py-2 text-sm" />
                <InputError :message="form.errors.note" />

                <label for="pegawai" class="block text-sm font-medium text-gray-700">Pilih Penandatangan<span
                        class="text-red-500"> *</span></label>
                <div class="relative">
                    <select id="jenis_surat_id" v-model="form.pegawai"
                        class="text-sm font-medium w-full rounded border border-gray-300 px-3 py-2 pr-10 focus:outline-none focus:ring-2 focus:ring-blue-500 appearance-none">
                        <option value="">Pilih pegawai</option>
                        <option v-for="item in daftarPenandatangan" :key="item.value" :value="item.value">
                            {{ item.label }}
                        </option>
                    </select>
                    <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-3 text-gray-500">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
                        </svg>
                    </div>
                </div>
                <InputError :message="form.errors.pegawai" />
            </div>
        </form>

        <template #footer>
            <Button @click="showModalKeputusan = false"
                class="border border-orange-400 text-orange-500 hover:bg-orange-100 bg-white-500">
                Kembali
            </Button>

            <Button @click="tolakKeputusan" class="bg-red-500 text-white px-4 py-2 rounded hover:bg-red-600">
                Tolak
            </Button>

            <Button @click="terimaKeputusan" class="bg-green-600 hover:bg-green-700 text-white">
                Terima & Generate Surat
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
                                {{ programStudiMapping[props.berkasSidangNol.user.program_studi]?.label || "Program Studi tidak dikenal" }}
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
                                    <a :href="fileUrl(fileField)" target="_blank" class="text-blue-600 hover:underline">
                                        Lihat Dokumen
                                    </a>
                                </template>
                                <template v-else>-</template>
                            </td>
                        </tr>
                    </tbody>
                </table>

                <div class="w-full flex justify-end gap-2 mt-4 ">
                    <Button type="button" @click="toBack"
                        class="border border-orange-400 text-orange-500 hover:bg-orange-100 bg-white-500">
                        <span>Kembali</span>
                    </Button>

                    <Button type="button" @click="onKeputusan"
                        class="border border-blue-400 text-blue-500 hover:bg-blue-100 bg-white-500">
                        <span>Tambah Keputusan</span>
                    </Button>
                </div>
            </div>
        </Transition>
    </AppLayout>
</template>