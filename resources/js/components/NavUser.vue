<script setup lang="ts">
import UserInfo from '@/components/UserInfo.vue';
import { DropdownMenu, DropdownMenuContent, DropdownMenuTrigger } from '@/components/ui/dropdown-menu';
import { SidebarMenu, SidebarMenuButton, SidebarMenuItem, useSidebar } from '@/components/ui/sidebar';
import { type User } from '@/types';
import { usePage } from '@inertiajs/vue3';
import { ChevronsUpDown } from 'lucide-vue-next';
import UserMenuContent from './UserMenuContent.vue';

const page = usePage();
const user = page.props.auth.user as User;
const { isMobile, state } = useSidebar();
</script>

<template>
    <SidebarMenu>
        <SidebarMenuItem>
            <DropdownMenu>
                <DropdownMenuTrigger as-child>
                    <SidebarMenuButton
                        size="lg"
                        class="group transition-all duration-300 hover:bg-gradient-to-r hover:from-blue-500/10 hover:to-cyan-500/10 border border-transparent hover:border-blue-500/30 text-white hover:text-white data-[state=open]:bg-gradient-to-r data-[state=open]:from-blue-500/20 data-[state=open]:to-cyan-500/20 data-[state=open]:border-blue-500/30 data-[state=open]:text-white"
                    >
                        <UserInfo :user="user" />
                        <ChevronsUpDown class="ml-auto size-4 transition-all duration-300 group-hover:scale-110 text-white/70 group-hover:text-white data-[state=open]:text-white" />
                    </SidebarMenuButton>
                </DropdownMenuTrigger>
                <DropdownMenuContent
                    class="w-(--reka-dropdown-menu-trigger-width) min-w-56 rounded-lg bg-[#0a1628]/95 backdrop-blur-xl border border-blue-500/10 shadow-xl"
                    :side="isMobile ? 'bottom' : state === 'collapsed' ? 'left' : 'bottom'"
                    align="end"
                    :side-offset="4"
                >
                    <UserMenuContent :user="user" />
                </DropdownMenuContent>
            </DropdownMenu>
        </SidebarMenuItem>
    </SidebarMenu>
</template>

<style scoped>
:deep(.sidebar-menu-button) {
    background: transparent;
    border-radius: 8px;
    margin: 3px 0;
    transition: all 0.3s ease;
    padding: 12px;
}

:deep(.sidebar-menu-button:hover) {
    transform: translateX(6px);
    box-shadow: 0 6px 20px rgba(59, 130, 246, 0.15);
}

:deep(.sidebar-menu-button[data-state="open"]) {
    background: linear-gradient(135deg, rgba(59, 130, 246, 0.1) 0%, rgba(99, 102, 241, 0.1) 100%);
    border: 1px solid rgba(59, 130, 246, 0.3);
    box-shadow: 0 6px 20px rgba(59, 130, 246, 0.2);
}
</style>
