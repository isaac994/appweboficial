<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;

class TestRolesCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test:roles';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test roles and permissions functionality';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🧪 Probando funcionalidad de roles y permisos...');
        $this->newLine();

        // Probar usuarios
        $users = User::with('roles')->get();

        foreach ($users as $user) {
            $this->info("👤 Usuario: {$user->name}");
            $this->line("   📧 Email: {$user->email}");
            $this->line("   🎭 Roles: " . $user->getRoleNames()->implode(', '));
            $this->line("   🔑 Permisos: " . $user->getAllPermissions()->pluck('name')->implode(', '));

            // Probar métodos específicos
            $this->line("   ✅ Es Administrador: " . ($user->hasRole('Administrador') ? 'SÍ' : 'NO'));
            $this->line("   ✅ Es Operador: " . ($user->hasRole('Operador') ? 'SÍ' : 'NO'));
            $this->line("   ✅ Puede ver dashboard: " . ($user->can('dashboard.view') ? 'SÍ' : 'NO'));
            $this->line("   ✅ Puede gestionar productos: " . ($user->can('productos.manage') ? 'SÍ' : 'NO'));
            $this->line("   ✅ Puede gestionar clientes: " . ($user->can('clientes.manage') ? 'SÍ' : 'NO'));
            $this->line("   ✅ Puede gestionar ventas: " . ($user->can('ventas.manage') ? 'SÍ' : 'NO'));
            $this->newLine();
        }

        $this->info('✅ Prueba completada exitosamente!');
    }
}
