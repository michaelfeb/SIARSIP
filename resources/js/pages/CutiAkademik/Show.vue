<script setup lang="ts">
import { showErrorAlert, showSuccessAlert } from '@/components/alerts/SwalAlert';
import Button from '@/components/ui/button/Button.vue';
import AppLayout from '@/layouts/AppLayout.vue';
import type { BreadcrumbItem } from '@/types';
import { Head, router } from '@inertiajs/vue3';
import { Pencil, Send } from 'lucide-vue-next';
import Badge from '@/components/letters/Badge.vue';
import LetterWorkflowStatus from '@/components/letters/LetterWorkflowStatus.vue';
import CutiAkademikAttachmentList from './components/CutiAkademikAttachmentList.vue';
import CutiAkademikDetails from './components/CutiAkademikDetails.vue';
import { getCutiAkademikCapabilities } from './ts/cutiAkademik.capabilities';
import type { CutiAkademikActor, CutiAkademikLetter, CutiAkademikWorkflowContext } from './ts/cutiAkademik.types';

const props = defineProps<{
    auth: { user: CutiAkademikActor };
    letter: CutiAkademikLetter;
    workflowContext: CutiAkademikWorkflowContext;
}>();
const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Cuti Akademik', href: '/cuti-akademik' },
    { title: 'Detail', href: `/cuti-akademik/${props.letter.id}` },
];
const capabilities = getCutiAkademikCapabilities(props.auth.user, props.letter);
function onBack(): void {
    router.get(route('cuti-akademik.index'));
}
function onEdit(): void {
    router.get(route('cuti-akademik.edit', props.letter.id));
}
function onSubmit(): void {
    router.put(route('cuti-akademik.submit', props.letter.id), {}, {
        onSuccess: () => showSuccessAlert({
            text: 'Permohonan Cuti Akademik berhasil dikirim.',
        }),
        onError: () => showErrorAlert({
            text: 'Permohonan tidak dapat dikirim. Silakan periksa kembali data permohonan.',
        }),
    });
}
</script>

<template>
    <Head title="Detail Cuti Akademik" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="mx-auto w-full max-w-[1800px] space-y-6 p-4 sm:p-6">
            <div class="flex flex-col justify-between gap-3 sm:flex-row sm:items-start">
                <div>
                    <h1 class="text-xl font-semibold">Detail Cuti Akademik</h1>
                    <p class="text-muted-foreground mt-1 text-sm">Tinjau data permohonan, dokumen, dan riwayat proses.
                    </p>
                </div>
                <div class="flex flex-wrap items-center gap-2">
                    <Badge type="status" :value="letter.status" />
                    <template v-if="letter.current_stage">
                        <Badge type="stage" :value="letter.current_stage" />
                    </template>
                </div>
            </div>
            <CutiAkademikDetails :letter="letter" />
            <CutiAkademikAttachmentList :letter="letter" />
            <LetterWorkflowStatus :approval-chain="workflowContext.approvalChain" :status="letter.status"
                :current-stage="letter.current_stage" :routes="letter.routes" :letter-number="letter.letter_number"
                :download-url="capabilities.canDownload ? route('cuti-akademik.download-final-letter', letter.id) : null" />
            <div class="flex flex-col-reverse gap-3 sm:flex-row sm:justify-end">
                <Button type="button"
                    class="w-full border border-orange-400 bg-white text-orange-500 hover:bg-orange-100 sm:w-auto"
                    @click="onBack">Kembali</Button>
                <div class="flex flex-col gap-3 sm:flex-row">
                    <Button v-if="capabilities.canEdit" variant="outline" class="w-full rounded-lg sm:w-auto"
                        @click="onEdit">
                        <Pencil class="mr-1 size-4" />Ubah Draft
                    </Button><Button v-if="capabilities.canSubmit" class="w-full rounded-lg sm:w-auto"
                        @click="onSubmit">
                        <Send class="mr-1 size-4" />Kirim Permohonan
                    </Button>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
