<script setup lang="ts">
import InputError from '@/components/InputError.vue';
import Button from '@/components/ui/button/Button.vue';
import Input from '@/components/ui/input/Input.vue';
import { Label } from '@/components/ui/label';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import AppLayout from '@/layouts/AppLayout.vue';
import type { BreadcrumbItem } from '@/types';
import { Head, router, useForm } from '@inertiajs/vue3';
import Swal from 'sweetalert2';
import CutiAkademikAttachmentList from './components/CutiAkademikAttachmentList.vue';
import CutiAkademikDetails from './components/CutiAkademikDetails.vue';
import type { CutiAkademikLetter } from './ts/cutiAkademik.types';

const props = defineProps<{ letter: CutiAkademikLetter; signatories: { id: number; name: string }[] }>();
const breadcrumbs: BreadcrumbItem[] = [{ title: 'Cuti Akademik', href: '/cuti-akademik' }, { title: 'Ajuan', href: `/cuti-akademik/${props.letter.id}/ajuan` }];
const isFinalStage = props.letter.current_stage === 'RESEPSIONIS';
const form = useForm({ decision: 'FORWARD', note: '', letter_number: '', signatory_id: '' as number | '' });
function onCancel(): void { router.get(route('cuti-akademik.index')); }
function onSignerChange(value: number): void { form.signatory_id = value; }
function onSubmitDecision(decision: 'FORWARD' | 'REJECT'): void { form.decision = decision; if (isFinalStage && decision === 'FORWARD') { form.post(route('cuti-akademik.finalize', props.letter.id)); return; } form.post(route('cuti-akademik.decide', props.letter.id)); }
async function onConfirmDecision(decision: 'FORWARD' | 'REJECT'): Promise<void> { const result = await Swal.fire({ icon: decision === 'REJECT' ? 'warning' : 'question', title: decision === 'REJECT' ? 'Tolak permohonan?' : isFinalStage ? 'Generate surat?' : 'Teruskan permohonan?', showCancelButton: true, confirmButtonText: decision === 'REJECT' ? 'Tolak' : 'Lanjutkan', cancelButtonText: 'Batal' }); if (result.isConfirmed) onSubmitDecision(decision); }
</script>

<template>
    <Head title="Proses Cuti Akademik" />
    <AppLayout :breadcrumbs="breadcrumbs"><div class="mx-auto w-full max-w-[1800px] space-y-6 p-4 sm:p-6"><div><h1 class="text-xl font-semibold">Proses Permohonan Cuti Akademik</h1><p class="mt-1 text-sm text-muted-foreground">Periksa detail dan dokumen sebelum mengambil keputusan.</p></div><div class="grid gap-6 xl:grid-cols-[minmax(0,2fr)_minmax(20rem,1fr)] xl:items-start"><div class="min-w-0 space-y-6"><CutiAkademikDetails :letter="letter" /><CutiAkademikAttachmentList :letter="letter" /></div><aside class="xl:sticky xl:top-6"><form class="overflow-hidden rounded-lg border border-border bg-card shadow-sm" @submit.prevent="onConfirmDecision('FORWARD')"><header class="border-b border-border px-5 py-4"><h2 class="text-lg font-semibold">Keputusan</h2><p class="mt-1 text-sm text-muted-foreground">Tahap saat ini: {{ letter.current_stage }}</p></header><div class="space-y-5 p-5"><div v-if="isFinalStage" class="space-y-2"><Label for="letter_number">Nomor Surat <span class="text-destructive">*</span></Label><Input id="letter_number" v-model="form.letter_number" placeholder="Masukkan nomor surat resmi" /><InputError :message="form.errors.letter_number" /></div><div v-if="isFinalStage" class="space-y-2"><Label for="signatory_id">Pilih Penandatangan <span class="text-destructive">*</span></Label><Select :model-value="form.signatory_id || undefined" @update:model-value="onSignerChange"><SelectTrigger id="signatory_id" class="h-10"><SelectValue placeholder="Pilih DEKAN" /></SelectTrigger><SelectContent><SelectItem v-for="person in signatories" :key="person.id" :value="person.id">{{ person.name }}</SelectItem></SelectContent></Select><InputError :message="form.errors.signatory_id" /></div><div class="space-y-2"><Label for="note">Catatan <span class="font-normal text-muted-foreground">(opsional)</span></Label><textarea id="note" v-model="form.note" class="min-h-28 w-full rounded-lg border border-input bg-transparent px-3 py-2 text-sm shadow-xs outline-none placeholder:text-muted-foreground focus-visible:border-ring focus-visible:ring-ring/50 focus-visible:ring-[1px]" placeholder="Tambahkan catatan atau alasan keputusan..." /><InputError :message="form.errors.note" /></div></div><footer class="space-y-3 border-t border-border p-5"><div class="grid grid-cols-2 gap-3"><Button type="button" variant="outline" class="rounded-lg" @click="onCancel">Batal</Button><Button type="button" class="rounded-lg bg-rose-600 text-white hover:bg-rose-700" :disabled="form.processing" @click="onConfirmDecision('REJECT')">Tolak</Button></div><Button type="submit" class="w-full rounded-lg" :disabled="form.processing">{{ form.processing ? 'Memproses...' : isFinalStage ? 'Terima & Generate Surat' : 'Teruskan ke Tahap Berikutnya' }}</Button></footer></form></aside></div></div></AppLayout>
</template>
