<script setup lang="ts">
import Button from '@/components/ui/button/Button.vue';
import { CirclePlus, GripVertical, LockKeyhole, Trash2 } from 'lucide-vue-next';
import { computed, ref } from 'vue';

const props = defineProps<{
    modelValue: string[];
    roles: string[];
    disabled?: boolean;
}>();

const emit = defineEmits<{ 'update:modelValue': [value: string[]] }>();
const draggedIndex = ref<number | null>(null);
const availableRoles = computed(() => props.roles.filter((role) => !props.modelValue.includes(role)));

function isLockedTerminal(role: string, index: number): boolean {
    return role === 'RESEPSIONIS' && index === props.modelValue.length - 1;
}

function onDragStart(index: number): void {
    draggedIndex.value = index;
}

function onDrop(targetIndex: number): void {
    const sourceIndex = draggedIndex.value;
    draggedIndex.value = null;

    if (props.disabled || sourceIndex === null || sourceIndex === targetIndex || isLockedTerminal(props.modelValue[sourceIndex], sourceIndex)) {
        return;
    }

    const chain = [...props.modelValue];
    const [stage] = chain.splice(sourceIndex, 1);
    let insertionIndex = targetIndex;
    if (sourceIndex < targetIndex) {
        insertionIndex -= 1;
    }

    const terminalIndex = chain.length - 1;
    if (chain[terminalIndex] === 'RESEPSIONIS') {
        insertionIndex = Math.min(insertionIndex, terminalIndex);
    }

    chain.splice(insertionIndex, 0, stage);
    emit('update:modelValue', chain);
}

function onAdd(role: string): void {
    if (props.disabled) {
        return;
    }

    const chain = [...props.modelValue];
    const terminalIndex = chain.length - 1;
    const insertionIndex = chain[terminalIndex] === 'RESEPSIONIS' ? terminalIndex : chain.length;
    chain.splice(insertionIndex, 0, role);
    emit('update:modelValue', chain);
}

function onDelete(index: number): void {
    if (props.disabled || isLockedTerminal(props.modelValue[index], index)) {
        return;
    }

    emit(
        'update:modelValue',
        props.modelValue.filter((_, stageIndex) => stageIndex !== index),
    );
}
</script>

<template>
    <section class="border-border bg-card overflow-hidden rounded-2xl border shadow-sm">
        <header class="relative overflow-hidden bg-linear-to-r from-[#9a3412] to-[#c2410c] px-5 py-5 text-white sm:px-6">
            <div class="absolute -top-12 -right-8 size-32 rounded-full border-[18px] border-white/10" aria-hidden="true" />
            <div class="relative flex items-center justify-between gap-4">
                <div>
                    <p class="text-xs font-semibold tracking-[0.14em] text-orange-100 uppercase">Alur proses</p>
                    <h2 class="mt-1 text-xl font-semibold">Urutan Persetujuan</h2>
                </div>
                <span class="rounded-full border border-white/15 bg-white/10 px-3 py-1.5 text-xs font-medium backdrop-blur-sm">
                    {{ modelValue.length }} tahap
                </span>
            </div>
        </header>

        <div class="space-y-7 p-5 sm:p-6">
            <div>
                <div class="mb-5 flex items-end justify-between gap-4">
                    <div>
                        <h3 class="text-sm font-semibold">Tahapan saat ini</h3>
                        <p class="text-muted-foreground mt-1 text-xs">Tarik tahap untuk mengubah urutan persetujuan.</p>
                    </div>
                </div>
                <ol class="relative space-y-2.5 before:absolute before:top-6 before:bottom-6 before:left-6 before:w-px before:bg-orange-100">
                    <li
                        v-for="(stage, index) in modelValue"
                        :key="stage"
                        :draggable="!disabled && !isLockedTerminal(stage, index)"
                        class="relative z-10 flex min-h-[4.25rem] items-center gap-3 rounded-xl border p-3 transition duration-200"
                        :class="
                            isLockedTerminal(stage, index)
                                ? 'border-slate-200 bg-slate-50'
                                : 'border-orange-100 bg-white hover:-translate-y-px hover:border-orange-300 hover:shadow-sm'
                        "
                        @dragstart="onDragStart(index)"
                        @dragover.prevent
                        @drop="onDrop(index)"
                    >
                        <span
                            class="relative z-10 flex size-7 shrink-0 items-center justify-center rounded-full text-xs font-bold"
                            :class="isLockedTerminal(stage, index) ? 'bg-slate-200 text-slate-600' : 'bg-[#c2410c] text-white'"
                        >
                            {{ index + 1 }}
                        </span>
                        <GripVertical v-if="!isLockedTerminal(stage, index)" class="text-muted-foreground size-4 shrink-0" aria-hidden="true" />
                        <LockKeyhole v-else class="text-muted-foreground size-4 shrink-0" aria-hidden="true" />
                        <span class="min-w-0 flex-1">
                            <span class="block font-medium">{{ stage }}</span>
                            <span v-if="isLockedTerminal(stage, index)" class="mt-0.5 block text-xs text-slate-500"
                                >Tahap akhir dan pembuat nomor surat</span
                            >
                        </span>
                        <span v-if="isLockedTerminal(stage, index)" class="rounded-full bg-slate-200 px-2.5 py-1 text-xs font-medium text-slate-600"
                            >Tetap</span
                        >
                        <Button
                            v-if="!isLockedTerminal(stage, index)"
                            type="button"
                            variant="outline"
                            size="icon"
                            class="size-8 border-rose-100 text-rose-600 hover:bg-rose-50 hover:text-rose-700"
                            :disabled="disabled"
                            :aria-label="`Hapus tahap ${stage}`"
                            :title="`Hapus tahap ${stage}`"
                            @click="onDelete(index)"
                        >
                            <Trash2 class="size-4" />
                        </Button>
                    </li>
                </ol>
            </div>

            <div class="rounded-xl border border-dashed border-orange-200 bg-orange-50/45 p-4 sm:p-5">
                <div class="mb-3 flex items-center gap-2">
                    <CirclePlus class="size-4 text-orange-700" aria-hidden="true" />
                    <p class="text-sm font-semibold text-orange-950">Tambahkan tahap</p>
                </div>
                <p class="text-muted-foreground mb-4 text-xs leading-5">
                    Pilih role yang belum ada di alur. Tahap baru akan masuk sebelum Resepsionis bila ia adalah tahap akhir.
                </p>
                <div class="flex flex-wrap gap-2">
                    <Button
                        v-for="role in availableRoles"
                        :key="role"
                        type="button"
                        variant="outline"
                        class="h-8 rounded-full border-orange-200 bg-white px-3 text-xs text-orange-800 hover:bg-orange-100 hover:text-orange-950"
                        :disabled="disabled"
                        @click="onAdd(role)"
                    >
                        + {{ role }}
                    </Button>
                    <p v-if="availableRoles.length === 0" class="text-muted-foreground text-sm">Semua role telah digunakan.</p>
                </div>
            </div>

            <div class="flex gap-3 rounded-xl border border-slate-200 bg-slate-50 p-4 text-sm text-slate-700">
                <span class="flex size-8 shrink-0 items-center justify-center rounded-full bg-white text-orange-700 shadow-sm">
                    <LockKeyhole class="size-4 text-orange-700" aria-hidden="true" />
                </span>
                <div>
                    <p class="font-medium text-slate-900">Tahap akhir terlindungi</p>
                    <p class="mt-0.5 text-xs leading-5 text-slate-600">RESEPSIONIS yang berada di akhir tidak dapat dipindahkan atau dihapus.</p>
                </div>
            </div>
        </div>
    </section>
</template>
