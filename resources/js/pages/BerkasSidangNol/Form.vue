<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue'
import { useForm, Head, router } from '@inertiajs/vue3'
import InputError from '@/components/InputError.vue'
import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'
import { nextTick, onMounted, reactive, ref, watch } from 'vue'
import Swal from 'sweetalert2'
import { BreadcrumbItem } from '@/types'
import vueFilePond from 'vue-filepond'
import axios from 'axios'
import statusMappingSidangNol from '@/utils/statusMappingSidangNol'


import VueSelect from "vue3-select-component";

// @ts-expect-error: Already Have
import FilePondPluginPdfPreview from 'filepond-plugin-pdf-preview'
import FilePondPluginFileValidateType from 'filepond-plugin-file-validate-type'

import 'filepond/dist/filepond.min.css'
import 'filepond-plugin-image-preview/dist/filepond-plugin-image-preview.min.css'
import 'filepond-plugin-pdf-preview/dist/filepond-plugin-pdf-preview.css'


const FilePond = vueFilePond(
    FilePondPluginFileValidateType,
    FilePondPluginPdfPreview,
)

const userSelected = ref(null)
const userOptions = ref([])
const tempFiles = reactive({})
const filepondFiles = reactive({
    dokumen_hasil_studi: [],
    dokumen_data_diri: [],
    dokumen_pddikti_ukt: [],
    dokumen_ruangbaca_laboratorium_pkkmb_skpi: [],
    dokumen_office_toefl: [],
    dokumen_tambahan: [],
});

const onUserOptionSearch = async (searchText: string) => {
    if (searchText.length < 5) {
        return
    }

    try {
        const response = await axios.get(route('api.users.search-user', { search: searchText }));
        userOptions.value = response.data.map((user: any) => ({
            label: `${user.nama} (${user.nim})`,
            value: user.id
        }))
    } catch (error) {
        console.error(error)
    }
}

const onUserSelected = (optionSelected: any) => {
    form.user_id = optionSelected.value;
}

const today = new Date().toISOString().split('T')[0]

const props = defineProps<{
    auth: any,
    berkasSidangNol?: any,
    mode: 'create' | 'edit'
}>()
const show = ref(false);

const form = useForm({
    user_id: props.berkasSidangNol?.user_id ?? '',
    nomor_surat: props.berkasSidangNol?.nomor_surat ?? '',
    dokumen_hasil_studi: props.berkasSidangNol?.dokumen_hasil_studi ?? '',
    dokumen_data_diri: props.berkasSidangNol?.dokumen_data_diri ?? '',
    dokumen_pddikti_ukt: props.berkasSidangNol?.dokumen_pddikti_ukt ?? '',
    dokumen_ruangbaca_laboratorium_pkkmb_skpi: props.berkasSidangNol?.dokumen_ruangbaca_laboratorium_pkkmb_skpi ?? '',
    dokumen_office_toefl: props.berkasSidangNol?.dokumen_office_toefl ?? '',
    dokumen_tambahan: props.berkasSidangNol?.dokumen_tambahan ?? '',
    status: props.berkasSidangNol?.status ?? 0,
    tanggal_dikirim: props.berkasSidangNol?.tanggal_dikirim ? props.berkasSidangNol.tanggal_dikirim.substring(0, 10) : today,
    tanggal_selesai: props.berkasSidangNol?.tanggal_selesai ? props.berkasSidangNol.tanggal_selesai.substring(0, 10) : '',
})

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Berkas Sidang Nol',
        href: '/berkas-sidang-nol',
    },
    ...(props.mode === 'create'
        ? [{ title: 'Tambah', href: '/berkas-sidang-nol' }]
        : [{ title: 'Ubah', href: '/berkas-sidang-nol' }]
    )
]


async function submit() {
    const isEdit = props.mode === 'edit'
    const url = isEdit
        ? route('berkas-sidang-nol.save', { id: props.berkasSidangNol.id })
        : route('berkas-sidang-nol.save')

    await form.submit(isEdit ? 'post' : 'post', url, { // selalu POST
        forceFormData: true,
        method: isEdit ? 'put' : 'post', // method override
        onSuccess: () => {
            Swal.fire({
                icon: 'success',
                title: 'Berhasil!',
                text: isEdit ? 'Data telah diperbarui!' : 'Data telah ditambahkan!',
                confirmButtonText: 'OK',
                customClass: {
                    confirmButton: 'swal-confirm-button',
                },
            })
        },
        onError: () => {
            for (const field in tempFiles) {
                if (tempFiles[field]?.length > 0) {
                    form[field] = tempFiles[field][0].file;
                }
            }
            Swal.fire({
                icon: 'error',
                title: 'Gagal!',
                text: isEdit ? 'Gagal memperbarui data!' : 'Gagal menambahkan data!',
                confirmButtonText: 'OK',
                customClass: {
                    confirmButton: 'swal-confirm-button',
                },
            })
        }
    })
}

const handleUpdateFiles = (files, field) => {
    filepondFiles[field] = files; // atur FilePond tampilannya

    if (files.length > 0) {
        form[field] = files[0].file;
    } else {
        form[field] = '';
    }
}


function toBack() {
    router.visit(route('berkas-sidang-nol.index'))
}

const loadExistingFiles = async () => {
    const response = await axios.get(route('berkas-sidang-nol.get-uploads', props.berkasSidangNol.id));
    const files = response.data;

    for (const field in files) {
        if (files[field]) {
            filepondFiles[field] = [{
                source: files[field],
                options: {
                    type: 'remote',
                },
            }];
        }
    }
}

const selectUserOption = async () => {
    if (props.auth.user.role_id === 1) {
        const option = {
            label: `${props.auth.user.nama} (${props.auth.user.nim})`,
            value: props.auth.user.id
        };
        userOptions.value = [option]
        userSelected.value = option.value
        form.user_id = option.value
    }

    else if (props.mode === 'edit' && props.berkasSidangNol?.user) {
        const option = {
            label: `${props.berkasSidangNol.user.nama} (${props.berkasSidangNol.user.nim})`,
            value: props.berkasSidangNol.user.id
        };
        userOptions.value = [option]
        userSelected.value = option.value
    }
}

onMounted(() => {
    setTimeout(() => {
        show.value = true;
    }, 50);

    selectUserOption()

    if (props.mode === 'edit') {
        loadExistingFiles();
    }
});


</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <Transition name="slide-up" mode="out-in">
            <div v-if="show" class="p-4 space-y-4">
                <div v-if="Object.keys(form.errors).length > 0" class="mb-4 rounded bg-red-100 p-4 text-red-600">
                    <ul class="list-disc pl-5 space-y-1 text-sm">
                        <li v-for="(error, key) in form.errors" :key="key">{{ error }}</li>
                    </ul>
                </div>


                <h2 class="text-xl font-semibold">
                    {{ props.mode === 'edit' ? 'Edit Berkas Sidang Nol' : 'Tambah Berkas Sidang Nol' }}
                </h2>


                <form @submit.prevent="submit" class="space-y-6">
                    <div class="space-y-2">
                        <Label for="nomor_surat">Nomor Surat</Label>
                        <Input id="nomor_surat" type="text" v-model="form.nomor_surat"
                            placeholder="Nomor surat akan diisi oleh operator"
                            :disabled="![2, 6, 7, 8].includes(props.auth.user.role_id)"
                            class="text-sm font-medium w-full rounded border border-gray-300 px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 disabled:bg-gray-100 disabled:cursor-not-allowed" />
                        <InputError :message="form.errors.nomor_surat" />
                    </div>

                    <div class="space-y-2">
                        <Label for="user_selected">Mahasiswa</Label>
                        <VueSelect id="user_selected" v-model="userSelected" :options="userOptions" editable
                            placeholder="Cari mahasiswa" class="mt-2 text-sm" @search="onUserOptionSearch"
                            :is-disabled="auth.user.role_id === 1" @option-selected="onUserSelected" />
                        <InputError :message="form.errors.user_id" />
                    </div>

                    <div class="space-y-2">
                        <Label for="dokumen_hasil_studi">Dokumen Hasil Studi</Label>

                        <div class="page-dokumen-hasil-studi">
                            <FilePond name="dokumen_hasil_studi"
                                label-idle="Seret & lepas dokumen atau <span class='filepond--label-action'>Telusuri</span>"
                                :allow-multiple="false" accepted-file-types="application/pdf" filePosterMaxHeight="250"
                                :files="filepondFiles.dokumen_hasil_studi" :pdf-preview-height="400"
                                @updatefiles="(files: any) => handleUpdateFiles(files, 'dokumen_hasil_studi')" />
                        </div>

                        <InputError :message="form.errors.dokumen_hasil_studi" />

                        <div>
                            <label for="" class="text-xs">Keterangan</label>
                            <ul class="list-disc pl-5 space-y-1 text-xs">
                                <li>Rekap hasil studi yang di print di simari yg di sah kan oleh ketua/sekretaris prodi
                                </li>
                                <li>Rekap mata kuliah recourse (jika ada)</li>
                                <li>Form hapus mata kuliah (jika ada)</li>
                            </ul>
                        </div>
                    </div>

                    <div class="space-y-2">
                        <Label for="dokumen_data_diri">Dokumen Data Diri</Label>

                        <FilePond name="dokumen_data_diri"
                            label-idle="Seret & lepas dokumen atau <span class='filepond--label-action'>Telusuri</span>"
                            :allow-multiple="false" accepted-file-types="application/pdf"
                            :files="filepondFiles.dokumen_data_diri" :pdf-preview-height="400"
                            @updatefiles="(files: any) => handleUpdateFiles(files, 'dokumen_data_diri')" />
                        <InputError :message="form.errors.dokumen_data_diri" />
                        <div>
                            <label for="" class="text-xs">Keterangan</label>
                            <ul class="list-disc pl-5 space-y-1 text-xs">
                                <li>Scan KTM</li>
                                <li>Ijazah Terakhir</li>
                                <li>Scan KTP</li>
                            </ul>
                        </div>
                    </div>

                    <!-- Dokumen PDDIKTI + UKT -->
                    <div class="space-y-2">
                        <Label for="dokumen_pddikti_ukt">Dokumen Tampilan PDDIKTI & Bukti Pembayaran UKT</Label>

                        <FilePond name="dokumen_pddikti_ukt"
                            label-idle="Seret & lepas dokumen atau <span class='filepond--label-action'>Telusuri</span>"
                            :allow-multiple="false" accepted-file-types="application/pdf" filePosterMaxHeight="250"
                            :files="filepondFiles.dokumen_pddikti_ukt" :pdf-preview-height="400"
                            @updatefiles="(files: any) => handleUpdateFiles(files, 'dokumen_pddikti_ukt')" />
                        <InputError :message="form.errors.dokumen_pddikti_ukt" />
                        <div>
                            <label for="" class="text-xs">Keterangan</label>
                            <ul class="list-disc pl-5 space-y-1 text-xs">
                                <li>Scan PDDIKTI</li>
                                <li>Slip UKT</li>
                            </ul>
                        </div>
                    </div>

                    <!-- Dokumen Ruang Baca + Laboratorium + PKKMB + SKPI -->
                    <div class="space-y-2">
                        <Label for="dokumen_ruangbaca_laboratorium_pkkmb_skpi">Dokumen Ruang Baca, Laboratorium, PKKMB,
                            dan SKPI</Label>

                        <FilePond name="dokumen_ruangbaca_laboratorium_pkkmb_skpi"
                            label-idle="Seret & lepas dokumen atau <span class='filepond--label-action'>Telusuri</span>"
                            :allow-multiple="false" accepted-file-types="application/pdf" filePosterMaxHeight="250"
                            :files="filepondFiles.dokumen_ruangbaca_laboratorium_pkkmb_skpi" :pdf-preview-height="400"
                            @updatefiles="(files: any) => handleUpdateFiles(files, 'dokumen_ruangbaca_laboratorium_pkkmb_skpi')" />
                        <InputError :message="form.errors.dokumen_ruangbaca_laboratorium_pkkmb_skpi" />
                        <div>
                            <label for="" class="text-xs">Keterangan</label>
                            <ul class="list-disc pl-5 space-y-1 text-xs">
                                <li>Scan surat Ruang Baca</li>
                                <li>Scan surat Bebas Lab</li>
                                <li>Scan sertifikat PKKMB (berikan tanda nama)</li>
                                <li>Scan surat SKPI</li>
                            </ul>
                        </div>
                    </div>

                    <!-- Dokumen Office + TOEFL -->
                    <div class="space-y-2">
                        <Label for="dokumen_office_toefl">Dokumen Sertifikat Office dan TOEFL</Label>

                        <FilePond name="dokumen_office_toefl"
                            label-idle="Seret & lepas dokumen atau <span class='filepond--label-action'>Telusuri</span>"
                            :allow-multiple="false" accepted-file-types="application/pdf" filePosterMaxHeight="250"
                            :files="filepondFiles.dokumen_office_toefl" :pdf-preview-height="400"
                            @updatefiles="(files: any) => handleUpdateFiles(files, 'dokumen_office_toefl')" />
                        <InputError :message="form.errors.dokumen_office_toefl" />
                        <div>
                            <label for="" class="text-xs">Keterangan</label>
                            <ul class="list-disc pl-5 space-y-1 text-xs">
                                <li>Scan Sertifikat Office</li>
                                <li>Scan sertifikat TOEFL yang diterbitkan UPT Bahasa ULM</li>
                            </ul>
                        </div>
                    </div>

                    <!-- Dokumen Tambahan (Opsional) -->
                    <div class="space-y-2">
                        <Label for="dokumen_tambahan">Dokumen Tambahan dari Prodi (Opsional)</Label>

                        <FilePond name="dokumen_tambahan"
                            label-idle="Seret & lepas dokumen atau <span class='filepond--label-action'>Telusuri</span>"
                            :allow-multiple="false" accepted-file-types="application/pdf" filePosterMaxHeight="250"
                            :files="filepondFiles.dokumen_tambahan" :pdf-preview-height="400"
                            @updatefiles="(files: any) => handleUpdateFiles(files, 'dokumen_tambahan')" />
                        <InputError :message="form.errors.dokumen_tambahan" />
                    </div>

                    <div class="space-y-2">
                        <Label for="status">Status</Label>
                        <div class="relative">
                            <select id="status" v-model="form.status" disabled
                                class="text-sm font-medium w-full rounded border border-gray-300 px-3 py-2 pr-10 focus:outline-none focus:ring-2 focus:ring-blue-500 appearance-none bg-gray-100">
                                <option v-for="(status, code) in statusMappingSidangNol" :key="code" :value="code">
                                    {{ status.label }}
                                </option>
                            </select>
                        </div>
                        <InputError :message="form.errors.status" />
                    </div>
                    <div class="space-y-2">
                        <Label for="tanggal_dikirim">Tanggal Dikirim</Label>
                        <Input id="tanggal_dikirim" type="date" v-model="form.tanggal_dikirim"
                            class="text-sm font-medium w-full rounded border border-gray-300 px-3 py-2 bg-gray-100 text-gray-700"
                            readonly />
                        <InputError :message="form.errors.tanggal_dikirim" />
                    </div>
                    <div class="space-y-2">
                        <Label for="tanggal_selesai">Tanggal Selesai</Label>
                        <Input id="tanggal_selesai" type="date" v-model="form.tanggal_selesai"
                            class="text-sm font-medium w-full rounded border border-gray-300 px-3 py-2 bg-gray-100 text-gray-700"
                            readonly />
                        <InputError :message="form.errors.tanggal_selesai" />
                    </div>
                    <div class="w-full flex justify-end gap-2">
                        <Button type="button" @click="toBack"
                            class="border border-orange-400 text-orange-500 hover:bg-orange-100 bg-white">
                            Kembali
                        </Button>
                        <Button type="submit" class="hover:bg-blue-600 bg-blue-500">
                            Simpan
                        </Button>
                    </div>
                </form>
            </div>
        </Transition>
    </AppLayout>
</template>
