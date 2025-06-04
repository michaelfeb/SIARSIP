<script setup lang="ts">
import Button from '@/components/ui/button/Button.vue';
import AppLayout from '@/layouts/AppLayout.vue';
import { BreadcrumbItem } from '@/types';
import { router, useForm } from '@inertiajs/vue3'
import { onMounted, reactive, ref } from 'vue';
import statusMapping from '@/utils/statusMapping'
import Swal from 'sweetalert2';
import Modal from '@/components/ui/modal/Modal.vue';
import vueFilePond from 'vue-filepond'
import FilePondPluginPdfPreview from 'filepond-plugin-pdf-preview'

import FilePondPluginFileValidateType from 'filepond-plugin-file-validate-type'

import 'filepond/dist/filepond.min.css'
import 'filepond-plugin-pdf-preview/dist/filepond-plugin-pdf-preview.css'
import InputError from '@/components/InputError.vue';

const FilePond = vueFilePond(
    FilePondPluginPdfPreview,
    FilePondPluginFileValidateType,
)

const props = defineProps<{
    berkasPersuratan: any,
    jenisSurat: any,
    auth: any,
    mode: 'detail' | 'ajuan'
}>()


const form = useForm({
    nomor_surat: props.berkasPersuratan?.nomor_surat ? props.berkasPersuratan?.nomor_surat : '',
    note: '',
    status: '',
    berkas_tambahan: null,
    berkas_balasan: null,
})

const filepondFiles = reactive({
    berkas_tambahan: [],
    berkas_balasan: [],
});

function handleUpdateFilesTambahan(files) {
    form.berkas_tambahan = files.map(f => f.file);
}

const handleUpdateFilesBalasan = (files) => {
    const newFiles = files.filter(file => !file?.metadata?.locked);
    form.berkas_balasan = newFiles.map(file => file.file);
};


const show = ref(false)
const showModal = ref(false)
const showModalAkademik = ref(false)

function onKeputusan() {
    const currentStatus = props.berkasPersuratan.status
    const currentStage = parseInt(String(currentStatus).charAt(0))

    if (currentStage >= 6) {
        showModalAkademik.value = true
    } else {
        showModal.value = true
    }
}



function onCloseModal() {
    showModal.value = false
    showModalAkademik.value = false
}


async function submitKeputusan(action: 'terima' | 'tolak', mode: 'biasa' | 'disposisi') {
    form.errors = {}

    const currentStatus = props.berkasPersuratan.status
    const currentStage = parseInt(String(currentStatus).charAt(0))
    let statusBaru: number

    if (action === 'terima') {
        statusBaru = mode === 'disposisi' ? 61 : currentStage >= 7 ? 72 : currentStatus + 10
    } else {
        statusBaru = parseInt(`${currentStage}3`)
    }

    form.status = statusBaru.toString()
    const url = route('berkas-persuratan.keputusan', props.berkasPersuratan.id);

    await form.submit('post', url, {
        forceFormData: true,
        method: 'post',
        preserveScroll: true,
        forceFormData: true,
        onSuccess: () => {
            showModal.value = false
            Swal.fire({
                title: 'Berhasil!',
                text: 'Keputusan telah ditambahkan!',
                icon: 'success',
                confirmButtonText: 'OK',
                customClass: {
                    confirmButton: 'swal-confirm-button',
                },
            }).then(() => {
                router.get(route('berkas-persuratan.index'))
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

async function submitKeputusanAkademik(action: 'terima' | 'tolak') {
    form.errors = {}
    const currentStatus = props.berkasPersuratan.status
    const currentStage = parseInt(String(currentStatus).charAt(0))
    let statusBaru: number

    if (action === 'terima') {
        statusBaru = currentStatus + 10
    } else {
        statusBaru = parseInt(`${currentStage}3`)
    }

    form.status = statusBaru.toString()
    const url = route('berkas-persuratan.keputusan', props.berkasPersuratan.id);

    await form.submit('post', url, {
        forceFormData: true,
        method: 'post',
        preserveScroll: true,
        forceFormData: true,
        onSuccess: () => {
            showModal.value = false
            Swal.fire({
                title: 'Berhasil!',
                text: 'Keputusan telah ditambahkan!',
                icon: 'success',
                confirmButtonText: 'OK',
                customClass: {
                    confirmButton: 'swal-confirm-button',
                },
            }).then(() => {
                router.get(route('berkas-persuratan.index'))
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

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Berkas Persuratan',
        href: '/berkas-persuratan',
    },
    {
        title: 'Detail',
        href: '/berkas-persuratan',
    },
]

function toBack() {
    router.visit(route('berkas-persuratan.index'))
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

onMounted(() => {

    setTimeout(() => {
        show.value = true
    }, 50)

    if (typeof props.berkasPersuratan.berkas_balasan === 'string') {
        try {
            const parsed = JSON.parse(props.berkasPersuratan.berkas_balasan);

            if (Array.isArray(parsed)) {
                filepondFiles.berkas_balasan = parsed.map(fullPath => {
                    const filename = fullPath
                    return {
                        source: route('berkas-persuratan.download-upload', { filename }),
                        options: {
                            type: 'remote',
                            metadata: { locked: true },
                        },
                    };
                });
            }
        } catch (error) {
            console.error('Gagal parse JSON berkas_balasan:', error);
        }
    }
})

</script>

<template>
    <Modal :show="showModal" @close="onCloseModal">
        <!-- Header -->
        <template #header>
            <h2 class="text-lg font-semibold">Tanggapan</h2>
        </template>

        <div v-if="Object.keys(form.errors).length > 0" class="mb-4 rounded bg-red-100 p-4 text-red-600">
            <ul class="list-disc pl-5 space-y-1 text-sm">
                <li v-for="(error, key) in form.errors" :key="key">{{ error }}</li>
            </ul>
        </div>

        <form @submit.prevent="" class="space-y-4">
            <label for="note" class="block text-sm font-medium text-gray-700">Notes</label>
            <textarea v-model="form.note" placeholder="Opsional. Tulis catatan, saran atau revisi di sini"
                class="w-full border border-gray-300 rounded px-3 py-2 text-sm" />
            <InputError :message="form.errors.note" />

            <label class="block text-sm font-medium text-gray-700 mb-1">Berkas Disposisi
                <span class="text-[12px]">{{ "( Max 1 MB )" }}</span>
            </label>
            <FilePond name="berkas_tambahan"
                label-idle="Seret & lepas file atau <span class='filepond--label-action'>Telusuri</span>"
                :allow-multiple="true" :accepted-file-types="['application/pdf']"
                :files="filepondFiles.berkas_tambahan || []" @updatefiles="handleUpdateFilesTambahan" />
            <InputError :message="form.errors.berkas_tambahan" />
        </form>

        <template #footer>
            <div class="flex justify-end gap-2">
                <Button @click="submitKeputusan('tolak', 'biasa')" class="bg-red-500 text-white hover:bg-red-600">
                    Tolak
                </Button>
                <Button @click="submitKeputusan('terima', 'biasa')" class="bg-green-500 text-white hover:bg-green-600">
                    Terima
                </Button>
                <Button @click="submitKeputusan('terima', 'disposisi')"
                    class="bg-green-500 text-white hover:bg-green-600">
                    Terima dan Disposisi
                </Button>
            </div>
        </template>
    </Modal>

    <Modal :show="showModalAkademik" @close="onCloseModal">
        <!-- Header -->
        <template #header>
            <h2 class="text-lg font-semibold">Tanggapan</h2>
        </template>

        <div v-if="Object.keys(form.errors).length > 0" class="mb-4 rounded bg-red-100 p-4 text-red-600">
            <ul class="list-disc pl-5 space-y-1 text-sm">
                <li v-for="(error, key) in form.errors" :key="key">{{ error }}</li>
            </ul>
        </div>

        <form @submit.prevent="" class="space-y-4 max-h-[70vh] overflow-y-auto">
            <label for="nomor_surat" class="block text-sm font-medium text-gray-700">Nomor Surat</label>
            <input v-model="form.nomor_surat" placeholder="Contoh: B-1234/UN1/2025"
                class="w-full border border-gray-300 rounded px-3 py-2 text-sm" />
            <InputError :message="form.errors.nomor_surat" />

            <label for="note" class="block text-sm font-medium text-gray-700">Notes</label>
            <textarea v-model="form.note" placeholder="Opsional. Tulis catatan, saran atau revisi di sini"
                class="w-full border border-gray-300 rounded px-3 py-2 text-sm" />
            <InputError :message="form.errors.note" />

            <label class="block text-sm font-medium text-gray-700 mb-1">Berkas Balasan
                <span class="text-[12px]">{{ "( docx/pdf, Max 1 MB )" }}</span></label>
            <FilePond name="berkas_balasan"
                label-idle="Seret & lepas file atau <span class='filepond--label-action'>Telusuri</span>"
                :allow-multiple="true" :accepted-file-types="[
                    'application/msword',
                    'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                    'application/pdf'
                ]"
                :files="filepondFiles.berkas_balasan || []" @updatefiles="handleUpdateFilesBalasan" />
            <InputError :message="form.errors.berkas_balasan" />


        </form>

        <template #footer>
            <div class="flex justify-end gap-2">
                <Button @click="submitKeputusanAkademik('tolak')" class="bg-red-500 text-white hover:bg-red-600">
                    Tolak
                </Button>
                <Button @click="submitKeputusanAkademik('terima')" class="bg-green-500 text-white hover:bg-green-600">
                    Terima
                </Button>
            </div>
        </template>
    </Modal>

    <!-- Main -->
    <AppLayout :breadcrumbs="breadcrumbs">
        <Transition name="slide-up" mode="out-in">
            <div v-if="show" class="p-4 space-y-4">

                <h2 class="text-xl font-semibold">Detail Berkas Persuratan</h2>
                <table class="w-full table-auto border border-gray-300 mb-0">
                    <tbody class="text-sm">
                        <tr class="border-b">
                            <td class="py-4 px-4 font-medium text-gray-600 border border-gray-200">Nomor Surat</td>
                            <td class="py-4 px-4 border border-gray-200">
                                {{ props.berkasPersuratan.nomor_surat || "Belum ada" }}
                            </td>
                        </tr>
                        <tr class="border-b">
                            <td class="py-4 px-4 font-medium text-gray-600 border border-gray-200">Mahasiswa</td>
                            <td class="py-4 px-4 border border-gray-200">
                                {{ props.berkasPersuratan.user?.nama || '-' }} ({{ props.berkasPersuratan.user?.nim ||
                                    '-' }})
                            </td>
                        </tr>
                        <tr class="border-b">
                            <td class="py-4 px-4 font-medium text-gray-600 border border-gray-200">Jenis Surat</td>
                            <td class="py-4 px-4 border border-gray-200">{{ props.berkasPersuratan.jenis_surat?.nama ||
                                '-' }}</td>
                        </tr>
                        <tr class="border-b">
                            <td class="py-4 px-4 font-medium text-gray-600 border border-gray-200">Keterangan</td>
                            <td class="py-4 px-4 border border-gray-200"
                                v-html="props.berkasPersuratan.keterangan ? props.berkasPersuratan.keterangan.replace(/\n/g, '<br>') : '-'">
                            </td>
                        </tr>
                        <tr class="border-b">
                            <td class="py-4 px-4 font-medium text-gray-600 border border-gray-200">Tanggal Dikirim</td>
                            <td class="py-4 px-4 border border-gray-200">
                                {{ formatTanggal(props.berkasPersuratan.tanggal_dikirim) || '-' }}
                            </td>
                        </tr>
                        <tr class="border-b">
                            <td class="py-4 px-4 font-medium text-gray-600 border border-gray-200">Status</td>
                            <td class="py-4 px-4 border border-gray-200">
                                <div v-if="statusMapping[props.berkasPersuratan.status]"
                                    :class="`inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-${statusMapping[props.berkasPersuratan.status].color}-100 text-${statusMapping[props.berkasPersuratan.status].color}-600`">
                                    <component :is="statusMapping[props.berkasPersuratan.status].icon"
                                        class="w-4 h-4 mr-1"
                                        :color="`${statusMapping[props.berkasPersuratan.status].colorIcon}`" />
                                    {{ statusMapping[props.berkasPersuratan.status].label }}
                                </div>
                            </td>
                        </tr>
                        <tr class="border-b">
                            <td class="py-4 px-4 font-medium text-gray-600 border border-gray-200">Berkas Mahasiswa</td>
                            <td class="py-4 px-4 border border-gray-200">
                                <label v-if="JSON.parse(props.berkasPersuratan.berkas_mahasiswa) == null" for="">
                                    -
                                </label>
                                <ul v-else class="list-disc list-inside space-y-1">
                                    <li v-for="(file, index) in JSON.parse(props.berkasPersuratan.berkas_mahasiswa || '[]')"
                                        :key="index">
                                        <a :href="route('berkas-persuratan.download-upload', file)"
                                            class="text-blue-600 hover:underline" target="_blank">
                                            Berkas {{ index + 1 }}
                                        </a>
                                    </li>
                                </ul>
                            </td>
                        </tr>
                        <tr v-if="props.auth.user.role_id !== 1">
                            <td class="py-4 px-4 font-medium text-gray-600 border border-gray-200">Surat Balasan</td>
                            <td class="py-4 px-4 border border-gray-200">
                                <label
                                    v-if="JSON.parse(props.berkasPersuratan.berkas_balasan) == null || JSON.parse(props.berkasPersuratan.berkas_balasan) == 0"
                                    for="">
                                    Tidak tersedia
                                </label>
                                <ul v-else class="list-disc list-inside space-y-1">
                                    <li v-for="(file, index) in JSON.parse(props.berkasPersuratan.berkas_balasan)"
                                        :key="index">
                                        <a :href="route('berkas-persuratan.download-upload', file)"
                                            class="text-blue-600 hover:underline" target="_blank">
                                            Berkas {{ index + 1 }}
                                        </a>
                                    </li>
                                </ul>
                            </td>
                        </tr>
                        <tr v-if="props.auth.user.role_id === 1">
                            <td class="py-4 px-4 font-medium text-gray-600 border border-gray-200">Surat Balasan</td>
                            <td class="py-4 px-4 border border-gray-200">
                                <label
                                    v-if="JSON.parse(props.berkasPersuratan.berkas_balasan) == null || JSON.parse(props.berkasPersuratan.berkas_balasan) == 0"
                                    for="">
                                    Tidak tersedia
                                </label>
                                <ul v-else class="list-disc list-inside space-y-1">
                                    <li v-for="(file, index) in JSON.parse(props.berkasPersuratan.berkas_balasan).filter(f => f.endsWith('.pdf'))"
                                        :key="index">
                                        <a :href="route('berkas-persuratan.download-upload', file)"
                                            class="text-blue-600 hover:underline" target="_blank">
                                            Berkas {{ index + 1 }}
                                        </a>
                                    </li>
                                </ul>
                            </td>
                        </tr>
                        <tr v-if="props.auth.user.role_id !== 1">
                            <td class="py-4 px-4 font-medium text-gray-600 border border-gray-200">Berkas Tambahan</td>
                            <td class="py-4 px-4 border border-gray-200">
                                <label
                                    v-if="!props.berkasPersuratan.berkas_tambahan || JSON.parse(props.berkasPersuratan.berkas_tambahan).length === 0">
                                    Tidak tersedia
                                </label>
                                <ul v-else class="list-disc list-inside space-y-1">
                                    <li v-for="(file, index) in JSON.parse(props.berkasPersuratan.berkas_tambahan)"
                                        :key="index">
                                        <a :href="route('berkas-persuratan.download-upload', file)"
                                            class="text-blue-600 hover:underline" target="_blank">
                                            Berkas {{ index + 1 }}
                                        </a>
                                    </li>
                                </ul>
                            </td>
                        </tr>

                        <tr>
                            <td class="py-4 px-4 font-medium text-gray-600 border border-gray-200 align-top">Catatan
                            </td>
                            <td class="py-4 px-4 border border-gray-200">
                                <div v-if="props.berkasPersuratan.notes && props.berkasPersuratan.notes.length">
                                    <table class="text-sm w-full border border-gray-300">
                                        <thead>
                                            <tr class="bg-gray-100">
                                                <th class="border px-3 py-2 text-left">Oleh</th>
                                                <th class="border px-3 py-2 text-left">Catatan</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr v-for="note in props.berkasPersuratan.notes" :key="note.id">
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

                <div class="w-full flex justify-end gap-2">
                    <Button type="button" @click="toBack"
                        class="mt-4 border border-orange-400 text-orange-500 hover:bg-orange-100 bg-white-500">
                        <span>Kembali</span>
                    </Button>

                    <Button v-if="props.mode === 'ajuan'" type="button" @click="onKeputusan"
                        class="mt-4 border border-blue-400 text-blue-500 hover:bg-blue-100 bg-white-500">
                        <span>Tambah Keputusan</span>
                    </Button>
                </div>
            </div>
        </Transition>
    </AppLayout>
</template>