<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;

class FixAdminUser extends Command
{
    protected $signature = 'user:fix-admin';
    protected $description = 'Verificar y corregir el usuario admin';

    public function handle()
    {
        $user = User::where('email', 'admin@admin.com')->first();
        
        if (!$user) {
            $this->error('Usuario admin no encontrado');
            return 1;
        }

        $this->info('Usuario encontrado: ' . $user->name);
        $this->info('Rol actual: ' . ($user->rol ?? 'NULL'));
        
        if ($user->rol !== 'Propietario') {
            $user->rol = 'Propietario';
            $user->estado = 'activo';
            $user->save();
            $this->info('✓ Rol actualizado a Propietario');
        }
        
        $permissions = $user->getPermissions();
        $this->info('Permisos: ' . count($permissions) . ' permisos');
        $this->info('Tiene dashboard.view: ' . ($user->hasPermissionTo('dashboard.view') ? 'Sí' : 'No'));
        
        return 0;
    }
}

