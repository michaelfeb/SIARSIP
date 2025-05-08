<script setup lang="ts">
import InputError from '@/components/InputError.vue';
import AppLayout from '@/layouts/AppLayout.vue';
import SettingsLayout from '@/layouts/settings/Layout.vue';
import { Head, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';

import HeadingSmall from '@/components/HeadingSmall.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { type BreadcrumbItem } from '@/types';
import Swal from 'sweetalert2';

const breadcrumbItems: BreadcrumbItem[] = [
    {
        title: 'Pengaturan Password',
        href: '/settings/password',
    },
];

const passwordInput = ref<HTMLInputElement | null>(null);
const currentPasswordInput = ref<HTMLInputElement | null>(null);

const form = useForm({
    current_password: '',
    password: '',
    password_confirmation: '',
});

const updatePassword = () => {
    form.put(route('password.update'), {
        preserveScroll: true,
        onSuccess: () => {
            Swal.fire({
                icon: 'success',
                title: 'Berhasil!',
                text: 'Profile telah diperbarui',
                confirmButtonText: 'OK',
                customClass: {
                    confirmButton: 'swal-confirm-button',
                },
            }).then(() => {
                location.reload(); 
                form.reset()
            })
        },
        onError: (errors) => {
            Swal.fire({
                icon: 'error',
                title: 'Gagal!',
                text: 'Gagal memperbarui data!',
                confirmButtonText: 'OK',
                customClass: {
                    confirmButton: 'swal-confirm-button',
                },
            }).then(() => {
                if (errors.password) {
                    form.reset('password', 'password_confirmation');
                    if (passwordInput.value instanceof HTMLInputElement) {
                        passwordInput.value.focus();
                    }
                }

                if (errors.current_password) {
                    form.reset('current_password');
                    if (currentPasswordInput.value instanceof HTMLInputElement) {
                        currentPasswordInput.value.focus();
                    }
                }
            })
        }
    });
};
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbItems">

        <Head title="Password settings" />

        <SettingsLayout>
            <div class="space-y-6">
                <HeadingSmall title="Perbaharui password" description="Masukan password lama dan baru" />

                <form @submit.prevent="updatePassword" class="space-y-6">
                    <div class="grid gap-2">
                        <Label for="current_password">Password lama</Label>
                        <Input id="current_password" ref="currentPasswordInput" v-model="form.current_password"
                            type="password" class="mt-1 block w-full" autocomplete="current-password"
                            placeholder="Current password" />
                        <InputError :message="form.errors.current_password" />
                    </div>

                    <div class="grid gap-2">
                        <Label for="password">Password baru</Label>
                        <Input id="password" ref="passwordInput" v-model="form.password" type="password"
                            class="mt-1 block w-full" autocomplete="new-password" placeholder="New password" />
                        <InputError :message="form.errors.password" />
                    </div>

                    <div class="grid gap-2">
                        <Label for="password_confirmation">Konfirmasi Password</Label>
                        <Input id="password_confirmation" v-model="form.password_confirmation" type="password"
                            class="mt-1 block w-full" autocomplete="new-password" placeholder="Confirm password" />
                        <InputError :message="form.errors.password_confirmation" />
                    </div>

                    <div class="flex items-center gap-4 justify-end">
                        <Button class="hover:bg-blue-600 bg-blue-500" :disabled="form.processing">Simpan
                            Password</Button>

                        <Transition enter-active-class="transition ease-in-out" enter-from-class="opacity-0"
                            leave-active-class="transition ease-in-out" leave-to-class="opacity-0">
                            <p v-show="form.recentlySuccessful" class="text-sm text-neutral-600">Saved.</p>
                        </Transition>
                    </div>
                </form>
            </div>
        </SettingsLayout>
    </AppLayout>
</template>
