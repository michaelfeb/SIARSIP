<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue'
import { useForm, Head, router } from '@inertiajs/vue3'
import InputError from '@/components/InputError.vue'
import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'
import { onMounted, ref } from 'vue'
import Swal from 'sweetalert2'
import { BreadcrumbItem } from '@/types'
import vueFilePond from 'vue-filepond'

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

function handleUpdateFiles(fileItems: any) {
    form.gambar = fileItems.length > 0 ? fileItems[0].file : null;
}

const props = defineProps<{
    carousel: any
    mode: 'create' | 'edit'
}>()
const show = ref(false);

const today = new Date().toISOString().split('T')[0]

const form = useForm({
    nama: props.carousel?.nama ?? '',
    gambar: props.carousel?.gambar ?? '',
    status: props.carousel?.status ?? '1',
    tanggal_publish: props.carousel?.tanggal_publish ? props.carousel.tanggal_publish.substring(0, 10) : today,
})

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Carousel',
        href: '/carousel',
    },
    ...(props.mode === 'create'
        ? [{ title: 'Tambah', href: '/carousel' }]
        : [{ title: 'Ubah', href: '/carousel' }]
    )
]


async function submit() {
    const isEdit = props.mode === 'edit'

    form.post(route('carousel.save', isEdit ? props.carousel.id : undefined), {
        forceFormData: true,
        method: isEdit ? 'put' : 'post',
        onSuccess: () => {
            Swal.fire({
                icon: 'success',
                title: 'Berhasil!',
                text: isEdit ? 'Data telah diperbarui!' : 'Data telah ditambahkan!',
                confirmButtonText: 'OK',
                customClass: {
                    confirmButton: 'swal-confirm-button',
                },
            });
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
            });
        }
    });
}


function toBack() {
    router.visit(route('carousel.index'))
}

onMounted(() => {
    setTimeout(() => {
        show.value = true
    }, 50)

    if (props.mode === 'edit' && props.carousel?.gambar) {
        initialFiles.value = [
            {
                source: `/storage/${props.carousel.gambar}`,
                options: {}
            }
        ];
    }
})
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
                <h2 class="text-xl font-semibold">Tambah Carousel</h2>

                <form @submit.prevent="submit" class="space-y-4">
                    <div class="space-y-2">
                        <Label class="gap-1" for="nama">Nama<span class="text-red-500">*</span></Label>
                        <Input v-model="form.nama" id="nama" type="text" placeholder="Nama" />
                        <InputError :message="form.errors.nama" />
                    </div>

                    <div class="space-y-2">
                        <Label for="gambar" class="gap-1">Upload Gambar<span class="text-red-500">*</span><span
                                class="text-[12px]">{{ "( Max 5 MB )" }}</span></Label>
                        <FilePond name="gambar"
                            label-idle="Seret & lepas gambar atau <span class='filepond--label-action'>Telusuri</span>"
                            :allow-multiple="false" accepted-file-types="image/png, image/jpeg, image/jpg"
                            filePosterMaxHeight="250" @updatefiles="handleUpdateFiles" :files="initialFiles" />
                        <InputError :message="form.errors.gambar" />
                    </div>

                    <div class="space-y-2">
                        <label class="block text-sm font-medium text-gray-700">Status<span class="text-red-500">
                                *</span> </label>
                        <div class="flex items-center gap-6">
                            <label class="flex items-center space-x-1 text-sm">
                                <input type="radio" value="1" v-model="form.status"
                                    class="w-4 h-4 text-blue-600 focus:ring-blue-500" />
                                <span>Aktif</span>
                            </label>
                            <label class="flex items-center space-x-1 text-sm">
                                <input type="radio" value="0" v-model="form.status"
                                    class="w-4 h-4 text-red-600 focus:ring-red-500" />
                                <span>Tidak Aktif</span>
                            </label>
                        </div>
                        <InputError :message="form.errors.status" />
                    </div>
                    <div class="space-y-2">
                        <Label class="gap-1" for="tanggal_publish">Tanggal Dipublish<span
                                class="text-red-500">*</span></Label>
                        <Input id="tanggal_publish" type="date" v-model="form.tanggal_publish"
                            class="text-sm font-medium w-full rounded border border-gray-300 px-3 py-2 bg-gray-100 text-gray-700"
                            readonly />
                        <InputError :message="form.errors.tanggal_publish" />
                    </div>

                    <div class="w-full flex justify-end gap-2">
                        <Button type="submit" class="mt-4 hover:bg-blue-600 bg-blue-500" :tabindex="4"
                            :disabled="form.processing">
                            <LoaderCircle v-if="form.processing" class="h-4 w-4 animate-spin" />
                            Simpan
                        </Button>
                        <Button type="button" @click="toBack"
                            class="mt-4 border border-orange-400 text-orange-500 hover:bg-orange-100 bg-white-500">
                            <span>Kembali</span>
                        </Button>
                    </div>
                </form>
            </div>
        </Transition>
    </AppLayout>
</template>
