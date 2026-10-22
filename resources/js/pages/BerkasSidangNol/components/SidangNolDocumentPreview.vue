<script setup lang="ts">
import Button from '@/components/ui/button/Button.vue';
import Modal from '@/components/ui/modal/Modal.vue';

defineProps<{
    open: boolean;
    title: string;
    url: string | null;
}>();

defineEmits<{
    close: [];
}>();
</script>

<template>
    <Modal :show="open" @close="$emit('close')">
        <template #header>
            <h2 class="text-lg font-semibold">{{ title }}</h2>
        </template>

        <iframe
            v-if="url"
            :src="url"
            :title="title"
            class="h-[60vh] w-full rounded border border-gray-200 sm:h-[70vh]"
        />
        <p v-else class="text-sm text-gray-500">Dokumen tidak tersedia.</p>

        <template #footer>
            <Button
                type="button"
                class="border border-orange-400 bg-white text-orange-500 hover:bg-orange-100"
                @click="$emit('close')"
            >
                Kembali
            </Button>
        </template>
    </Modal>
</template>
