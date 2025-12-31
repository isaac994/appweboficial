<?php

namespace App\Models;

class StaticRole
{
    const ADMINISTRADOR = 'Propietario';
    const OPERADOR = 'Operador';

    /**
     * Obtener todos los roles disponibles
     */
    public static function all()
    {
        return [
            [
                'id' => 1,
                'name' => self::ADMINISTRADOR,
                'guard_name' => 'web',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 2,
                'name' => self::OPERADOR,
                'guard_name' => 'web',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];
    }

    /**
     * Obtener un rol por ID
     */
    public static function find($id)
    {
        $roles = self::all();
        return collect($roles)->firstWhere('id', $id);
    }

    /**
     * Obtener un rol por nombre
     */
    public static function findByName($name)
    {
        $roles = self::all();
        return collect($roles)->firstWhere('name', $name);
    }

    /**
     * Verificar si un rol existe
     */
    public static function exists($name)
    {
        return in_array($name, [self::ADMINISTRADOR, self::OPERADOR]);
    }

    /**
     * Obtener los permisos de un rol
     */
    public static function getPermissions($roleName)
    {
        // Normalizar el nombre del rol
        $roleName = trim($roleName);
        
        switch ($roleName) {
            case self::ADMINISTRADOR:
            case 'Propietario':
                return [
                    'dashboard.view',
                    'productos.manage',
                    'productos.create',
                    'productos.edit',
                    'productos.delete',
                    'productos.view',
                    'categorias.manage',
                    'categorias.create',
                    'categorias.edit',
                    'categorias.delete',
                    'categorias.view',
                    'marcas.manage',
                    'marcas.create',
                    'marcas.edit',
                    'marcas.delete',
                    'marcas.view',
                    'clientes.manage',
                    'clientes.create',
                    'clientes.edit',
                    'clientes.delete',
                    'clientes.view',
                    'ventas.manage',
                    'ventas.create',
                    'ventas.edit',
                    'ventas.delete',
                    'ventas.view',
                    'compras.manage',
                    'compras.create',
                    'compras.edit',
                    'compras.delete',
                    'compras.view',
                    'proveedores.manage',
                    'proveedores.create',
                    'proveedores.edit',
                    'proveedores.delete',
                    'proveedores.view',
                    'inventario.view',
                    'inventario.movimientos',
                    'inventario.reportes',
                    'reportes.view',
                    'admin.manage',
                    'users.manage',
                    'roles.manage',
                ];
            case self::OPERADOR:
            case 'Operador':
                return [
                    'dashboard.view',
                    'clientes.manage',
                    'clientes.create',
                    'clientes.edit',
                    'clientes.view',
                    'ventas.manage',
                    'ventas.create',
                    'ventas.edit',
                    'ventas.view',
                    'compras.manage',
                    'compras.create',
                    'compras.edit',
                    'compras.view',
                    'inventario.view',
                    'inventario.movimientos',
                    'inventario.reportes',
                ];
            default:
                return [];
        }
    }
}
