<script setup lang="ts">
import { useBaseUrl } from '@/utils/useBaseUrl';
import type { HTMLAttributes } from 'vue';

defineOptions({
    inheritAttrs: false,
});

const logoUrl = useBaseUrl('images/logo-ulm.png')

interface Props {
    className?: HTMLAttributes['class'];
}

defineProps<Props>();
</script>

<template>
    <img :src="logoUrl" alt="Logo" class="" />
</template>
