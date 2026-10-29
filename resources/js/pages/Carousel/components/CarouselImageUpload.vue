<script setup lang="ts">
import InputError from '@/components/InputError.vue';
import { Label } from '@/components/ui/label';
import vueFilePond from 'vue-filepond';
import FilePondPluginFileValidateType from 'filepond-plugin-file-validate-type';
import FilePondPluginImagePreview from 'filepond-plugin-image-preview';
import { CAROUSEL_IMAGE_ACCEPTED_TYPES, CAROUSEL_IMAGE_MAX_SIZE_LABEL } from '../ts/carousel.constants';

import 'filepond/dist/filepond.min.css';
import 'filepond-plugin-image-preview/dist/filepond-plugin-image-preview.min.css';

const FilePond = vueFilePond(FilePondPluginImagePreview, FilePondPluginFileValidateType);

defineProps<{ files: unknown[]; error?: string; required: boolean }>();
defineEmits<{ update: [files: unknown[]] }>();
</script>

<template>
    <div class="space-y-2">
        <Label for="gambar" class="gap-1"
            >Upload Gambar <span v-if="required" class="text-destructive">*</span
            ><span class="text-muted-foreground text-xs">(PNG/JPG, maks. {{ CAROUSEL_IMAGE_MAX_SIZE_LABEL }})</span></Label
        >
        <FilePond
            name="gambar"
            label-idle="Seret & lepas gambar atau <span class='filepond--label-action'>Telusuri</span>"
            :allow-multiple="false"
            :accepted-file-types="[...CAROUSEL_IMAGE_ACCEPTED_TYPES]"
            :files="files"
            @updatefiles="$emit('update', $event)"
        />
        <InputError :message="error" />
    </div>
</template>
