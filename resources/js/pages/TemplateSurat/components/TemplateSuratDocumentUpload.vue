<script setup lang="ts">
import InputError from '@/components/InputError.vue';
import { Label } from '@/components/ui/label';
import vueFilePond from 'vue-filepond';
import FilePondPluginFileValidateType from 'filepond-plugin-file-validate-type';
import { TEMPLATE_SURAT_DOCUMENT_ACCEPTED_TYPES, TEMPLATE_SURAT_DOCUMENT_MAX_SIZE_LABEL } from '../ts/templateSurat.constants';

import 'filepond/dist/filepond.min.css';

const FilePond = vueFilePond(FilePondPluginFileValidateType);

defineProps<{ files: unknown[]; error?: string; required: boolean }>();
defineEmits<{ update: [files: unknown[]] }>();
</script>

<template>
    <div class="space-y-2">
        <Label for="dokumen_path" class="gap-1"
            >Upload Dokumen <span v-if="required" class="text-destructive">*</span
            ><span class="text-muted-foreground text-xs">(DOC/DOCX, maks. {{ TEMPLATE_SURAT_DOCUMENT_MAX_SIZE_LABEL }})</span></Label
        >
        <FilePond
            name="dokumen_path"
            label-idle="Seret & lepas dokumen atau <span class='filepond--label-action'>Telusuri</span>"
            :allow-multiple="false"
            :accepted-file-types="[...TEMPLATE_SURAT_DOCUMENT_ACCEPTED_TYPES]"
            :files="files"
            @updatefiles="$emit('update', $event)"
        />
        <InputError :message="error" />
    </div>
</template>
