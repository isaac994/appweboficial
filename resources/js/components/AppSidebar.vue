<script setup lang="ts">
import NavFooter from '@/components/NavFooter.vue';
import NavMain from '@/components/NavMain.vue';
import NavUser from '@/components/NavUser.vue';
import { Sidebar, SidebarContent, SidebarFooter, SidebarHeader, SidebarMenu, SidebarMenuButton, SidebarMenuItem, useSidebar } from '@/components/ui/sidebar';
import { type NavItem } from '@/types';
import { Link } from '@inertiajs/vue3';
import { BookOpen, Folder, LayoutGrid, Package, Users, Tags, Building2, ShoppingCart, Settings, BarChart3 } from 'lucide-vue-next';
import AppLogo from './AppLogo.vue';
import { ref, onMounted, onUnmounted, watch } from 'vue';

const { setOpen, state } = useSidebar();
const isHovering = ref(false);

// Estado global para mantener el hover entre navegaciones
if (typeof window !== 'undefined') {
    if (!window.sidebarHoverState) {
        window.sidebarHoverState = { isHovering: false };
    }
}

// Función para guardar el estado en localStorage
const saveSidebarState = (hovering: boolean) => {
    if (typeof window !== 'undefined') {
        localStorage.setItem('sidebarHoverState', JSON.stringify({ isHovering: hovering }));
    }
};

// Función para cargar el estado desde localStorage
const loadSidebarState = () => {
    if (typeof window !== 'undefined') {
        const saved = localStorage.getItem('sidebarHoverState');
        if (saved) {
            const state = JSON.parse(saved);
            return state.isHovering;
        }
    }
    return false;
};

const mainNavItems: NavItem[] = [
    {
        title: 'Dashboard',
        href: '/dashboard',
        icon: LayoutGrid,
        permission: 'dashboard.view',
    },
    {
        title: 'Productos',
        href: '/productos',
        icon: Package,
        permission: 'productos.view',
    },
    {
        title: 'Categorías',
        href: '/categorias',
        icon: Tags,
        permission: 'categorias.manage',
    },
    {
        title: 'Marcas',
        href: '/marcas',
        icon: Building2,
        permission: 'marcas.manage',
    },
    {
        title: 'Compras',
        href: '/compras',
        icon: Folder,
        permission: 'compras.manage',
    },
    {
        title: 'Ventas',
        href: '/ventas',
        icon: ShoppingCart,
        permission: 'ventas.manage',
    },
    {
        title: 'Proveedores',
        href: '/proveedores',
        icon: Building2,
        permission: 'proveedores.manage',
    },
    {
        title: 'Clientes',
        href: '/clientes',
        icon: Users,
        permission: 'clientes.manage',
    },
    {
        title: 'Reportes',
        icon: BarChart3,
        permission: 'reportes.view',
        children: [
            {
                title: 'Productos',
                href: '/reportes/productos',
                permission: 'reportes.view',
            },
            {
                title: 'Compras',
                href: '/reportes/compras',
                permission: 'reportes.view',
            },
            {
                title: 'Proveedores',
                href: '/reportes/proveedores',
                permission: 'reportes.view',
            },
            {
                title: 'Clientes',
                href: '/reportes/clientes',
                permission: 'reportes.view',
            },
            {
                title: 'Ventas',
                href: '/reportes/ventas',
                permission: 'reportes.view',
            },
        ],
    },
    {
        title: 'Configuración',
        href: '/settings',
        icon: Settings,
        role: 'Administrador',
    },
];

const footerNavItems: NavItem[] = [
    {
        title: 'Documentación',
        href: 'https://laravel.com/docs/starter-kits#vue',
        icon: BookOpen,
    },
];

const handleMouseEnter = () => {
    isHovering.value = true;
    if (typeof window !== 'undefined') {
        window.sidebarHoverState.isHovering = true;
    }
    saveSidebarState(true);
    setOpen(true);
};

const handleMouseLeave = () => {
    isHovering.value = false;
    if (typeof window !== 'undefined') {
        window.sidebarHoverState.isHovering = false;
    }
    saveSidebarState(false);
    setOpen(false);
};

// Función para verificar y restaurar el estado del sidebar
const checkAndRestoreSidebarState = () => {
    const shouldBeOpen = loadSidebarState();
    if (shouldBeOpen) {
        isHovering.value = true;
        if (typeof window !== 'undefined') {
            window.sidebarHoverState.isHovering = true;
        }
        setOpen(true);
    }
};

// Watcher para el estado del sidebar
watch(state, (newState) => {
    if (newState === 'collapsed' && loadSidebarState()) {
        setTimeout(() => {
            setOpen(true);
        }, 100);
    }
});

// Verificar el estado de hover al montar el componente
onMounted(() => {
    // Restaurar el estado de hover si estaba activo
    checkAndRestoreSidebarState();

    // Verificar continuamente si debe estar abierto
    const checkHoverState = () => {
        if (isHovering.value && state.value === 'collapsed') {
            setOpen(true);
        }
    };

    const interval = setInterval(checkHoverState, 100);

    // También verificar el estado global cada 200ms
    const checkGlobalState = () => {
        if (loadSidebarState() && state.value === 'collapsed') {
            setOpen(true);
        }
    };

    const globalInterval = setInterval(checkGlobalState, 200);

    // Verificar el estado al cambiar de página
    const handlePageChange = () => {
        if (loadSidebarState()) {
            setTimeout(() => {
                setOpen(true);
            }, 100);
        }
    };

    // Escuchar eventos de navegación
    window.addEventListener('popstate', handlePageChange);
    window.addEventListener('pushstate', handlePageChange);

    // También verificar después de un tiempo para asegurar que se mantenga
    setTimeout(checkAndRestoreSidebarState, 500);

    // Verificación adicional para páginas específicas
    const checkForSpecificPages = () => {
        const currentPath = window.location.pathname;
        const isViewPage = currentPath.includes('/show') ||
                          currentPath.includes('/categorias/') ||
                          currentPath.includes('/marcas/') ||
                          currentPath.includes('/proveedores/');

        if (isViewPage && loadSidebarState()) {
            setOpen(true);
        }
    };

    // Verificar inmediatamente y después de un delay
    checkForSpecificPages();
    setTimeout(checkForSpecificPages, 300);
    setTimeout(checkForSpecificPages, 1000);

    onUnmounted(() => {
        clearInterval(interval);
        clearInterval(globalInterval);
        window.removeEventListener('popstate', handlePageChange);
        window.removeEventListener('pushstate', handlePageChange);
    });
});
</script>

<template>
    <Sidebar
        collapsible="icon"
        variant="inset"
        class="bg-gradient-to-b from-slate-100 via-slate-50 to-slate-100 border-r border-slate-300 shadow-xl"
        @mouseenter="handleMouseEnter"
        @mouseleave="handleMouseLeave"
    >
        <SidebarHeader class="bg-white/90 backdrop-blur-sm border-b border-slate-300 p-4">
            <SidebarMenu>
                <SidebarMenuItem>
                    <SidebarMenuButton
                        size="lg"
                        as-child
                        class="bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 text-white shadow-lg border-0"
                    >
                        <Link :href="route('dashboard')">
                            <AppLogo />
                        </Link>
                    </SidebarMenuButton>
                </SidebarMenuItem>
            </SidebarMenu>
        </SidebarHeader>

        <SidebarContent class="bg-transparent p-4">
            <NavMain :items="mainNavItems" />
        </SidebarContent>

        <SidebarFooter class="bg-white/90 backdrop-blur-sm border-t border-slate-300 p-4">
            <NavFooter :items="footerNavItems" />
            <NavUser />
        </SidebarFooter>
    </Sidebar>
    <slot />
</template>

<style scoped>
:deep(.sidebar) {
    background: linear-gradient(180deg,
        rgba(241, 245, 249, 0.98) 0%,
        rgba(248, 250, 252, 0.95) 50%,
        rgba(241, 245, 249, 0.98) 100%);
    backdrop-filter: blur(20px);
    border-right: 1px solid rgba(203, 213, 225, 0.8);
    box-shadow: 0 0 30px rgba(0, 0, 0, 0.1);
    transition: width 0.3s ease;
}

:deep(.sidebar-header) {
    background: rgba(255, 255, 255, 0.95);
    backdrop-filter: blur(20px);
    border-bottom: 1px solid rgba(203, 213, 225, 0.8);
}

:deep(.sidebar-footer) {
    background: rgba(255, 255, 255, 0.95);
    backdrop-filter: blur(20px);
    border-top: 1px solid rgba(203, 213, 225, 0.8);
}

:deep(.sidebar-menu-button) {
    background: transparent;
    border: 1px solid rgba(203, 213, 225, 0.3);
    transition: all 0.3s ease;
    color: rgba(51, 65, 85, 0.95);
    border-radius: 8px;
}

:deep(.sidebar-menu-button:hover) {
    background: linear-gradient(90deg, rgba(59, 130, 246, 0.1) 0%, rgba(99, 102, 241, 0.1) 100%);
    border-color: rgba(59, 130, 246, 0.5);
    color: rgba(30, 58, 138, 1);
    transform: translateX(4px);
    box-shadow: 0 4px 15px rgba(59, 130, 246, 0.2);
}

:deep(.sidebar-menu-button[data-active="true"]) {
    background: linear-gradient(90deg, rgba(59, 130, 246, 0.15) 0%, rgba(99, 102, 241, 0.15) 100%);
    color: rgba(30, 58, 138, 1);
    border-color: rgba(59, 130, 246, 0.7);
    box-shadow: 0 4px 15px rgba(59, 130, 246, 0.3);
}

:deep(.sidebar-group-label) {
    color: rgba(71, 85, 105, 0.9);
    font-weight: 700;
    font-size: 0.75rem;
    letter-spacing: 0.1em;
    text-transform: uppercase;
    margin-bottom: 12px;
    padding-left: 12px;
}
</style>
