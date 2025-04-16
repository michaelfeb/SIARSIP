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


const props = defineProps<{
    jenis_surat: any
    mode: 'create' | 'edit'
}>()
const show = ref(false);

console.log('====================================');
console.log(props.jenis_surat);
console.log('====================================');

const form = useForm({
    nama: props.jenis_surat?.nama ?? '',
    status: props.jenis_surat?.status ?? '',
})

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Jenis Surat',
        href: '/jenis-surat',
    },
    ...(props.mode === 'create'
        ? [{ title: 'Tambah', href: '/jenis-surat' }]
        : [{ title: 'Ubah', href: '/jenis-surat' }]
    )
]


async function submit() {
    const isEdit = props.mode === 'edit'

    form.submit(isEdit ? 'put' : 'post', route('jenis-surat.save', isEdit ? props.jenis_surat.id : undefined), {
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
    router.visit(route('jenis-surat.index'))
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

                <div v-if="Object.keys(form.errors).length > 0" class="mb-4 rounded bg-red-100 p-4 text-red-600">
                    <ul class="list-disc pl-5 space-y-1 text-sm">
                        <li v-for="(error, key) in form.errors" :key="key">{{ error }}</li>
                    </ul>
                </div>
                <h2 class="text-xl font-semibold">Tambah Jenis Surat</h2>

                <form @submit.prevent="submit" class="space-y-4">
                    <div class="space-y-2">
                        <Label for="nama">Nama</Label>
                        <Input v-model="form.nama" id="nama" type="text" placeholder="Nama" />
                        <InputError :message="form.errors.nama" />
                    </div>

                    <div class="space-y-2">
                        <label class="block text-sm font-medium text-gray-700">Status</label>
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
