<script setup lang="ts">
import { CUTI_AKADEMIK_STATUS } from '../ts/cutiAkademik.constants';
import type { CutiAkademikStatus } from '../ts/cutiAkademik.types';

defineProps<{ status: CutiAkademikStatus; stage?: string | null }>();
</script>

<template>
    <div class="space-y-1">
        <span :class="['inline-flex rounded-full px-2.5 py-1 text-xs font-medium', CUTI_AKADEMIK_STATUS[status].className]">
            {{ CUTI_AKADEMIK_STATUS[status].label }}
        </span>
        <p v-if="stage" class="text-xs text-muted-foreground">Menunggu {{ stage }}</p>
    </div>
</template>
