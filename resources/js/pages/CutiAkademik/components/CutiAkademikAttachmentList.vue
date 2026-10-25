<script setup lang="ts">
import Button from '@/components/ui/button/Button.vue';
import { Download, FileText } from 'lucide-vue-next';
import type { CutiAkademikLetter } from '../ts/cutiAkademik.types';

const props = defineProps<{ letter: CutiAkademikLetter }>();

function attachmentUrl(id: number): string {
    return route('cuti-akademik.show-attachment', [props.letter.id, id]);
}

function onOpenAttachment(id: number): void {
    window.open(attachmentUrl(id), '_blank', 'noopener');
}
</script>

<template>
    <section class="rounded-lg border border-border bg-card shadow-sm">
        <header class="border-b border-border px-5 py-4"><h2 class="text-lg font-semibold">Dokumen Pendukung</h2></header>
        <div v-if="letter.attachments?.length" class="divide-y divide-border">
            <div v-for="attachment in letter.attachments" :key="attachment.id" class="flex items-center gap-3 px-5 py-3">
                <FileText class="size-5 text-primary" />
                <div class="min-w-0 flex-1"><p class="truncate text-sm font-medium">{{ attachment.original_name }}</p><p class="text-xs text-muted-foreground">{{ Math.ceil(attachment.size_bytes / 1024) }} KB</p></div>
                <Button type="button" variant="outline" size="sm" class="rounded-lg" @click="onOpenAttachment(attachment.id)"><Download class="mr-1 size-4" />Lihat</Button>
            </div>
        </div>
        <p v-else class="p-5 text-sm text-muted-foreground">Belum ada dokumen pendukung.</p>
    </section>
</template>
