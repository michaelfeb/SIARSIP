<script setup lang="ts">
import Input from '@/components/ui/input/Input.vue';
import { formatThousandSeparatedNumber, toNumericString } from '@/utils/number';
import { computed } from 'vue';

defineOptions({ inheritAttrs: false });

const props = defineProps<{ modelValue?: string | number | null }>();
const emit = defineEmits<{ 'update:modelValue': [value: string] }>();

const displayValue = computed({
    get: () => formatThousandSeparatedNumber(props.modelValue),
    set: (value: string | number) => emit('update:modelValue', toNumericString(value)),
});
</script>

<template>
    <Input v-bind="$attrs" v-model="displayValue" type="text" inputmode="numeric" />
</template>
