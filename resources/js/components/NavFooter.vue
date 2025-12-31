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
                        class="group transition-all duration-300 hover:bg-gradient-to-r hover:from-blue-500/10 hover:to-cyan-500/10 border border-transparent hover:border-blue-500/30 text-white hover:text-white"
                        as-child
                    >
                        <a :href="item.href" target="_blank" rel="noopener noreferrer" class="flex items-center space-x-3 w-full">
                            <component
                                :is="item.icon"
                                class="w-5 h-5 transition-all duration-300 group-hover:scale-110 text-white/70 group-hover:text-white"
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
    border: 1px solid rgba(59, 130, 246, 0.1);
    border-radius: 8px;
    margin: 3px 0;
    transition: all 0.3s ease;
    padding: 10px 12px;
    color: rgba(255, 255, 255, 0.9);
}

:deep(.sidebar-menu-button:hover) {
    background: linear-gradient(90deg, rgba(59, 130, 246, 0.1) 0%, rgba(34, 211, 238, 0.1) 100%);
    border-color: rgba(59, 130, 246, 0.3);
    color: rgba(255, 255, 255, 1);
    transform: translateX(4px);
    box-shadow: 0 4px 15px rgba(59, 130, 246, 0.2);
}
</style>
