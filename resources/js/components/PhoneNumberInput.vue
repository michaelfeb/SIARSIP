<script setup lang="ts">
import Input from '@/components/ui/input/Input.vue';
import { computed, onMounted } from 'vue';

defineOptions({
    inheritAttrs: false,
});

const PREFIX = '628';

const props = defineProps<{
    modelValue?: string | null;
}>();

const emit = defineEmits<{
    'update:modelValue': [value: string];
}>();

function digits(value: string | null | undefined): string {
    return (value ?? '').replace(/\D/g, '');
}

function suffixOf(value: string | null | undefined): string {
    const number = digits(value);

    if (number.startsWith(PREFIX)) {
        return number.slice(PREFIX.length);
    }

    const withoutCountryCode = number.startsWith('62') ? number.slice(2) : number.startsWith('0') ? number.slice(1) : number;

    return withoutCountryCode.startsWith('8') ? withoutCountryCode.slice(1) : withoutCountryCode;
}

function normalisedValue(value: string | null | undefined): string {
    return `${PREFIX}${suffixOf(value)}`;
}

const phoneNumber = computed({
    get: () => normalisedValue(props.modelValue),
    set: (value: string | number) => emit('update:modelValue', normalisedValue(String(value))),
});

onMounted(() => {
    const value = normalisedValue(props.modelValue);

    if (value !== props.modelValue) {
        emit('update:modelValue', value);
    }
});
</script>

<template>
    <div class="flex">
        <span
            class="border-input bg-muted text-muted-foreground inline-flex h-9 items-center rounded-l-lg border border-r-0 px-3 text-sm"
            aria-hidden="true">
            ID
        </span>
        <Input v-bind="$attrs" v-model="phoneNumber" class="rounded-l-none" inputmode="numeric" autocomplete="tel" />
    </div>
</template>
