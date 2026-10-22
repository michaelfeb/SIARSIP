<script setup lang="ts">
import Button from '@/components/ui/button/Button.vue';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, router } from '@inertiajs/vue3';
import { onMounted } from 'vue';
import SidangNolDetails from './components/SidangNolDetails.vue';
import { SIDANG_NOL_DOCUMENTS } from './ts/constants';
import { sidangNolPdfEmbedUrl } from './ts/pdf';
import type { SidangNol, SidangNolAuth } from './ts/types';
import { useSidangNolDocuments } from './ts/useSidangNolDocuments';

const props = defineProps<{
    auth: SidangNolAuth;
    berkasSidangNol: SidangNol;
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Berkas Sidang Nol', href: '/berkas-sidang-nol' },
    { title: 'Lihat', href: `/berkas-sidang-nol/${props.berkasSidangNol.id}` },
];

const { documentUrls, isLoadingDocuments, loadDocumentUrls } = useSidangNolDocuments();

onMounted(() => loadDocumentUrls(props.berkasSidangNol.id));

function backToIndex() {
    router.get(route('berkas-sidang-nol.index'));
}
</script>

<template>
    <Head title="Detail Berkas Sidang Nol" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="mx-auto w-full max-w-[1800px] space-y-6 p-4 sm:p-6">
            <div>
                <h1 class="text-xl font-semibold">Detail Berkas Sidang Nol</h1>
                <p class="mt-1 text-sm text-gray-500">Tinjau seluruh dokumen pengajuan tanpa membuka preview terpisah.</p>
            </div>

            <SidangNolDetails
                variant="clean"
                :berkas="berkasSidangNol"
                :document-urls="documentUrls"
                :show-documents="false"
                show-notes
            />

            <div class="grid gap-6 md:grid-cols-2">
                <section
                    v-for="document in SIDANG_NOL_DOCUMENTS"
                    :key="document.key"
                    class="overflow-hidden rounded-lg border border-border bg-card shadow-sm"
                >
                    <header class="border-b border-border px-4 py-3 sm:px-5">
                        <h2 class="font-semibold text-foreground">{{ document.label }}</h2>
                        <p class="mt-1 text-sm text-muted-foreground">Scroll untuk membaca dokumen PDF.</p>
                    </header>

                    <div v-if="isLoadingDocuments" class="flex h-[32rem] items-center justify-center p-6 text-sm text-muted-foreground">
                        Memuat dokumen...
                    </div>
                    <iframe
                        v-else-if="documentUrls[document.key]"
                        :src="sidangNolPdfEmbedUrl(documentUrls[document.key])"
                        :title="document.label"
                        class="h-[32rem] w-full bg-muted sm:h-[40rem]"
                    />
                    <div v-else class="flex h-[32rem] items-center justify-center p-6 text-center text-sm text-muted-foreground sm:h-[40rem]">
                        Dokumen belum tersedia.
                    </div>
                </section>
            </div>

            <div class="flex justify-end">
                <Button type="button" class="w-full border border-orange-400 bg-white text-orange-500 hover:bg-orange-100 sm:w-auto" @click="backToIndex">Kembali</Button>
            </div>
        </div>
    </AppLayout>
</template>
