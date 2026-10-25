<script setup lang="ts">
import { computed } from 'vue';

type BadgeType = 'status' | 'stage';

type BadgePresentation = {
    label: string;
    className: string;
};

const props = defineProps<{
    type: BadgeType;
    value?: string | null;
}>();

const statusPresentations: Record<string, BadgePresentation> = {
    DRAFT: { label: 'Draft', className: 'bg-slate-100 text-slate-700' },
    IN_PROGRESS: { label: 'Dalam Proses', className: 'bg-blue-100 text-blue-700' },
    REJECTED: { label: 'Ditolak', className: 'bg-rose-100 text-rose-700' },
    ARCHIVED: { label: 'Selesai', className: 'bg-emerald-100 text-emerald-700' },
};

const stagePresentations: Record<string, BadgePresentation> = {
    PRODI: { label: 'PRODI', className: 'border-sky-200 bg-sky-50 text-sky-700' },
    AKADEMIK: { label: 'AKADEMIK', className: 'border-violet-200 bg-violet-50 text-violet-700' },
    KTU: { label: 'KTU', className: 'border-amber-200 bg-amber-50 text-amber-700' },
    DEKAN: { label: 'DEKAN', className: 'border-rose-200 bg-rose-50 text-rose-700' },
    WD: { label: 'WD', className: 'border-blue-200 bg-blue-50 text-blue-700' },
    WD1: { label: 'WD1', className: 'border-blue-200 bg-blue-50 text-blue-700' },
    WD2: { label: 'WD2', className: 'border-blue-200 bg-blue-50 text-blue-700' },
    WD3: { label: 'WD3', className: 'border-blue-200 bg-blue-50 text-blue-700' },
    RESEPSIONIS: { label: 'RESEPSIONIS', className: 'border-emerald-200 bg-emerald-50 text-emerald-700' },
    'RUANG BACA': { label: 'RUANG BACA', className: 'border-orange-200 bg-orange-50 text-orange-700' },
};

const presentation = computed<BadgePresentation | null>(() => {
    if (!props.value) {
        return null;
    }

    const presentations = props.type === 'status' ? statusPresentations : stagePresentations;
    const fallbackClassName = props.type === 'status'
        ? 'bg-slate-100 text-slate-700'
        : 'border-slate-200 bg-slate-50 text-slate-700';

    return presentations[props.value] ?? {
        label: props.value,
        className: fallbackClassName,
    };
});
</script>

<template>
    <span v-if="presentation"
        :class="['inline-flex rounded-full px-2.5 py-1 text-xs font-medium', type === 'stage' && 'border', presentation.className]">
        {{ presentation.label }}
    </span>
    <span v-else class="text-sm text-muted-foreground">—</span>
</template>
