<script setup lang="ts">
import Heading from '@/components/Heading.vue';
import { showConfirmationAlert, showErrorAlert, showSuccessAlert } from '@/components/alerts/SwalAlert';
import { Button } from '@/components/ui/button';
import AppLayout from '@/layouts/AppLayout.vue';
import type { BreadcrumbItem } from '@/types';
import { Head, router } from '@inertiajs/vue3';
import { Plus } from 'lucide-vue-next';
import { computed, ref } from 'vue';
import DataTable from './DataTable.vue';
import { getTemplateSuratCapabilities } from './ts/templateSurat.capabilities';
import type {
    TemplateSurat,
    TemplateSuratActor,
    TemplateSuratFilters,
    TemplateSuratPaginated,
    TemplateSuratTableQuery,
} from './ts/templateSurat.types';

const props = defineProps<{ auth: { user: TemplateSuratActor }; templates: TemplateSuratPaginated<TemplateSurat>; filters: TemplateSuratFilters }>();
const breadcrumbs: BreadcrumbItem[] = [{ title: 'Template Surat', href: '/template-surat' }];
const loading = ref(false);
const capabilities = computed(() => getTemplateSuratCapabilities(props.auth.user));
function onDataTableQueryChange(query: TemplateSuratTableQuery): void {
    loading.value = true;
    router.get(
        route('template-surat.index'),
        { page: query.page, per_page: query.perPage, search: query.search || undefined },
        {
            only: ['templates', 'filters'],
            preserveScroll: true,
            preserveState: true,
            replace: true,
            onFinish: () => {
                loading.value = false;
            },
        },
    );
}
function onCreate(): void {
    router.get(route('template-surat.create'));
}
function onView(templateSurat: TemplateSurat): void {
    router.get(route('template-surat.show', templateSurat.id));
}
function onEdit(templateSurat: TemplateSurat): void {
    router.get(route('template-surat.edit', templateSurat.id));
}
async function onDelete(templateSurat: TemplateSurat): Promise<void> {
    const result = await showConfirmationAlert({
        variant: 'warning',
        title: 'Hapus template?',
        text: 'Dokumen template juga akan dihapus dan tidak dapat dikembalikan.',
        confirmButtonText: 'Hapus',
        destructive: true,
    });
    if (result.isConfirmed)
        router.delete(route('template-surat.destroy', templateSurat.id), {
            preserveScroll: true,
            onSuccess: () => showSuccessAlert({ text: 'Template surat berhasil dihapus.' }),
            onError: () => showErrorAlert({ text: 'Template surat tidak dapat dihapus. Silakan coba lagi.' }),
        });
}
</script>

<template>

    <Head title="Template Surat" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="mx-auto w-full space-y-7 p-4 sm:p-6">
            <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
                <Heading title="Template Surat" description="Kelola template surat yang tersedia di sistem."
                    class="!mb-0" /><Button v-if="capabilities.canCreate" class="w-full sm:w-auto" @click="onCreate">
                    <Plus class="mr-1 size-4" />Tambah Template
                </Button>
            </div>
            <DataTable :auth="auth" :templates="templates" :filters="filters" :loading="loading" audience="management"
                @query-change="onDataTableQueryChange" @view="onView" @edit="onEdit" @delete="onDelete" />
        </div>
    </AppLayout>
</template>
