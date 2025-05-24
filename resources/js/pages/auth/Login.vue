<script setup lang="ts">
import InputError from '@/components/InputError.vue';
import TextLink from '@/components/TextLink.vue';
import { Button } from '@/components/ui/button';
import { Checkbox } from '@/components/ui/checkbox';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import AuthBase from '@/layouts/AuthLayout.vue';
import { Head, router, useForm } from '@inertiajs/vue3';
import { Eye, EyeClosed, LoaderCircle } from 'lucide-vue-next';
import { onlyAllowNumbers } from '@/utils/inputValidatior'
import { computed, ref } from 'vue';

const props = defineProps<{
    status?: string;
    canResetPassword: boolean;
    mode: 'mahasiswa' | 'pegawai'
}>();

const mode = computed(() => props.mode ?? 'mahasiswa');

const form = useForm({
    nim: '',
    password: '',
    remember: false,
});

const showPassword = ref(false)

import Swal from 'sweetalert2'

const submit = () => {
    form.post(route('login'), {
        onError: () => {
            Swal.fire({
                icon: 'error',
                title: 'Login Gagal',
                text: 'NIM/NIP atau password salah',
                confirmButtonText: 'OK',
                customClass: {
                    confirmButton: 'swal-confirm-button',
                },
            });
        },
    });
};


function onRegister() {
    router.visit(route('register'))
}
</script>

<template>
    <AuthBase title="Selamat datang di PORLAS FMIPA"
        :description="`Masukan ${mode === 'mahasiswa' ? 'NIM' : 'NIP'} yang terdaftar`">

        <Head title="Log in" />

        <div v-if="status" class="mb-4 text-center text-sm font-medium text-green-600">
            {{ status }}
        </div>

        <form @submit.prevent="submit" class="flex flex-col gap-6">
            <div class="grid gap-6">
                <div class="grid gap-2">
                    <Label for="nim">{{ mode === 'mahasiswa' ? 'NIM' : 'NIP' }}</Label>
                    <Input id="nim" type="text" required autofocus :tabindex="1" @keypress="onlyAllowNumbers"
                        v-model="form.nim" :placeholder="`Masukkan ${mode === 'mahasiswa' ? 'NIM' : 'NIP'}`" />
                </div>

                <div class="grid gap-2">
                    <div class="flex items-center justify-between">
                        <Label for="password">Password</Label>
                        <!-- <TextLink v-if="canResetPassword" :href="route('password.request')" class="text-sm"
                            :tabindex="5">
                            Lupa Password?
                        </TextLink> -->
                    </div>
                    <div class="relative">
                        <Input :type="showPassword ? 'text' : 'password'" id="password" required :tabindex="2"
                            autocomplete="current-password" v-model="form.password" placeholder="Password"
                            class="pr-10" />

                        <button type="button" @click="showPassword = !showPassword"
                            class="absolute inset-y-0 right-0 flex items-center px-3 text-gray-500" :tabindex="-1">
                            <Eye v-if="showPassword" color="black" class="h-5 w-5"></Eye>
                            <EyeClosed v-else color="black" class="h-5 w-5"></EyeClosed>
                        </button>
                    </div>
                </div>


                <div class="mt-4">
                    <Button type="submit" class=" w-full hover:bg-orange-500 bg-orange-400" :tabindex="4"
                        :disabled="form.processing">
                        <LoaderCircle v-if="form.processing" class="h-4 w-4 animate-spin" />
                        Log in
                    </Button>
                    <Button v-if="mode === 'mahasiswa'" @click="onRegister()"
                        class="w-full mt-3 border border-orange-500 text-orange-500 bg-white hover:bg-orange-500 hover:text-white transition-colors duration-300"
                        :tabindex="4">
                        Daftar
                    </Button>
                </div>
            </div>

            <div v-if="mode === 'mahasiswa'" class="text-center text-sm text-muted-foreground">
                Jika terdapat keluhan bisa melapor ke Sub Bagian Akdemik FMIPA ULM
            </div>
            <div v-else class="text-center text-sm text-muted-foreground">
                Hubungi Sub Bagian Akdemik FMIPA ULM untuk mendaftar akun pegawai
            </div>
        </form>
    </AuthBase>
</template>
