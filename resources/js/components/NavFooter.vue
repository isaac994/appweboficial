<script setup lang="ts">
import { SidebarGroup, SidebarGroupContent, SidebarMenu, SidebarMenuButton, SidebarMenuItem } from '@/components/ui/sidebar';
import { type NavItem } from '@/types';

interface Props {
    items: NavItem[];
    class?: string;
}

defineProps<Props>();
</script>

<template>
    <SidebarGroup :class="`group-data-[collapsible=icon]:p-0 ${$props.class || ''}`">
        <SidebarGroupContent>
            <SidebarMenu>
                <SidebarMenuItem v-for="item in items" :key="item.title">
                    <SidebarMenuButton
                        class="group transition-all duration-300 hover:bg-gradient-to-r hover:from-blue-50 hover:to-indigo-50 border border-transparent hover:border-blue-200 text-slate-700 hover:text-blue-900"
                        as-child
                    >
                        <a :href="item.href" target="_blank" rel="noopener noreferrer" class="flex items-center space-x-3 w-full">
                            <component
                                :is="item.icon"
                                class="w-5 h-5 transition-all duration-300 group-hover:scale-110 text-slate-500 group-hover:text-blue-600"
                            />
                            <span class="font-semibold">{{ item.title }}</span>
                        </a>
                    </SidebarMenuButton>
                </SidebarMenuItem>
            </SidebarMenu>
        </SidebarGroupContent>
    </SidebarGroup>
</template>

<style scoped>
:deep(.sidebar-menu-button) {
    background: transparent;
    border-radius: 8px;
    margin: 3px 0;
    transition: all 0.3s ease;
    padding: 10px 12px;
}

:deep(.sidebar-menu-button:hover) {
    transform: translateX(6px);
    box-shadow: 0 6px 20px rgba(59, 130, 246, 0.15);
}
</style>
