<template>
    <div v-if="hasPermission">
        <slot />
    </div>
</template>

<script setup>
import { computed } from 'vue'
import { usePage } from '@inertiajs/vue3'
import { can, hasRole, hasAnyRole, canAny } from '@/utils/permissions'

const props = defineProps({
    permission: {
        type: String,
        default: null
    },
    permissions: {
        type: Array,
        default: () => []
    },
    role: {
        type: String,
        default: null
    },
    roles: {
        type: Array,
        default: () => []
    },
    requireAll: {
        type: Boolean,
        default: false
    }
})

const page = usePage()
const user = computed(() => page.props.auth?.user || null)

const hasPermission = computed(() => {
    if (!user.value) return false

    // Verificar por rol individual
    if (props.role) {
        return hasRole(user.value, props.role)
    }

    // Verificar por roles múltiples
    if (props.roles && props.roles.length > 0) {
        return props.requireAll
            ? props.roles.every(role => hasRole(user.value, role))
            : hasAnyRole(user.value, props.roles)
    }

    // Verificar por permiso individual
    if (props.permission) {
        return can(user.value, props.permission)
    }

    // Verificar por permisos múltiples
    if (props.permissions && props.permissions.length > 0) {
        return props.requireAll
            ? props.permissions.every(permission => can(user.value, permission))
            : canAny(user.value, props.permissions)
    }

    return false
})
</script>
