<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Hash;

class TestUserCreationCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test:user-creation';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test user creation functionality';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🧪 Probando creación de usuarios...');
        $this->newLine();

        try {
            // Obtener rol de operador
            $operadorRole = Role::where('name', 'Operador')->first();

            if (!$operadorRole) {
                $this->error('❌ No se encontró el rol Operador');
                return;
            }

            $this->line("   🎭 Rol encontrado: {$operadorRole->name} (ID: {$operadorRole->id})");

            // Crear usuario de prueba
            $user = User::create([
                'name' => 'Usuario Prueba',
                'email' => 'prueba@test.com',
                'password' => Hash::make('password123'),
                'estado' => 'activo',
                'id_rol' => $operadorRole->id,
            ]);

            $this->info("   ✅ Usuario creado: {$user->name} (ID: {$user->id})");
            $this->line("   📧 Email: {$user->email}");
            $this->line("   🎭 ID Rol: {$user->id_rol}");
            $this->line("   📊 Estado: {$user->estado}");

            // Asignar rol usando Spatie
            $user->assignRole('Operador');
            $this->line("   🔑 Rol asignado: " . $user->getRoleNames()->implode(', '));

            // Limpiar usuario de prueba
            $user->delete();
            $this->line("   🗑️ Usuario de prueba eliminado");

            $this->newLine();
            $this->info('✅ Prueba de creación de usuarios exitosa!');

        } catch (\Exception $e) {
            $this->error('❌ Error: ' . $e->getMessage());
            $this->line('   Archivo: ' . $e->getFile());
            $this->line('   Línea: ' . $e->getLine());
        }
    }
}
