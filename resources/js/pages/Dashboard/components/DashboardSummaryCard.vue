<script setup lang="ts">
import { formatThousandSeparatedNumber } from '@/utils/number';
import type { LucideIcon } from 'lucide-vue-next';
import { computed } from 'vue';

type DashboardSummaryTone = 'default' | 'progress' | 'archived' | 'rejected';

const props = defineProps<{
    title: string;
    value: number;
    description: string;
    icon: LucideIcon;
    tone: DashboardSummaryTone;
}>();

const toneClasses = computed(
    () =>
        ({
            default: 'border-orange-200 bg-orange-50/60 text-orange-950',
            progress: 'border-orange-200 bg-orange-50/40 text-orange-950',
            archived: 'border-emerald-200 bg-emerald-50 text-emerald-950',
            rejected: 'border-rose-200 bg-rose-50 text-rose-950',
        })[props.tone],
);

const iconClasses = computed(
    () =>
        ({
            default: 'border-orange-200 bg-white text-[#c2410c]',
            progress: 'border-orange-200 bg-white text-[#c2410c]',
            archived: 'border-emerald-200 bg-white text-emerald-600',
            rejected: 'border-rose-200 bg-white text-rose-600',
        })[props.tone],
);
</script>

<template>
    <article :class="['relative min-h-40 overflow-hidden rounded-lg border p-5 shadow-sm', toneClasses]">
        <div class="pointer-events-none absolute -top-7 -right-7 size-24 rounded-full border-[10px] border-current opacity-5" />
        <div class="relative flex h-full flex-col">
            <div class="flex items-start justify-between gap-4">
                <div>
                    <p class="text-muted-foreground text-[10px] font-semibold tracking-wide uppercase">{{ title }}</p>
                    <p class="mt-2 text-2xl font-bold tracking-tight">{{ formatThousandSeparatedNumber(value) }}</p>
                </div>
                <span :class="['flex size-9 items-center justify-center rounded-lg border', iconClasses]">
                    <component :is="icon" class="size-4" />
                </span>
            </div>
            <p class="text-muted-foreground mt-auto pt-6 text-xs">{{ description }}</p>
        </div>
    </article>
</template>
