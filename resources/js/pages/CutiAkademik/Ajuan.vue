<script setup lang="ts">
import InputError from '@/components/InputError.vue';
import { showConfirmationAlert } from '@/components/alerts/SwalAlert';
import LetterWorkflowStatus from '@/components/letters/LetterWorkflowStatus.vue';
import Button from '@/components/ui/button/Button.vue';
import Input from '@/components/ui/input/Input.vue';
import { Label } from '@/components/ui/label';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import AppLayout from '@/layouts/AppLayout.vue';
import type { BreadcrumbItem } from '@/types';
import { Head, router, useForm } from '@inertiajs/vue3';
import { computed } from 'vue';
import CutiAkademikAttachmentList from './components/CutiAkademikAttachmentList.vue';
import CutiAkademikDetails from './components/CutiAkademikDetails.vue';
import type { CutiAkademikFinalizationContext, CutiAkademikLetter, CutiAkademikWorkflowContext } from './ts/cutiAkademik.types';

const props = defineProps<{
    letter: CutiAkademikLetter;
    signatories: Record<string, { id: number; name: string }[]>;
    finalizationContext: CutiAkademikFinalizationContext;
    workflowContext: CutiAkademikWorkflowContext;
}>();
const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Cuti Akademik', href: '/cuti-akademik' },
    { title: 'Ajuan', href: `/cuti-akademik/${props.letter.id}/ajuan` },
];
const isFinalStage = props.letter.current_stage === props.finalizationContext.finalizationRole;
const form = useForm({
    decision: 'FORWARD',
    note: '',
    letter_number: '',
    signatory_role: props.finalizationContext.defaultSignatoryRole,
    signatory_id: '' as number | '',
});
const selectableSignatories = computed(() => props.signatories[form.signatory_role] ?? []);
function onCancel(): void {
    router.get(route('cuti-akademik.index'));
}
function onSignerChange(value: number): void {
    form.signatory_id = value;
}
function onSignatoryRoleChange(value: string): void {
    form.signatory_role = value;
    form.signatory_id = '';
}
function onSubmitDecision(decision: 'FORWARD' | 'REJECT'): void {
    form.decision = decision;
    if (isFinalStage && decision === 'FORWARD') {
        form.post(route('cuti-akademik.finalize', props.letter.id));
        return;
    }
    form.post(route('cuti-akademik.decide', props.letter.id));
}
async function onConfirmDecision(decision: 'FORWARD' | 'REJECT'): Promise<void> {
    const isReject = decision === 'REJECT';
    const result = await showConfirmationAlert({
        variant: isReject ? 'warning' : 'info',
        title: isReject ? 'Tolak permohonan?' : isFinalStage ? 'Generate surat?' : 'Teruskan permohonan?',
        text: isReject
            ? 'Permohonan akan ditolak dan tidak dapat dilanjutkan.'
            : isFinalStage
              ? 'Surat akan dibuat dan diarsipkan.'
              : 'Permohonan akan diteruskan ke tahap berikutnya.',
        confirmButtonText: isReject ? 'Tolak' : 'Lanjutkan',
        destructive: isReject,
    });

    if (result.isConfirmed) onSubmitDecision(decision);
}
</script>

<template>
    <Head title="Proses Cuti Akademik" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="mx-auto w-full max-w-[1800px] space-y-6 p-4 sm:p-6">
            <div>
                <h1 class="text-xl font-semibold">Proses Permohonan Cuti Akademik</h1>
                <p class="text-muted-foreground mt-1 text-sm">Periksa detail dan dokumen sebelum mengambil keputusan.</p>
            </div>
            <div class="grid gap-6 xl:grid-cols-[minmax(0,2fr)_minmax(20rem,1fr)] xl:items-start">
                <div class="min-w-0 space-y-6">
                    <CutiAkademikDetails :letter="letter" />
                    <CutiAkademikAttachmentList :letter="letter" />
                    <LetterWorkflowStatus
                        :approval-chain="workflowContext.approvalChain"
                        :status="letter.status"
                        :current-stage="letter.current_stage"
                        :routes="letter.routes"
                        :letter-number="letter.letter_number"
                    />
                </div>
                <aside class="xl:sticky xl:top-6">
                    <form class="border-border bg-card overflow-hidden rounded-lg border shadow-sm" @submit.prevent="onConfirmDecision('FORWARD')">
                        <header class="border-border border-b px-5 py-4">
                            <h2 class="text-lg font-semibold">Keputusan</h2>
                            <p class="text-muted-foreground mt-1 text-sm">Tahap saat ini: {{ letter.current_stage }}</p>
                        </header>
                        <div class="space-y-5 p-5">
                            <div v-if="isFinalStage" class="space-y-2">
                                <Label for="letter_number">Nomor Surat <span class="text-destructive">*</span></Label
                                ><Input id="letter_number" v-model="form.letter_number" placeholder="Masukkan nomor surat resmi" />
                                <InputError :message="form.errors.letter_number" />
                            </div>
                            <div v-if="isFinalStage" class="space-y-2">
                                <Label for="signatory_role">Peran Penandatangan <span class="text-destructive">*</span></Label>
                                <Select :model-value="form.signatory_role" @update:model-value="onSignatoryRoleChange">
                                    <SelectTrigger id="signatory_role" class="h-10">
                                        <SelectValue placeholder="Pilih peran penandatangan" />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem v-for="role in finalizationContext.signatoryRoles" :key="role" :value="role">{{
                                            role
                                        }}</SelectItem>
                                    </SelectContent>
                                </Select>
                                <p class="text-muted-foreground text-xs">
                                    Default mengikuti tahap sebelum {{ finalizationContext.finalizationRole }}.
                                </p>
                                <InputError :message="form.errors.signatory_role" />
                            </div>
                            <div v-if="isFinalStage" class="space-y-2">
                                <Label for="signatory_id"
                                    >Pilih Penandatangan ({{ form.signatory_role }}) <span class="text-destructive">*</span></Label
                                ><Select :model-value="form.signatory_id || undefined" @update:model-value="onSignerChange">
                                    <SelectTrigger id="signatory_id" class="h-10">
                                        <SelectValue :placeholder="`Pilih ${form.signatory_role}`" />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem v-for="person in selectableSignatories" :key="person.id" :value="person.id">{{
                                            person.name
                                        }}</SelectItem>
                                    </SelectContent>
                                </Select>
                                <InputError :message="form.errors.signatory_id" />
                            </div>
                            <div class="space-y-2">
                                <Label for="note">Catatan <span class="text-muted-foreground font-normal">(opsional)</span></Label
                                ><textarea
                                    id="note"
                                    v-model="form.note"
                                    class="border-input placeholder:text-muted-foreground focus-visible:border-ring focus-visible:ring-ring/50 min-h-28 w-full rounded-lg border bg-transparent px-3 py-2 text-sm shadow-xs outline-none focus-visible:ring-[1px]"
                                    placeholder="Tambahkan catatan atau alasan keputusan..."
                                />
                                <InputError :message="form.errors.note" />
                            </div>
                        </div>
                        <footer class="border-border space-y-3 border-t p-5">
                            <div class="grid grid-cols-2 gap-3">
                                <Button type="button" variant="outline" class="rounded-lg" @click="onCancel">Batal</Button
                                ><Button
                                    type="button"
                                    class="rounded-lg bg-rose-600 text-white hover:bg-rose-700"
                                    :disabled="form.processing"
                                    @click="onConfirmDecision('REJECT')"
                                    >Tolak</Button
                                >
                            </div>
                            <Button type="submit" class="w-full rounded-lg" :disabled="form.processing">
                                {{
                                    form.processing ? 'Memproses...' : isFinalStage ? 'Terima & Generate Surat' : 'Teruskan ke Tahap Berikutnya'
                                }}</Button
                            >
                        </footer>
                    </form>
                </aside>
            </div>
        </div>
    </AppLayout>
</template>
