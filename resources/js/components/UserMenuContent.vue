<script setup lang="ts">
import UserInfo from '@/components/UserInfo.vue';
import { DropdownMenuGroup, DropdownMenuItem, DropdownMenuLabel, DropdownMenuSeparator } from '@/components/ui/dropdown-menu';
import type { User } from '@/types';
import { Link, router } from '@inertiajs/vue3';
import { LogOut, Settings } from 'lucide-vue-next';

interface Props {
    user: User;
}

const handleLogout = () => {
    router.flushAll();
};

defineProps<Props>();
</script>

<template>
    <DropdownMenuLabel class="p-0 font-normal">
        <div class="flex items-center gap-2 px-1 py-1.5 text-left text-sm">
            <UserInfo :user="user" :show-email="true" />
        </div>
    </DropdownMenuLabel>
    <DropdownMenuSeparator class="bg-slate-200" />
    <DropdownMenuGroup>
        <DropdownMenuItem
            :as-child="true"
            class="text-slate-700 hover:text-blue-900 hover:bg-gradient-to-r hover:from-blue-50 hover:to-indigo-50 focus:bg-gradient-to-r focus:from-blue-50 focus:to-indigo-50 focus:text-blue-900"
        >
            <Link class="block w-full" :href="route('profile.edit')" prefetch as="button">
                <Settings class="mr-2 h-4 w-4 text-slate-500" />
                Configuración
            </Link>
        </DropdownMenuItem>
    </DropdownMenuGroup>
    <DropdownMenuSeparator class="bg-slate-200" />
    <DropdownMenuItem
        :as-child="true"
        class="text-red-600 hover:text-red-700 hover:bg-gradient-to-r hover:from-red-50 hover:to-pink-50 focus:bg-gradient-to-r focus:from-red-50 focus:to-pink-50 focus:text-red-700"
    >
        <Link class="block w-full" method="post" :href="route('logout')" @click="handleLogout" as="button">
            <LogOut class="mr-2 h-4 w-4 text-red-500" />
            Cerrar Sesión
        </Link>
    </DropdownMenuItem>
</template>

<style scoped>
:deep(.dropdown-menu-item) {
    transition: all 0.3s ease;
    border-radius: 6px;
    margin: 3px 6px;
    padding: 10px 12px;
    font-weight: 500;
}

:deep(.dropdown-menu-item:hover) {
    transform: translateX(6px);
    box-shadow: 0 4px 15px rgba(59, 130, 246, 0.15);
}

:deep(.dropdown-menu-separator) {
    margin: 6px 10px;
    height: 1px;
}
</style>
