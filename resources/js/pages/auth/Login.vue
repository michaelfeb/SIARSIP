<script setup lang="ts">
import InputError from '@/components/InputError.vue';
import TextLink from '@/components/TextLink.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import AuthBase from '@/layouts/AuthLayout.vue';
import { Head, router, useForm } from '@inertiajs/vue3';
import { Eye, EyeOff, LoaderCircle } from 'lucide-vue-next';
import { onlyAllowNumbers } from '@/utils/inputValidatior';
import { computed, ref } from 'vue';
import Swal from 'sweetalert2';

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

const showPassword = ref(false);

function onSubmit() {
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
}

function onRegister() {
    router.visit(route('register'))
}
</script>

<template>
    <AuthBase title="Selamat datang di PORLAS FMIPA"
        :description="`Masukkan ${mode === 'mahasiswa' ? 'NIM' : 'NIP'} dan password yang terdaftar.`">

        <Head title="Masuk" />

        <div v-if="status" class="mb-4 text-center text-sm font-medium text-green-600">
            {{ status }}
        </div>

        <form @submit.prevent="onSubmit" class="flex flex-col gap-6">
            <div class="grid gap-5">
                <div class="grid gap-2">
                    <Label for="nim">{{ mode === 'mahasiswa' ? 'NIM' : 'NIP' }}</Label>
                    <Input id="nim" v-model="form.nim" type="text" required autofocus :tabindex="1" inputmode="numeric"
                        autocomplete="username" @keypress="onlyAllowNumbers" :aria-invalid="Boolean(form.errors.nim)"
                        :placeholder="`Masukkan ${mode === 'mahasiswa' ? 'NIM' : 'NIP'}`" />
                    <InputError :message="form.errors.nim" />
                </div>

                <div class="grid gap-2">
                    <div class="flex items-center justify-between">
                        <Label for="password">Password</Label>
                        <TextLink v-if="canResetPassword" :href="route('password.request')" class="text-sm text-[#c2410c] hover:text-[#9a3412]" :tabindex="4">
                            Lupa password?
                        </TextLink>
                    </div>
                    <div class="relative">
                        <Input :type="showPassword ? 'text' : 'password'" id="password" required :tabindex="2"
                            autocomplete="current-password" v-model="form.password" placeholder="Password"
                            class="pr-11" :aria-invalid="Boolean(form.errors.password)" />

                        <button type="button" @click="showPassword = !showPassword"
                            class="absolute inset-y-0 right-0 flex items-center px-3 text-slate-500 transition hover:text-[#c2410c] focus-visible:outline-none" :aria-label="showPassword ? 'Sembunyikan password' : 'Tampilkan password'" :tabindex="-1">
                            <EyeOff v-if="showPassword" class="size-4" />
                            <Eye v-else class="size-4" />
                        </button>
                    </div>
                    <InputError :message="form.errors.password" />
                </div>

                <div class="mt-2">
                    <Button type="submit" class="w-full" :tabindex="3"
                        :disabled="form.processing">
                        <LoaderCircle v-if="form.processing" class="h-4 w-4 animate-spin" />
                        Masuk
                    </Button>
                    <Button v-if="mode === 'mahasiswa'" type="button" variant="outline" @click="onRegister()"
                        class="mt-3 w-full border-[#c2410c] text-[#c2410c] hover:bg-[#c2410c] hover:text-white"
                        :tabindex="5">
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
