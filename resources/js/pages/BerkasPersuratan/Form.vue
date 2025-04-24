<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue'
import { useForm, Head, router } from '@inertiajs/vue3'
import InputError from '@/components/InputError.vue'
import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'
import { nextTick, onMounted, ref, watch } from 'vue'
import Swal from 'sweetalert2'
import { BreadcrumbItem } from '@/types'
import vueFilePond from 'vue-filepond'
import axios from 'axios'
import statusMapping from '@/utils/statusMapping'


import VueSelect from "vue3-select-component";

// @ts-expect-error: Already Have
import FilePondPluginPdfPreview from 'filepond-plugin-pdf-preview'

import FilePondPluginFileValidateType from 'filepond-plugin-file-validate-type'
import FilePondPluginImagePreview from 'filepond-plugin-image-preview'

import 'filepond/dist/filepond.min.css'
import 'filepond-plugin-image-preview/dist/filepond-plugin-image-preview.min.css'
import 'filepond-plugin-pdf-preview/dist/filepond-plugin-pdf-preview.css'


const FilePond = vueFilePond(
    FilePondPluginImagePreview,
    FilePondPluginFileValidateType,
    FilePondPluginPdfPreview,
)

const initialFiles = ref([]);

const handleUpdateFiles = (files: any[]) => {
    form.berkas_mahasiswa = files.map(fileItem => fileItem.file ?? fileItem.source);
};

const userSelected = ref(null)
const userOptions = ref([])

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
    berkasPersuratan?: any,
    jenisSurat: any,
    mode: 'create' | 'edit'
}>()
const show = ref(false);

const form = useForm({
    user_id: props.berkasPersuratan?.user_id ?? '',
    nomor_surat: props.berkasPersuratan?.nomor_surat ?? '',
    jenis_surat_id: props.berkasPersuratan?.jenis_surat_id ?? '',
    keterangan: props.berkasPersuratan?.keterangan ?? '',
    berkas_mahasiswa: props.berkasPersuratan?.berkas_mahasiswa ?? [],
    berkas_balasan: props.berkasPersuratan?.berkas_balasan ?? [],
    status: props.berkasPersuratan?.status ?? 11,
    tanggal_dikirim: props.berkasPersuratan?.tanggal_dikirim ? props.berkasPersuratan.tanggal_dikirim.substring(0, 10) : today,
})

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Berkas Persuratan',
        href: '/berkas-persuratan',
    },
    ...(props.mode === 'create'
        ? [{ title: 'Tambah', href: '/berkas-persuratan' }]
        : [{ title: 'Ubah', href: '/berkas-persuratan' }]
    )
]


async function submit() {
    const isEdit = props.mode === 'edit'
    const url = isEdit
        ? route('berkas-persuratan.save', { id: props.berkasPersuratan.id })
        : route('berkas-persuratan.save')

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


function toBack() {
    router.visit(route('template-surat.index'))
}

onMounted(() => {
    setTimeout(() => {
        show.value = true;
    }, 50);

    if (props.mode === 'edit' && props.berkasPersuratan?.berkas_mahasiswa) {
        const files = JSON.parse(props.berkasPersuratan.berkas_mahasiswa);
        initialFiles.value = files.map((filePath: string) => ({
            source: `/storage/${filePath}`,
            options: {
            }
        }));
    }

    if (props.mode === 'edit' && props.berkasPersuratan?.user) {
        const option = {
            label: `${props.berkasPersuratan.user.nama} (${props.berkasPersuratan.user.nim})`,
            value: props.berkasPersuratan.user.id
        };

        userOptions.value = [option]
        userSelected.value = userOptions.value[0].value
    }

    console.log('====================================');
    console.log(props);
    console.log('====================================');
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
                    {{ props.mode === 'edit' ? 'Edit Berkas Persuratan' : 'Tambah Berkas Persuratan' }}
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
                            @option-selected="onUserSelected" />
                    </div>

                    <div class="space-y-2">
                        <Label for="jenis_surat_id">Jenis Surat</Label>
                        <div class="relative">
                            <select id="jenis_surat_id" v-model="form.jenis_surat_id"
                                class="text-sm font-medium w-full rounded border border-gray-300 px-3 py-2 pr-10 focus:outline-none focus:ring-2 focus:ring-blue-500 appearance-none">
                                <option value="">Pilih Jenis Surat</option>
                                <option v-for="jenis in props.jenisSurat" :key="jenis.id" :value="jenis.id">
                                    {{ jenis.nama }}
                                </option>
                            </select>
                            <div
                                class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-3 text-gray-500">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
                                </svg>
                            </div>
                        </div>
                        <InputError :message="form.errors.jenis_surat_id" />
                    </div>
                    <div class="space-y-2">
                        <Label for="keterangan">Keterangan</Label>
                        <textarea id="keterangan" v-model="form.keterangan" rows="4"
                            class="text-sm font-medium w-full rounded border border-gray-300 px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
                            placeholder="Isi alasan/keterangan surat..."></textarea>
                        <InputError :message="form.errors.keterangan" />
                    </div>
                    <div class="space-y-2">
                        <Label>Upload Berkas Mahasiswa</Label>
                        <FilePond name="berkas_mahasiswa[]" multiple
                            label-idle="Seret & lepas dokumen atau <span class='filepond--label-action'>Telusuri</span>"
                            :allow-multiple="true" :files="initialFiles" accepted-file-types="application/pdf"
                            filePosterMaxHeight="250" @updatefiles="handleUpdateFiles" />
                        <InputError :message="form.errors.berkas_mahasiswa" />
                    </div>
                    <div class="space-y-2">
                        <Label for="status">Status</Label>
                        <div class="relative">
                            <select id="status" v-model="form.status" disabled
                                class="text-sm font-medium w-full rounded border border-gray-300 px-3 py-2 pr-10 focus:outline-none focus:ring-2 focus:ring-blue-500 appearance-none bg-gray-100">
                                <option v-for="(status, code) in statusMapping" :key="code" :value="code">
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
                    <div class="w-full flex justify-end gap-2">
                        <Button type="submit" class="hover:bg-blue-600 bg-blue-500">
                            Simpan
                        </Button>
                        <Button type="button" @click="toBack"
                            class="border border-orange-400 text-orange-500 hover:bg-orange-100 bg-white">
                            Kembali
                        </Button>
                    </div>
                </form>
            </div>
        </Transition>
    </AppLayout>
</template>
