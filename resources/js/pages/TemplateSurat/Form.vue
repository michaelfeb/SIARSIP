<script setup lang="ts">
import InputError from '@/components/InputError.vue';
import { showErrorAlert, showSuccessAlert } from '@/components/alerts/SwalAlert';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import AppLayout from '@/layouts/AppLayout.vue';
import type { BreadcrumbItem } from '@/types';
import { Head, router, useForm } from '@inertiajs/vue3';
import { computed, onMounted, ref } from 'vue';
import TemplateSuratDocumentUpload from './components/TemplateSuratDocumentUpload.vue';
import type { TemplateSurat, TemplateSuratFormData } from './ts/templateSurat.types';

const props = defineProps<{ templateSurat?: TemplateSurat; mode: 'create' | 'edit' }>();
const isEdit = computed(() => props.mode === 'edit');
const today = new Date().toISOString().slice(0, 10);
const form = useForm<TemplateSuratFormData>({
    nama: props.templateSurat?.nama ?? '',
    deskripsi: props.templateSurat?.deskripsi ?? '',
    status: props.templateSurat?.status ?? true,
    dokumen_path: null,
    tanggal_publish: props.templateSurat?.tanggal_publish?.slice(0, 10) ?? today,
});
const files = ref<unknown[]>([]);
const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Template Surat', href: '/template-surat' },
    { title: isEdit.value ? 'Ubah' : 'Tambah', href: '/template-surat' },
];
function onDocumentUpdate(fileItems: unknown[]): void {
    files.value = fileItems;
    const first = fileItems[0] as { file?: File } | undefined;
    form.dokumen_path = first?.file ?? null;
}
function onSubmit(): void {
    const options = {
        forceFormData: true,
        onSuccess: () => showSuccessAlert({ text: isEdit.value ? 'Template surat berhasil diperbarui.' : 'Template surat berhasil ditambahkan.' }),
        onError: () => showErrorAlert({ text: 'Periksa kembali data dan dokumen yang diunggah.' }),
    };
    if (isEdit.value) {
        form.transform((data) => ({ ...data, _method: 'put' })).post(route('template-surat.update', props.templateSurat?.id), options);
        return;
    }
    form.post(route('template-surat.store'), options);
}
function onCancel(): void {
    router.get(route('template-surat.index'));
}
onMounted(() => {
    if (props.templateSurat?.dokumen_path) files.value = [{ source: `/storage/${props.templateSurat.dokumen_path}`, options: { type: 'remote' } }];
});
</script>

<template>

    <Head :title="isEdit ? 'Ubah Template Surat' : 'Tambah Template Surat'" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="mx-auto w-full space-y-6 p-4 sm:p-6">
            <div>
                <h1 class="text-xl font-semibold">{{ isEdit ? 'Ubah Template Surat' : 'Tambah Template Surat' }}</h1>
                <p class="text-muted-foreground mt-1 text-sm">Unggah dokumen template dalam format DOC atau DOCX.</p>
            </div>
            <form class="space-y-6" @submit.prevent="onSubmit">
                <section class="border-border bg-card rounded-lg border p-5 shadow-sm">
                    <div class="grid gap-5 md:grid-cols-2">
                        <div class="space-y-2 md:col-span-2">
                            <Label for="nama">Nama Template <span class="text-destructive">*</span></Label><Input
                                id="nama" v-model="form.nama" placeholder="Contoh: Template Cuti Akademik" />
                            <InputError :message="form.errors.nama" />
                        </div>
                        <div class="space-y-2 md:col-span-2">
                            <Label for="deskripsi">Deskripsi</Label><textarea id="deskripsi" v-model="form.deskripsi"
                                rows="4" placeholder="Tulis deskripsi template"
                                class="border-input focus-visible:border-ring focus-visible:ring-ring/50 w-full rounded-lg border bg-transparent px-3 py-2 text-sm shadow-xs outline-none focus-visible:ring-[3px]" />
                            <InputError :message="form.errors.deskripsi" />
                        </div>
                        <div class="space-y-2">
                            <Label for="status">Status <span class="text-destructive">*</span></Label><Select
                                v-model="form.status">
                                <SelectTrigger id="status" class="h-10">
                                    <SelectValue />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem :value="true">Aktif</SelectItem>
                                    <SelectItem :value="false">Tidak Aktif</SelectItem>
                                </SelectContent>
                            </Select>
                            <InputError :message="form.errors.status" />
                        </div>
                        <div class="space-y-2">
                            <Label for="tanggal_publish">Tanggal Publikasi <span
                                    class="text-destructive">*</span></Label><Input id="tanggal_publish"
                                v-model="form.tanggal_publish" type="date" />
                            <InputError :message="form.errors.tanggal_publish" />
                        </div>
                        <div class="md:col-span-2">
                            <TemplateSuratDocumentUpload :files="files" :required="!isEdit"
                                :error="form.errors.dokumen_path" @update="onDocumentUpdate" />
                        </div>
                    </div>
                </section>
                <div class="flex flex-col-reverse gap-2 sm:flex-row sm:justify-end">
                    <Button type="button" variant="outline" @click="onCancel">Kembali</Button><Button type="submit"
                        :disabled="form.processing">{{ form.processing ? 'Menyimpan...' : 'Simpan' }}</Button>
                </div>
            </form>
        </div>
    </AppLayout>
</template>
