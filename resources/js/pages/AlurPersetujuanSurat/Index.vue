<script setup lang="ts">
import InputError from '@/components/InputError.vue';
import Button from '@/components/ui/button/Button.vue';
import AppLayout from '@/layouts/AppLayout.vue';
import type { BreadcrumbItem } from '@/types';
import { Head, useForm } from '@inertiajs/vue3';
import { AlertTriangle, CheckCircle2, FileCog, LockKeyhole, Settings2 } from 'lucide-vue-next';
import { computed, ref, watch } from 'vue';
import ApprovalWorkflowNotesEditor from './components/ApprovalWorkflowNotesEditor.vue';
import ApprovalWorkflowStages from './components/ApprovalWorkflowStages.vue';
import type { ApprovalWorkflowLetterType } from './ts/approvalWorkflow.types';

const props = defineProps<{ letterTypes: ApprovalWorkflowLetterType[]; roles: string[] }>();
const breadcrumbs: BreadcrumbItem[] = [{ title: 'Alur Persetujuan Surat', href: '/alur-persetujuan-surat' }];
const selectedLetterTypeId = ref<number | null>(props.letterTypes[0]?.id ?? null);
const selectedLetterType = computed(() => props.letterTypes.find((letterType) => letterType.id === selectedLetterTypeId.value) ?? null);
const form = useForm<{ approval_chain: string[]; notes_markdown: string | null }>({ approval_chain: [], notes_markdown: null });
const isLocked = computed(() => (selectedLetterType.value?.activeLettersCount ?? 0) > 0);
const configuredLetterTypes = computed(() => props.letterTypes.filter((letterType) => letterType.approvalChain.length > 0).length);

function resetForm(letterType: ApprovalWorkflowLetterType | null): void {
    form.clearErrors();
    form.approval_chain = [...(letterType?.approvalChain ?? [])];
    form.notes_markdown = letterType?.notesMarkdown ?? null;
}

watch(selectedLetterType, resetForm, { immediate: true });

function onSelectLetterType(id: number): void {
    selectedLetterTypeId.value = id;
}

function onSave(): void {
    if (!selectedLetterType.value || isLocked.value) {
        return;
    }

    form.put(route('approval-workflows.update', selectedLetterType.value.id), { preserveScroll: true });
}
</script>

<template>
    <Head title="Alur Persetujuan Surat" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <main class="mx-auto w-full max-w-[1800px] space-y-6 p-4 sm:p-6 lg:p-8">
            <header
                class="relative overflow-hidden rounded-2xl border border-orange-950/10 bg-[#9a3412] px-6 py-7 text-white shadow-[0_18px_45px_-28px_rgba(154,52,18,0.85)] sm:px-8 sm:py-8"
            >
                <div class="absolute -top-18 -right-14 size-56 rounded-full border-[28px] border-white/[0.07]" aria-hidden="true" />
                <div class="absolute right-20 bottom-[-7rem] size-52 rounded-full bg-orange-200/20 blur-3xl" aria-hidden="true" />
                <div class="relative flex flex-col justify-between gap-6 lg:flex-row lg:items-end">
                    <div class="max-w-2xl">
                        <div class="mb-3 flex items-center gap-2 text-xs font-semibold tracking-[0.16em] text-orange-100 uppercase">
                            <Settings2 class="size-4" aria-hidden="true" /> Pengaturan layanan surat
                        </div>
                        <h1 class="text-2xl font-semibold tracking-tight sm:text-3xl">Alur Persetujuan Surat</h1>
                        <p class="mt-2 max-w-xl text-sm leading-6 text-orange-50/85">
                            Atur siapa yang meninjau surat, urutannya, dan panduan proses yang akan dibaca pemohon.
                        </p>
                    </div>
                    <div class="grid grid-cols-2 gap-3 sm:min-w-[21.5rem]">
                        <div class="rounded-xl border border-white/12 bg-white/10 px-4 py-3 backdrop-blur-sm">
                            <p class="text-xs text-orange-50/75">Jenis surat</p>
                            <p class="mt-1 text-xl font-semibold">{{ letterTypes.length }}</p>
                        </div>
                        <div class="rounded-xl border border-white/12 bg-white/10 px-4 py-3 backdrop-blur-sm">
                            <p class="text-xs text-orange-50/75">Sudah diatur</p>
                            <p class="mt-1 text-xl font-semibold">{{ configuredLetterTypes }}</p>
                        </div>
                    </div>
                </div>
            </header>

            <div
                v-if="letterTypes.length === 0"
                class="border-border bg-card text-muted-foreground rounded-2xl border border-dashed p-10 text-center text-sm"
            >
                <FileCog class="mx-auto mb-3 size-8 text-orange-700" aria-hidden="true" />
                Belum ada jenis surat yang dapat dikonfigurasi.
            </div>

            <div v-else class="grid gap-6 xl:grid-cols-[minmax(17rem,0.52fr)_minmax(0,1.48fr)] xl:items-start">
                <aside class="border-border bg-card overflow-hidden rounded-2xl border shadow-sm xl:sticky xl:top-6">
                    <div class="border-border flex items-center justify-between border-b px-5 py-4">
                        <div>
                            <h2 class="text-sm font-semibold">Jenis Surat</h2>
                            <p class="text-muted-foreground mt-0.5 text-xs">Pilih untuk mulai mengatur</p>
                        </div>
                        <span class="bg-muted text-muted-foreground rounded-full px-2.5 py-1 text-xs font-medium">{{ letterTypes.length }}</span>
                    </div>
                    <nav class="max-h-[calc(100vh-15rem)] space-y-1 overflow-y-auto p-2" aria-label="Daftar jenis surat">
                        <Button
                            v-for="letterType in letterTypes"
                            :key="letterType.id"
                            type="button"
                            variant="ghost"
                            class="h-auto w-full justify-start rounded-xl px-3.5 py-3 text-left"
                            :class="
                                letterType.id === selectedLetterTypeId
                                    ? 'bg-orange-50 text-orange-950 hover:bg-orange-100 hover:text-orange-950'
                                    : 'hover:bg-muted/70'
                            "
                            :aria-current="letterType.id === selectedLetterTypeId ? 'page' : undefined"
                            @click="onSelectLetterType(letterType.id)"
                        >
                            <span class="flex min-w-0 flex-1 flex-col gap-1">
                                <span class="truncate font-medium">{{ letterType.name }}</span>
                                <span class="text-muted-foreground text-xs">{{ letterType.approvalChain.length }} tahap persetujuan</span>
                            </span>
                            <LockKeyhole
                                v-if="letterType.activeLettersCount > 0"
                                class="size-4 shrink-0 text-amber-600"
                                :aria-label="`${letterType.activeLettersCount} surat sedang diproses`"
                            />
                            <CheckCircle2 v-else class="size-4 shrink-0 text-orange-600" aria-label="Dapat diubah" />
                        </Button>
                    </nav>
                </aside>

                <form v-if="selectedLetterType" class="space-y-6" @submit.prevent="onSave">
                    <div
                        class="border-border bg-card flex flex-col gap-3 rounded-2xl border px-5 py-4 shadow-sm sm:flex-row sm:items-center sm:justify-between"
                    >
                        <div class="min-w-0">
                            <p class="text-muted-foreground text-xs font-semibold tracking-[0.12em] uppercase">Sedang mengatur</p>
                            <h2 class="mt-1 truncate text-lg font-semibold">{{ selectedLetterType.name }}</h2>
                        </div>
                        <div
                            class="inline-flex w-fit items-center gap-2 rounded-full px-3 py-1.5 text-xs font-medium"
                            :class="isLocked ? 'bg-amber-50 text-amber-800' : 'bg-orange-50 text-orange-800'"
                        >
                            <LockKeyhole v-if="isLocked" class="size-3.5" aria-hidden="true" />
                            <CheckCircle2 v-else class="size-3.5" aria-hidden="true" />
                            {{ isLocked ? 'Perubahan dikunci' : 'Siap diperbarui' }}
                        </div>
                    </div>

                    <div v-if="isLocked" class="flex gap-3 rounded-2xl border border-amber-200 bg-amber-50/80 p-4 text-sm leading-6 text-amber-950">
                        <AlertTriangle class="mt-0.5 size-5 shrink-0" aria-hidden="true" />
                        <p>
                            Ada {{ selectedLetterType.activeLettersCount }} surat sedang dalam proses persetujuan. Perubahan belum bisa dilakukan saat
                            ada proses berlangsung.
                        </p>
                    </div>

                    <ApprovalWorkflowStages v-model="form.approval_chain" :roles="roles" :disabled="isLocked || form.processing" />
                    <InputError :message="form.errors.approval_chain" />
                    <ApprovalWorkflowNotesEditor v-model="form.notes_markdown" :disabled="isLocked || form.processing" />
                    <InputError :message="form.errors.notes_markdown" />

                    <footer
                        class="border-border bg-card sticky bottom-4 z-10 flex flex-col gap-3 rounded-2xl border p-3 shadow-[0_12px_30px_-18px_rgba(15,23,42,0.35)] sm:flex-row sm:items-center sm:justify-between"
                    >
                        <p class="text-muted-foreground px-2 text-xs">Perubahan berlaku untuk pengajuan baru setelah disimpan.</p>
                        <Button type="submit" class="bg-[#c2410c] px-6 hover:bg-[#9a3412]" :disabled="isLocked || form.processing">
                            {{ form.processing ? 'Menyimpan...' : 'Simpan Perubahan' }}
                        </Button>
                    </footer>
                </form>
            </div>
        </main>
    </AppLayout>
</template>
