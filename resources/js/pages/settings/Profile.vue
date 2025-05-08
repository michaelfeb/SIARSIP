<script setup lang="ts">
import { Head, Link, useForm, usePage } from '@inertiajs/vue3';

import DeleteUser from '@/components/DeleteUser.vue';
import HeadingSmall from '@/components/HeadingSmall.vue';
import InputError from '@/components/InputError.vue'
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import AppLayout from '@/layouts/AppLayout.vue';
import SettingsLayout from '@/layouts/settings/Layout.vue';
import { type BreadcrumbItem, type SharedData, type User } from '@/types';
import { getInitials } from '@/composables/useInitials';
import programStudiMapping from '@/utils/programStudiMapping';
import { onlyAllowNumbers } from '@/utils/inputValidatior';
import Swal from 'sweetalert2';

interface Props {
    mustVerifyEmail: boolean;
    status?: string;
}

defineProps<Props>();

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Pengaturan Profil',
        href: '/settings/profile',
    },
];

const page = usePage<SharedData>();
const user = page.props.auth.user as User;
const role = page.props.role_name;


const form = useForm({
    nama: user.nama,
    nim: user.nim,
    email: user.email,
    nomor_telpon: user.nomor_telpon,
    program_studi: user.program_studi,

});

const submit = () => {
    form.patch(route('profile.update'), {
        preserveScroll: true,
        onSuccess: () => {
            Swal.fire({
                icon: 'success',
                title: 'Berhasil!',
                text: 'Password telah diperbarui',
                confirmButtonText: 'OK',
                customClass: {
                    confirmButton: 'swal-confirm-button',
                },
            }).then(() => {
                location.reload(); 
            });
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
            });
        }
    });
};

</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">

        <Head title="Profile settings" />

        <SettingsLayout>
            <div class="flex flex-col space-y-6">
                <HeadingSmall title="" description="" />

                <div class="flex flex-col items-center text-white p-6 rounded-lg">
                    <div
                        class="w-32 h-32 rounded-full bg-gray-400 flex items-center justify-center text-4xl font-bold mb-4">
                        {{ getInitials(user.nama) }}
                    </div>

                    <!-- Nama -->
                    <h2 class="text-2xl font-semibold text-black"> {{ user.nama }}</h2>

                    <!-- Jabatan -->
                    <p class="text-lg text-gray-400 mt-1">{{ role }}</p>
                </div>


                <form @submit.prevent="submit" class="space-y-6">
                    <div class="grid gap-2">
                        <Label for="nama">Nama</Label>
                        <Input id="nama" class="mt-1 block w-full" v-model="form.nama" required autocomplete="nama"
                            placeholder="Nama lengkap" />
                        <InputError class="mt-2" :message="form.errors.nama" />
                    </div>

                    <div class="grid gap-2">
                        <Label v-if="user.role_id === 1" for="nim">NIM</Label>
                        <Label v-else>NIP</Label>
                        <Input id="nim" class="mt-1 block w-full bg-gray-100" v-model="form.nim" disabled />
                    </div>

                    <div v-if="user.role_id === 1" class="grid gap-2">
                        <Label for="program_studi">Program Studi</Label>
                        <select v-model="form.program_studi" id="program_studi" disabled
                            class="text-sm font-medium w-full rounded border border-gray-300 px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 appearance-none">
                            <option value="">Pilih Program Studi</option>
                            <option v-for="(value, key) in programStudiMapping" :key="key" :value="value.value">
                                {{ value.label }}
                            </option>
                        </select>
                    </div>

                    <div class="grid gap-2">
                        <Label for="nomor_telpon">Nomor Telpon</Label>
                        <div
                            class="mt-1 flex rounded-md shadow-sm border border-gray-300 overflow-hidden focus-within:ring-1 focus-within:ring-orange-500 focus-within:border-orange-500">
                            <span
                                class="inline-flex items-center px-3 bg-gray-100 text-gray-600 text-sm border-r border-gray-300">
                                08
                            </span>
                            <input v-model="form.nomor_telpon" type="text" id="nomor_telpon"
                                @keypress="onlyAllowNumbers"
                                class="flex-1 block w-full text-sm px-3 py-2 focus:outline-none" placeholder="Nomor" />
                        </div>
                        <InputError class="mt-2" :message="form.errors.nomor_telpon" />
                    </div>

                    <div class="grid gap-2">
                        <Label for="email">Email</Label>
                        <Input id="email" type="email" class="mt-1 block w-full" v-model="form.email" required
                            autocomplete="username" placeholder="Email address" />
                        <InputError class="mt-2" :message="form.errors.email" />
                    </div>

                    <div class="flex items-center justify-end gap-4">
                        <Button class="hover:bg-blue-600 bg-blue-500" :disabled="form.processing">
                            Simpan
                        </Button>
                    </div>
                </form>
            </div>
        </SettingsLayout>
    </AppLayout>
</template>
