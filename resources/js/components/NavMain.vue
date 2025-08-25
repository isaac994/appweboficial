<script setup lang="ts">
import { SidebarGroup, SidebarGroupLabel, SidebarMenu, SidebarMenuButton, SidebarMenuItem } from '@/components/ui/sidebar';
import { type NavItem } from '@/types';
import { Link, usePage } from '@inertiajs/vue3';

defineProps<{
    items: NavItem[];
}>();

const page = usePage();
</script>

<template>
    <SidebarGroup class="px-2 py-0">
        <SidebarGroupLabel class="text-slate-600 font-bold text-sm uppercase tracking-wider">
            Sistema de Gestión
        </SidebarGroupLabel>
        <SidebarMenu>
            <SidebarMenuItem v-for="item in items" :key="item.title">
                <SidebarMenuButton
                    as-child
                    :is-active="item.href === page.url"
                    :tooltip="item.title"
                    class="group transition-all duration-300 hover:bg-gradient-to-r hover:from-blue-50 hover:to-indigo-50 border border-transparent hover:border-blue-200"
                    :class="{
                        'bg-gradient-to-r from-blue-100 to-indigo-100 border-blue-300 text-blue-900 shadow-sm': item.href === page.url,
                        'text-slate-700 hover:text-blue-900': item.href !== page.url
                    }"
                >
                    <Link :href="item.href" class="flex items-center space-x-3 w-full">
                        <component
                            :is="item.icon"
                            class="w-5 h-5 transition-all duration-300 group-hover:scale-110"
                            :class="{
                                'text-slate-500 group-hover:text-blue-600': item.href !== page.url,
                                'text-blue-600': item.href === page.url
                            }"
                        />
                        <span class="font-semibold">{{ item.title }}</span>
                    </Link>
                </SidebarMenuButton>
            </SidebarMenuItem>
        </SidebarMenu>
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

:deep(.sidebar-menu-button[data-active="true"]) {
    background: linear-gradient(135deg, rgba(59, 130, 246, 0.1) 0%, rgba(99, 102, 241, 0.1) 100%);
    border: 1px solid rgba(59, 130, 246, 0.3);
    box-shadow: 0 6px 20px rgba(59, 130, 246, 0.2);
}

:deep(.sidebar-group-label) {
    color: rgba(71, 85, 105, 0.9);
    font-weight: 700;
    font-size: 0.75rem;
    letter-spacing: 0.15em;
    text-transform: uppercase;
    margin-bottom: 16px;
    padding-left: 12px;
}
</style>
