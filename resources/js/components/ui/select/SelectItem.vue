<script setup lang="ts">
import { cn } from '@/lib/utils';
import { reactiveOmit } from '@vueuse/core';
import { Check } from 'lucide-vue-next';
import {
    SelectItem,
    SelectItemIndicator,
    SelectItemText,
    type SelectItemEmits,
    type SelectItemProps,
    useForwardPropsEmits,
} from 'reka-ui';
import type { HTMLAttributes } from 'vue';

const props = defineProps<SelectItemProps & { class?: HTMLAttributes['class'] }>();
const emits = defineEmits<SelectItemEmits>();

const delegatedProps = reactiveOmit(props, 'class');
const forwarded = useForwardPropsEmits(delegatedProps, emits);
</script>

<template>
    <SelectItem
        data-slot="select-item"
        v-bind="forwarded"
        :class="cn('focus:bg-accent focus:text-accent-foreground data-[state=checked]:bg-accent data-[state=checked]:text-accent-foreground relative flex w-full cursor-default items-center rounded-sm py-1.5 pr-8 pl-2 text-sm outline-hidden select-none data-[disabled]:pointer-events-none data-[disabled]:opacity-50', props.class)"
    >
        <SelectItemText>
            <slot />
        </SelectItemText>
        <span class="absolute right-2 flex size-3.5 items-center justify-center">
            <SelectItemIndicator>
                <Check class="size-4" />
            </SelectItemIndicator>
        </span>
    </SelectItem>
</template>
