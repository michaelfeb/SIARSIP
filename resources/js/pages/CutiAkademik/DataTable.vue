<script setup lang="ts">
import DataTable from '@/components/data-table/DataTable.vue';
import type { DataTableHeader, DataTableServerOptions } from '@/components/data-table/DataTable.types';
import Button from '@/components/ui/button/Button.vue';
import Input from '@/components/ui/input/Input.vue';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Download, Eye, Pencil, Search, Send, Trash2, UserRoundCheck } from 'lucide-vue-next';
import { computed, onBeforeUnmount, ref, watch } from 'vue';
import Badge from '@/components/letters/Badge.vue';
import { getCutiAkademikCapabilities } from './ts/cutiAkademik.capabilities';
import { CUTI_AKADEMIK_STATUS } from './ts/cutiAkademik.constants';
import type { CutiAkademikActor, CutiAkademikFilters, CutiAkademikLetter, CutiAkademikPaginated, CutiAkademikStatus, CutiAkademikTableQuery } from './ts/cutiAkademik.types';

const props = defineProps<{ auth: { user: CutiAkademikActor }; letters: CutiAkademikPaginated<CutiAkademikLetter>; filters: CutiAkademikFilters; loading: boolean }>();
const emit = defineEmits<{
    'query-change': [query: CutiAkademikTableQuery];
    view: [letter: CutiAkademikLetter];
    edit: [letter: CutiAkademikLetter];
    delete: [letter: CutiAkademikLetter];
    submit: [letter: CutiAkademikLetter];
    review: [letter: CutiAkademikLetter];
    download: [letter: CutiAkademikLetter];
}>();

const headers: DataTableHeader[] = [
    { text: 'Mahasiswa', value: 'applicant_name' },
    { text: 'Program Studi', value: 'program_studi' },
    { text: 'Nomor Surat', value: 'letter_number' },
    { text: 'Semester', value: 'semester' },
    { text: 'Status', value: 'status' },
    { text: 'Stage', value: 'current_stage' },
    { text: 'Dikirim', value: 'submitted_at' },
    { text: 'Aksi', value: 'id', width: 220 },
];

const search = ref(props.filters.search ?? '');
const status = ref<CutiAkademikStatus | 'all'>(props.filters.status ?? 'all');
const serverOptions = ref<DataTableServerOptions>({ page: props.letters.current_page, rowsPerPage: props.letters.per_page });
let searchTimer: ReturnType<typeof setTimeout> | undefined;
const items = computed(() => props.letters.data);

watch(() => [props.letters.current_page, props.letters.per_page], ([page, rowsPerPage]) => { serverOptions.value = { page, rowsPerPage }; });
watch(() => props.filters, (filters) => { search.value = filters.search ?? ''; status.value = filters.status ?? 'all'; }, { deep: true });
watch(() => [serverOptions.value.page, serverOptions.value.rowsPerPage], ([page, rowsPerPage]) => {
    if (page !== props.letters.current_page || rowsPerPage !== props.letters.per_page) onEmitQuery(page);
});
onBeforeUnmount(() => { if (searchTimer) clearTimeout(searchTimer); });

function onSearchChange(): void {
    if (searchTimer) clearTimeout(searchTimer);
    searchTimer = setTimeout(() => onFirstPage(), 300);
}

function onStatusChange(value: CutiAkademikStatus | 'all'): void {
    status.value = value;
    onFirstPage();
}

function onFirstPage(): void {
    if (serverOptions.value.page !== 1) { serverOptions.value = { ...serverOptions.value, page: 1 }; return; }
    onEmitQuery(1);
}

function onEmitQuery(page = serverOptions.value.page): void {
    emit('query-change', { page, perPage: serverOptions.value.rowsPerPage, search: search.value.trim() || null, status: status.value === 'all' ? null : status.value });
}

function onView(letter: CutiAkademikLetter): void { emit('view', letter); }
function onEdit(letter: CutiAkademikLetter): void { emit('edit', letter); }
function onDelete(letter: CutiAkademikLetter): void { emit('delete', letter); }
function onSubmit(letter: CutiAkademikLetter): void { emit('submit', letter); }
function onReview(letter: CutiAkademikLetter): void { emit('review', letter); }
function onDownload(letter: CutiAkademikLetter): void { emit('download', letter); }

function formatDate(value: string | null): string {
    return value ? new Intl.DateTimeFormat('id-ID', { day: '2-digit', month: 'short', year: 'numeric' }).format(new Date(value)) : '—';
}
</script>

<template>
    <section class="space-y-3">
        <div class="space-y-3">
            <p class="text-xs font-semibold uppercase tracking-wide">Cari &amp; Filter</p>
            <div class="grid gap-3 sm:grid-cols-[minmax(0,1fr)_minmax(12rem,0.45fr)]">
                <div class="relative">
                    <Search
                        class="pointer-events-none absolute top-1/2 left-3 size-4 -translate-y-1/2 text-muted-foreground" />
                    <Input v-model="search" type="search" placeholder="Cari nama, NIM, atau nomor surat..."
                        class="h-10 pl-9" @input="onSearchChange" />
                </div>
                <Select :model-value="status" @update:model-value="onStatusChange">
                    <SelectTrigger class="h-10 w-full">
                        <SelectValue placeholder="Semua Status" />
                    </SelectTrigger>
                    <SelectContent>
                        <SelectItem value="all">Semua Status</SelectItem>
                        <SelectItem v-for="(presentation, value) in CUTI_AKADEMIK_STATUS" :key="value" :value="value">{{
                            presentation.label }}</SelectItem>
                    </SelectContent>
                </Select>
            </div>
        </div>

        <DataTable v-model:server-options="serverOptions" :headers="headers" :items="items" :loading="loading"
            :server-items-length="letters.total" items-label="permohonan">
            <template #item-applicant_name="{ applicant_name, applicant_id_number }">
                <div class="space-y-0.5">
                    <p class="font-semibold">{{ applicant_name }}</p>
                    <p class="text-xs text-muted-foreground">{{ applicant_id_number }}</p>
                </div>
            </template>
            <template #item-program_studi="{ program_studi }">{{ program_studi ?? '—' }}</template>
            <template #item-letter_number="{ letter_number }">{{ letter_number ?? '—' }}</template>
            <template #item-semester="{ meta }">{{ meta?.semester ?? '—' }}</template>
            <template #item-status="{ status: rowStatus }">
                <Badge type="status" :value="rowStatus" />
            </template>
            <template #item-current_stage="{ current_stage }"><Badge type="stage" :value="current_stage" /></template>
            <template #item-submitted_at="{ submitted_at }">{{ formatDate(submitted_at) }}</template>
            <template #item-id="{ id, ...rawLetter }">
                <div class="flex min-w-max items-center gap-2"><Button variant="outline" size="icon"
                        class="size-8 rounded-lg border-blue-200 bg-white text-blue-600 hover:bg-blue-600 hover:text-white"
                        title="Detail" aria-label="Detail" @click="onView({ id, ...rawLetter })">
                        <Eye class="size-4" />
                    </Button><Button v-if="getCutiAkademikCapabilities(auth.user, { id, ...rawLetter }).canReview"
                        variant="outline" size="icon"
                        class="size-8 rounded-lg border-emerald-200 bg-white text-emerald-600 hover:bg-emerald-600 hover:text-white"
                        title="Proses" aria-label="Proses" @click="onReview({ id, ...rawLetter })">
                        <UserRoundCheck class="size-4" />
                    </Button><Button v-if="getCutiAkademikCapabilities(auth.user, { id, ...rawLetter }).canEdit"
                        variant="outline" size="icon"
                        class="size-8 rounded-lg border-amber-200 bg-white text-amber-600 hover:bg-amber-500 hover:text-white"
                        title="Ubah" aria-label="Ubah" @click="onEdit({ id, ...rawLetter })">
                        <Pencil class="size-4" />
                    </Button><Button v-if="getCutiAkademikCapabilities(auth.user, { id, ...rawLetter }).canSubmit"
                        variant="outline" size="icon"
                        class="size-8 rounded-lg border-blue-200 bg-white text-blue-600 hover:bg-blue-600 hover:text-white"
                        title="Kirim" aria-label="Kirim" @click="onSubmit({ id, ...rawLetter })">
                        <Send class="size-4" />
                    </Button><Button v-if="getCutiAkademikCapabilities(auth.user, { id, ...rawLetter }).canDelete"
                        variant="outline" size="icon"
                        class="size-8 rounded-lg border-rose-200 bg-white text-rose-600 hover:bg-rose-600 hover:text-white"
                        title="Hapus" aria-label="Hapus" @click="onDelete({ id, ...rawLetter })">
                        <Trash2 class="size-4" />
                    </Button><Button v-if="getCutiAkademikCapabilities(auth.user, { id, ...rawLetter }).canDownload"
                        variant="outline" size="icon"
                        class="size-8 rounded-lg border-emerald-200 bg-white text-emerald-600 hover:bg-emerald-600 hover:text-white"
                        title="Unduh surat" aria-label="Unduh surat" @click="onDownload({ id, ...rawLetter })">
                        <Download class="size-4" />
                    </Button></div>
            </template>
        </DataTable>
    </section>
</template>
