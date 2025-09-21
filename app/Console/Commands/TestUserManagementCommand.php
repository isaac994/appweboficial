<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use Spatie\Permission\Models\Role;

class TestUserManagementCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test:user-management';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test user management functionality';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🧪 Probando funcionalidad de gestión de usuarios...');
        $this->newLine();

        // Probar usuarios y estados
        $users = User::with('roles')->get();

        foreach ($users as $user) {
            $this->info("👤 Usuario: {$user->name}");
            $this->line("   📧 Email: {$user->email}");
            $this->line("   🎭 Roles: " . $user->getRoleNames()->implode(', '));
            $this->line("   📊 Estado: {$user->estado}");
            $this->line("   ✅ Está activo: " . ($user->isActivo() ? 'SÍ' : 'NO'));
            $this->line("   🔑 Permisos: " . $user->getAllPermissions()->pluck('name')->implode(', '));
            $this->newLine();
        }

        // Probar funcionalidad de activar/desactivar
        $this->info('🔄 Probando funcionalidad de activar/desactivar...');

        $testUser = User::where('email', 'moises@gmail.com')->first();
        if ($testUser) {
            $this->line("   Usuario de prueba: {$testUser->name}");
            $this->line("   Estado actual: {$testUser->estado}");

            // Desactivar
            $testUser->desactivar();
            $this->line("   ✅ Desactivado: " . ($testUser->fresh()->estado === 'inactivo' ? 'SÍ' : 'NO'));

            // Activar
            $testUser->activar();
            $this->line("   ✅ Activado: " . ($testUser->fresh()->estado === 'activo' ? 'SÍ' : 'NO'));
        }

        $this->newLine();
        $this->info('✅ Prueba de gestión de usuarios completada exitosamente!');

        // Mostrar estadísticas
        $this->newLine();
        $this->info('📊 Estadísticas:');
        $this->line("   👥 Total usuarios: " . User::count());
        $this->line("   ✅ Usuarios activos: " . User::activos()->count());
        $this->line("   ❌ Usuarios inactivos: " . User::inactivos()->count());
        $this->line("   👑 Administradores: " . User::role('Administrador')->count());
        $this->line("   👷 Operadores: " . User::role('Operador')->count());
    }
}
