<script setup lang="ts">
import InputError from '@/components/InputError.vue';
import Button from '@/components/ui/button/Button.vue';
import Input from '@/components/ui/input/Input.vue';
import { Label } from '@/components/ui/label';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, router, useForm } from '@inertiajs/vue3';
import Swal from 'sweetalert2';
import { onMounted } from 'vue';
import daftarPenandatangan from '@/utils/penandatangan';
import SidangNolDetails from './components/SidangNolDetails.vue';
import { SIDANG_NOL_DOCUMENTS } from './ts/constants';
import { sidangNolPdfEmbedUrl } from './ts/pdf';
import type { SidangNol } from './ts/types';
import { useSidangNolDocuments } from './ts/useSidangNolDocuments';

const props = defineProps<{
    berkasSidangNol: SidangNol;
    nomorSuratTerakhir: number;
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Berkas Sidang Nol', href: '/berkas-sidang-nol' },
    { title: 'Ajuan', href: `/berkas-sidang-nol/${props.berkasSidangNol.id}/ajuan` },
];

const currentYear = new Date().getFullYear();
const { documentUrls, isLoadingDocuments, loadDocumentUrls } = useSidangNolDocuments();

const form = useForm({
    nomor_surat: props.berkasSidangNol.nomor_surat?.split('SN-')[1]?.split('/')[0] ?? props.nomorSuratTerakhir + 1,
    status: 1,
    note: '',
    pegawai: '',
});

function onSubmitDecision(status: 2 | 3): void {
    form.status = status;
    form.post(route('berkas-sidang-nol.decide', props.berkasSidangNol.id), {
        onSuccess: async () => {
            await Swal.fire({ icon: 'success', title: 'Berhasil', text: 'Keputusan telah disimpan.' });
            router.get(route('berkas-sidang-nol.index'));
        },
        onError: () => Swal.fire({ icon: 'error', title: 'Gagal', text: 'Periksa kembali keputusan yang diisi.' }),
    });
}

function onSignerChange(value: string): void {
    form.pegawai = value;
}

function onCancel(): void {
    router.get(route('berkas-sidang-nol.index'));
}

onMounted(() => loadDocumentUrls(props.berkasSidangNol.id));
</script>

<template>
    <Head title="Ajuan Sidang Nol" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="mx-auto w-full max-w-[1800px] space-y-6 p-4 sm:p-6">
            <div>
                <h1 class="text-xl font-semibold">Ajuan Surat Sidang Nol</h1>
                <p class="mt-1 text-sm text-gray-500">Periksa dokumen sebelum mengambil keputusan.</p>
            </div>

            <div class="grid gap-6 xl:grid-cols-[minmax(0,2fr)_minmax(20rem,1fr)] xl:items-start">
                <div class="min-w-0 space-y-6">
                    <SidangNolDetails
                        heading="Data Permohonan"
                        variant="clean"
                        :berkas="berkasSidangNol"
                        :document-urls="documentUrls"
                        :show-documents="false"
                    />

                    <section class="space-y-4">
                        <div>
                            <h2 class="text-lg font-semibold">Dokumen Permohonan</h2>
                            <p class="mt-1 text-sm text-muted-foreground">Tinjau dokumen mahasiswa langsung pada halaman ini.</p>
                        </div>

                        <div class="grid gap-6 md:grid-cols-2">
                            <section
                                v-for="document in SIDANG_NOL_DOCUMENTS"
                                :key="document.key"
                                class="overflow-hidden rounded-lg border border-border bg-card shadow-sm"
                            >
                                <header class="border-b border-border px-4 py-3 sm:px-5">
                                    <h3 class="font-semibold text-foreground">{{ document.label }}</h3>
                                    <p class="mt-1 text-sm text-muted-foreground">Scroll untuk membaca dokumen PDF.</p>
                                </header>

                                <div v-if="isLoadingDocuments" class="flex h-[30rem] items-center justify-center p-6 text-sm text-muted-foreground">
                                    Memuat dokumen...
                                </div>
                                <iframe
                                    v-else-if="documentUrls[document.key]"
                                    :src="sidangNolPdfEmbedUrl(documentUrls[document.key])"
                                    :title="document.label"
                                    class="h-[30rem] w-full bg-muted"
                                />
                                <div v-else class="flex h-[30rem] items-center justify-center p-6 text-center text-sm text-muted-foreground">
                                    Dokumen belum tersedia.
                                </div>
                            </section>
                        </div>
                    </section>
                </div>

                <aside class="xl:sticky xl:top-6">
                    <form class="overflow-hidden rounded-lg border border-border bg-card shadow-sm" @submit.prevent="onSubmitDecision(2)">
                        <header class="border-b border-border px-5 py-4">
                            <h2 class="text-lg font-semibold">Keputusan</h2>
                            <p class="mt-1 text-sm text-muted-foreground">Lengkapi informasi sebelum memproses permohonan.</p>
                        </header>

                        <div class="space-y-5 p-5">
                            <div v-if="Object.keys(form.errors).length" class="rounded-lg border border-destructive/20 bg-destructive/10 p-3 text-sm text-destructive">
                                Periksa kembali data keputusan yang diisi.
                            </div>

                            <div class="space-y-2">
                                <Label for="nomor_surat">Nomor Surat <span class="text-destructive">*</span></Label>
                                <div class="flex overflow-hidden rounded-lg border border-input bg-background shadow-xs focus-within:border-ring focus-within:ring-ring/50 focus-within:ring-[1px]">
                                    <span class="inline-flex shrink-0 items-center border-r border-input bg-muted px-2 text-sm text-muted-foreground sm:px-3">SN-</span>
                                    <Input id="nomor_surat" v-model="form.nomor_surat" inputmode="numeric" class="h-10 min-w-0 flex-1 rounded-none border-0 shadow-none focus-visible:ring-0" placeholder="Nomor" />
                                    <span class="inline-flex shrink-0 items-center border-l border-input bg-muted px-2 text-[11px] text-muted-foreground sm:px-3 sm:text-sm">/UN8.1.28/DV.01/{{ currentYear }}</span>
                                </div>
                                <InputError :message="form.errors.nomor_surat" />
                            </div>

                            <div class="space-y-2">
                                <Label for="pegawai">Pilih Penandatangan <span class="text-destructive">*</span></Label>
                                <Select :model-value="form.pegawai || undefined" @update:model-value="onSignerChange">
                                    <SelectTrigger id="pegawai" class="h-10">
                                        <SelectValue placeholder="Pilih penandatangan" />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem v-for="item in daftarPenandatangan" :key="item.value" :value="item.value">
                                            {{ item.label }}
                                        </SelectItem>
                                    </SelectContent>
                                </Select>
                                <InputError :message="form.errors.pegawai" />
                            </div>

                            <div class="space-y-2">
                                <Label for="note">Catatan <span class="font-normal text-muted-foreground">(opsional)</span></Label>
                                <textarea id="note" v-model="form.note" class="min-h-28 w-full rounded-lg border border-input bg-transparent px-3 py-2 text-sm shadow-xs outline-none placeholder:text-muted-foreground focus-visible:border-ring focus-visible:ring-ring/50 focus-visible:ring-[1px]" placeholder="Tambahkan catatan, saran, atau alasan..." />
                                <InputError :message="form.errors.note" />
                            </div>
                        </div>

                        <footer class="space-y-3 border-t border-border p-5">
                            <div class="grid grid-cols-2 gap-3">
                                <Button type="button" variant="outline" @click="onCancel">Batal</Button>
                                <Button type="button" class="bg-red-600 text-white hover:bg-red-700" :disabled="form.processing" @click="onSubmitDecision(3)">Tolak</Button>
                            </div>
                            <Button type="submit" class="w-full bg-emerald-700 text-white hover:bg-emerald-800" :disabled="form.processing">
                                {{ form.processing ? 'Memproses...' : 'Terima & Generate Surat' }}
                            </Button>
                        </footer>
                    </form>
                </aside>
            </div>
        </div>
    </AppLayout>
</template>
