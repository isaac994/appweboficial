<script setup lang="ts">
import Heading from '@/components/Heading.vue';
import { Button } from '@/components/ui/button';
import { Separator } from '@/components/ui/separator';
import { type NavItem } from '@/types';
import { Link, usePage } from '@inertiajs/vue3';

const sidebarNavItems: NavItem[] = [
    {
        title: 'Perfil',
        href: '/settings/profile',
    },
    {
        title: 'Contraseña',
        href: '/settings/password',
    },
    {
        title: 'Apariencia',
        href: '/settings/appearance',
    },
];

const page = usePage();

const currentPath = page.props.ziggy?.location ? new URL(page.props.ziggy.location).pathname : '';
</script>

<template>
    <div class="px-4 py-6 bg-gradient-to-br from-[#0a1628] via-[#0d1b2e] to-[#0a1628] min-h-screen">
        <div class="mb-8 space-y-0.5">
            <h2 class="text-2xl font-bold text-white tracking-tight">Configuración</h2>
            <p class="text-sm text-gray-300">
                Gestiona tu perfil y configuración de cuenta
            </p>
        </div>

        <div class="flex flex-col lg:flex-row lg:space-x-12">
            <aside class="w-full max-w-xl lg:w-48">
                <nav class="flex flex-col space-y-2">
                    <Link
                        v-for="item in sidebarNavItems"
                        :key="item.href"
                        :href="item.href"
                        :class="[
                            'w-full px-4 py-3 rounded-lg text-sm font-medium transition-all duration-200',
                            currentPath === item.href
                                ? 'bg-gradient-to-r from-blue-600 to-cyan-500 text-white shadow-lg'
                                : 'text-gray-300 hover:text-white hover:bg-blue-500/20 border border-transparent hover:border-blue-500/30'
                        ]"
                    >
                        {{ item.title }}
                    </Link>
                </nav>
            </aside>

            <Separator class="my-6 lg:hidden bg-gray-600" />

            <div class="flex-1 md:max-w-2xl">
                <section class="max-w-xl space-y-12">
                    <slot />
                </section>
            </div>
        </div>
    </div>
</template>
