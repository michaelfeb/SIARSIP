<script setup lang="ts">
import AppLogoIcon from '@/components/AppLogoIcon.vue';
import { Link } from '@inertiajs/vue3';

defineProps<{
    title?: string;
    description?: string;
}>();
</script>

<template>
    <div class="relative flex min-h-svh flex-col items-center justify-center overflow-hidden bg-[#fff7ed] p-6 md:p-10">
        <div class="pointer-events-none absolute -left-20 top-0 size-72 rounded-full bg-orange-200/50 blur-3xl" />
        <div class="pointer-events-none absolute -bottom-24 -right-16 size-80 rounded-full bg-orange-100 blur-3xl" />

        <div class="relative w-full max-w-md rounded-2xl border border-orange-100 bg-background p-6 shadow-xl shadow-orange-950/10 sm:p-8">
            <div class="flex flex-col gap-7">
                <div class="flex flex-col items-center gap-4">
                    <Link :href="route('home')" class="flex flex-col items-center gap-2 font-medium">
                        <div class="flex size-18 items-center justify-center rounded-2xl bg-orange-50">
                            <AppLogoIcon class="size-14" />
                        </div>
                        <span class="text-sm font-semibold text-[#9a3412]">PORLAS FMIPA</span>
                    </Link>
                    <div class="space-y-2 text-center">
                        <h1 class="text-2xl font-semibold tracking-tight">{{ title }}</h1>
                        <p class="text-center text-sm text-muted-foreground">{{ description }}</p>
                    </div>
                </div>
                <slot />
            </div>
        </div>
    </div>
</template>
