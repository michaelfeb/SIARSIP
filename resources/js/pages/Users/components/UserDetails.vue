<script setup lang="ts">
import type { UserRecord } from '../ts/users.types';
defineProps<{ user: UserRecord }>();
</script>
<template>
    <dl class="divide-border border-border bg-card divide-y rounded-lg border px-5 shadow-sm">
        <div
            v-for="[label, value] in [
                ['Nama', user.nama],
                ['NIP/NIM', user.nim || '—'],
                ['Email', user.email],
                ['Role', user.role?.nama || '—'],
                ['Program Studi', user.program_studi || '—'],
            ]"
            :key="label"
            class="flex justify-between gap-6 py-4 text-sm"
        >
            <dt class="text-muted-foreground font-medium">{{ label }}</dt>
            <dd>{{ value }}</dd>
        </div>
    </dl>
</template>
