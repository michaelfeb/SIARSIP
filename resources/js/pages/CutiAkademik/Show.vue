<script setup lang="ts">
import Button from '@/components/ui/button/Button.vue';
import AppLayout from '@/layouts/AppLayout.vue';
import type { BreadcrumbItem } from '@/types';
import { Head, router } from '@inertiajs/vue3';
import { Download, Pencil, Send } from 'lucide-vue-next';
import Badge from '@/components/letters/Badge.vue';
import CutiAkademikAttachmentList from './components/CutiAkademikAttachmentList.vue';
import CutiAkademikDetails from './components/CutiAkademikDetails.vue';
import { getCutiAkademikCapabilities } from './ts/cutiAkademik.capabilities';
import type { CutiAkademikActor, CutiAkademikLetter } from './ts/cutiAkademik.types';

const props = defineProps<{ auth: { user: CutiAkademikActor }; letter: CutiAkademikLetter }>();
const breadcrumbs: BreadcrumbItem[] = [{ title: 'Cuti Akademik', href: '/cuti-akademik' }, { title: 'Detail', href: `/cuti-akademik/${props.letter.id}` }];
const capabilities = getCutiAkademikCapabilities(props.auth.user, props.letter);
function onBack(): void { router.get(route('cuti-akademik.index')); }
function onEdit(): void { router.get(route('cuti-akademik.edit', props.letter.id)); }
function onSubmit(): void { router.put(route('cuti-akademik.submit', props.letter.id)); }
function onDownload(): void { window.open(route('cuti-akademik.download-final-letter', props.letter.id), '_blank', 'noopener'); }
</script>

<template>

    <Head title="Detail Cuti Akademik" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="mx-auto w-full space-y-6 p-4 sm:p-6">
            <div class="flex flex-col justify-between gap-3 sm:flex-row sm:items-start">
                <div>
                    <h1 class="text-xl font-semibold">Detail Cuti Akademik</h1>
                    <p class="mt-1 text-sm text-muted-foreground">Tinjau data permohonan, dokumen, dan riwayat proses.
                    </p>
                </div>
                <div class="flex flex-wrap items-center gap-2">
                    <Badge type="status" :value="letter.status" />
                    <Badge type="stage" :value="letter.current_stage" />
                </div>
            </div>
            <CutiAkademikDetails :letter="letter" />
            <CutiAkademikAttachmentList :letter="letter" />
            <section class="rounded-lg border border-border bg-card shadow-sm">
                <header class="border-b border-border px-5 py-4">
                    <h2 class="text-lg font-semibold">Riwayat Proses</h2>
                </header>
                <ol v-if="letter.routes?.length" class="divide-y divide-border">
                    <li v-for="item in letter.routes" :key="item.id" class="p-4">
                        <p class="font-medium">{{ item.from_stage ?? 'Mahasiswa' }} → {{ item.to_stage }}</p>
                        <p class="mt-1 text-sm text-muted-foreground">{{ item.decider?.nama ?? '-' }}<span
                                v-if="item.note"> · {{ item.note }}</span></p>
                    </li>
                </ol>
                <p v-else class="p-5 text-sm text-muted-foreground">Belum ada riwayat proses.</p>
            </section>
            <div class="flex flex-col-reverse justify-between gap-3 sm:flex-row"><Button variant="outline"
                    class="rounded-lg" @click="onBack">Kembali</Button>
                <div class="flex flex-col gap-3 sm:flex-row"><Button v-if="capabilities.canEdit" variant="outline"
                        class="rounded-lg" @click="onEdit">
                        <Pencil class="mr-1 size-4" />Ubah Draft
                    </Button><Button v-if="capabilities.canSubmit" class="rounded-lg" @click="onSubmit">
                        <Send class="mr-1 size-4" />Kirim Permohonan
                    </Button><Button v-if="capabilities.canDownload" class="rounded-lg" @click="onDownload">
                        <Download class="mr-1 size-4" />Unduh Surat
                    </Button></div>
            </div>
        </div>
    </AppLayout>
</template>
