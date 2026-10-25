<script setup lang="ts">
import Heading from '@/components/Heading.vue';
import Button from '@/components/ui/button/Button.vue';
import AppLayout from '@/layouts/AppLayout.vue';
import type { BreadcrumbItem } from '@/types';
import { Head, router } from '@inertiajs/vue3';
import { Plus } from 'lucide-vue-next';
import Swal from 'sweetalert2';
import { computed, ref } from 'vue';
import DataTable from './DataTable.vue';
import type { CutiAkademikActor, CutiAkademikFilters, CutiAkademikLetter, CutiAkademikPaginated, CutiAkademikTableQuery } from './ts/cutiAkademik.types';

const props = defineProps<{ auth: { user: CutiAkademikActor }; letters: CutiAkademikPaginated<CutiAkademikLetter>; filters: CutiAkademikFilters }>();
const breadcrumbs: BreadcrumbItem[] = [{ title: 'Cuti Akademik', href: '/cuti-akademik' }];
const loading = ref(false);
const canCreate = computed(() => props.auth.user.role_id === 1);

function onDataTableQueryChange(query: CutiAkademikTableQuery): void {
    loading.value = true;
    router.get(route('cuti-akademik.index'), { page: query.page, per_page: query.perPage, search: query.search || undefined, status: query.status || undefined }, { only: ['letters', 'filters'], preserveScroll: true, preserveState: true, replace: true, onFinish: () => { loading.value = false; } });
}
function onCreate(): void { router.get(route('cuti-akademik.create')); }
function onView(letter: CutiAkademikLetter): void { router.get(route('cuti-akademik.show', letter.id)); }
function onEdit(letter: CutiAkademikLetter): void { router.get(route('cuti-akademik.edit', letter.id)); }
function onReview(letter: CutiAkademikLetter): void { router.get(route('cuti-akademik.review', letter.id)); }
function onDownload(letter: CutiAkademikLetter): void { window.open(route('cuti-akademik.download-final-letter', letter.id), '_blank', 'noopener'); }
async function onDelete(letter: CutiAkademikLetter): Promise<void> { const result = await Swal.fire({ icon: 'warning', title: 'Hapus draft?', text: 'Draft dan dokumen pendukung akan dihapus.', showCancelButton: true, confirmButtonText: 'Hapus', cancelButtonText: 'Batal' }); if (result.isConfirmed) router.delete(route('cuti-akademik.destroy', letter.id), { preserveScroll: true }); }
async function onSubmit(letter: CutiAkademikLetter): Promise<void> { const result = await Swal.fire({ icon: 'question', title: 'Kirim permohonan?', text: 'Permohonan yang dikirim tidak dapat diubah.', showCancelButton: true, confirmButtonText: 'Kirim', cancelButtonText: 'Batal' }); if (result.isConfirmed) router.put(route('cuti-akademik.submit', letter.id), {}, { preserveScroll: true }); }
</script>

<template>
    <Head title="Cuti Akademik" />
    <AppLayout :breadcrumbs="breadcrumbs"><div class="mx-auto w-full max-w-[1800px] space-y-6 p-4 sm:p-6"><div class="flex flex-col justify-between gap-4 sm:flex-row sm:items-start"><Heading title="Permohonan Cuti Akademik" description="Kelola pengajuan cuti akademik mahasiswa." /><Button v-if="canCreate" class="rounded-lg" @click="onCreate"><Plus class="mr-1 size-4" />Tambah Permohonan</Button></div><DataTable :auth="auth" :letters="letters" :filters="filters" :loading="loading" @query-change="onDataTableQueryChange" @view="onView" @edit="onEdit" @delete="onDelete" @submit="onSubmit" @review="onReview" @download="onDownload" /></div></AppLayout>
</template>
