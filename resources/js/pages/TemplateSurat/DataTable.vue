<script setup lang="ts">
import DataTable from '@/components/data-table/DataTable.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { formatDate } from '@/utils/date';
import { Download, Eye, Pencil, Search, Trash2 } from 'lucide-vue-next';
import { computed, onBeforeUnmount, ref, watch } from 'vue';
import { getTemplateSuratCapabilities } from './ts/templateSurat.capabilities';
import { TEMPLATE_SURAT_STATUS } from './ts/templateSurat.constants';
import type {
    TemplateSurat,
    TemplateSuratActor,
    TemplateSuratFilters,
    TemplateSuratPaginated,
    TemplateSuratTableQuery,
} from './ts/templateSurat.types';

const props = defineProps<{
    auth: { user: TemplateSuratActor };
    templates: TemplateSuratPaginated<TemplateSurat>;
    filters: TemplateSuratFilters;
    loading: boolean;
    audience: 'management' | 'student';
}>();
const emit = defineEmits<{
    'query-change': [query: TemplateSuratTableQuery];
    view: [templateSurat: TemplateSurat];
    edit: [templateSurat: TemplateSurat];
    delete: [templateSurat: TemplateSurat];
    download: [templateSurat: TemplateSurat];
}>();
const isManagement = computed(() => props.audience === 'management');
const capabilities = computed(() => getTemplateSuratCapabilities(props.auth.user));
const search = ref(props.filters.search ?? '');
const serverOptions = ref({ page: props.templates.current_page, rowsPerPage: props.templates.per_page });
const headers = computed(() => [
    { text: 'Nama', value: 'nama' },
    { text: 'Deskripsi', value: 'deskripsi' },
    ...(isManagement.value ? [{ text: 'Status', value: 'status' }] : []),
    { text: 'Tanggal terbit', value: 'tanggal_publish' },
    { text: 'Aksi', value: 'id', sortable: false, width: isManagement.value ? 180 : 104 },
]);
let searchTimer: ReturnType<typeof setTimeout> | undefined;
watch(
    () => props.templates,
    (templates) => {
        serverOptions.value = { page: templates.current_page, rowsPerPage: templates.per_page };
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
        if (page !== props.templates.current_page || perPage !== props.templates.per_page) onEmitQuery(page);
    },
);
onBeforeUnmount(() => {
    if (searchTimer) clearTimeout(searchTimer);
});
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
function onView(templateSurat: TemplateSurat): void {
    emit('view', templateSurat);
}
function onEdit(templateSurat: TemplateSurat): void {
    emit('edit', templateSurat);
}
function onDelete(templateSurat: TemplateSurat): void {
    emit('delete', templateSurat);
}
function onDownload(templateSurat: TemplateSurat): void {
    emit('download', templateSurat);
}
</script>

<template>
    <section class="space-y-3">
        <div class="space-y-3">
            <p class="text-xs font-semibold tracking-wide uppercase">Cari Template</p>
            <div class="relative">
                <Search class="text-muted-foreground pointer-events-none absolute top-1/2 left-3 size-4 -translate-y-1/2" /><Input
                    v-model="search"
                    type="search"
                    placeholder="Cari nama template..."
                    class="h-10 pl-9"
                    @input="onSearchChange"
                />
            </div>
        </div>
        <DataTable
            v-model:server-options="serverOptions"
            :headers="headers"
            :items="templates.data"
            :loading="loading"
            :server-items-length="templates.total"
            items-label="template"
            :table-min-width="720"
            ><template #item-nama="{ nama }"
                ><span class="font-medium">{{ nama }}</span></template
            ><template #item-deskripsi="{ deskripsi }"
                ><span class="text-muted-foreground line-clamp-2 max-w-md">{{ deskripsi || '—' }}</span></template
            ><template #item-status="{ status }"
                ><span
                    :class="[
                        'inline-flex rounded-full px-2.5 py-1 text-xs font-medium',
                        status ? TEMPLATE_SURAT_STATUS.active.badgeClass : TEMPLATE_SURAT_STATUS.inactive.badgeClass,
                    ]"
                    >{{ status ? TEMPLATE_SURAT_STATUS.active.label : TEMPLATE_SURAT_STATUS.inactive.label }}</span
                ></template
            ><template #item-tanggal_publish="{ tanggal_publish }">{{ formatDate(tanggal_publish) }}</template
            ><template #item-id="{ id, ...rawTemplate }"
                ><div class="flex min-w-max items-center gap-2">
                    <Button
                        variant="outline"
                        size="icon"
                        class="size-8 rounded-lg border-blue-200 bg-white text-blue-600 hover:bg-blue-600 hover:text-white"
                        title="Detail"
                        aria-label="Detail"
                        @click="onView({ id, ...rawTemplate })"
                        ><Eye class="size-4" /></Button
                    ><Button
                        v-if="isManagement && capabilities.canEdit"
                        variant="outline"
                        size="icon"
                        class="size-8 rounded-lg border-amber-200 bg-white text-amber-600 hover:bg-amber-500 hover:text-white"
                        title="Ubah"
                        aria-label="Ubah"
                        @click="onEdit({ id, ...rawTemplate })"
                        ><Pencil class="size-4" /></Button
                    ><Button
                        v-if="isManagement && capabilities.canDelete"
                        variant="outline"
                        size="icon"
                        class="size-8 rounded-lg border-rose-200 bg-white text-rose-600 hover:bg-rose-600 hover:text-white"
                        title="Hapus"
                        aria-label="Hapus"
                        @click="onDelete({ id, ...rawTemplate })"
                        ><Trash2 class="size-4" /></Button
                    ><Button
                        v-if="!isManagement"
                        variant="outline"
                        size="icon"
                        class="size-8 rounded-lg border-emerald-200 bg-white text-emerald-600 hover:bg-emerald-600 hover:text-white"
                        title="Unduh"
                        aria-label="Unduh"
                        @click="onDownload({ id, ...rawTemplate })"
                        ><Download class="size-4"
                    /></Button></div></template
        ></DataTable>
    </section>
</template>
