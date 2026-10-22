<script setup lang="ts">
// @ts-expect-error The package type definitions omit its Vue component default export.
import EasyDataTable from 'vue3-easy-data-table';
import { ChevronLeft, ChevronRight } from 'lucide-vue-next';
import { computed, useSlots } from 'vue';
import type { DataTableHeader, DataTableItem, DataTableServerOptions } from './DataTable.types';

interface Props {
    headers: DataTableHeader[];
    items: DataTableItem[];
    loading: boolean;
    serverItemsLength: number;
    serverOptions: DataTableServerOptions;
    itemsLabel?: string;
    tableMinWidth?: number;
}

const props = withDefaults(defineProps<Props>(), {
    itemsLabel: 'data',
    tableMinWidth: 960,
});

const emit = defineEmits<{
    'update:serverOptions': [value: DataTableServerOptions];
}>();

const slots = useSlots();

const serverOptions = computed({
    get: () => props.serverOptions,
    set: (value: DataTableServerOptions) => emit('update:serverOptions', value),
});

const firstItem = computed(() => props.serverItemsLength === 0 ? 0 : ((serverOptions.value.page - 1) * serverOptions.value.rowsPerPage) + 1);
const lastItem = computed(() => Math.min(serverOptions.value.page * serverOptions.value.rowsPerPage, props.serverItemsLength));
const totalPages = computed(() => Math.max(1, Math.ceil(props.serverItemsLength / serverOptions.value.rowsPerPage)));
const visiblePages = computed(() => {
    const firstPage = Math.min(Math.max(serverOptions.value.page - 2, 1), Math.max(totalPages.value - 4, 1));
    const pageCount = Math.min(5, totalPages.value);

    return Array.from({ length: pageCount }, (_, index) => firstPage + index);
});

function onPageChange(page: number): void {
    if (page < 1 || page > totalPages.value || page === serverOptions.value.page) {
        return;
    }

    serverOptions.value = { ...serverOptions.value, page };
}

function onRowsPerPageChange(event: Event): void {
    const rowsPerPage = Number((event.target as HTMLSelectElement).value);

    if (!Number.isInteger(rowsPerPage) || rowsPerPage === serverOptions.value.rowsPerPage) {
        return;
    }

    serverOptions.value = { page: 1, rowsPerPage };
}
</script>

<template>
    <div class="data-table overflow-hidden rounded-2xl border border-border bg-card shadow-sm" :style="{ '--data-table-min-width': `${tableMinWidth}px` }">
        <EasyDataTable
            v-model:server-options="serverOptions"
            :headers="headers"
            :items="items"
            :loading="loading"
            :server-items-length="serverItemsLength"
            :rows-items="[5, 10, 25, 50, 100]"
            show-index
            hide-footer
            show-index-symbol="No"
            table-class-name="data-table__table"
        >
            <template v-for="(_, slotName) in slots" :key="slotName" #[slotName]="slotProps">
                <slot :name="slotName" v-bind="slotProps" />
            </template>
        </EasyDataTable>

        <div class="data-table__footer">
            <p class="data-table__summary">
                Menampilkan <strong>{{ firstItem }}</strong> hingga <strong>{{ lastItem }}</strong> dari <strong>{{ serverItemsLength }}</strong> {{ itemsLabel }}
            </p>

            <div class="data-table__controls">
                <label class="data-table__rows-label">
                    <span>Tampilkan</span>
                    <select :value="serverOptions.rowsPerPage" aria-label="Jumlah baris per halaman" @change="onRowsPerPageChange">
                        <option v-for="rows in [5, 10, 25, 50, 100]" :key="rows" :value="rows">{{ rows }}</option>
                    </select>
                </label>

                <nav class="data-table__pagination" aria-label="Pagination">
                    <button type="button" class="data-table__page-button" :disabled="serverOptions.page === 1" aria-label="Halaman sebelumnya" @click="onPageChange(serverOptions.page - 1)">
                        <ChevronLeft class="size-4" />
                    </button>
                    <button
                        v-for="page in visiblePages"
                        :key="page"
                        type="button"
                        :class="['data-table__page-button', { 'data-table__page-button--active': page === serverOptions.page }]"
                        :aria-label="`Halaman ${page}`"
                        :aria-current="page === serverOptions.page ? 'page' : undefined"
                        @click="onPageChange(page)"
                    >
                        {{ page }}
                    </button>
                    <button type="button" class="data-table__page-button" :disabled="serverOptions.page === totalPages" aria-label="Halaman berikutnya" @click="onPageChange(serverOptions.page + 1)">
                        <ChevronRight class="size-4" />
                    </button>
                </nav>
            </div>
        </div>
    </div>
</template>

<style scoped>
.data-table :deep(.vue3-easy-data-table__main) {
    border: 0;
    font-size: 0.8125rem;
}

.data-table :deep(.vue3-easy-data-table__main.fixed-header) {
    border: 0;
}

.data-table :deep(.data-table__table) {
    min-width: var(--data-table-min-width);
}

.data-table :deep(.vue3-easy-data-table__header) {
    background: hsl(var(--muted));
}

.data-table :deep(.vue3-easy-data-table__header th),
.data-table :deep(.vue3-easy-data-table__body tr:not(:last-child) td) {
    border-bottom: 1px solid hsl(var(--border)) !important;
}

.data-table :deep(.vue3-easy-data-table__body tr:last-child td) {
    border-bottom: 0 !important;
}

.data-table :deep(th) {
    height: 2.75rem;
    color: hsl(var(--muted-foreground));
    font-size: 0.6875rem;
    font-weight: 700;
    letter-spacing: 0.025em;
    text-transform: uppercase;
}

.data-table :deep(td) {
    height: 3.75rem;
}

.data-table :deep(tr:hover td) {
    background: hsl(var(--muted) / 0.45);
}

.data-table__footer {
    display: flex;
    min-height: 3.5rem;
    align-items: center;
    justify-content: space-between;
    gap: 1rem;
    border-top: 1px solid hsl(var(--border));
    padding: 0.625rem 0.875rem;
}

.data-table__summary {
    color: hsl(var(--muted-foreground));
    font-size: 0.75rem;
}

.data-table__summary strong {
    color: hsl(var(--foreground));
    font-weight: 600;
}

.data-table__controls,
.data-table__pagination,
.data-table__rows-label {
    display: flex;
    align-items: center;
}

.data-table__controls {
    gap: 1rem;
}

.data-table__rows-label {
    gap: 0.5rem;
    color: hsl(var(--muted-foreground));
    font-size: 0.75rem;
}

.data-table__rows-label select {
    height: 2.25rem;
    min-width: 5.25rem;
    cursor: pointer;
    border: 1px solid hsl(var(--border));
    border-radius: 9999px;
    background: hsl(var(--background));
    padding: 0 1.875rem 0 0.875rem;
    color: hsl(var(--foreground));
    font-size: 0.75rem;
    outline: none;
}

.data-table__rows-label select:focus-visible {
    border-color: hsl(var(--ring));
    box-shadow: 0 0 0 1px hsl(var(--ring) / 0.5);
}

.data-table__pagination {
    gap: 0.375rem;
}

.data-table__page-button {
    display: inline-flex;
    width: 2.25rem;
    height: 2.25rem;
    align-items: center;
    justify-content: center;
    border: 1px solid hsl(var(--border));
    border-radius: 9999px;
    background: hsl(var(--background));
    color: hsl(var(--foreground));
    cursor: pointer;
    font-size: 0.75rem;
    transition: background-color 150ms ease, border-color 150ms ease, color 150ms ease;
}

.data-table__page-button:hover:not(:disabled):not(.data-table__page-button--active) {
    background: hsl(var(--muted));
}

.data-table__page-button:focus-visible {
    outline: 2px solid hsl(var(--ring));
    outline-offset: 2px;
}

.data-table__page-button:disabled {
    cursor: not-allowed;
    color: hsl(var(--muted-foreground) / 0.45);
}

.data-table__page-button--active {
    border-color: hsl(158 64% 25%);
    background: hsl(158 64% 25%);
    color: white;
}

@media (max-width: 640px) {
    .data-table__footer {
        align-items: flex-start;
        flex-direction: column;
    }

    .data-table__controls {
        width: 100%;
        justify-content: space-between;
    }
}
</style>
