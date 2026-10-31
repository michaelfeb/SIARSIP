<script setup lang="ts">
import Button from '@/components/ui/button/Button.vue';
import { Bold, Code2, Eye, Italic, List, ListOrdered, Quote, Text } from 'lucide-vue-next';
import { computed, ref } from 'vue';

const props = defineProps<{ modelValue: string | null; disabled?: boolean }>();
const emit = defineEmits<{ 'update:modelValue': [value: string | null] }>();
const textarea = ref<HTMLTextAreaElement | null>(null);
const previewOpen = ref(false);

const previewHtml = computed(() => {
    const escaped = (props.modelValue ?? '').replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;');

    return escaped
        .replace(/^### (.+)$/gm, '<h3>$1</h3>')
        .replace(/^## (.+)$/gm, '<h2>$1</h2>')
        .replace(/^# (.+)$/gm, '<h1>$1</h1>')
        .replace(/^&gt; (.+)$/gm, '<blockquote>$1</blockquote>')
        .replace(/^[-*] (.+)$/gm, '<li>$1</li>')
        .replace(/\*\*(.+?)\*\*/g, '<strong>$1</strong>')
        .replace(/\*(.+?)\*/g, '<em>$1</em>')
        .replace(/`(.+?)`/g, '<code>$1</code>')
        .replace(/\n/g, '<br>');
});

function onInput(event: Event): void {
    const value = (event.target as HTMLTextAreaElement).value;
    emit('update:modelValue', value === '' ? null : value);
}

function onFormat(prefix: string, suffix = prefix): void {
    const field = textarea.value;
    if (!field || props.disabled) {
        return;
    }

    const value = props.modelValue ?? '';
    const start = field.selectionStart;
    const end = field.selectionEnd;
    const selected = value.slice(start, end) || 'teks';
    const nextValue = `${value.slice(0, start)}${prefix}${selected}${suffix}${value.slice(end)}`;
    emit('update:modelValue', nextValue);

    requestAnimationFrame(() => {
        field.focus();
        field.setSelectionRange(start + prefix.length, start + prefix.length + selected.length);
    });
}
</script>

<template>
    <section class="border-border bg-card overflow-hidden rounded-2xl border shadow-sm">
        <header
            class="flex flex-col justify-between gap-4 bg-linear-to-r from-[#9a3412] to-[#c2410c] px-5 py-5 text-white sm:flex-row sm:items-center sm:px-6"
        >
            <div>
                <p class="text-xs font-semibold tracking-[0.14em] text-orange-100 uppercase">Panduan pemohon</p>
                <h2 class="mt-1 text-xl font-semibold">Catatan Proses</h2>
            </div>
            <Button
                type="button"
                variant="outline"
                class="w-full border-white/30 bg-white/15 text-white hover:bg-white/25 hover:text-white sm:w-auto"
                :disabled="disabled"
                @click="previewOpen = !previewOpen"
            >
                <Eye class="size-4" /> {{ previewOpen ? 'Editor' : 'Preview' }}
            </Button>
        </header>

        <div v-if="!previewOpen" class="p-5 sm:p-6">
            <div class="mb-3 flex flex-wrap gap-1 rounded-xl border border-slate-200 bg-slate-50 p-1.5">
                <Button
                    type="button"
                    variant="ghost"
                    size="icon"
                    class="size-8 hover:bg-white"
                    :disabled="disabled"
                    title="Judul"
                    aria-label="Judul"
                    @click="onFormat('## ', '')"
                    ><Text class="size-4"
                /></Button>
                <Button
                    type="button"
                    variant="ghost"
                    size="icon"
                    class="size-8 hover:bg-white"
                    :disabled="disabled"
                    title="Tebal"
                    aria-label="Tebal"
                    @click="onFormat('**')"
                    ><Bold class="size-4"
                /></Button>
                <Button
                    type="button"
                    variant="ghost"
                    size="icon"
                    class="size-8 hover:bg-white"
                    :disabled="disabled"
                    title="Miring"
                    aria-label="Miring"
                    @click="onFormat('*')"
                    ><Italic class="size-4"
                /></Button>
                <Button
                    type="button"
                    variant="ghost"
                    size="icon"
                    class="size-8 hover:bg-white"
                    :disabled="disabled"
                    title="Kutipan"
                    aria-label="Kutipan"
                    @click="onFormat('> ', '')"
                    ><Quote class="size-4"
                /></Button>
                <Button
                    type="button"
                    variant="ghost"
                    size="icon"
                    class="size-8 hover:bg-white"
                    :disabled="disabled"
                    title="Kode"
                    aria-label="Kode"
                    @click="onFormat('`')"
                    ><Code2 class="size-4"
                /></Button>
                <Button
                    type="button"
                    variant="ghost"
                    size="icon"
                    class="size-8 hover:bg-white"
                    :disabled="disabled"
                    title="Daftar"
                    aria-label="Daftar"
                    @click="onFormat('- ', '')"
                    ><List class="size-4"
                /></Button>
                <Button
                    type="button"
                    variant="ghost"
                    size="icon"
                    class="size-8 hover:bg-white"
                    :disabled="disabled"
                    title="Daftar bernomor"
                    aria-label="Daftar bernomor"
                    @click="onFormat('1. ', '')"
                    ><ListOrdered class="size-4"
                /></Button>
            </div>
            <textarea
                ref="textarea"
                :value="modelValue ?? ''"
                :disabled="disabled"
                rows="18"
                class="border-input placeholder:text-muted-foreground focus-visible:border-ring focus-visible:ring-ring min-h-96 w-full rounded-xl border bg-slate-50/40 p-4 text-sm leading-6 shadow-xs outline-none focus-visible:bg-white focus-visible:ring-1 disabled:cursor-not-allowed disabled:opacity-60"
                placeholder="Tulis panduan atau catatan proses untuk jenis surat ini..."
                @input="onInput"
            />
            <p class="text-muted-foreground mt-3 text-xs">
                Gunakan toolbar untuk menyisipkan format Markdown sederhana. Konten akan ditampilkan sebagai panduan pada formulir surat.
            </p>
        </div>

        <div v-else class="prose prose-slate max-w-none p-5 sm:p-6">
            <div class="mb-5 flex items-center gap-2 text-xs font-semibold tracking-[0.12em] text-orange-700 uppercase">
                <Eye class="size-4" aria-hidden="true" /> Tampilan pemohon
            </div>
            <div class="rounded-xl border border-slate-200 bg-slate-50/60 p-5 sm:p-6" v-html="previewHtml" />
        </div>
    </section>
</template>
