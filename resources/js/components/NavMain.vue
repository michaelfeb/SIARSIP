<script setup lang="ts">
import { SidebarGroup, SidebarGroupLabel, SidebarMenu, SidebarMenuButton, SidebarMenuItem } from '@/components/ui/sidebar';
import { type NavItem, type SharedData } from '@/types';
import { Link, usePage } from '@inertiajs/vue3';
import { ChevronDown, Minus } from 'lucide-vue-next';
import { ref, watch } from 'vue';
import SidebarMenuSubButton from './ui/sidebar/SidebarMenuSubButton.vue';

const props = defineProps<{ items: NavItem[] }>()

const page = usePage<SharedData>();

const expanded = ref<string[]>([])

function toggleGroup(title: string) {
    if (expanded.value.includes(title)) {
        expanded.value = expanded.value.filter(t => t !== title)
    } else {
        expanded.value.push(title)
    }
}

function isOpen(title: string) {
    return expanded.value.includes(title)
}

watch(
    () => page.url,
    () => {
        expanded.value = []

        for (const item of props.items) {
            if (item.children?.some(sub => page.url.startsWith(sub.href))) {
                expanded.value.push(item.title)
                console.log('aktif:', item.title)
            }
        }
    },
    { immediate: true }
)


</script>

<template>
    <SidebarGroup class="px-2 py-0">
        <SidebarGroupLabel>Menu</SidebarGroupLabel>
        <SidebarMenu>
            <SidebarMenuItem v-for="item in items" :key="item.title">

                <template v-if="item.children">
                    <SidebarMenuButton as="button" :tooltip="item.title" @click="toggleGroup(item.title)">
                        <component :is="item.icon" />
                        <span>{{ item.title }}</span>
                        <ChevronDown class="ml-auto transition-transform"
                            :class="{ 'rotate-180': isOpen(item.title) }" />
                    </SidebarMenuButton>

                    <div v-show="isOpen(item.title)" class="ml-6 mt-1 space-y-1">
                        <SidebarMenuSubButton v-for="sub in item.children" :key="sub.title" as-child
                            :is-active="page.url.startsWith(sub.href)">
                            <Link :href="sub.href" class="flex items-center gap-2">
                            <component :is="sub.icon" class="w-6 h-6" />
                            <span>{{ sub.title }}</span>
                            </Link>
                        </SidebarMenuSubButton>
                    </div>
                </template>

                <template v-else>
                    <SidebarMenuButton as-child :is-active="page.url.startsWith(item.href ?? '')" :tooltip="item.title">
                        <Link :href="item.href">
                        <component :is="item.icon" />
                        <span>{{ item.title }}</span>
                        </Link>
                    </SidebarMenuButton>
                </template>
            </SidebarMenuItem>
        </SidebarMenu>
    </SidebarGroup>
</template>
