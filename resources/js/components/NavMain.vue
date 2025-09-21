<script setup lang="ts">
import { SidebarGroup, SidebarGroupLabel, SidebarMenu, SidebarMenuButton, SidebarMenuItem } from '@/components/ui/sidebar';
import { type NavItem } from '@/types';
import { Link, usePage } from '@inertiajs/vue3';
import { useAuth } from '@/composables/useAuth';
import PermissionGate from '@/components/PermissionGate.vue';

import { ref, computed } from 'vue';

defineProps<{
    items: NavItem[];
}>();

const page = usePage();
const { can, hasRole } = useAuth();

// Estado para controlar qué menús están expandidos
const expandedMenus = ref<string[]>([]);

// Función para verificar si un elemento de menú debe mostrarse
const shouldShowMenuItem = (item: NavItem) => {
    // Si no tiene permisos definidos, mostrar siempre
    if (!item.permission && !item.role && !item.permissions && !item.roles) {
        return true;
    }

    // Verificar por permiso individual
    if (item.permission) {
        return can(item.permission);
    }

    // Verificar por permisos múltiples
    if (item.permissions && item.permissions.length > 0) {
        return item.permissions.some(permission => can(permission));
    }

    // Verificar por rol individual
    if (item.role) {
        return hasRole(item.role);
    }

    // Verificar por roles múltiples
    if (item.roles && item.roles.length > 0) {
        return item.roles.some(role => hasRole(role));
    }

    return true;
};

// Función para alternar la expansión de un menú
const toggleMenu = (title: string) => {
    const index = expandedMenus.value.indexOf(title);
    if (index > -1) {
        expandedMenus.value.splice(index, 1);
    } else {
        expandedMenus.value.push(title);
    }
};

// Función para verificar si un menú está expandido
const isMenuExpanded = (title: string) => {
    return expandedMenus.value.includes(title);
};
</script>

<template>
    <SidebarGroup class="px-2 py-0">
        <SidebarGroupLabel class="text-slate-600 font-bold text-sm uppercase tracking-wider">
            Sistema de Gestión
        </SidebarGroupLabel>
        <SidebarMenu>
            <SidebarMenuItem v-for="item in items" :key="item.title" v-show="shouldShowMenuItem(item)">
                <!-- Elemento con submenús -->
                <template v-if="item.children && item.children.length > 0">
                    <SidebarMenuButton
                        @click="toggleMenu(item.title)"
                        :tooltip="item.title"
                        class="group transition-all duration-300 hover:bg-gradient-to-r hover:from-blue-50 hover:to-indigo-50 border border-transparent hover:border-blue-200 text-slate-700 hover:text-blue-900 cursor-pointer"
                    >
                        <div class="flex items-center space-x-3 w-full">
                            <component
                                :is="item.icon"
                                class="w-5 h-5 transition-all duration-300 group-hover:scale-110 text-slate-500 group-hover:text-blue-600"
                            />
                            <span class="font-semibold">{{ item.title }}</span>
                            <svg
                                class="w-4 h-4 ml-auto transition-transform duration-200"
                                fill="none"
                                stroke="currentColor"
                                viewBox="0 0 24 24"
                                :class="{ 'rotate-180': isMenuExpanded(item.title) }"
                            >
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                            </svg>
                        </div>
                    </SidebarMenuButton>

                    <!-- Submenús (solo visibles cuando está expandido) -->
                    <div v-if="isMenuExpanded(item.title)" class="ml-6 mt-2 space-y-1">
                        <SidebarMenuItem v-for="child in item.children" :key="child.title" v-show="shouldShowMenuItem(child)">
                            <SidebarMenuButton
                                as-child
                                :is-active="child.href === page.url"
                                :tooltip="child.title"
                                class="group transition-all duration-300 hover:bg-gradient-to-r hover:from-blue-50 hover:to-indigo-50 border border-transparent hover:border-blue-200"
                                :class="{
                                    'bg-gradient-to-r from-blue-100 to-indigo-100 border-blue-300 text-blue-900 shadow-sm': child.href === page.url,
                                    'text-slate-700 hover:text-blue-900': child.href !== page.url
                                }"
                            >
                                <Link :href="child.href" class="flex items-center space-x-3 w-full">
                                    <span class="font-medium text-sm">{{ child.title }}</span>
                                </Link>
                            </SidebarMenuButton>
                        </SidebarMenuItem>
                    </div>
                </template>

                <!-- Elemento sin submenús -->
                <template v-else>
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
                </template>
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
