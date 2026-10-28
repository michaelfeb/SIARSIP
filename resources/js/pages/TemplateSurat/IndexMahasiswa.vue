<script setup lang="ts">
import Heading from '@/components/Heading.vue';
import AppLayout from '@/layouts/AppLayout.vue';
import type { BreadcrumbItem } from '@/types';
import { Head, router } from '@inertiajs/vue3';
import { ref } from 'vue';
import DataTable from './DataTable.vue';
import type {
    TemplateSurat,
    TemplateSuratActor,
    TemplateSuratFilters,
    TemplateSuratPaginated,
    TemplateSuratTableQuery,
} from './ts/templateSurat.types';

defineProps<{ auth: { user: TemplateSuratActor }; templates: TemplateSuratPaginated<TemplateSurat>; filters: TemplateSuratFilters }>();
const breadcrumbs: BreadcrumbItem[] = [{ title: 'Template Surat', href: '/template-surat-mahasiswa' }];
const loading = ref(false);
function onDataTableQueryChange(query: TemplateSuratTableQuery): void {
    loading.value = true;
    router.get(
        route('template-surat.mahasiswa'),
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
function onView(templateSurat: TemplateSurat): void {
    router.get(route('template-surat.show', templateSurat.id));
}
function onDownload(templateSurat: TemplateSurat): void {
    window.open(route('template-surat.download', templateSurat.id), '_blank', 'noopener');
}
</script>

<template>
    <Head title="Template Surat" /><AppLayout :breadcrumbs="breadcrumbs"
        ><div class="mx-auto w-full space-y-7 p-4 sm:p-6">
            <Heading title="Template Surat" description="Unduh template surat yang telah dipublikasikan." class="!mb-0" /><DataTable
                :auth="auth"
                :templates="templates"
                :filters="filters"
                :loading="loading"
                audience="student"
                @query-change="onDataTableQueryChange"
                @view="onView"
                @download="onDownload"
            /></div
    ></AppLayout>
</template>
