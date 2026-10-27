<script setup lang="ts">
import InputError from '@/components/InputError.vue';
import NumberStringInput from '@/components/NumberStringInput.vue';
import PhoneNumberInput from '@/components/PhoneNumberInput.vue';
import { showErrorAlert, showSuccessAlert } from '@/components/alerts/SwalAlert';
import LetterApprovalFlow from '@/components/letters/LetterApprovalFlow.vue';
import LetterAttachmentUpload from '@/components/letters/LetterAttachmentUpload.vue';
import Button from '@/components/ui/button/Button.vue';
import Input from '@/components/ui/input/Input.vue';
import { Label } from '@/components/ui/label';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import AppLayout from '@/layouts/AppLayout.vue';
import PdfPreviewDialog from '@/components/PdfPreviewDialog.vue';
import type { BreadcrumbItem } from '@/types';
import axios from 'axios';
import { Head, router, useForm } from '@inertiajs/vue3';
import { computed, onBeforeUnmount, ref } from 'vue';
import { CUTI_AKADEMIK_SEMESTERS } from './ts/cutiAkademik.constants';
import type { CutiAkademikActor, CutiAkademikFormContext, CutiAkademikLetter } from './ts/cutiAkademik.types';

const props = defineProps<{
    mode: 'create' | 'edit';
    applicant: CutiAkademikActor;
    formContext: CutiAkademikFormContext;
    letter?: CutiAkademikLetter;
}>();
const isEdit = computed(() => props.mode === 'edit');
const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Cuti Akademik', href: '/cuti-akademik' },
    { title: isEdit.value ? 'Ubah' : 'Buat', href: '/cuti-akademik' },
];
const form = useForm({
    semester: String(props.letter?.meta.semester ?? ''),
    tahun_akademik: String(props.letter?.meta.tahun_akademik ?? ''),
    nomor_hp: String(props.letter?.meta.nomor_hp ?? props.applicant.nomor_telpon ?? '628'),
    nominal_ukt: String(props.letter?.meta.nominal_ukt ?? ''),
    alasan_cuti: String(props.letter?.meta.alasan_cuti ?? props.letter?.purpose ?? ''),
    attachments: [] as File[],
});
const isPreviewing = ref(false);
const previewUrl = ref<string | null>(null);
const previewOpen = ref(false);

function closePreview(): void {
    previewOpen.value = false;
    if (previewUrl.value) URL.revokeObjectURL(previewUrl.value);
    previewUrl.value = null;
}

async function onPreview(): Promise<void> {
    form.clearErrors();
    isPreviewing.value = true;
    const payload = new FormData();
    Object.entries(form.data()).forEach(([key, value]) => {
        if (key === 'attachments') (value as File[]).forEach((file) => payload.append('attachments[]', file));
        else payload.append(key, String(value));
    });

    try {
        const response = await axios.post(route('cuti-akademik.preview'), payload, { responseType: 'blob' });
        closePreview();
        previewUrl.value = URL.createObjectURL(new Blob([response.data], { type: 'application/pdf' }));
        previewOpen.value = true;
    } catch (error) {
        if (axios.isAxiosError(error) && error.response?.status === 422) {
            const body = JSON.parse(await (error.response.data as Blob).text()) as { errors: Record<string, string[]> };
            form.setError(Object.fromEntries(Object.entries(body.errors).map(([key, messages]) => [key, messages[0]])));
        } else showErrorAlert({ text: 'Pratinjau PDF tidak dapat dibuat. Silakan coba lagi.' });
    } finally { isPreviewing.value = false; }
}

onBeforeUnmount(closePreview);

function onSubmit(): void {
    const visitOptions = {
        forceFormData: true,
        onSuccess: () =>
            showSuccessAlert({
                text: isEdit.value ? 'Draft Cuti Akademik berhasil diperbarui.' : 'Draft Cuti Akademik berhasil disimpan.',
            }),
        onError: () =>
            showErrorAlert({
                text: 'Periksa kembali data dan dokumen yang diunggah.',
            }),
    };

    if (isEdit.value) {
        form.transform((data) => ({ ...data, _method: 'put' })).post(route('cuti-akademik.update', props.letter?.id), visitOptions);
        return;
    }
    form.post(route('cuti-akademik.store'), visitOptions);
}
function onCancel(): void {
    router.get(route('cuti-akademik.index'));
}
function programStudiLabel(value: number | null | undefined): string {
    return (
        { 1: 'Matematika', 2: 'Kimia', 3: 'Biologi', 4: 'Fisika', 5: 'Farmasi', 6: 'Ilmu Komputer', 7: 'Statistika', 8: 'Profesi Apoteker' }[
        value ?? 0
        ] ?? '-'
    );
}
</script>

<template>

    <Head :title="isEdit ? 'Ubah Draft Cuti Akademik' : 'Buat Cuti Akademik'" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="mx-auto w-full space-y-6 p-4 sm:p-6">
            <div>
                <h1 class="text-xl font-semibold">{{ isEdit ? 'Ubah Draft Cuti Akademik' : 'Permohonan Cuti Akademik' }}
                </h1>
                <p class="text-muted-foreground mt-1 text-sm">Lengkapi data permohonan; dokumen pendukung dapat
                    ditambahkan bila tersedia.</p>
            </div>
            <section v-if="formContext.notesHtml"
                class="overflow-hidden rounded-lg border border-orange-200 bg-orange-50/60 shadow-sm">
                <header class="flex items-center gap-2 bg-[#c2410c] px-5 py-3 text-sm font-semibold text-white">
                    <span
                        class="flex size-5 items-center justify-center rounded-full border border-white/70 text-xs">i</span>
                    Panduan Pengisian
                </header>
                <div class="text-foreground p-5 text-sm leading-7 sm:p-6 [&_h1]:mb-5 [&_h1]:text-xl [&_h1]:font-semibold [&_h2]:mt-5 [&_h2]:mb-2 [&_h2]:font-semibold [&_ol]:my-3 [&_ol]:list-decimal [&_ol]:space-y-1.5 [&_ol]:pl-5 [&_p]:my-1 [&_strong]:font-semibold"
                    v-html="formContext.notesHtml" />
            </section>
            <form class="space-y-6" @submit.prevent="onSubmit">
                <section class="border-border bg-card rounded-lg border shadow-sm">
                    <header class="border-border border-b px-5 py-4">
                        <h2 class="text-lg font-semibold">Data Mahasiswa</h2>
                        <p class="text-muted-foreground mt-1 text-sm">Data ini diambil dari biodata akun dan tidak dapat
                            diubah di formulir.</p>
                    </header>
                    <div class="grid gap-5 p-5 md:grid-cols-3">
                        <div class="space-y-2"><Label>Nama Mahasiswa</Label><Input :model-value="applicant.nama"
                                disabled /></div>
                        <div class="space-y-2"><Label>NIM</Label><Input :model-value="applicant.nim ?? '-'" disabled />
                        </div>
                        <div class="space-y-2">
                            <Label>Program Studi</Label><Input :model-value="programStudiLabel(applicant.program_studi)"
                                disabled />
                        </div>
                    </div>
                </section>
                <section class="border-border bg-card rounded-lg border shadow-sm">
                    <header class="border-border border-b px-5 py-4">
                        <h2 class="text-lg font-semibold">Data Permohonan</h2>
                    </header>
                    <div class="grid gap-x-6 gap-y-5 p-5 md:grid-cols-2">
                        <div class="space-y-2">
                            <Label for="semester">Semester <span class="text-destructive">*</span></Label><Select
                                v-model="form.semester">
                                <SelectTrigger id="semester" class="h-10">
                                    <SelectValue placeholder="Pilih semester" />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem v-for="item in CUTI_AKADEMIK_SEMESTERS" :key="item" :value="item">
                                        Semester {{ item }}</SelectItem>
                                </SelectContent>
                            </Select>
                            <InputError :message="form.errors.semester" />
                        </div>
                        <div class="space-y-2">
                            <Label for="tahun_akademik">Tahun Akademik <span
                                    class="text-destructive">*</span></Label><Input id="tahun_akademik"
                                v-model="form.tahun_akademik" placeholder="Contoh: 2026/2027" />
                            <InputError :message="form.errors.tahun_akademik" />
                        </div>
                        <div class="space-y-2">
                            <Label for="nomor_hp">Nomor Kontak <span class="text-destructive">*</span></Label>
                            <PhoneNumberInput id="nomor_hp" v-model="form.nomor_hp" placeholder="628123456789" />
                            <InputError :message="form.errors.nomor_hp" />
                        </div>
                        <div class="space-y-2">
                            <Label for="nominal_ukt">Nominal UKT <span class="text-destructive">*</span></Label>
                            <NumberStringInput id="nominal_ukt" v-model="form.nominal_ukt"
                                placeholder="Contoh: 2.500.000" />
                            <InputError :message="form.errors.nominal_ukt" />
                        </div>
                        <div class="space-y-2 md:col-span-2">
                            <Label for="alasan_cuti">Tujuan <span class="text-destructive">*</span></Label><textarea
                                id="alasan_cuti" v-model="form.alasan_cuti"
                                class="border-input placeholder:text-muted-foreground focus-visible:border-ring focus-visible:ring-ring/50 min-h-28 w-full rounded-lg border bg-transparent px-3 py-2 text-sm shadow-xs outline-none focus-visible:ring-[1px]"
                                placeholder="Jelaskan alasan pengambilan cuti akademik..." />
                            <InputError :message="form.errors.alasan_cuti" />
                        </div>
                    </div>
                </section>
                <LetterAttachmentUpload v-model="form.attachments" :error="form.errors.attachments" />
                <LetterApprovalFlow :approval-chain="formContext.approvalChain"
                    information-description="Permohonan Anda akan diproses melalui tahapan persetujuan secara bertingkat. Setiap tahapan akan ditinjau oleh pihak berwenang."
                    estimate="3–5 hari kerja" />
                <div class="flex flex-col-reverse justify-end gap-3 sm:flex-row">
                    <Button type="button" variant="outline" class="rounded-lg" @click="onCancel">Batal</Button><Button
                        type="button" variant="outline" class="rounded-lg" :disabled="isPreviewing" @click="onPreview">{{ isPreviewing ? 'Membuat pratinjau...' : 'Pratinjau PDF' }}</Button><Button
                        type="submit" class="rounded-lg" :disabled="form.processing">{{
                            form.processing ? 'Menyimpan...' : 'Simpan Draft'
                        }}</Button>
                </div>
            </form>
        </div>
        <PdfPreviewDialog :open="previewOpen" :url="previewUrl" @close="closePreview" />
    </AppLayout>
</template>
