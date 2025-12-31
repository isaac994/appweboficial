<script setup lang="ts">
import { ref } from 'vue';
import { Link, usePage, router } from '@inertiajs/vue3';
import { Package, Tags, Building2, Layers, Box } from 'lucide-vue-next';
import AppLayout from './AppLayout.vue';

const page = usePage();

const productNavItems = [
    {
        title: 'Categorías',
        href: '/categorias',
        icon: Tags,
    },
    {
        title: 'Marcas',
        href: '/marcas',
        icon: Building2,
    },
    {
        title: 'Modelos',
        href: '/modelos',
        icon: Layers,
    },
    {
        title: 'Productos',
        href: '/productos',
        icon: Box,
    },
];

const currentPath = page.props.ziggy?.location ? new URL(page.props.ziggy.location).pathname : '';

// Búsqueda
const searchQuery = ref('');

const handleSearch = () => {
    // Solo hacer búsqueda en la página de productos
    if (currentPath === '/productos') {
        router.get('/productos', {
            search: searchQuery.value
        }, {
            preserveState: true,
            preserveScroll: true,
            replace: true
        });
    }
};
</script>

<template>
    <AppLayout>
        <div class="px-4 py-6 bg-gradient-to-br from-[#0a1628] via-[#0d1b2e] to-[#0a1628] min-h-screen">
            <div class="mb-8 space-y-0.5">
                <h2 class="text-2xl font-bold text-white tracking-tight">Gestión de Productos</h2>
                <p class="text-sm text-gray-300">
                    Administra categorías, marcas y modelos de productos
                </p>
            </div>

            <!-- Horizontal Navigation and Controls -->
            <div class="mb-8">
                <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
                    <!-- Navigation Buttons -->
                    <nav class="flex space-x-2">
                        <Link
                            v-for="item in productNavItems"
                            :key="item.href"
                            :href="item.href"
                            :class="[
                                'px-4 py-2 rounded-lg text-sm font-medium transition-all duration-200 flex items-center space-x-2',
                                currentPath === item.href
                                    ? 'bg-gradient-to-r from-blue-600 to-cyan-500 text-white shadow-lg'
                                    : 'text-gray-300 hover:text-white hover:bg-blue-500/20 border border-transparent hover:border-blue-500/30'
                            ]"
                        >
                            <component :is="item.icon" class="h-4 w-4" />
                            <span>{{ item.title }}</span>
                        </Link>
                    </nav>

                    <!-- Search and Actions Container -->
                    <div class="flex flex-col sm:flex-row items-start sm:items-center gap-4 flex-1 lg:justify-end">
                        <!-- Search Input -->
                        <div class="relative max-w-md w-full sm:w-auto">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <svg class="h-4 w-4 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                                </svg>
                            </div>
                            <input
                                type="text"
                                placeholder="Buscar por nombre, modelo, marca, categoría, estado..."
                                class="w-full pl-10 pr-4 py-2 bg-black/30 border border-blue-500/50 rounded-lg text-white placeholder-blue-300 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent text-sm"
                                v-model="searchQuery"
                                @input="handleSearch"
                            />
                        </div>

                        <!-- Action Buttons Slot -->
                        <div class="flex-shrink-0">
                            <slot name="actions" />
                        </div>
                    </div>
                </div>
            </div>

            <!-- Main Content -->
            <div class="w-full">
                <section class="max-w-full space-y-12">
                    <slot />
                </section>
            </div>
        </div>
    </AppLayout>
</template>
