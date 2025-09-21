import { computed } from 'vue'
import { usePage } from '@inertiajs/vue3'

export function useAuth() {
    const page = usePage()

    const user = computed(() => page.props.auth?.user || null)

    const isAuthenticated = computed(() => !!user.value)

    const hasRole = (role) => {
        if (!user.value || !user.value.roles) return false
        return user.value.roles.includes(role)
    }

    const hasAnyRole = (roles) => {
        if (!user.value || !user.value.roles) return false
        return roles.some(role => user.value.roles.includes(role))
    }

    const hasPermission = (permission) => {
        if (!user.value || !user.value.permissions) return false
        return user.value.permissions.includes(permission)
    }

    const hasAnyPermission = (permissions) => {
        if (!user.value || !user.value.permissions) return false
        return permissions.some(permission => user.value.permissions.includes(permission))
    }

    const can = (permission) => hasPermission(permission)

    const isAdmin = computed(() => hasRole('Administrador'))
    const isOperator = computed(() => hasRole('Operador'))

    return {
        user,
        isAuthenticated,
        hasRole,
        hasAnyRole,
        hasPermission,
        hasAnyPermission,
        can,
        isAdmin,
        isOperator
    }
}
