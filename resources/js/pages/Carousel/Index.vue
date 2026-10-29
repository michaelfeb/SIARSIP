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
import { getCarouselCapabilities } from './ts/carousel.capabilities';
import type { CarouselActor, CarouselFilters, CarouselPaginated, CarouselRecord, CarouselTableQuery } from './ts/carousel.types';

const props = defineProps<{
    auth: { user: CarouselActor };
    carousel: CarouselPaginated<CarouselRecord>;
    filters: CarouselFilters;
}>();

const breadcrumbs: BreadcrumbItem[] = [{ title: 'Carousel', href: '/carousel' }];
const loading = ref(false);
const capabilities = computed(() => getCarouselCapabilities(props.auth.user));

function onDataTableQueryChange(query: CarouselTableQuery): void {
    loading.value = true;
    router.get(
        route('carousel.index'),
        { page: query.page, per_page: query.perPage, search: query.search || undefined },
        {
            only: ['carousel', 'filters'],
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
    router.get(route('carousel.create'));
}

function onEdit(carousel: CarouselRecord): void {
    router.get(route('carousel.edit', carousel.id));
}

async function onDelete(carousel: CarouselRecord): Promise<void> {
    const result = await showConfirmationAlert({
        title: 'Hapus carousel?',
        text: `Gambar “${carousel.nama}” dan datanya akan dihapus.`,
        confirmButtonText: 'Hapus',
        cancelButtonText: 'Batal',
        variant: 'warning',
        destructive: true,
    });

    if (!result.isConfirmed) return;

    router.delete(route('carousel.destroy', carousel.id), {
        preserveScroll: true,
        onSuccess: () => showSuccessAlert({ text: 'Carousel berhasil dihapus.' }),
        onError: () => showErrorAlert({ text: 'Carousel gagal dihapus. Silakan coba lagi.' }),
    });
}

function onToggleStatus(carousel: CarouselRecord): void {
    router.put(
        route('carousel.toggle', carousel.id),
        {},
        {
            preserveScroll: true,
            onSuccess: () => showSuccessAlert({ text: `Carousel berhasil ${carousel.status ? 'dinonaktifkan' : 'diaktifkan'}.` }),
            onError: () => showErrorAlert({ text: 'Status carousel gagal diperbarui. Silakan coba lagi.' }),
        },
    );
}
</script>

<template>
    <Head title="Carousel" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="mx-auto w-full space-y-6 p-4 sm:p-6">
            <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
                <Heading title="Manajemen Carousel" description="Kelola gambar pengumuman yang tampil di halaman depan." />
                <Button v-if="capabilities.canCreate" class="rounded-lg" @click="onCreate">
                    <Plus class="size-4" />
                    Tambah Carousel
                </Button>
            </div>

            <DataTable
                :carousel="carousel"
                :filters="filters"
                :loading="loading"
                :capabilities="capabilities"
                @query-change="onDataTableQueryChange"
                @edit="onEdit"
                @delete="onDelete"
                @toggle="onToggleStatus"
            />
        </div>
    </AppLayout>
</template>
