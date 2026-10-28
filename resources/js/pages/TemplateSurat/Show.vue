<script setup lang="ts">
import { Button } from '@/components/ui/button';
import AppLayout from '@/layouts/AppLayout.vue';
import type { BreadcrumbItem } from '@/types';
import { Head, router } from '@inertiajs/vue3';
import { Download } from 'lucide-vue-next';
import TemplateSuratDetails from './components/TemplateSuratDetails.vue';
import type { TemplateSurat } from './ts/templateSurat.types';

const props = defineProps<{ templateSurat: TemplateSurat }>();
const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Template Surat', href: '/template-surat' },
    { title: 'Detail', href: '/template-surat' },
];
function onDownload(): void {
    window.open(route('template-surat.download', props.templateSurat.id), '_blank', 'noopener');
}
function onCancel(): void {
    router.get(route('template-surat.index'));
}
</script>

<template>
    <Head title="Detail Template Surat" /><AppLayout :breadcrumbs="breadcrumbs"
        ><div class="mx-auto w-full space-y-6 p-4 sm:p-6">
            <div>
                <h1 class="text-xl font-semibold">Detail Template Surat</h1>
                <p class="text-muted-foreground mt-1 text-sm">Informasi dan dokumen template surat.</p>
            </div>
            <TemplateSuratDetails :template-surat="templateSurat" />
            <div class="flex flex-col-reverse gap-2 sm:flex-row sm:justify-end">
                <Button variant="outline" @click="onCancel">Kembali</Button
                ><Button :disabled="!templateSurat.dokumen_path" @click="onDownload"><Download class="size-4" />Unduh Dokumen</Button>
            </div>
        </div></AppLayout
    >
</template>
