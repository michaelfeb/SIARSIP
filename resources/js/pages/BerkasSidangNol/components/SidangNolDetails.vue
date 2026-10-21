<script setup lang="ts">
import { SIDANG_NOL_DOCUMENTS, SIDANG_NOL_STATUS, formatSidangNolDate, programStudiLabel } from '../ts/constants';
import type { SidangNol, SidangNolDocumentKey, SidangNolDocumentUrls } from '../ts/types';

defineProps<{
    berkas: SidangNol;
    documentUrls: SidangNolDocumentUrls;
    showNotes?: boolean;
}>();

defineEmits<{
    preview: [key: SidangNolDocumentKey];
}>();
</script>

<template>
    <table class="w-full table-auto border border-gray-300">
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
</template>
