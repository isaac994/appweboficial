<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Crear usuario admin por defecto si no existe
        $adminExists = User::where('email', 'admin@admin.com')->exists();

        if (!$adminExists) {
            User::create([
                'name' => 'Administrador',
                'apellidos' => 'Sistema',
                'email' => 'admin@admin.com',
                'password' => Hash::make('password'),
                'rol' => 'Propietario',
                'estado' => 'activo',
                'ci' => '0000000',
                'telefono' => '',
                'direccion' => '',
                'email_verified_at' => now(),
            ]);

            $this->command->info('✓ Usuario admin creado exitosamente');
            $this->command->info('  Email: admin@admin.com');
            $this->command->info('  Password: password');
        } else {
            $this->command->info('ℹ El usuario admin ya existe (admin@admin.com)');
        }
    }
}
