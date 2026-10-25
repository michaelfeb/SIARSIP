<script setup lang="ts">
import { Button } from '@/components/ui/button';
import Badge from '@/components/letters/Badge.vue';
import { Check, ChevronRight, CircleAlert, CircleCheck, Clock3, Download, UserRound } from 'lucide-vue-next';
import { computed } from 'vue';

type LetterWorkflowRoute = {
    id: number;
    from_stage: string | null;
    to_stage: string;
    note: string | null;
    created_at: string;
    decider?: { nama: string } | null;
};

const props = withDefaults(
    defineProps<{
        approvalChain: string[];
        status: string;
        currentStage?: string | null;
        routes?: LetterWorkflowRoute[];
        letterNumber?: string | null;
        downloadUrl?: string | null;
    }>(),
    {
        currentStage: null,
        routes: () => [],
        letterNumber: null,
        downloadUrl: null,
    },
);

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

const steps = computed(() =>
    props.approvalChain.map((stage, index) => ({
        id: `${stage}-${index}`,
        stage,
        number: index + 1,
        label: stageLabels[stage] ?? stage,
    })),
);

const currentStepIndex = computed(() => (props.currentStage ? props.approvalChain.indexOf(props.currentStage) : -1));

const rejectedStepIndex = computed(() => {
    const rejection = props.routes.find((item) => item.to_stage === 'REJECTED');

    return rejection?.from_stage ? props.approvalChain.indexOf(rejection.from_stage) : -1;
});

const history = computed(() => [...props.routes].sort((left, right) => new Date(right.created_at).getTime() - new Date(left.created_at).getTime()));

const statusMessage = computed(() => {
    if (props.status === 'DRAFT') {
        return { title: 'Permohonan Masih Draft', description: 'Lengkapi data permohonan, lalu kirimkan untuk memulai proses persetujuan.' };
    }

    if (props.status === 'REJECTED') {
        return { title: 'Permohonan Ditolak', description: 'Permohonan tidak dapat dilanjutkan. Periksa catatan pada riwayat persetujuan.' };
    }

    if (props.status === 'ARCHIVED') {
        return { title: 'Surat Telah Diarsipkan', description: 'Permohonan telah disetujui dan surat resmi tersimpan dalam arsip.' };
    }

    return {
        title: 'Status Terkini',
        description: props.currentStage ? `Tahap: ${stageLabels[props.currentStage] ?? props.currentStage}` : 'Permohonan sedang diproses.',
    };
});

function getStepState(index: number): 'completed' | 'current' | 'upcoming' {
    if (props.status === 'ARCHIVED') {
        return 'completed';
    }

    if (props.status === 'REJECTED') {
        return index <= rejectedStepIndex.value ? 'completed' : 'upcoming';
    }

    if (index < currentStepIndex.value) {
        return 'completed';
    }

    if (index === currentStepIndex.value) {
        return 'current';
    }

    return 'upcoming';
}

function formatDate(value: string): string {
    const date = new Date(value);

    return Number.isNaN(date.getTime())
        ? '—'
        : new Intl.DateTimeFormat('id-ID', {
              day: '2-digit',
              month: 'short',
              year: 'numeric',
              hour: '2-digit',
              minute: '2-digit',
          }).format(date);
}
</script>

<template>
    <section class="border-border bg-card overflow-hidden rounded-lg border shadow-sm">
        <header class="bg-[#c2410c] px-5 py-4 text-white sm:px-6">
            <h2 class="text-lg font-semibold">Status Permohonan &amp; Tahapan Persetujuan</h2>
        </header>

        <div class="space-y-8 p-5 sm:p-6">
            <section>
                <h3 class="text-muted-foreground text-sm font-semibold tracking-wide uppercase">Alur Persetujuan</h3>

                <div class="mt-5 flex gap-3 rounded-lg border border-orange-200 bg-orange-50/60 p-4">
                    <CircleAlert class="mt-0.5 size-5 shrink-0 text-[#c2410c]" aria-hidden="true" />
                    <div class="space-y-1 text-sm">
                        <p class="text-foreground font-semibold">{{ statusMessage.title }}</p>
                        <p class="text-muted-foreground leading-6">{{ statusMessage.description }}</p>
                    </div>
                </div>

                <ol v-if="steps.length" class="mt-7 flex flex-col gap-4 lg:hidden">
                    <li v-for="(step, index) in steps" :key="step.id" class="relative flex min-w-0 gap-3">
                        <span
                            v-if="index < steps.length - 1"
                            class="absolute top-10 bottom-[-1rem] left-5 border-l border-orange-200"
                            aria-hidden="true"
                        />
                        <span
                            :class="[
                                'z-10 flex size-10 shrink-0 items-center justify-center rounded-full border-2 text-sm font-semibold',
                                getStepState(index) === 'completed' && 'border-[#c2410c] bg-orange-50 text-[#c2410c]',
                                getStepState(index) === 'current' && 'border-[#c2410c] bg-[#c2410c] text-white ring-4 ring-orange-100',
                                getStepState(index) === 'upcoming' && 'border-muted-foreground/25 bg-background text-muted-foreground',
                            ]"
                        >
                            <Check v-if="getStepState(index) === 'completed'" class="size-5" aria-hidden="true" />
                            <template v-else>{{ step.number }}</template>
                        </span>
                        <div class="min-w-0 pt-2">
                            <p class="font-medium">{{ step.label }}</p>
                            <span
                                v-if="getStepState(index) === 'current'"
                                class="mt-1 inline-flex rounded-full bg-[#c2410c] px-2 py-0.5 text-xs font-medium text-white"
                                >Sekarang</span
                            >
                        </div>
                    </li>
                </ol>

                <ol v-if="steps.length" class="relative mt-8 hidden items-start justify-between gap-2 lg:flex">
                    <span class="absolute top-5 right-6 left-6 h-px bg-orange-200" aria-hidden="true" />
                    <li v-for="(step, index) in steps" :key="step.id" class="relative z-10 flex min-w-0 flex-1 flex-col items-center text-center">
                        <span
                            :class="[
                                'flex size-11 shrink-0 items-center justify-center rounded-full border-2 text-sm font-semibold',
                                getStepState(index) === 'completed' && 'border-[#c2410c] bg-orange-50 text-[#c2410c]',
                                getStepState(index) === 'current' && 'border-[#c2410c] bg-[#c2410c] text-white ring-4 ring-orange-100',
                                getStepState(index) === 'upcoming' && 'border-muted-foreground/25 bg-background text-muted-foreground',
                            ]"
                        >
                            <Check v-if="getStepState(index) === 'completed'" class="size-5" aria-hidden="true" />
                            <template v-else>{{ step.number }}</template>
                        </span>
                        <p class="mt-3 max-w-40 text-sm leading-5 font-medium break-words">{{ step.label }}</p>
                        <span
                            v-if="getStepState(index) === 'current'"
                            class="mt-1 inline-flex rounded-full bg-[#c2410c] px-2 py-0.5 text-xs font-medium text-white"
                            >Sekarang</span
                        >
                    </li>
                </ol>
                <p v-else class="text-muted-foreground mt-4 text-sm">Alur persetujuan belum dikonfigurasi.</p>
            </section>

            <section class="border-border border-t pt-7">
                <h3 class="text-muted-foreground text-sm font-semibold tracking-wide uppercase">Riwayat Persetujuan</h3>

                <ol v-if="history.length" class="mt-5 space-y-5">
                    <li v-for="(item, index) in history" :key="item.id" class="relative flex gap-3 sm:gap-4">
                        <span
                            v-if="index < history.length - 1"
                            class="border-border absolute top-12 bottom-[-1.25rem] left-5 border-l"
                            aria-hidden="true"
                        />
                        <span class="bg-muted text-muted-foreground z-10 flex size-10 shrink-0 items-center justify-center rounded-full">
                            <UserRound class="size-5" aria-hidden="true" />
                        </span>
                        <div class="min-w-0 pt-0.5 pb-1">
                            <div class="flex flex-wrap items-center gap-2">
                                <p class="font-semibold">{{ item.decider?.nama ?? 'Sistem' }}</p>
                                <Badge v-if="item.from_stage" type="stage" :value="item.from_stage" />
                                <span v-else class="bg-muted text-muted-foreground rounded-full px-2 py-0.5 text-xs font-medium">MAHASISWA</span>
                            </div>
                            <p class="text-muted-foreground mt-1 flex items-center gap-1.5 text-sm">
                                <Clock3 class="size-4" aria-hidden="true" />
                                {{ formatDate(item.created_at) }}
                            </p>
                            <p v-if="item.note" class="text-muted-foreground mt-2 text-sm leading-6">{{ item.note }}</p>
                            <p class="text-foreground mt-2 flex items-center gap-1.5 text-sm font-medium">
                                <span>{{ item.from_stage ? (stageLabels[item.from_stage] ?? item.from_stage) : 'Mahasiswa' }}</span>
                                <ChevronRight class="text-muted-foreground size-4" aria-hidden="true" />
                                <span>{{ item.to_stage === 'ARCHIVED' ? 'Diarsipkan' : (stageLabels[item.to_stage] ?? item.to_stage) }}</span>
                            </p>
                        </div>
                    </li>
                </ol>
                <p v-else class="text-muted-foreground mt-4 text-sm">Belum ada riwayat persetujuan.</p>
            </section>

            <section v-if="status === 'ARCHIVED'" class="border-border border-t pt-7">
                <h3 class="text-muted-foreground text-sm font-semibold tracking-wide uppercase">Surat Terarsipkan</h3>
                <div class="border-border bg-muted/30 mt-4 flex flex-col gap-4 rounded-lg border p-4 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <p class="text-muted-foreground text-xs font-semibold tracking-wide uppercase">Nomor Surat</p>
                        <p class="mt-1 font-semibold">{{ letterNumber ?? '—' }}</p>
                    </div>
                    <Button v-if="downloadUrl" as="a" :href="downloadUrl" target="_blank" rel="noopener" class="w-full sm:w-auto">
                        <Download class="size-4" aria-hidden="true" />
                        Unduh
                    </Button>
                </div>
                <div class="mt-4 flex gap-3 rounded-lg border border-emerald-200 bg-emerald-50 p-4 text-emerald-800">
                    <CircleCheck class="mt-0.5 size-5 shrink-0" aria-hidden="true" />
                    <div class="text-sm">
                        <p class="font-semibold">Surat Telah Diarsipkan</p>
                        <p class="mt-1 leading-6">Permohonan telah disetujui dan surat resmi tersimpan dalam arsip.</p>
                    </div>
                </div>
            </section>
        </div>
    </section>
</template>
