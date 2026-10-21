<script setup lang="ts">
import InputError from '@/components/InputError.vue';
import Button from '@/components/ui/button/Button.vue';
import Modal from '@/components/ui/modal/Modal.vue';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, router, useForm } from '@inertiajs/vue3';
import Swal from 'sweetalert2';
import { computed, onMounted, ref } from 'vue';
import daftarPenandatangan from '@/utils/penandatangan';
import SidangNolDetails from './components/SidangNolDetails.vue';
import SidangNolDocumentPreview from './components/SidangNolDocumentPreview.vue';
import { SIDANG_NOL_DOCUMENTS } from './ts/constants';
import type { SidangNol, SidangNolDocumentKey } from './ts/types';
import { useSidangNolDocuments } from './ts/useSidangNolDocuments';

const props = defineProps<{
    berkasSidangNol: SidangNol;
    nomorSuratTerakhir: number;
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Berkas Sidang Nol', href: '/berkas-sidang-nol' },
    { title: 'Ajuan', href: `/berkas-sidang-nol/${props.berkasSidangNol.id}/ajuan` },
];

const showDecisionModal = ref(false);
const selectedDocument = ref<SidangNolDocumentKey | null>(null);
const currentYear = new Date().getFullYear();
const { documentUrls, loadDocumentUrls } = useSidangNolDocuments();
const selectedDocumentDefinition = computed(() =>
    SIDANG_NOL_DOCUMENTS.find((document) => document.key === selectedDocument.value),
);
const selectedDocumentUrl = computed(() =>
    selectedDocument.value ? (documentUrls.value[selectedDocument.value] ?? null) : null,
);

const form = useForm({
    nomor_surat: props.berkasSidangNol.nomor_surat?.split('SN-')[1]?.split('/')[0] ?? props.nomorSuratTerakhir + 1,
    status: 1,
    note: '',
    pegawai: '',
});

function submitDecision(status: 2 | 3) {
    form.status = status;
    form.post(route('berkas-sidang-nol.keputusan', props.berkasSidangNol.id), {
        onSuccess: async () => {
            await Swal.fire({ icon: 'success', title: 'Berhasil', text: 'Keputusan telah disimpan.' });
            showDecisionModal.value = false;
            router.get(route('berkas-sidang-nol.index'));
        },
        onError: () => Swal.fire({ icon: 'error', title: 'Gagal', text: 'Periksa kembali keputusan yang diisi.' }),
    });
}

function backToIndex() {
    router.get(route('berkas-sidang-nol.index'));
}

onMounted(() => loadDocumentUrls(props.berkasSidangNol.id));
</script>

<template>
    <Head title="Ajuan Sidang Nol" />

    <SidangNolDocumentPreview
        :open="selectedDocument !== null"
        :title="selectedDocumentDefinition?.label ?? 'Preview dokumen'"
        :url="selectedDocumentUrl"
        @close="selectedDocument = null"
    />

    <Modal :show="showDecisionModal" @close="showDecisionModal = false">
        <template #header><h2 class="text-lg font-semibold">Tambah Keputusan</h2></template>

        <div v-if="Object.keys(form.errors).length" class="mb-4 rounded bg-red-100 p-4 text-red-600">
            <ul class="list-disc space-y-1 pl-5 text-sm"><li v-for="(error, key) in form.errors" :key="key">{{ error }}</li></ul>
        </div>

        <form class="space-y-5" @submit.prevent="submitDecision(2)">
            <div class="space-y-2">
                <label for="nomor_surat" class="block text-sm font-medium text-gray-700">Nomor Surat</label>
                <div class="flex overflow-hidden rounded-md border border-gray-300">
                    <span class="inline-flex items-center border-r border-gray-300 bg-gray-100 px-3 text-sm text-gray-600">SN-</span>
                    <input id="nomor_surat" v-model="form.nomor_surat" inputmode="numeric" class="min-w-0 flex-1 px-3 py-2 text-sm" placeholder="Nomor" />
                    <span class="inline-flex items-center border-l border-gray-300 bg-gray-100 px-3 text-sm text-gray-600">/UN8.1.28/DV.01/{{ currentYear }}</span>
                </div>
                <InputError :message="form.errors.nomor_surat" />
            </div>

            <div class="space-y-2">
                <label for="note" class="block text-sm font-medium text-gray-700">Catatan</label>
                <textarea id="note" v-model="form.note" class="min-h-24 w-full rounded border border-gray-300 px-3 py-2 text-sm" placeholder="Opsional. Tulis catatan, saran, atau revisi di sini." />
                <InputError :message="form.errors.note" />
            </div>

            <div class="space-y-2">
                <label for="pegawai" class="block text-sm font-medium text-gray-700">Pilih Penandatangan</label>
                <select id="pegawai" v-model="form.pegawai" class="w-full rounded border border-gray-300 px-3 py-2 text-sm">
                    <option value="">Pilih pegawai</option>
                    <option v-for="item in daftarPenandatangan" :key="item.value" :value="item.value">{{ item.label }}</option>
                </select>
                <InputError :message="form.errors.pegawai" />
            </div>
        </form>

        <template #footer>
            <Button type="button" class="border border-orange-400 bg-white text-orange-500 hover:bg-orange-100" @click="showDecisionModal = false">Kembali</Button>
            <Button type="button" class="bg-red-500 text-white hover:bg-red-600" :disabled="form.processing" @click="submitDecision(3)">Tolak</Button>
            <Button type="button" class="bg-green-600 text-white hover:bg-green-700" :disabled="form.processing" @click="submitDecision(2)">Terima &amp; Generate Surat</Button>
        </template>
    </Modal>

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="space-y-4 p-4">
            <div>
                <h1 class="text-xl font-semibold">Ajuan Surat Sidang Nol</h1>
                <p class="mt-1 text-sm text-gray-500">Periksa dokumen sebelum mengambil keputusan.</p>
            </div>

            <SidangNolDetails :berkas="berkasSidangNol" :document-urls="documentUrls" @preview="selectedDocument = $event" />

            <div class="flex justify-end gap-2">
                <Button type="button" class="border border-orange-400 bg-white text-orange-500 hover:bg-orange-100" @click="backToIndex">Kembali</Button>
                <Button type="button" class="border border-blue-400 bg-white text-blue-500 hover:bg-blue-50" @click="showDecisionModal = true">Tambah Keputusan</Button>
            </div>
        </div>
    </AppLayout>
</template>
