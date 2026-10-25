<script setup lang="ts">
import type { CutiAkademikLetter } from '../ts/cutiAkademik.types';

defineProps<{ letter: CutiAkademikLetter; heading?: string }>();

function formatCurrency(value: string | number | null | undefined): string {
    return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', maximumFractionDigits: 0 }).format(Number(value ?? 0));
}
</script>

<template>
    <section class="rounded-lg border border-border bg-card px-4 shadow-sm sm:px-5">
        <header class="-mx-4 border-b border-border px-4 py-4 sm:-mx-5 sm:px-5">
            <h2 class="text-lg font-semibold text-foreground">{{ heading ?? 'Data Permohonan' }}</h2>
        </header>
        <dl class="divide-y divide-border">
            <div class="flex flex-col gap-1 py-3 sm:flex-row sm:items-baseline sm:justify-between sm:gap-6"><dt class="text-sm font-medium text-muted-foreground">Nama Mahasiswa</dt><dd class="text-sm font-medium text-foreground sm:text-right">{{ letter.applicant_name }}</dd></div>
            <div class="flex flex-col gap-1 py-3 sm:flex-row sm:items-baseline sm:justify-between sm:gap-6"><dt class="text-sm font-medium text-muted-foreground">NIM</dt><dd class="text-sm text-foreground sm:text-right">{{ letter.applicant_id_number }}</dd></div>
            <div class="flex flex-col gap-1 py-3 sm:flex-row sm:items-baseline sm:justify-between sm:gap-6"><dt class="text-sm font-medium text-muted-foreground">Program Studi</dt><dd class="text-sm text-foreground sm:text-right">{{ letter.program_studi ?? '-' }}</dd></div>
            <div class="flex flex-col gap-1 py-3 sm:flex-row sm:items-baseline sm:justify-between sm:gap-6"><dt class="text-sm font-medium text-muted-foreground">Semester</dt><dd class="text-sm text-foreground sm:text-right">{{ letter.meta.semester ?? '-' }}</dd></div>
            <div class="flex flex-col gap-1 py-3 sm:flex-row sm:items-baseline sm:justify-between sm:gap-6"><dt class="text-sm font-medium text-muted-foreground">Tahun Akademik</dt><dd class="text-sm text-foreground sm:text-right">{{ letter.meta.tahun_akademik ?? '-' }}</dd></div>
            <div class="flex flex-col gap-1 py-3 sm:flex-row sm:items-baseline sm:justify-between sm:gap-6"><dt class="text-sm font-medium text-muted-foreground">Nomor Kontak</dt><dd class="text-sm text-foreground sm:text-right">{{ letter.meta.nomor_hp ?? '-' }}</dd></div>
            <div class="flex flex-col gap-1 py-3 sm:flex-row sm:items-baseline sm:justify-between sm:gap-6"><dt class="text-sm font-medium text-muted-foreground">Nominal UKT</dt><dd class="text-sm text-foreground sm:text-right">{{ formatCurrency(letter.meta.nominal_ukt) }}</dd></div>
            <div class="flex flex-col gap-1 py-3 sm:flex-row sm:items-start sm:justify-between sm:gap-6"><dt class="text-sm font-medium text-muted-foreground">Tujuan</dt><dd class="whitespace-pre-line text-sm text-foreground sm:max-w-2xl sm:text-right">{{ letter.meta.alasan_cuti ?? letter.purpose ?? '-' }}</dd></div>
            <div v-if="letter.letter_number" class="flex flex-col gap-1 py-3 sm:flex-row sm:items-baseline sm:justify-between sm:gap-6"><dt class="text-sm font-medium text-muted-foreground">Nomor Surat</dt><dd class="sm:text-right"><code class="rounded bg-muted px-2 py-1 text-xs text-foreground">{{ letter.letter_number }}</code></dd></div>
        </dl>
    </section>
</template>
