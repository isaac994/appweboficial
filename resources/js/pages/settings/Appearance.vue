<script setup lang="ts">
import { Head } from '@inertiajs/vue3';

import AppearanceTabs from '@/components/AppearanceTabs.vue';
import HeadingSmall from '@/components/HeadingSmall.vue';
import { type BreadcrumbItem } from '@/types';

import AppLayout from '@/layouts/AppLayout.vue';
import SettingsLayout from '@/layouts/settings/Layout.vue';

const breadcrumbItems: BreadcrumbItem[] = [
    {
        title: 'Configuración de Apariencia',
        href: '/settings/appearance',
    },
];
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbItems">
        <Head title="Configuración de Apariencia" />

        <SettingsLayout>
            <div class="space-y-6">
                <div class="mb-8 space-y-0.5">
                    <h2 class="text-2xl font-bold text-white tracking-tight">Configuración de Apariencia</h2>
                    <p class="text-sm text-gray-300">
                        Actualiza la configuración de apariencia de tu cuenta
                    </p>
                </div>
                <AppearanceTabs />
            </div>
        </SettingsLayout>
    </AppLayout>
</template>
