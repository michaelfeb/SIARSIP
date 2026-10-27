<script setup lang="ts">
import Heading from '@/components/Heading.vue';
import { showConfirmationAlert } from '@/components/alerts/SwalAlert';
import Button from '@/components/ui/button/Button.vue';
import AppLayout from '@/layouts/AppLayout.vue';
import type { BreadcrumbItem } from '@/types';
import { Head, router } from '@inertiajs/vue3';
import { Plus } from 'lucide-vue-next';
import { computed, ref } from 'vue';
import DataTable from './DataTable.vue';
import type { CutiAkademikActor, CutiAkademikFilters, CutiAkademikLetter, CutiAkademikPaginated, CutiAkademikTableQuery } from './ts/cutiAkademik.types';

const props = defineProps<{ auth: { user: CutiAkademikActor }; letters: CutiAkademikPaginated<CutiAkademikLetter>; filters: CutiAkademikFilters }>();
const breadcrumbs: BreadcrumbItem[] = [{ title: 'Cuti Akademik', href: '/cuti-akademik' }];
const loading = ref(false);
const canCreate = computed(() => [1, 8].includes(props.auth.user.role_id));

function onDataTableQueryChange(query: CutiAkademikTableQuery): void {
    loading.value = true;
    router.get(route('cuti-akademik.index'), { page: query.page, per_page: query.perPage, search: query.search || undefined, status: query.status || undefined }, { only: ['letters', 'filters'], preserveScroll: true, preserveState: true, replace: true, onFinish: () => { loading.value = false; } });
}
function onCreate(): void { router.get(route('cuti-akademik.create')); }
function onView(letter: CutiAkademikLetter): void { router.get(route('cuti-akademik.show', letter.id)); }
function onEdit(letter: CutiAkademikLetter): void { router.get(route('cuti-akademik.edit', letter.id)); }
function onReview(letter: CutiAkademikLetter): void { router.get(route('cuti-akademik.review', letter.id)); }
function onDownload(letter: CutiAkademikLetter): void { window.open(route('cuti-akademik.download-final-letter', letter.id), '_blank', 'noopener'); }
async function onDelete(letter: CutiAkademikLetter): Promise<void> { const result = await showConfirmationAlert({ variant: 'warning', title: 'Hapus draft?', text: 'Draft dan dokumen pendukung akan dihapus.', confirmButtonText: 'Hapus', destructive: true }); if (result.isConfirmed) router.delete(route('cuti-akademik.destroy', letter.id), { preserveScroll: true }); }
async function onSubmit(letter: CutiAkademikLetter): Promise<void> { const result = await showConfirmationAlert({ title: 'Kirim permohonan?', text: 'Permohonan yang dikirim tidak dapat diubah.', confirmButtonText: 'Kirim' }); if (result.isConfirmed) router.put(route('cuti-akademik.submit', letter.id), {}, { preserveScroll: true }); }
</script>

<template>

    <Head title="Cuti Akademik" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="mx-auto w-full space-y-7 p-4 sm:p-6">
            <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
                <Heading title="Permohonan Cuti Akademik" description="Kelola pengajuan cuti akademik mahasiswa." class="!mb-0" />
                <div class="flex w-full flex-col gap-2 sm:w-auto sm:flex-row sm:flex-wrap sm:justify-end">
                    <Button v-if="canCreate" class="w-full sm:w-auto" @click="onCreate">
                        <Plus class="mr-1 size-4" />Tambah Berkas
                    </Button>
                </div>
            </div>
            <DataTable :auth="auth" :letters="letters" :filters="filters" :loading="loading"
                @query-change="onDataTableQueryChange" @view="onView" @edit="onEdit" @delete="onDelete"
                @submit="onSubmit" @review="onReview" @download="onDownload" />
        </div>
    </AppLayout>
</template>
