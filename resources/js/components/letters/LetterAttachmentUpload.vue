<script setup lang="ts">
import Button from '@/components/ui/button/Button.vue';
import InputError from '@/components/InputError.vue';
import { FileText, Upload, X } from 'lucide-vue-next';
import { computed, ref } from 'vue';

const props = withDefaults(defineProps<{
    modelValue: File[];
    error?: string;
    required?: boolean;
    maxFiles?: number;
    maxSizeKiB?: number;
}>(), {
    maxFiles: 5,
    maxSizeKiB: 100,
    required: false,
});

const emit = defineEmits<{ 'update:modelValue': [files: File[]] }>();
const input = ref<HTMLInputElement | null>(null);
const localError = ref<string | null>(null);
const accept = '.pdf,.jpg,.jpeg,.png,application/pdf,image/jpeg,image/png';
const maxBytes = computed(() => props.maxSizeKiB * 1024);

function onOpenFilePicker(): void {
    input.value?.click();
}

function onFilesSelected(event: Event): void {
    const files = Array.from((event.target as HTMLInputElement).files ?? []);
    addFiles(files);
    (event.target as HTMLInputElement).value = '';
}

function onDrop(event: DragEvent): void {
    event.preventDefault();
    addFiles(Array.from(event.dataTransfer?.files ?? []));
}

function addFiles(files: File[]): void {
    localError.value = null;

    if (props.modelValue.length + files.length > props.maxFiles) {
        localError.value = `Maksimal ${props.maxFiles} dokumen dapat diunggah.`;
        return;
    }

    const invalid = files.find((file) => !['application/pdf', 'image/jpeg', 'image/png'].includes(file.type) || file.size > maxBytes.value);
    if (invalid) {
        localError.value = `Gunakan PDF, JPG, JPEG, atau PNG dengan ukuran maksimal ${props.maxSizeKiB} KB.`;
        return;
    }

    emit('update:modelValue', [...props.modelValue, ...files]);
}

function onRemove(index: number): void {
    emit('update:modelValue', props.modelValue.filter((_, currentIndex) => currentIndex !== index));
}
</script>

<template>
    <section class="space-y-3 rounded-lg border border-border bg-card p-4 sm:p-5">
        <div class="flex flex-col justify-between gap-2 sm:flex-row sm:items-start">
            <div>
                <h3 class="font-semibold">Dokumen Pendukung <span v-if="required" class="text-destructive">*</span></h3>
                <p class="mt-1 text-sm text-muted-foreground">Unggah {{ required ? '1' : '0' }}–{{ maxFiles }} file PDF, JPG, JPEG, atau PNG. Maksimal {{ maxSizeKiB }} KB per file.</p>
            </div>
            <span class="text-xs font-medium text-muted-foreground">{{ modelValue.length }}/{{ maxFiles }} file</span>
        </div>

        <input ref="input" class="sr-only" type="file" multiple :accept="accept" @change="onFilesSelected">
        <div
            class="flex min-h-36 cursor-pointer flex-col items-center justify-center rounded-lg border border-dashed border-border bg-muted/30 p-5 text-center transition hover:border-primary hover:bg-primary/5"
            @click="onOpenFilePicker"
            @dragover.prevent
            @drop="onDrop"
        >
            <Upload class="mb-2 size-6 text-primary" />
            <p class="text-sm font-medium">Pilih dokumen atau tarik ke sini</p>
            <p class="mt-1 text-xs text-muted-foreground">{{ required ? 'Dokumen pendukung wajib saat permohonan dikirim.' : 'Dokumen pendukung bersifat opsional.' }}</p>
        </div>

        <ul v-if="modelValue.length" class="space-y-2">
            <li v-for="(file, index) in modelValue" :key="`${file.name}-${file.lastModified}`" class="flex items-center gap-3 rounded-lg border border-border px-3 py-2">
                <FileText class="size-4 shrink-0 text-primary" />
                <span class="min-w-0 flex-1 truncate text-sm">{{ file.name }}</span>
                <span class="shrink-0 text-xs text-muted-foreground">{{ Math.ceil(file.size / 1024) }} KB</span>
                <Button type="button" variant="ghost" size="icon" class="size-7 rounded-lg text-destructive" title="Hapus dokumen" aria-label="Hapus dokumen" @click="onRemove(index)">
                    <X class="size-4" />
                </Button>
            </li>
        </ul>

        <InputError :message="error ?? localError ?? undefined" />
    </section>
</template>
