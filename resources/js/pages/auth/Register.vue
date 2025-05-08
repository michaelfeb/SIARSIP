<script setup lang="ts">
import InputError from '@/components/InputError.vue';
import TextLink from '@/components/TextLink.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import AuthBase from '@/layouts/AuthLayout.vue';
import { Head, router, useForm } from '@inertiajs/vue3';
import { LoaderCircle } from 'lucide-vue-next';
import { onlyAllowNumbers } from '@/utils/inputValidatior'
import programStudiMapping from '@/utils/programStudiMapping';
import Swal from 'sweetalert2';

const form = useForm({
    nama: '',
    nim: '',
    email: '',
    program_studi: '',
    nomor_telpon: '',
    password: '',
    password_confirmation: '',
});

const submit = () => {
    form.post(route('register'), {
        onSuccess: () => {
            Swal.fire({
                icon: 'success',
                title: 'Berhasil!',
                text: 'Akun telah dibuat',
                confirmButtonText: 'OK',
                customClass: {
                    confirmButton: 'swal-confirm-button',
                },
            }).then(() => {
                router.visit(route('login'))
                form.reset('password');
            });
        },
        onError: () => {
            Swal.fire({
                icon: 'error',
                title: 'Gagal!',
                text: 'Gagal membuat akun!',
                confirmButtonText: 'OK',
                customClass: {
                    confirmButton: 'swal-confirm-button',
                },
            });
        }
    });
};

function onLogin() {
    router.visit(route('login'))
}
</script>

<template>
    <AuthBase title="Daftar akun mahasiswa" description="Masukan data diri anda">

        <Head title="Register" />

        <form @submit.prevent="submit" class="flex flex-col gap-6">
            <div class="grid gap-6">
                <div class="grid gap-2">
                    <Label for="nama">Nama</Label>
                    <Input id="nama" type="text" required autofocus :tabindex="1" autocomplete="nama"
                        v-model="form.nama" placeholder="Full nama" />
                    <InputError :message="form.errors.nama" />
                </div>

                <div class="grid gap-2">
                    <Label for="nim">NIM</Label>
                    <Input id="nim" type="text" required autofocus :tabindex="1" autocomplete="nim"
                        @keypress="onlyAllowNumbers" v-model="form.nim" placeholder="NIM" />
                    <InputError :message="form.errors.nim" />
                </div>

                <div class="grid gap-2">
                    <Label for="program_studi">Program Studi</Label>
                    <select v-model="form.program_studi" id="program_studi"
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
                        <input v-model="form.nomor_telpon" type="text" id="nomor_telpon" @keypress="onlyAllowNumbers"
                            class="flex-1 block w-full text-sm px-3 py-2 focus:outline-none" placeholder="Nomor" />
                    </div>
                    <InputError class="mt-2" :message="form.errors.nomor_telpon" />
                </div>

                <div class="grid gap-2">
                    <Label for="email">Email</Label>
                    <Input id="email" type="email" required :tabindex="2" autocomplete="email" v-model="form.email"
                        placeholder="email@mhs.ulm.com" />
                    <InputError :message="form.errors.email" />
                </div>

                <div class="grid gap-2">
                    <Label for="password">Password</Label>
                    <Input id="password" type="password" required :tabindex="3" autocomplete="new-password"
                        v-model="form.password" placeholder="Password" />
                    <InputError :message="form.errors.password" />
                </div>

                <div class="grid gap-2">
                        <Label for="password_confirmation">Konfirmasi Password</Label>
                        <Input id="password_confirmation" required v-model="form.password_confirmation" type="password"
                            class="mt-1 block w-full" autocomplete="password_confirmation" tabindex="4" placeholder="Confirm password" />
                        <InputError :message="form.errors.password_confirmation" />
                    </div>

                <div class="">
                    <Button type="submit" class="w-full hover:bg-orange-500 bg-orange-400" tabindex="5"
                        :disabled="form.processing">
                        <LoaderCircle v-if="form.processing" class="h-4 w-4 animate-spin" />
                        Daftar
                    </Button>

                    <Button @click="onLogin()"
                        class="w-full mt-3 border border-orange-500 text-orange-500 bg-white hover:bg-orange-500 hover:text-white transition-colors duration-300"
                        :tabindex="6">
                        Login
                    </Button>
                </div>
            </div>
        </form>
    </AuthBase>
</template>
