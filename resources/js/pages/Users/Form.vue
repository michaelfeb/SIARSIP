<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue'
import { useForm, Head, router } from '@inertiajs/vue3'
import InputError from '@/components/InputError.vue'
import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'
import { onMounted, ref } from 'vue'
import Swal from 'sweetalert2'


const props = defineProps<{
    user: any
    roles: Array<{ id: number; nama: string }>
    mode: 'create' | 'edit'
}>()
const show = ref(false);

const form = useForm({
    nama: props.user?.nama ?? '',
    email: props.user?.email ?? '',
    role_id: props.user?.role_id ?? '',
    password: props.user?.password ?? '',
})

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Pengguna',
        href: '/users',
    },
    ...(props.mode === 'create'
        ? [{ title: 'Tambah', href: '/users' }]
        : [{ title: 'Ubah', href: '/users' }]
    )
]


async function submit() {
    const isEdit = props.mode === 'edit'
    
    form.submit(isEdit ? 'put' : 'post', route('users.save', isEdit ? props.user.id : undefined), {
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
    router.visit(route('users.index'))
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
                <h2 class="text-xl font-semibold">Tambah Pengguna</h2>

                <form @submit.prevent="submit" class="space-y-4">
                    <div class="space-y-2">
                        <Label for="nama">Nama</Label>
                        <Input v-model="form.nama" id="nama" type="text" placeholder="Nama" />
                        <InputError :message="form.errors.nama" />
                    </div>

                    <div class="space-y-2">
                        <Label for="email">Email</Label>
                        <Input v-model="form.email" id="email" type="email" placeholder="user@example.com" />
                        <InputError :message="form.errors.email" />
                    </div>

                    <div class="space-y-2">
                        <Label for="role">Role</Label>
                        <div class="relative">
                            <select v-model="form.role_id" id="role"
                                class="text-sm font-medium w-full rounded border border-gray-300 px-3 py-2 pr-10 focus:outline-none focus:ring-2 focus:ring-blue-500 appearance-none">
                                <option value="">Pilih Role</option>
                                <option v-for="role in props.roles" :key="role.id" :value="role.id">
                                    {{ role.nama }}
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
                        <InputError :message="form.errors.role_id" />
                    </div>


                    <div v-if="mode === 'create'" class="space-y-2">
                        <Label for="password">Password</Label>
                        <Input v-model="form.password" id="password" type="password" placeholder="••••••••" />
                        <InputError :message="form.errors.password" />
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
