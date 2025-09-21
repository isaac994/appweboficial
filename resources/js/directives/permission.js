import { can, hasRole, hasAnyRole, canAny } from '@/utils/permissions'

export default {
    mounted(el, binding) {
        const { value, modifiers } = binding
        const user = window.Laravel?.user || null

        if (!user) {
            el.style.display = 'none'
            return
        }

        let hasAccess = false

        if (typeof value === 'string') {
            // Verificar permiso individual
            hasAccess = can(user, value)
        } else if (Array.isArray(value)) {
            // Verificar múltiples permisos
            hasAccess = modifiers.all
                ? value.every(permission => can(user, permission))
                : canAny(user, value)
        } else if (typeof value === 'object' && value !== null) {
            // Verificar por rol o permiso
            if (value.role) {
                hasAccess = hasRole(user, value.role)
            } else if (value.roles) {
                hasAccess = modifiers.all
                    ? value.roles.every(role => hasRole(user, role))
                    : hasAnyRole(user, value.roles)
            } else if (value.permission) {
                hasAccess = can(user, value.permission)
            } else if (value.permissions) {
                hasAccess = modifiers.all
                    ? value.permissions.every(permission => can(user, permission))
                    : canAny(user, value.permissions)
            }
        }

        if (!hasAccess) {
            el.style.display = 'none'
        }
    },

    updated(el, binding) {
        // Re-evaluar cuando el binding cambie
        this.mounted(el, binding)
    }
}
