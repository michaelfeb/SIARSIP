<template>
    <div v-if="show" class="fixed inset-0 z-50 flex items-center justify-center bg-black/30 backdrop-blur-sm">
        <div :class="`bg-white w-full max-w-5xl rounded-lg shadow-lg overflow-hidden relative`">
            <!-- Tombol Close -->
            <button @click="$emit('close')" class="absolute top-4 right-4 text-gray-400 hover:text-gray-600 text-xl">
                &times;
            </button>

            <!-- Header -->
            <div v-if="$slots.header" class="px-6 pt-6 pb-3 border-b border-gray-200">
                <slot name="header" />
            </div>

            <!-- Body dengan min-height -->
            <!-- Body -->
            <div class="px-6 py-4 max-h-[90vh] overflow-y-auto">
                <slot />
            </div>


            <!-- Footer -->
            <div v-if="$slots.footer" class="px-6 pt-3 pb-6 border-t border-gray-200 flex justify-end gap-2">
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