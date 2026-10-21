<script setup lang="ts">
import InputError from '@/components/InputError.vue';
import { Label } from '@/components/ui/label';
import vueFilePond from 'vue-filepond';
import FilePondPluginFileValidateType from 'filepond-plugin-file-validate-type';
import FilePondPluginPdfPreview from 'filepond-plugin-pdf-preview';
import type { SidangNolDocumentDefinition } from '../ts/types';

import 'filepond/dist/filepond.min.css';
import 'filepond-plugin-pdf-preview/dist/filepond-plugin-pdf-preview.css';

const FilePond = vueFilePond(FilePondPluginFileValidateType, FilePondPluginPdfPreview);

defineProps<{
    document: SidangNolDocumentDefinition;
    files: unknown[];
    error?: string;
}>();

defineEmits<{
    update: [files: unknown[]];
}>();
</script>

<template>
    <div class="space-y-2">
        <Label :for="document.key" class="gap-1">
            {{ document.label }}
            <span v-if="document.required" class="text-red-500">*</span>
            <span class="text-[12px]">(Max 1 MB)</span>
        </Label>

        <FilePond
            :name="document.key"
            label-idle="Seret & lepas dokumen atau <span class='filepond--label-action'>Telusuri</span>"
            :allow-multiple="false"
            accepted-file-types="application/pdf"
            :files="files"
            :pdf-preview-height="400"
            @updatefiles="$emit('update', $event)"
        />

        <InputError :message="error" />

        <div v-if="document.instructions?.length" class="text-xs">
            <p>Keterangan</p>
            <ul class="list-disc space-y-1 pl-5">
                <li v-for="instruction in document.instructions" :key="instruction">
                    {{ instruction }}
                </li>
            </ul>
        </div>
    </div>
</template>
