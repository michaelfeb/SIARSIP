<script setup lang="ts">
import { SIDANG_NOL_DOCUMENTS, SIDANG_NOL_STATUS, formatSidangNolDate, programStudiLabel } from '../ts/constants';
import type { SidangNol, SidangNolDocumentKey, SidangNolDocumentUrls } from '../ts/types';

withDefaults(defineProps<{
    berkas: SidangNol;
    documentUrls: SidangNolDocumentUrls;
    showNotes?: boolean;
    showDocuments?: boolean;
    variant?: 'table' | 'clean';
    heading?: string;
}>(), {
    showNotes: false,
    showDocuments: true,
    variant: 'table',
    heading: undefined,
});

defineEmits<{
    preview: [key: SidangNolDocumentKey];
}>();
</script>

<template>
    <section v-if="variant === 'clean'" class="rounded-lg border border-border bg-card px-4 shadow-sm sm:px-5">
        <header v-if="heading" class="-mx-4 border-b border-border px-4 py-4 sm:-mx-5 sm:px-5">
            <h2 class="text-lg font-semibold text-foreground">{{ heading }}</h2>
        </header>
        <dl class="divide-y divide-border">
            <div class="flex flex-col gap-1 py-3 sm:flex-row sm:items-baseline sm:justify-between sm:gap-6">
                <dt class="text-sm font-medium text-muted-foreground">Nomor Surat</dt>
                <dd class="text-sm font-medium text-foreground">{{ berkas.nomor_surat || '-' }}</dd>
            </div>
            <div class="flex flex-col gap-1 py-3 sm:flex-row sm:items-baseline sm:justify-between sm:gap-6">
                <dt class="text-sm font-medium text-muted-foreground">Nama Mahasiswa</dt>
                <dd class="text-sm font-medium text-foreground">{{ berkas.user?.nama || '-' }}</dd>
            </div>
            <div class="flex flex-col gap-1 py-3 sm:flex-row sm:items-baseline sm:justify-between sm:gap-6">
                <dt class="text-sm font-medium text-muted-foreground">NIM</dt>
                <dd class="text-sm text-foreground">{{ berkas.user?.nim || '-' }}</dd>
            </div>
            <div class="flex flex-col gap-1 py-3 sm:flex-row sm:items-baseline sm:justify-between sm:gap-6">
                <dt class="text-sm font-medium text-muted-foreground">Program Studi</dt>
                <dd class="text-sm text-foreground">{{ programStudiLabel(berkas.user?.program_studi) }}</dd>
            </div>
            <div class="flex flex-col gap-1 py-3 sm:flex-row sm:items-baseline sm:justify-between sm:gap-6">
                <dt class="text-sm font-medium text-muted-foreground">Tanggal Dikirim</dt>
                <dd class="text-sm text-foreground">{{ formatSidangNolDate(berkas.tanggal_dikirim) }}</dd>
            </div>
            <div class="flex flex-col gap-1 py-3 sm:flex-row sm:items-baseline sm:justify-between sm:gap-6">
                <dt class="text-sm font-medium text-muted-foreground">Tanggal Selesai</dt>
                <dd class="text-sm text-foreground">{{ formatSidangNolDate(berkas.tanggal_selesai) }}</dd>
            </div>
            <div class="flex flex-col gap-1 py-3 sm:flex-row sm:items-baseline sm:justify-between sm:gap-6">
                <dt class="text-sm font-medium text-muted-foreground">Status</dt>
                <dd class="text-sm text-foreground">{{ SIDANG_NOL_STATUS[berkas.status].label }}</dd>
            </div>
            <div v-if="showNotes" class="py-3">
                <dt class="text-sm font-medium text-muted-foreground">Catatan</dt>
                <dd class="mt-2 text-sm text-foreground">
                    <template v-if="berkas.notes?.length">
                        <div v-for="note in berkas.notes" :key="note.id" class="border-b border-border py-2 first:pt-0 last:border-0 last:pb-0">
                            <p class="font-medium">{{ note.user?.nama || 'Tidak diketahui' }}</p>
                            <p class="mt-1">{{ note.pesan }}</p>
                        </div>
                    </template>
                    <span v-else>-</span>
                </dd>
            </div>
        </dl>
    </section>

    <div v-else class="overflow-x-auto rounded-lg border border-gray-300">
        <table class="min-w-[36rem] w-full table-auto">
        <tbody class="text-sm">
            <tr class="border-b">
                <td class="border border-gray-200 px-4 py-4 font-medium text-gray-600">Nomor Surat</td>
                <td class="border border-gray-200 px-4 py-4">{{ berkas.nomor_surat || '-' }}</td>
            </tr>
            <tr class="border-b">
                <td class="border border-gray-200 px-4 py-4 font-medium text-gray-600">Nama Mahasiswa</td>
                <td class="border border-gray-200 px-4 py-4">{{ berkas.user?.nama || '-' }}</td>
            </tr>
            <tr class="border-b">
                <td class="border border-gray-200 px-4 py-4 font-medium text-gray-600">NIM</td>
                <td class="border border-gray-200 px-4 py-4">{{ berkas.user?.nim || '-' }}</td>
            </tr>
            <tr class="border-b">
                <td class="border border-gray-200 px-4 py-4 font-medium text-gray-600">Program Studi</td>
                <td class="border border-gray-200 px-4 py-4">{{ programStudiLabel(berkas.user?.program_studi) }}</td>
            </tr>
            <tr class="border-b">
                <td class="border border-gray-200 px-4 py-4 font-medium text-gray-600">Tanggal Dikirim</td>
                <td class="border border-gray-200 px-4 py-4">{{ formatSidangNolDate(berkas.tanggal_dikirim) }}</td>
            </tr>
            <tr class="border-b">
                <td class="border border-gray-200 px-4 py-4 font-medium text-gray-600">Tanggal Selesai</td>
                <td class="border border-gray-200 px-4 py-4">{{ formatSidangNolDate(berkas.tanggal_selesai) }}</td>
            </tr>
            <tr class="border-b">
                <td class="border border-gray-200 px-4 py-4 font-medium text-gray-600">Status</td>
                <td class="border border-gray-200 px-4 py-4">
                    {{ SIDANG_NOL_STATUS[berkas.status].label }}
                </td>
            </tr>
            <template v-if="showDocuments">
                <tr v-for="document in SIDANG_NOL_DOCUMENTS" :key="document.key" class="border-b">
                    <td class="border border-gray-200 px-4 py-4 font-medium text-gray-600">{{ document.label }}</td>
                    <td class="border border-gray-200 px-4 py-4">
                        <button
                            v-if="documentUrls[document.key]"
                            type="button"
                            class="text-blue-600 hover:underline"
                            @click="$emit('preview', document.key)"
                        >
                            Lihat Dokumen
                        </button>
                        <span v-else>-</span>
                    </td>
                </tr>
            </template>
            <tr v-if="showNotes">
                <td class="border border-gray-200 px-4 py-4 align-top font-medium text-gray-600">Catatan</td>
                <td class="border border-gray-200 px-4 py-4">
                    <template v-if="berkas.notes?.length">
                        <div v-for="note in berkas.notes" :key="note.id" class="border-b py-2 last:border-0">
                            <p class="font-medium">{{ note.user?.nama || 'Tidak diketahui' }}</p>
                            <p>{{ note.pesan }}</p>
                        </div>
                    </template>
                    <span v-else>-</span>
                </td>
            </tr>
        </tbody>
        </table>
    </div>
</template>
