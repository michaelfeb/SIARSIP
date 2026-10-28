<script setup lang="ts">
import Heading from '@/components/Heading.vue';
import { showConfirmationAlert, showSuccessAlert } from '@/components/alerts/SwalAlert';
import { Button } from '@/components/ui/button';
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, router } from '@inertiajs/vue3';
import { Plus } from 'lucide-vue-next';
import { ref } from 'vue';
import DataTable from './DataTable.vue';
import { canResetUserPassword } from './ts/users.capabilities';
import type { UserFilters, UserPage, UserQuery, UserRecord } from './ts/users.types';
defineProps<{ auth: { user: { role_id: number } }; users: UserPage<UserRecord>; filters: UserFilters }>();
const loading = ref(false);
function query(q: UserQuery) {
    loading.value = true;
    router.get(
        route('users.index'),
        { audience: q.audience, search: q.search || undefined, page: q.page, per_page: q.perPage },
        { only: ['users', 'filters'], preserveState: true, preserveScroll: true, replace: true, onFinish: () => (loading.value = false) },
    );
}
function onView(u: UserRecord) {
    router.get(route('users.show', u.id));
}
function onEdit(u: UserRecord) {
    router.get(route('users.edit', u.id));
}
async function onRemove(u: UserRecord) {
    if (
        (
            await showConfirmationAlert({
                variant: 'warning',
                title: 'Hapus pengguna?',
                text: 'Data pengguna tidak dapat dikembalikan.',
                confirmButtonText: 'Hapus',
                destructive: true,
            })
        ).isConfirmed
    )
        router.delete(route('users.destroy', u.id));
}
async function onReset(u: UserRecord) {
    if (
        (
            await showConfirmationAlert({
                variant: 'warning',
                title: 'Reset password?',
                text: 'Password akan direset ke Password1234.',
                confirmButtonText: 'Reset',
            })
        ).isConfirmed
    )
        router.post(route('users.reset-password', u.id), {}, { onSuccess: () => showSuccessAlert({ text: 'Password berhasil direset.' }) });
}
</script>
<template>

    <Head title="Pengguna" />
    <AppLayout :breadcrumbs="[{ title: 'Pengguna', href: '/users' }]">
        <div class="space-y-6 p-4 sm:p-6">
            <div class="flex justify-between">
                <Heading title="Manajemen Pengguna" class="!mb-0" description="Kelola pegawai dan mahasiswa." /><Button
                    @click="router.get(route('users.create'))">
                    <Plus />Tambah Pengguna
                </Button>
            </div>
            <DataTable :users="users" :filters="filters" :loading="loading"
                :can-reset="canResetUserPassword(auth.user.role_id)" @query="query" @view="onView" @edit="onEdit"
                @delete="onRemove" @reset="onReset" />
        </div>
    </AppLayout>
</template>
