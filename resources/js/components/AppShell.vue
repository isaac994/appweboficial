<script setup lang="ts">
import { SidebarProvider } from '@/components/ui/sidebar';
import { ref, onMounted } from 'vue';

interface Props {
    variant?: 'header' | 'sidebar';
}

defineProps<Props>();

// Usar localStorage para mantener el sidebar abierto
const isOpen = ref(false);

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

onMounted(() => {
    // Verificar si el sidebar debe estar abierto basado en localStorage
    const shouldBeOpen = loadSidebarState();
    if (shouldBeOpen) {
        isOpen.value = true;
    }
});
</script>

<template>
    <div v-if="variant === 'header'" class="flex min-h-screen w-full flex-col">
        <slot />
    </div>
    <SidebarProvider v-else :default-open="isOpen">
        <slot />
    </SidebarProvider>
</template>
