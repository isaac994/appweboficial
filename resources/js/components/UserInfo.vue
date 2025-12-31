<script setup lang="ts">
import { Avatar, AvatarFallback, AvatarImage } from '@/components/ui/avatar';
import { useInitials } from '@/composables/useInitials';
import type { User } from '@/types';
import { computed } from 'vue';

interface Props {
    user: User;
    showEmail?: boolean;
}

const props = withDefaults(defineProps<Props>(), {
    showEmail: false,
});

const { getInitials } = useInitials();

// Compute whether we should show the avatar image
const showAvatar = computed(() => props.user.avatar && props.user.avatar !== '');
</script>

<template>
    <Avatar class="h-8 w-8 overflow-hidden rounded-lg border border-blue-500/50 shadow-lg">
        <AvatarImage v-if="showAvatar" :src="user.avatar!" :alt="user.name" />
        <AvatarFallback class="rounded-lg bg-gradient-to-br from-blue-600 to-cyan-500 text-white font-bold shadow-lg">
            {{ getInitials(user.name) }}
        </AvatarFallback>
    </Avatar>

    <div class="grid flex-1 text-left text-sm leading-tight">
        <span class="truncate font-bold text-white group-hover:text-white transition-colors duration-300">{{ user.name }}</span>
        <span v-if="showEmail" class="truncate text-xs text-white/70 group-hover:text-white/90 transition-colors duration-300">{{ user.email }}</span>
    </div>
</template>

<style scoped>
:deep(.avatar) {
    box-shadow: 0 4px 12px rgba(59, 130, 246, 0.4);
    transition: all 0.3s ease;
}

:deep(.avatar:hover) {
    box-shadow: 0 6px 20px rgba(59, 130, 246, 0.6);
    transform: scale(1.05);
}
</style>
