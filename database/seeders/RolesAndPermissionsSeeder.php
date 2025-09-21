<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use App\Models\User;

class RolesAndPermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Crear permisos específicos para el sistema
        $permissions = [
            // Dashboard
            'dashboard.view',

            // Gestión de Productos
            'productos.manage',
            'productos.create',
            'productos.edit',
            'productos.delete',
            'productos.view',

            // Gestión de Categorías
            'categorias.manage',
            'categorias.create',
            'categorias.edit',
            'categorias.delete',
            'categorias.view',

            // Gestión de Marcas
            'marcas.manage',
            'marcas.create',
            'marcas.edit',
            'marcas.delete',
            'marcas.view',

            // Gestión de Clientes
            'clientes.manage',
            'clientes.create',
            'clientes.edit',
            'clientes.delete',
            'clientes.view',

            // Gestión de Ventas
            'ventas.manage',
            'ventas.create',
            'ventas.edit',
            'ventas.delete',
            'ventas.view',

            // Gestión de Compras
            'compras.manage',
            'compras.create',
            'compras.edit',
            'compras.delete',
            'compras.view',

            // Gestión de Proveedores
            'proveedores.manage',
            'proveedores.create',
            'proveedores.edit',
            'proveedores.delete',
            'proveedores.view',

            // Reportes
            'reportes.view',

            // Administración del sistema
            'admin.manage',
            'users.manage',
            'roles.manage',
        ];

        // Crear permisos
        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'web']);
        }

        // Crear roles
        $adminRole = Role::firstOrCreate(['name' => 'Administrador', 'guard_name' => 'web']);
        $operadorRole = Role::firstOrCreate(['name' => 'Operador', 'guard_name' => 'web']);

        // Asignar TODOS los permisos al Administrador
        $adminRole->givePermissionTo(Permission::all());

        // Asignar permisos específicos al Operador
        $operadorPermissions = [
            'dashboard.view',
            'clientes.manage',
            'clientes.create',
            'clientes.edit',
            'clientes.view',
            'ventas.manage',
            'ventas.create',
            'ventas.edit',
            'ventas.view',
            'productos.view', // Solo puede ver productos, no gestionarlos
        ];

        $operadorRole->givePermissionTo($operadorPermissions);

        // Migrar usuarios existentes según su id_rol actual
        $roleMapping = [
            1 => 'Administrador', // id_rol 1 = Administrador
            2 => 'Operador',      // id_rol 2 = Operador
        ];

        foreach (User::whereNotNull('id_rol')->get() as $user) {
            $roleName = $roleMapping[$user->id_rol] ?? null;
            if ($roleName) {
                $user->assignRole($roleName);
                echo "Usuario {$user->name} asignado al rol: {$roleName}\n";
            }
        }

        echo "Seeder completado exitosamente!\n";
        echo "Roles creados: Administrador, Operador\n";
        echo "Permisos creados: " . count($permissions) . " permisos\n";
    }
}
