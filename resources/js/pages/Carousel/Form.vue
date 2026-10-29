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
import CarouselImageUpload from './components/CarouselImageUpload.vue';
import type { CarouselFormData, CarouselRecord } from './ts/carousel.types';

const props = defineProps<{ carousel?: CarouselRecord; mode: 'create' | 'edit' }>();
const isEdit = computed(() => props.mode === 'edit');
const today = new Date().toISOString().slice(0, 10);
const files = ref<unknown[]>([]);
const form = useForm<CarouselFormData>({
    nama: props.carousel?.nama ?? '',
    gambar: null,
    status: props.carousel?.status ?? true,
    tanggal_publish: props.carousel?.tanggal_publish?.slice(0, 10) ?? today,
});
const statusValue = computed({
    get: () => String(form.status),
    set: (value: string) => {
        form.status = value === 'true';
    },
});
const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Carousel', href: '/carousel' },
    { title: isEdit.value ? 'Ubah' : 'Tambah', href: '/carousel' },
];

function onImageUpdate(fileItems: unknown[]): void {
    files.value = fileItems;
    const first = fileItems[0] as { file?: File } | undefined;
    form.gambar = first?.file ?? null;
}

function onSubmit(): void {
    const options = {
        forceFormData: true,
        onSuccess: () => showSuccessAlert({ text: isEdit.value ? 'Carousel berhasil diperbarui.' : 'Carousel berhasil ditambahkan.' }),
        onError: () => showErrorAlert({ text: 'Periksa kembali data dan gambar yang diunggah.' }),
    };

    if (isEdit.value && props.carousel) {
        form.transform((data) => ({ ...data, _method: 'put' })).post(route('carousel.update', props.carousel.id), options);

        return;
    }

    form.post(route('carousel.store'), options);
}

function onCancel(): void {
    router.get(route('carousel.index'));
}

function carouselImageUrl(imagePath: string): string {
    const filename = imagePath.split('/').pop();

    return filename ? route('carousel.image', filename) : '';
}

onMounted(() => {
    if (props.carousel?.gambar) {
        files.value = [{ source: carouselImageUrl(props.carousel.gambar), options: { type: 'remote' } }];
    }
});
</script>

<template>
    <Head :title="isEdit ? 'Ubah Carousel' : 'Tambah Carousel'" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="mx-auto w-full space-y-6 p-4 sm:p-6">
            <div>
                <h1 class="text-xl font-semibold">{{ isEdit ? 'Ubah Carousel' : 'Tambah Carousel' }}</h1>
                <p class="text-muted-foreground mt-1 text-sm">Unggah gambar pengumuman untuk halaman depan.</p>
            </div>

            <form class="space-y-6" @submit.prevent="onSubmit">
                <section class="border-border bg-card rounded-lg border p-5 shadow-sm">
                    <div class="grid gap-5 md:grid-cols-2">
                        <div class="space-y-2 md:col-span-2">
                            <Label for="nama">Nama <span class="text-destructive">*</span></Label>
                            <Input id="nama" v-model="form.nama" placeholder="Contoh: Pengumuman Akademik" />
                            <InputError :message="form.errors.nama" />
                        </div>

                        <div class="space-y-2">
                            <Label for="status">Status <span class="text-destructive">*</span></Label>
                            <Select v-model="statusValue">
                                <SelectTrigger id="status" class="h-10"><SelectValue /></SelectTrigger>
                                <SelectContent>
                                    <SelectItem value="true">Aktif</SelectItem>
                                    <SelectItem value="false">Tidak Aktif</SelectItem>
                                </SelectContent>
                            </Select>
                            <InputError :message="form.errors.status" />
                        </div>

                        <div class="space-y-2">
                            <Label for="tanggal_publish">Tanggal Publikasi <span class="text-destructive">*</span></Label>
                            <Input id="tanggal_publish" v-model="form.tanggal_publish" type="date" readonly />
                            <InputError :message="form.errors.tanggal_publish" />
                        </div>

                        <div class="md:col-span-2">
                            <CarouselImageUpload :files="files" :required="!isEdit" :error="form.errors.gambar" @update="onImageUpdate" />
                        </div>
                    </div>
                </section>

                <div class="flex flex-col-reverse gap-2 sm:flex-row sm:justify-end">
                    <Button type="button" variant="outline" @click="onCancel">Kembali</Button>
                    <Button type="submit" :disabled="form.processing">{{ form.processing ? 'Menyimpan...' : 'Simpan' }}</Button>
                </div>
            </form>
        </div>
    </AppLayout>
</template>
