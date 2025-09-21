/**
 * Utilidades para verificar permisos y roles en el frontend
 */

/**
 * Verifica si el usuario tiene un permiso específico
 * @param {Object} user - Usuario autenticado
 * @param {string} permission - Nombre del permiso
 * @returns {boolean}
 */
export function can(user, permission) {
    if (!user || !user.permissions) return false
    return user.permissions.includes(permission)
}

/**
 * Verifica si el usuario tiene alguno de los permisos especificados
 * @param {Object} user - Usuario autenticado
 * @param {Array<string>} permissions - Array de permisos
 * @returns {boolean}
 */
export function canAny(user, permissions) {
    if (!user || !user.permissions) return false
    return permissions.some(permission => user.permissions.includes(permission))
}

/**
 * Verifica si el usuario tiene todos los permisos especificados
 * @param {Object} user - Usuario autenticado
 * @param {Array<string>} permissions - Array de permisos
 * @returns {boolean}
 */
export function canAll(user, permissions) {
    if (!user || !user.permissions) return false
    return permissions.every(permission => user.permissions.includes(permission))
}

/**
 * Verifica si el usuario tiene un rol específico
 * @param {Object} user - Usuario autenticado
 * @param {string} role - Nombre del rol
 * @returns {boolean}
 */
export function hasRole(user, role) {
    if (!user || !user.roles) return false
    return user.roles.includes(role)
}

/**
 * Verifica si el usuario tiene alguno de los roles especificados
 * @param {Object} user - Usuario autenticado
 * @param {Array<string>} roles - Array de roles
 * @returns {boolean}
 */
export function hasAnyRole(user, roles) {
    if (!user || !user.roles) return false
    return roles.some(role => user.roles.includes(role))
}

/**
 * Verifica si el usuario es administrador
 * @param {Object} user - Usuario autenticado
 * @returns {boolean}
 */
export function isAdmin(user) {
    return hasRole(user, 'Administrador')
}

/**
 * Verifica si el usuario es operador
 * @param {Object} user - Usuario autenticado
 * @returns {boolean}
 */
export function isOperator(user) {
    return hasRole(user, 'Operador')
}

/**
 * Verifica si el usuario puede gestionar productos
 * @param {Object} user - Usuario autenticado
 * @returns {boolean}
 */
export function canManageProducts(user) {
    return can(user, 'productos.manage') || isAdmin(user)
}

/**
 * Verifica si el usuario puede gestionar clientes
 * @param {Object} user - Usuario autenticado
 * @returns {boolean}
 */
export function canManageClients(user) {
    return can(user, 'clientes.manage') || isAdmin(user)
}

/**
 * Verifica si el usuario puede gestionar ventas
 * @param {Object} user - Usuario autenticado
 * @returns {boolean}
 */
export function canManageSales(user) {
    return can(user, 'ventas.manage') || isAdmin(user)
}

/**
 * Verifica si el usuario puede ver reportes
 * @param {Object} user - Usuario autenticado
 * @returns {boolean}
 */
export function canViewReports(user) {
    return can(user, 'reportes.view') || isAdmin(user)
}

/**
 * Constantes de permisos para facilitar el uso
 */
export const PERMISSIONS = {
    // Dashboard
    DASHBOARD_VIEW: 'dashboard.view',

    // Productos
    PRODUCTOS_MANAGE: 'productos.manage',
    PRODUCTOS_CREATE: 'productos.create',
    PRODUCTOS_EDIT: 'productos.edit',
    PRODUCTOS_DELETE: 'productos.delete',
    PRODUCTOS_VIEW: 'productos.view',

    // Categorías
    CATEGORIAS_MANAGE: 'categorias.manage',
    CATEGORIAS_CREATE: 'categorias.create',
    CATEGORIAS_EDIT: 'categorias.edit',
    CATEGORIAS_DELETE: 'categorias.delete',
    CATEGORIAS_VIEW: 'categorias.view',

    // Marcas
    MARCAS_MANAGE: 'marcas.manage',
    MARCAS_CREATE: 'marcas.create',
    MARCAS_EDIT: 'marcas.edit',
    MARCAS_DELETE: 'marcas.delete',
    MARCAS_VIEW: 'marcas.view',

    // Clientes
    CLIENTES_MANAGE: 'clientes.manage',
    CLIENTES_CREATE: 'clientes.create',
    CLIENTES_EDIT: 'clientes.edit',
    CLIENTES_DELETE: 'clientes.delete',
    CLIENTES_VIEW: 'clientes.view',

    // Ventas
    VENTAS_MANAGE: 'ventas.manage',
    VENTAS_CREATE: 'ventas.create',
    VENTAS_EDIT: 'ventas.edit',
    VENTAS_DELETE: 'ventas.delete',
    VENTAS_VIEW: 'ventas.view',

    // Compras
    COMPRAS_MANAGE: 'compras.manage',
    COMPRAS_CREATE: 'compras.create',
    COMPRAS_EDIT: 'compras.edit',
    COMPRAS_DELETE: 'compras.delete',
    COMPRAS_VIEW: 'compras.view',

    // Proveedores
    PROVEEDORES_MANAGE: 'proveedores.manage',
    PROVEEDORES_CREATE: 'proveedores.create',
    PROVEEDORES_EDIT: 'proveedores.edit',
    PROVEEDORES_DELETE: 'proveedores.delete',
    PROVEEDORES_VIEW: 'proveedores.view',

    // Reportes
    REPORTES_VIEW: 'reportes.view',

    // Administración
    ADMIN_MANAGE: 'admin.manage',
    USERS_MANAGE: 'users.manage',
    ROLES_MANAGE: 'roles.manage',
}

/**
 * Constantes de roles para facilitar el uso
 */
export const ROLES = {
    ADMINISTRADOR: 'Administrador',
    OPERADOR: 'Operador',
}
