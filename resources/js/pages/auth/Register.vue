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

const form = useForm({
    nama: '',
    nim: '',
    email: '',
    password: '',
});

const submit = () => {
    form.post(route('register'), {
        onFinish: () => form.reset('password'),
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
                    <Input id="nim" type="text" required autofocus :tabindex="1" autocomplete="nim" @keypress="onlyAllowNumbers"
                        v-model="form.nim" placeholder="NIM" />
                    <InputError :message="form.errors.nim" />
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

                <div class="">
                    <Button type="submit" class="w-full hover:bg-orange-500 bg-orange-400" tabindex="5" :disabled="form.processing">
                        <LoaderCircle v-if="form.processing" class="h-4 w-4 animate-spin" />
                        Daftar
                    </Button>

                    <Button @click="onLogin()"
                        class="w-full mt-3 border border-orange-500 text-orange-500 bg-white hover:bg-orange-500 hover:text-white transition-colors duration-300"
                        :tabindex="4">
                        Login
                    </Button>
                </div>
            </div>
        </form>
    </AuthBase>
</template>
