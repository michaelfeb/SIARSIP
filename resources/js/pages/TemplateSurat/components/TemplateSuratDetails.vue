<script setup lang="ts">
import { TEMPLATE_SURAT_STATUS } from '../ts/templateSurat.constants';
import { formatDate } from '@/utils/date';
import type { TemplateSurat } from '../ts/templateSurat.types';

defineProps<{ templateSurat: TemplateSurat }>();
</script>

<template>
    <section class="border-border bg-card rounded-lg border shadow-sm">
        <dl class="divide-border divide-y px-5">
            <div class="flex flex-col gap-1 py-4 sm:flex-row sm:justify-between sm:gap-6">
                <dt class="text-muted-foreground text-sm font-medium">Nama Template</dt>
                <dd class="text-sm font-medium">{{ templateSurat.nama }}</dd>
            </div>
            <div class="flex flex-col gap-1 py-4 sm:flex-row sm:justify-between sm:gap-6">
                <dt class="text-muted-foreground text-sm font-medium">Deskripsi</dt>
                <dd class="text-sm">{{ templateSurat.deskripsi || '—' }}</dd>
            </div>
            <div class="flex flex-col gap-1 py-4 sm:flex-row sm:justify-between sm:gap-6">
                <dt class="text-muted-foreground text-sm font-medium">Status</dt>
                <dd>
                    <span
                        :class="[
                            'inline-flex rounded-full px-2.5 py-1 text-xs font-medium',
                            templateSurat.status ? TEMPLATE_SURAT_STATUS.active.badgeClass : TEMPLATE_SURAT_STATUS.inactive.badgeClass,
                        ]"
                        >{{ templateSurat.status ? TEMPLATE_SURAT_STATUS.active.label : TEMPLATE_SURAT_STATUS.inactive.label }}</span
                    >
                </dd>
            </div>
            <div class="flex flex-col gap-1 py-4 sm:flex-row sm:justify-between sm:gap-6">
                <dt class="text-muted-foreground text-sm font-medium">Tanggal Publikasi</dt>
                <dd class="text-sm">{{ formatDate(templateSurat.tanggal_publish, { day: '2-digit', month: 'long', year: 'numeric' }) }}</dd>
            </div>
        </dl>
    </section>
</template>
