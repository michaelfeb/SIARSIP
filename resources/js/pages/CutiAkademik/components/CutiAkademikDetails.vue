<script setup lang="ts">
import type { CutiAkademikLetter } from '../ts/cutiAkademik.types';

defineProps<{ letter: CutiAkademikLetter; heading?: string }>();

function formatCurrency(value: string | number | null | undefined): string {
    return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', maximumFractionDigits: 0 }).format(Number(value ?? 0));
}
</script>

<template>
    <section class="rounded-lg border border-border bg-card shadow-sm">
        <header class="border-b border-border px-5 py-4">
            <h2 class="text-lg font-semibold">{{ heading ?? 'Data Permohonan' }}</h2>
        </header>
        <dl class="divide-y divide-border px-5">
            <div class="grid gap-1 py-3 sm:grid-cols-[10rem_minmax(0,1fr)] sm:gap-4"><dt class="font-medium">Nama</dt><dd>{{ letter.applicant_name }}</dd></div>
            <div class="grid gap-1 py-3 sm:grid-cols-[10rem_minmax(0,1fr)] sm:gap-4"><dt class="font-medium">NIM</dt><dd>{{ letter.applicant_id_number }}</dd></div>
            <div class="grid gap-1 py-3 sm:grid-cols-[10rem_minmax(0,1fr)] sm:gap-4"><dt class="font-medium">Program Studi</dt><dd>{{ letter.program_studi ?? '-' }}</dd></div>
            <div class="grid gap-1 py-3 sm:grid-cols-[10rem_minmax(0,1fr)] sm:gap-4"><dt class="font-medium">Semester</dt><dd>{{ letter.meta.semester ?? '-' }}</dd></div>
            <div class="grid gap-1 py-3 sm:grid-cols-[10rem_minmax(0,1fr)] sm:gap-4"><dt class="font-medium">Tahun Akademik</dt><dd>{{ letter.meta.tahun_akademik ?? '-' }}</dd></div>
            <div class="grid gap-1 py-3 sm:grid-cols-[10rem_minmax(0,1fr)] sm:gap-4"><dt class="font-medium">Nomor Kontak</dt><dd>{{ letter.meta.nomor_hp ?? '-' }}</dd></div>
            <div class="grid gap-1 py-3 sm:grid-cols-[10rem_minmax(0,1fr)] sm:gap-4"><dt class="font-medium">Nominal UKT</dt><dd>{{ formatCurrency(letter.meta.nominal_ukt) }}</dd></div>
            <div class="grid gap-1 py-3 sm:grid-cols-[10rem_minmax(0,1fr)] sm:gap-4"><dt class="font-medium">Tujuan</dt><dd class="whitespace-pre-line">{{ letter.meta.alasan_cuti ?? letter.purpose ?? '-' }}</dd></div>
            <div v-if="letter.letter_number" class="grid gap-1 py-3 sm:grid-cols-[10rem_minmax(0,1fr)] sm:gap-4"><dt class="font-medium">Nomor Surat</dt><dd><code class="rounded bg-muted px-2 py-1 text-xs">{{ letter.letter_number }}</code></dd></div>
        </dl>
    </section>
</template>
