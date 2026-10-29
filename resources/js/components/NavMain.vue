<script setup lang="ts">
import { SidebarGroup, SidebarMenu, SidebarMenuButton, SidebarMenuItem } from '@/components/ui/sidebar';
import { type NavItem, type SharedData } from '@/types';
import { Link, usePage } from '@inertiajs/vue3';
import { ChevronDown } from 'lucide-vue-next';
import { ref, watch } from 'vue';
import SidebarMenuSubButton from './ui/sidebar/SidebarMenuSubButton.vue';
import { removeBasePrefix, useBaseUrl } from '@/utils/useBaseUrl';

const props = defineProps<{ items: NavItem[] }>();

const page = usePage<SharedData>();

const expanded = ref<string[]>([]);

function toggleGroup(title: string): void {
    if (expanded.value.includes(title)) {
        expanded.value = expanded.value.filter((itemTitle) => itemTitle !== title);
    } else {
        expanded.value.push(title);
    }
}

function isOpen(title: string): boolean {
    return expanded.value.includes(title);
}

function isActiveHref(href: string): boolean {
    return removeBasePrefix(page.url).startsWith(href);
}

function hasActiveChild(item: NavItem): boolean {
    return item.children?.some((child) => isActiveHref(child.href)) ?? false;
}

function isActiveItem(item: NavItem): boolean {
    return item.children ? hasActiveChild(item) : isActiveHref(item.href);
}

watch(
    () => page.url,
    () => {
        expanded.value = [];

        for (const item of props.items) {
            if (hasActiveChild(item)) {
                expanded.value.push(item.title);
            }
        }
    },
    { immediate: true },
);
</script>

<template>
    <SidebarGroup class="px-3 py-0 group-data-[collapsible=icon]:px-2">
        <SidebarMenu class="space-y-1">
            <SidebarMenuItem v-for="item in items" :key="item.title">
                <template v-if="item.children">
                    <SidebarMenuButton
                        as="button"
                        :tooltip="item.title"
                        :class="[
                            'h-10 rounded-lg px-3 text-sm font-medium',
                            isOpen(item.title) || isActiveItem(item)
                                ? 'bg-[#c2410c] text-white hover:bg-[#9a3412] hover:text-white'
                                : 'text-sidebar-foreground hover:bg-sidebar-accent hover:text-sidebar-accent-foreground',
                        ]"
                        @click="toggleGroup(item.title)"
                    >
                        <component :is="item.icon" />
                        <span>{{ item.title }}</span>
                        <ChevronDown class="ml-auto size-4 transition-transform" :class="{ 'rotate-180': isOpen(item.title) }" />
                    </SidebarMenuButton>

                    <div v-show="isOpen(item.title)" class="mt-1 ml-7 space-y-1">
                        <template v-for="sub in item.children" :key="sub.title">
                            <div
                                v-if="sub.comingSoon"
                                class="text-sidebar-foreground/60 flex min-h-9 items-center gap-2 rounded-lg px-3 py-2 text-sm"
                                aria-disabled="true"
                            >
                                <component :is="sub.icon" class="size-4 shrink-0" />
                                <span>{{ sub.title }}</span>
                                <span
                                    class="ml-auto shrink-0 rounded-full border border-amber-200 bg-amber-50 px-1.5 py-0.5 text-[10px] font-semibold tracking-wide text-amber-700"
                                >
                                    Soon
                                </span>
                            </div>

                            <SidebarMenuSubButton
                                v-else
                                as-child
                                :is-active="isActiveHref(sub.href)"
                                :class="[
                                    'h-auto min-h-9 rounded-lg px-3 py-2 text-sm',
                                    isActiveHref(sub.href)
                                        ? 'bg-[#c2410c] text-white hover:bg-[#9a3412] hover:text-white data-[active=true]:!bg-[#c2410c] data-[active=true]:!text-white [&>svg]:!text-white'
                                        : 'hover:bg-sidebar-accent',
                                ]"
                            >
                                <Link :href="useBaseUrl(sub.href)" class="flex items-start gap-2 whitespace-normal">
                                    <component :is="sub.icon" class="mt-0.5 size-4 shrink-0" />
                                    <span>{{ sub.title }}</span>
                                </Link>
                            </SidebarMenuSubButton>
                        </template>
                    </div>
                </template>

                <template v-else>
                    <SidebarMenuButton
                        as-child
                        :is-active="isActiveItem(item)"
                        :tooltip="item.title"
                        :class="[
                            'h-10 rounded-lg px-3 text-sm font-medium',
                            isActiveItem(item)
                                ? 'bg-[#c2410c] text-white hover:bg-[#9a3412] hover:text-white data-[active=true]:!bg-[#c2410c] data-[active=true]:!text-white'
                                : 'text-sidebar-foreground hover:bg-sidebar-accent hover:text-sidebar-accent-foreground',
                        ]"
                    >
                        <Link :href="useBaseUrl(item.href)">
                            <component :is="item.icon" />
                            <span>{{ item.title }}</span>
                        </Link>
                    </SidebarMenuButton>
                </template>
            </SidebarMenuItem>
        </SidebarMenu>
    </SidebarGroup>
</template>
