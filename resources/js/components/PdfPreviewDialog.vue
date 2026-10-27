<script setup lang="ts">
import Button from '@/components/ui/button/Button.vue';

defineProps<{ open: boolean; url: string | null }>();
const emit = defineEmits<{ close: [] }>();
</script>

<template>
    <div v-if="open" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4">
        <section class="flex h-[min(90dvh,58rem)] w-full max-w-5xl flex-col overflow-hidden rounded-lg bg-background shadow-xl">
            <header class="flex items-center justify-between border-b px-5 py-4">
                <div><h2 class="font-semibold">Pratinjau PDF</h2><p class="text-sm text-muted-foreground">Dokumen ini belum berlaku.</p></div>
                <Button type="button" variant="outline" @click="emit('close')">Tutup</Button>
            </header>
            <iframe v-if="url" :src="url" title="Pratinjau PDF" class="min-h-0 flex-1 bg-muted" />
        </section>
    </div>
</template>
