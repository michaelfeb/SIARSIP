<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue'
import { useForm, Head, router } from '@inertiajs/vue3'
import InputError from '@/components/InputError.vue'
import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'
import { onMounted, ref, watch } from 'vue'
import Swal from 'sweetalert2'
import { BreadcrumbItem } from '@/types'
import { BookOpen, ListChecks, GraduationCap, University, FileText, Circle } from 'lucide-vue-next'

// @ts-expect-error: Import Default untuk vue-select
import VueSelect from 'vue-select'
import 'vue-select/dist/vue-select.css'

const Lucide = {
    BookOpen,
    ListChecks,
    GraduationCap,
    University,
    FileText,
    Circle
}


const iconList = [
    {
        label: 'Buku',
        value: 'BookOpen',
        component: BookOpen
    },
    {
        label: 'List Check',
        value: 'ListChecks',
        component: ListChecks
    },
    {
        label: 'Topi Wisuda',
        value: 'GraduationCap',
        component: GraduationCap
    },
    {
        label: 'Universitas',
        value: 'University',
        component: University
    },
    {
        label: 'File',
        value: 'FileText',
        component: FileText
    }
]

console.log(iconList);


const colorOptions = [
    { label: 'Abu-abu', value: 'text-gray-600' },
    { label: 'Merah', value: 'text-red-600' },
    { label: 'Kuning', value: 'text-yellow-600' },
    { label: 'Hijau', value: 'text-green-600' },
    { label: 'Biru', value: 'text-blue-600' },
    { label: 'Indigo', value: 'text-indigo-600' },
    { label: 'Ungu', value: 'text-purple-600' },
    { label: 'Pink', value: 'text-pink-600' },
    { label: 'Oranye', value: 'text-orange-600' },
]

const props = defineProps<{
    defaultNoUrut: number,
    link: any,
    mode: 'create' | 'edit'
}>()
const show = ref(false);

const form = useForm({
    no_urut: props.link?.no_urut ?? props.defaultNoUrut ?? '',
    nama: props.link?.nama ?? '',
    icon: props.link?.icon ?? '',
    color: props.link?.color ?? '',
    deskripsi: props.link?.deskripsi ?? '',
    status: props.link?.status ?? '1',
    link: props.link?.link ?? '',
})

const selectedIcon = ref(null)
const selectedColor = ref(null)

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Link Landing Page',
        href: '/link-landing-page',
    },
    ...(props.mode === 'create'
        ? [{ title: 'Tambah', href: '/link-landing-page' }]
        : [{ title: 'Ubah', href: '/link-landing-page' }]
    )
]

watch(selectedIcon, (val) => {
    form.icon = val.value
})

watch(selectedColor, (val) => {
    form.color = val.value
})

onMounted(() => {
    if (props.mode === 'edit') {
        selectedIcon.value = iconList.find(icon => icon.value === form.icon) ?? null
        selectedColor.value = colorOptions.find(color => color.value === form.color) ?? null
    }
})


async function submit() {
    const isEdit = props.mode === 'edit'

    form.submit(isEdit ? 'put' : 'post', route('link-landing-page.save', isEdit ? props.link.id : undefined), {
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
    router.visit(route('link-landing-page.index'))
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
                <h2 class="text-xl font-semibold">Tambah Link Landing Page</h2>

                <form @submit.prevent="submit" class="space-y-4">

                    <!-- Nama -->
                    <div class="space-y-2">
                        <Label class="gap-1" for="nama">Nama<span class="text-red-500">*</span></Label>
                        <Input v-model="form.nama" id="nama" type="text" placeholder="Judul link" />
                        <InputError :message="form.errors.nama" />
                    </div>

                    <!-- Select Ikon -->
                    <div class="space-y-2">
                        <Label class="gap-1" for="icon">Icon<span class="text-red-500">*</span></Label>
                        <VueSelect id="icon" v-model="selectedIcon" :options="iconList" placeholder="Cari Ikon"
                            class="mt-2 text-sm">
                            <template #option="option">
                                <div class="flex items-center gap-2">
                                    <component :is="Lucide[option.value]" class="w-4 h-4" />
                                    <span>{{ option.label }}</span>
                                </div>
                            </template>

                            <template #selected-option="option">
                                <div class="flex items-center gap-2">
                                    <component :is="Lucide[option.value]" class="w-4 h-4" />
                                    <span>{{ option.label }}</span>
                                </div>
                            </template>
                        </VueSelect>
                        <InputError :message="form.errors.icon" />
                    </div>

                    <div class="space-y-2">
                        <Label class="gap-1" for="color">Warna<span class="text-red-500">*</span></Label>
                        <VueSelect id="color" v-model="selectedColor" :options="colorOptions" placeholder="Pilih Warna"
                            class="mt-2 text-sm">
                            <template #option="option">
                                <div class="flex items-center gap-2">
                                    <component :is="Lucide[selectedIcon?.value] ?? Circle"
                                        :class="['w-4 h-4', option.value]" />
                                    <span>{{ option.label }}</span>
                                </div>
                            </template>

                            <template #selected-option="option">
                                <div class="flex items-center gap-2">
                                    <component :is="Lucide[selectedIcon?.value] ?? Circle"
                                        :class="['w-4 h-4', option.value]" />
                                    <span>{{ option.label }}</span>
                                </div>
                            </template>

                        </VueSelect>
                        <InputError :message="form.errors.color" />
                    </div>

                    <!-- Deskripsi -->
                    <div class="space-y-2">
                        <Label class="gap-1" for="deskripsi">Deskripsi<span class="text-red-500">*</span></Label>
                        <textarea v-model="form.deskripsi" id="deskripsi" placeholder="Tulis deskripsi link" rows="4"
                            class="w-full text-sm rounded border border-gray-300 px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"></textarea>
                        <InputError :message="form.errors.deskripsi" />
                    </div>

                    <!-- Link -->
                    <div class="space-y-2">
                        <Label class="gap-1" for="link">Link<span class="text-red-500">*</span></Label>
                        <Input v-model="form.link" id="link" type="url" placeholder="https://contoh.com" />
                        <InputError :message="form.errors.link" />
                    </div>

                    <!-- Status -->
                    <div class="space-y-2">
                        <label class="block text-sm font-medium text-gray-700">Status <span
                                class="text-red-500">*</span></label>
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
