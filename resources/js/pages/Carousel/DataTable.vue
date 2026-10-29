<script setup lang="ts">
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import DataTable from '@/components/data-table/DataTable.vue';
import type { DataTableHeader, DataTableServerOptions } from '@/components/data-table/DataTable.types';
import { formatDate } from '@/utils/date';
import { Pencil, Power, Search, Trash2 } from 'lucide-vue-next';
import { computed, onBeforeUnmount, ref, watch } from 'vue';
import { CAROUSEL_STATUS } from './ts/carousel.constants';
import type { CarouselFilters, CarouselPaginated, CarouselRecord, CarouselTableQuery } from './ts/carousel.types';

const props = defineProps<{
    carousel: CarouselPaginated<CarouselRecord>;
    filters: CarouselFilters;
    loading: boolean;
    capabilities: { canEdit: boolean; canDelete: boolean; canToggleStatus: boolean };
}>();

const emit = defineEmits<{
    'query-change': [query: CarouselTableQuery];
    edit: [carousel: CarouselRecord];
    delete: [carousel: CarouselRecord];
    toggle: [carousel: CarouselRecord];
}>();

const headers: DataTableHeader[] = [
    { text: 'Nama', value: 'nama' },
    { text: 'Tanggal Publikasi', value: 'tanggal_publish' },
    { text: 'Status', value: 'status', width: 160 },
    { text: 'Aksi', value: 'id', sortable: false, width: 120 },
];

const search = ref(props.filters.search ?? '');
const serverOptions = ref<DataTableServerOptions>({
    page: props.carousel.current_page,
    rowsPerPage: props.carousel.per_page,
});
let searchTimer: ReturnType<typeof setTimeout> | undefined;

watch(
    () => props.carousel,
    (carousel) => {
        serverOptions.value = { page: carousel.current_page, rowsPerPage: carousel.per_page };
    },
);

watch(
    () => props.filters.search,
    (value) => {
        search.value = value ?? '';
    },
);

watch(
    () => [serverOptions.value.page, serverOptions.value.rowsPerPage],
    ([page, perPage]) => {
        if (page !== props.carousel.current_page || perPage !== props.carousel.per_page) {
            onEmitQuery(page);
        }
    },
);

const statusLabel = computed(() => (status: boolean) => (status ? CAROUSEL_STATUS.active : CAROUSEL_STATUS.inactive));

function onSearchChange(): void {
    if (searchTimer) clearTimeout(searchTimer);
    searchTimer = setTimeout(() => onFirstPage(), 300);
}

function onFirstPage(): void {
    if (serverOptions.value.page !== 1) {
        serverOptions.value = { ...serverOptions.value, page: 1 };

        return;
    }

    onEmitQuery(1);
}

function onEmitQuery(page = serverOptions.value.page): void {
    emit('query-change', { page, perPage: serverOptions.value.rowsPerPage, search: search.value.trim() || null });
}

function onEdit(carousel: CarouselRecord): void {
    emit('edit', carousel);
}

function onDelete(carousel: CarouselRecord): void {
    emit('delete', carousel);
}

function onToggle(carousel: CarouselRecord): void {
    emit('toggle', carousel);
}

onBeforeUnmount(() => {
    if (searchTimer) clearTimeout(searchTimer);
});
</script>

<template>
    <section class="space-y-3">
        <div class="space-y-3">
            <p class="text-xs font-semibold tracking-wide uppercase">Cari Carousel</p>
            <div class="relative">
                <Search class="text-muted-foreground pointer-events-none absolute top-1/2 left-3 size-4 -translate-y-1/2" />
                <Input v-model="search" type="search" placeholder="Cari nama carousel..." class="h-10 pl-9" @input="onSearchChange" />
            </div>
        </div>

        <DataTable
            v-model:server-options="serverOptions"
            :headers="headers"
            :items="carousel.data"
            :loading="loading"
            :server-items-length="carousel.total"
            items-label="carousel"
            :table-min-width="680"
        >
            <template #item-nama="{ nama }">
                <span class="font-medium">{{ nama }}</span>
            </template>
            <template #item-tanggal_publish="{ tanggal_publish }">{{ formatDate(tanggal_publish) }}</template>
            <template #item-status="{ status, ...rawCarousel }">
                <Button
                    v-if="capabilities.canToggleStatus"
                    variant="outline"
                    size="sm"
                    :class="['rounded-lg', statusLabel(status).className]"
                    :title="status ? 'Nonaktifkan carousel' : 'Aktifkan carousel'"
                    :aria-label="status ? 'Nonaktifkan carousel' : 'Aktifkan carousel'"
                    @click="onToggle({ status, ...rawCarousel })"
                >
                    <Power class="size-3.5" />
                    {{ statusLabel(status).label }}
                </Button>
                <span v-else :class="['inline-flex rounded-full px-2.5 py-1 text-xs font-medium', statusLabel(status).className]">{{
                    statusLabel(status).label
                }}</span>
            </template>
            <template #item-id="{ id, ...rawCarousel }">
                <div class="flex min-w-max items-center gap-2">
                    <Button
                        v-if="capabilities.canEdit"
                        variant="outline"
                        size="icon"
                        class="size-8 rounded-lg border-amber-200 bg-white text-amber-600 hover:bg-amber-500 hover:text-white"
                        title="Ubah"
                        aria-label="Ubah"
                        @click="onEdit({ id, ...rawCarousel })"
                    >
                        <Pencil class="size-4" />
                    </Button>
                    <Button
                        v-if="capabilities.canDelete"
                        variant="outline"
                        size="icon"
                        class="size-8 rounded-lg border-rose-200 bg-white text-rose-600 hover:bg-rose-600 hover:text-white"
                        title="Hapus"
                        aria-label="Hapus"
                        @click="onDelete({ id, ...rawCarousel })"
                    >
                        <Trash2 class="size-4" />
                    </Button>
                </div>
            </template>
        </DataTable>
    </section>
</template>
