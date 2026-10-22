<template>
    <div v-if="show" class="fixed inset-0 z-50 flex items-center justify-center bg-black/30 p-4 backdrop-blur-sm">
        <div :class="`relative max-h-[calc(100dvh-2rem)] w-full max-w-5xl overflow-hidden rounded-lg bg-white shadow-lg`">
            <!-- Tombol Close -->
            <button @click="$emit('close')" class="absolute top-4 right-4 text-gray-400 hover:text-gray-600 text-xl">
                &times;
            </button>

            <!-- Header -->
            <div v-if="$slots.header" class="border-b border-gray-200 px-4 pt-5 pb-3 sm:px-6 sm:pt-6">
                <slot name="header" />
            </div>

            <!-- Body dengan min-height -->
            <!-- Body -->
            <div class="max-h-[calc(100dvh-11rem)] overflow-y-auto px-4 py-4 sm:px-6">
                <slot />
            </div>


            <!-- Footer -->
            <div v-if="$slots.footer" class="flex flex-col-reverse gap-2 border-t border-gray-200 px-4 pt-3 pb-4 sm:flex-row sm:justify-end sm:px-6 sm:pb-6 [&>button]:w-full sm:[&>button]:w-auto">
                <slot name="footer" />
            </div>
        </div>
    </div>
</template>

<script setup>
defineProps({
    show: Boolean,
    size: {
        type: String,
        default: '2xl'
    }
})
defineEmits(['close']);
</script>
