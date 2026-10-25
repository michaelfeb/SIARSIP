<script setup lang="ts">
import { CircleHelp } from 'lucide-vue-next';
import { computed } from 'vue';

const props = withDefaults(defineProps<{
    approvalChain: string[];
    title?: string;
    informationTitle?: string;
    informationDescription?: string;
    estimateLabel?: string;
    estimate?: string;
}>(), {
    title: 'Alur Persetujuan Surat',
    informationTitle: 'Informasi Proses Persetujuan',
    informationDescription: 'Permohonan Anda akan diproses melalui tahapan persetujuan secara bertingkat. Setiap tahapan akan ditinjau oleh pihak berwenang.',
    estimateLabel: 'Estimasi waktu',
    estimate: '3–5 hari kerja',
});

const stageLabels: Record<string, string> = {
    PRODI: 'Program Studi',
    AKADEMIK: 'Bagian Akademik',
    KTU: 'Kepala Tata Usaha',
    DEKAN: 'Dekan',
    WD: 'Wakil Dekan',
    WD1: 'Wakil Dekan 1',
    WD2: 'Wakil Dekan 2',
    WD3: 'Wakil Dekan 3',
    RESEPSIONIS: 'Resepsionis',
    'RUANG BACA': 'Ruang Baca',
};

const steps = computed(() => props.approvalChain.map((stage, index) => ({
    id: `${stage}-${index}`,
    number: index + 1,
    label: stageLabels[stage] ?? stage,
})));

function stepPosition(index: number): string {
    if (steps.value.length <= 1) {
        return '50%';
    }

    return `${(index / (steps.value.length - 1)) * 100}%`;
}
</script>

<template>
    <section class="rounded-lg border border-border bg-card p-5 shadow-sm sm:p-6">
        <h2 class="text-lg font-semibold">{{ title }}</h2>

        <ol v-if="steps.length" class="mt-6 flex flex-col gap-3 xl:hidden">
            <li v-for="(step, index) in steps" :key="step.id" class="flex min-w-0 items-center gap-3">
                <span
                    class="flex size-10 shrink-0 items-center justify-center rounded-full bg-[#c2410c] text-sm font-semibold text-white shadow-sm">
                    {{ step.number }}
                </span>
                <span class="min-w-0 text-sm font-medium leading-5">{{ step.label }}</span>
                <span v-if="index < steps.length - 1" class="ml-5 h-4 border-l border-[#c2410c]/35" />
            </li>
        </ol>
        <ol v-if="steps.length" class="relative mx-6 mt-6 hidden h-24 xl:block">
            <span class="absolute top-5 right-0 left-0 h-px bg-orange-200" aria-hidden="true" />
            <li v-for="(step, index) in steps" :key="step.id" class="absolute top-0"
                :style="{ left: stepPosition(index) }">
                <span
                    class="flex size-10 -translate-x-1/2 items-center justify-center rounded-full bg-[#c2410c] text-sm font-semibold text-white shadow-sm">
                    {{ step.number }}
                </span>
                <span class="absolute top-13 w-44 break-words text-sm font-medium leading-5" :class="[
                    index === 0 && 'left-0 text-left',
                    index === steps.length - 1 && 'left-0 -translate-x-full text-right',
                    index > 0 && index < steps.length - 1 && 'left-1/2 -translate-x-1/2 text-center',
                ]">
                    {{ step.label }}
                </span>
            </li>
        </ol>
        <p v-else class="mt-4 text-sm text-muted-foreground">Alur persetujuan belum dikonfigurasi.</p>

        <div class="mt-6 flex gap-3 rounded-lg border border-orange-200 bg-orange-50/60 p-4">
            <CircleHelp class="mt-0.5 size-5 shrink-0 text-[#c2410c]" aria-hidden="true" />
            <div class="space-y-1.5 text-sm">
                <h3 class="font-semibold">{{ informationTitle }}</h3>
                <p class="leading-6 text-muted-foreground">{{ informationDescription }}</p>
                <p v-if="estimate" class="pt-1 text-muted-foreground">
                    <span class="mr-2 text-xs font-semibold uppercase tracking-wide">{{ estimateLabel }}</span>
                    {{ estimate }}
                </p>
            </div>
        </div>
    </section>
</template>
