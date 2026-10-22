<script setup lang="ts">
import { cn } from '@/lib/utils';
import { useBaseUrl } from '@/utils/useBaseUrl';
import type { HTMLAttributes } from 'vue';

defineOptions({
    inheritAttrs: false,
});

const logoUrl = useBaseUrl('images/logo-ulm.png')

interface Props {
    class?: HTMLAttributes['class'];
}

const props = defineProps<Props>();
</script>

<template>
    <img :src="logoUrl" alt="Logo Universitas Lambung Mangkurat" :class="cn('object-contain', props.class)" />
</template>
