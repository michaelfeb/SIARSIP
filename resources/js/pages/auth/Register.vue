<script setup lang="ts">
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import AuthBase from '@/layouts/AuthLayout.vue';
import { Head, router, useForm } from '@inertiajs/vue3';
import { Eye, EyeOff, LoaderCircle } from 'lucide-vue-next';
import { onlyAllowNumbers } from '@/utils/inputValidatior';
import programStudiMapping from '@/utils/programStudiMapping';
import Swal from 'sweetalert2';
import { ref } from 'vue';

const form = useForm({
    nama: '',
    nim: '',
    email: '',
    program_studi: '',
    nomor_telpon: '',
    password: '',
    password_confirmation: '',
});

const showPassword = ref(false);
const showPasswordConfirmation = ref(false);

function onSubmit() {
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
}

function onLogin() {
    router.visit(route('login'));
}
</script>

<template>
    <AuthBase title="Daftar akun mahasiswa" description="Lengkapi data diri untuk membuat akun mahasiswa.">

        <Head title="Daftar" />

        <form @submit.prevent="onSubmit" class="flex flex-col gap-6">
            <div class="grid gap-5">
                <div class="grid gap-2">
                    <Label for="nama">Nama</Label>
                    <Input id="nama" type="text" required autofocus :tabindex="1" autocomplete="nama"
                        v-model="form.nama" :aria-invalid="Boolean(form.errors.nama)" placeholder="Nama lengkap" />
                    <InputError :message="form.errors.nama" />
                </div>

                <div class="grid gap-2">
                    <Label for="nim">NIM</Label>
                    <Input id="nim" type="text" required :tabindex="2" autocomplete="username" inputmode="numeric"
                        @keypress="onlyAllowNumbers" v-model="form.nim" :aria-invalid="Boolean(form.errors.nim)" placeholder="Masukkan NIM" />
                    <InputError :message="form.errors.nim" />
                </div>

                <div class="grid gap-2">
                    <Label for="program_studi">Program Studi</Label>
                    <Select v-model="form.program_studi">
                        <SelectTrigger id="program_studi" :tabindex="3" :aria-invalid="Boolean(form.errors.program_studi)">
                            <SelectValue placeholder="Pilih program studi" />
                        </SelectTrigger>
                        <SelectContent>
                            <SelectItem v-for="(value, key) in programStudiMapping" :key="key" :value="String(value.value)">
                                {{ value.label }}
                            </SelectItem>
                        </SelectContent>
                    </Select>
                    <InputError :message="form.errors.program_studi" />
                </div>

                <div class="grid gap-2">
                    <Label for="nomor_telpon">Nomor Telpon</Label>
                    <Input id="nomor_telpon" v-model="form.nomor_telpon" type="tel" required :tabindex="4" inputmode="numeric"
                        autocomplete="tel" @keypress="onlyAllowNumbers" :aria-invalid="Boolean(form.errors.nomor_telpon)"
                        placeholder="Contoh: 081234567890" />
                    <InputError :message="form.errors.nomor_telpon" />
                </div>

                <div class="grid gap-2">
                    <Label for="email">Email</Label>
                    <Input id="email" type="email" required :tabindex="5" autocomplete="email" v-model="form.email"
                        :aria-invalid="Boolean(form.errors.email)" placeholder="email@ulm.ac.id" />
                    <InputError :message="form.errors.email" />
                </div>

                <div class="grid gap-2">
                    <Label for="password">Password</Label>
                    <div class="relative">
                        <Input id="password" :type="showPassword ? 'text' : 'password'" required :tabindex="6" autocomplete="new-password"
                            v-model="form.password" class="pr-11" :aria-invalid="Boolean(form.errors.password)" placeholder="Minimal 8 karakter" />
                        <button type="button" class="password-toggle" :aria-label="showPassword ? 'Sembunyikan password' : 'Tampilkan password'" :tabindex="-1" @click="showPassword = !showPassword">
                            <EyeOff v-if="showPassword" class="size-4" />
                            <Eye v-else class="size-4" />
                        </button>
                    </div>
                    <InputError :message="form.errors.password" />
                </div>

                <div class="grid gap-2">
                    <Label for="password_confirmation">Konfirmasi Password</Label>
                    <div class="relative">
                        <Input id="password_confirmation" required v-model="form.password_confirmation" :type="showPasswordConfirmation ? 'text' : 'password'"
                            class="pr-11" autocomplete="new-password" :tabindex="7" :aria-invalid="Boolean(form.errors.password_confirmation)" placeholder="Ulangi password" />
                        <button type="button" class="password-toggle" :aria-label="showPasswordConfirmation ? 'Sembunyikan konfirmasi password' : 'Tampilkan konfirmasi password'" :tabindex="-1" @click="showPasswordConfirmation = !showPasswordConfirmation">
                            <EyeOff v-if="showPasswordConfirmation" class="size-4" />
                            <Eye v-else class="size-4" />
                        </button>
                    </div>
                    <InputError :message="form.errors.password_confirmation" />
                </div>

                <div class="mt-2">
                    <Button type="submit" class="w-full" tabindex="8"
                        :disabled="form.processing">
                        <LoaderCircle v-if="form.processing" class="h-4 w-4 animate-spin" />
                        Daftar
                    </Button>

                    <Button type="button" variant="outline" @click="onLogin()"
                        class="mt-3 w-full border-[#c2410c] text-[#c2410c] hover:bg-[#c2410c] hover:text-white"
                        :tabindex="9">
                        Login
                    </Button>
                </div>
            </div>
        </form>
    </AuthBase>
</template>

<style scoped>
.password-toggle {
    position: absolute;
    top: 0;
    right: 0;
    bottom: 0;
    display: flex;
    align-items: center;
    padding: 0 0.75rem;
    color: #64748b;
    transition: color 150ms;
}

.password-toggle:hover {
    color: #c2410c;
}

.password-toggle:focus-visible {
    outline: none;
    color: #c2410c;
}
</style>
