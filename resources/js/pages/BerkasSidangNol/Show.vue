<script setup lang="ts">
import Button from '@/components/ui/button/Button.vue';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, router } from '@inertiajs/vue3';
import { computed, onMounted, ref } from 'vue';
import SidangNolDetails from './components/SidangNolDetails.vue';
import SidangNolDocumentPreview from './components/SidangNolDocumentPreview.vue';
import { SIDANG_NOL_DOCUMENTS } from './ts/constants';
import type { SidangNol, SidangNolAuth, SidangNolDocumentKey } from './ts/types';
import { useSidangNolDocuments } from './ts/useSidangNolDocuments';

const props = defineProps<{
    auth: SidangNolAuth;
    berkasSidangNol: SidangNol;
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Berkas Sidang Nol', href: '/berkas-sidang-nol' },
    { title: 'Lihat', href: `/berkas-sidang-nol/${props.berkasSidangNol.id}` },
];

const { documentUrls, loadDocumentUrls } = useSidangNolDocuments();
const selectedDocument = ref<SidangNolDocumentKey | null>(null);
const selectedDocumentDefinition = computed(() =>
    SIDANG_NOL_DOCUMENTS.find((document) => document.key === selectedDocument.value),
);
const selectedDocumentUrl = computed(() =>
    selectedDocument.value ? (documentUrls.value[selectedDocument.value] ?? null) : null,
);

onMounted(() => loadDocumentUrls(props.berkasSidangNol.id));

function backToIndex() {
    router.get(route('berkas-sidang-nol.index'));
}
</script>

<template>
    <Head title="Detail Berkas Sidang Nol" />

    <SidangNolDocumentPreview
        :open="selectedDocument !== null"
        :title="selectedDocumentDefinition?.label ?? 'Preview dokumen'"
        :url="selectedDocumentUrl"
        @close="selectedDocument = null"
    />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="space-y-4 p-4">
            <div>
                <h1 class="text-xl font-semibold">Detail Berkas Sidang Nol</h1>
                <p class="mt-1 text-sm text-gray-500">Informasi pengajuan, dokumen, dan catatan petugas.</p>
            </div>

            <SidangNolDetails
                :berkas="berkasSidangNol"
                :document-urls="documentUrls"
                show-notes
                @preview="selectedDocument = $event"
            />

            <div class="flex justify-end">
                <Button type="button" class="border border-orange-400 bg-white text-orange-500 hover:bg-orange-100" @click="backToIndex">Kembali</Button>
            </div>
        </div>
    </AppLayout>
</template>
