<script setup lang="ts">
import DataTable from '@/components/data-table/DataTable.vue';
import type { DataTableHeader, DataTableServerOptions } from '@/components/data-table/DataTable.types';
import Button from '@/components/ui/button/Button.vue';
import Input from '@/components/ui/input/Input.vue';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Check, Download, Eye, Pencil, RotateCcw, Search, Send, Trash2 } from 'lucide-vue-next';
import { computed, onBeforeUnmount, ref, watch } from 'vue';
import {
    SIDANG_NOL_STATUS,
    formatSidangNolDate,
    programStudiLabel,
    sidangNolCapabilities,
} from './ts/constants';
import type {
    BerkasSidangNolDataTableFilters,
    BerkasSidangNolDataTableQuery,
    Paginated,
    SidangNol,
    SidangNolAuth,
    SidangNolStatus,
} from './ts/types';

interface Props {
    auth: SidangNolAuth;
    berkas: Paginated<SidangNol>;
    filters: BerkasSidangNolDataTableFilters;
    loading: boolean;
}

const props = defineProps<Props>();

const emit = defineEmits<{
    'query-change': [query: BerkasSidangNolDataTableQuery];
    view: [id: number];
    edit: [id: number];
    delete: [berkas: SidangNol];
    submit: [berkas: SidangNol];
    reset: [berkas: SidangNol];
    review: [id: number];
    'download-reply': [berkas: SidangNol];
}>();

const headers: DataTableHeader[] = [
    { text: 'Mahasiswa', value: 'mahasiswa_nim' },
    { text: 'Program Studi', value: 'program_studi' },
    { text: 'Nomor Surat', value: 'nomor_surat' },
    { text: 'Status', value: 'status' },
    { text: 'Dikirim', value: 'tanggal_dikirim' },
    { text: 'Aksi', value: 'id', width: 230 },
];

const search = ref(props.filters.search ?? '');
const status = ref<SidangNolStatus | 'all'>(props.filters.status ?? 'all');
const serverOptions = ref<DataTableServerOptions>({
    page: props.berkas.current_page,
    rowsPerPage: props.berkas.per_page,
});

let searchTimer: ReturnType<typeof setTimeout> | undefined;

const items = computed(() => props.berkas.data);
const isStudent = computed(() => props.auth.user.role_id === 1);
const statusOptions = computed(() =>
    Object.entries(SIDANG_NOL_STATUS)
        .map(([value, presentation]) => ({
            value: Number(value) as SidangNolStatus,
            label: presentation.label,
        }))
        .filter((option) => isStudent.value || option.value !== 0),
);

watch(
    () => [props.berkas.current_page, props.berkas.per_page],
    ([page, rowsPerPage]) => {
        if (serverOptions.value.page !== page || serverOptions.value.rowsPerPage !== rowsPerPage) {
            serverOptions.value = { page, rowsPerPage };
        }
    },
);

watch(
    () => props.filters,
    (filters) => {
        const nextSearch = filters.search ?? '';
        const nextStatus = filters.status ?? 'all';

        if (search.value !== nextSearch) {
            search.value = nextSearch;
        }

        if (status.value !== nextStatus) {
            status.value = nextStatus;
        }
    },
    { deep: true },
);

watch(
    () => [serverOptions.value.page, serverOptions.value.rowsPerPage],
    ([page, rowsPerPage]) => {
        if (page !== props.berkas.current_page || rowsPerPage !== props.berkas.per_page) {
            emitQueryChange(page);
        }
    },
);

onBeforeUnmount(() => {
    if (searchTimer) {
        clearTimeout(searchTimer);
    }
});

function onSearchChange(): void {
    if (searchTimer) {
        clearTimeout(searchTimer);
    }

    searchTimer = setTimeout(() => {
        requestFirstPage();
    }, 300);
}

function onStatusFilterChange(value: SidangNolStatus | 'all'): void {
    status.value = value;

    if (searchTimer) {
        clearTimeout(searchTimer);
    }

    requestFirstPage();
}

function requestFirstPage(): void {
    if (serverOptions.value.page !== 1) {
        serverOptions.value = { ...serverOptions.value, page: 1 };

        return;
    }

    emitQueryChange(1);
}

function emitQueryChange(page = serverOptions.value.page): void {
    emit('query-change', {
        page,
        perPage: serverOptions.value.rowsPerPage,
        search: search.value.trim() || null,
        status: status.value === 'all' ? null : status.value,
    });
}

function onView(id: number): void {
    emit('view', id);
}

function onEdit(id: number): void {
    emit('edit', id);
}

function onDelete(berkas: SidangNol): void {
    emit('delete', berkas);
}

function onSubmit(berkas: SidangNol): void {
    emit('submit', berkas);
}

function onReset(berkas: SidangNol): void {
    emit('reset', berkas);
}

function onReview(id: number): void {
    emit('review', id);
}

function onDownloadReply(berkas: SidangNol): void {
    emit('download-reply', berkas);
}

function statusPresentation(status: number) {
    return SIDANG_NOL_STATUS[status as SidangNolStatus] ?? SIDANG_NOL_STATUS[0];
}
</script>

<template>
    <section class="space-y-3">
        <div class="space-y-3">
            <p class="text-xs font-semibold uppercase tracking-wide text-foreground">Cari &amp; Filter</p>

            <div class="grid gap-3 sm:grid-cols-[minmax(0,1fr)_minmax(12rem,0.45fr)]">
                <div class="relative">
                    <Search
                        class="pointer-events-none absolute top-1/2 left-3 size-4 -translate-y-1/2 text-muted-foreground" />
                    <Input v-model="search" type="search" placeholder="Cari nama, NIM, atau nomor surat..."
                        class="h-10 pl-9" @input="onSearchChange" />
                </div>

                <Select :model-value="status" @update:model-value="onStatusFilterChange">
                    <SelectTrigger class="h-10 w-full">
                        <SelectValue placeholder="Semua Status" />
                    </SelectTrigger>
                    <SelectContent>
                        <SelectItem value="all">Semua Status</SelectItem>
                        <SelectItem v-for="option in statusOptions" :key="option.value" :value="option.value">
                            {{ option.label }}
                        </SelectItem>
                    </SelectContent>
                </Select>
            </div>
        </div>

        <DataTable v-model:server-options="serverOptions" :headers="headers" :items="items" :loading="loading"
            :server-items-length="berkas.total" items-label="surat">

            <template #item-mahasiswa_nim="{ user }">
                <div class="space-y-0.5">
                    <p class="font-semibold text-foreground">{{ user?.nama ?? '-' }}</p>
                    <p class="text-xs text-muted-foreground">{{ user?.nim ?? '-' }}</p>
                </div>
            </template>

            <template #item-program_studi="{ user }">
                {{ programStudiLabel(user?.program_studi) }}
            </template>

            <template #item-nomor_surat="{ nomor_surat }">
                <code v-if="nomor_surat"
                    class="rounded bg-muted px-2 py-1 text-xs text-muted-foreground">{{ nomor_surat }}</code>
                <span v-else class="text-muted-foreground">—</span>
            </template>

            <template #item-tanggal_dikirim="{ tanggal_dikirim }">
                {{ formatSidangNolDate(tanggal_dikirim) }}
            </template>

            <template #item-status="{ status: berkasStatus }">
                <span
                    :class="['inline-flex items-center rounded-full px-2.5 py-1 text-xs font-medium', statusPresentation(berkasStatus).badgeClass]">
                    <component :is="statusPresentation(berkasStatus).icon" class="mr-1 size-3" />
                    {{ statusPresentation(berkasStatus).label }}
                </span>
            </template>

            <template #item-id="{ id, status: berkasStatus, user, ...berkas }">
                <div class="flex min-w-max items-center gap-2">
                    <Button variant="outline" size="icon"
                        class="size-8 rounded-lg border-blue-200 bg-white text-blue-600 hover:bg-blue-600 hover:text-white"
                        title="Detail" aria-label="Detail" @click="onView(id)">
                        <Eye class="size-4" />
                    </Button>
                    <Button v-if="sidangNolCapabilities(auth.user, { status: berkasStatus }).canReview"
                        variant="outline" size="icon"
                        class="size-8 rounded-lg border-emerald-200 bg-white text-emerald-600 hover:bg-emerald-600 hover:text-white"
                        title="Periksa berkas" aria-label="Periksa berkas" @click="onReview(id)">
                        <Check class="size-4" />
                    </Button>
                    <Button v-if="sidangNolCapabilities(auth.user, { status: berkasStatus }).canSubmit"
                        variant="outline" size="icon"
                        class="size-8 rounded-lg border-blue-200 bg-white text-blue-600 hover:bg-blue-600 hover:text-white"
                        title="Kirim" aria-label="Kirim"
                        @click="onSubmit({ id, status: berkasStatus, user, ...berkas } as SidangNol)">
                        <Send class="size-4" />
                    </Button>
                    <Button v-if="sidangNolCapabilities(auth.user, { status: berkasStatus }).canReset" variant="outline"
                        size="icon"
                        class="size-8 rounded-lg border-sky-200 bg-white text-sky-600 hover:bg-sky-600 hover:text-white"
                        title="Reset" aria-label="Reset"
                        @click="onReset({ id, status: berkasStatus, user, ...berkas } as SidangNol)">
                        <RotateCcw class="size-4" />
                    </Button>
                    <Button v-if="sidangNolCapabilities(auth.user, { status: berkasStatus }).canEdit" variant="outline"
                        size="icon"
                        class="size-8 rounded-lg border-amber-200 bg-white text-amber-600 hover:bg-amber-500 hover:text-white"
                        title="Ubah" aria-label="Ubah" @click="onEdit(id)">
                        <Pencil class="size-4" />
                    </Button>
                    <Button v-if="sidangNolCapabilities(auth.user, { status: berkasStatus }).canDelete"
                        variant="outline" size="icon"
                        class="size-8 rounded-lg border-rose-200 bg-white text-rose-600 hover:bg-rose-600 hover:text-white"
                        title="Hapus" aria-label="Hapus"
                        @click="onDelete({ id, status: berkasStatus, user, ...berkas } as SidangNol)">
                        <Trash2 class="size-4" />
                    </Button>
                    <Button v-if="sidangNolCapabilities(auth.user, { status: berkasStatus }).canDownloadReply"
                        variant="outline" size="icon"
                        class="size-8 rounded-lg border-emerald-200 bg-white text-emerald-600 hover:bg-emerald-600 hover:text-white"
                        title="Download surat balasan" aria-label="Download surat balasan"
                        @click="onDownloadReply({ id, status: berkasStatus, user, ...berkas } as SidangNol)">
                        <Download class="size-4" />
                    </Button>
                </div>
            </template>
        </DataTable>
    </section>
</template>
