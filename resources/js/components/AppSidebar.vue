<template>
    <Sidebar collapsible="icon" class="border-r border-sidebar-border bg-sidebar">
        <SidebarHeader class="mx-3 border-b border-[#c2410c]/80 px-0 pt-4 pb-3 group-data-[collapsible=icon]:mx-2">
            <SidebarMenu>
                <SidebarMenuItem>
                    <SidebarMenuButton size="lg" as-child class="h-auto rounded-lg px-2 py-1.5 hover:bg-transparent group-data-[collapsible=icon]:px-0">
                        <Link :href="auth.user.role_id === 1 ? route('home') : route('dashboard')" class="flex items-center">
                            <AppLogo />
                        </Link>
                    </SidebarMenuButton>
                </SidebarMenuItem>
            </SidebarMenu>
        </SidebarHeader>

        <SidebarContent class="py-3">
            <NavMain :items="mainNavItems" />
        </SidebarContent>

        <SidebarFooter>
            <NavFooter :items="footerNavItems" />
            <NavUser />
        </SidebarFooter>
    </Sidebar>
    <slot />
</template>

<script setup lang="ts">
import NavFooter from '@/components/NavFooter.vue';
import NavMain from '@/components/NavMain.vue';
import NavUser from '@/components/NavUser.vue';
import { Sidebar, SidebarContent, SidebarFooter, SidebarHeader, SidebarMenu, SidebarMenuButton, SidebarMenuItem } from '@/components/ui/sidebar';
import { type NavItem } from '@/types';
import { Link, usePage } from '@inertiajs/vue3';
import AppLogo from './AppLogo.vue';
import { sidebars } from '@/components/AppSidebarMenu';
import { computed } from 'vue';

const page = usePage();
const auth = computed(() => page.props.auth);

const mainNavItems: NavItem[] = sidebars[auth.value.user.role_id] || [];

const footerNavItems: NavItem[] = [];
</script>
