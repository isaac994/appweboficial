<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use App\Models\User;
use App\Models\StaticRole;

class RolesAndPermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Crear permisos básicos
        $permissions = [
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

        // Crear permisos
        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'web']);
        }

        // Asignar rol de Propietario al primer usuario (si existe)
        $firstUser = User::first();
        if ($firstUser && !$firstUser->hasRole(StaticRole::ADMINISTRADOR)) {
            $firstUser->assignRole(StaticRole::ADMINISTRADOR);
            echo "Usuario {$firstUser->name} asignado como Propietario\n";
        }

        echo "Seeder completado: Permisos creados y roles estáticos configurados\n";
    }
}
