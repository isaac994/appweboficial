<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class ResetPasswordCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'user:reset-password {email} {password}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Reset user password by email';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $email = $this->argument('email');
        $password = $this->argument('password');

        $user = User::where('email', $email)->first();

        if (!$user) {
            $this->error("❌ Usuario con email '{$email}' no encontrado.");
            return 1;
        }

        $user->password = Hash::make($password);
        $user->save();

        $this->info("✅ Contraseña actualizada exitosamente para: {$user->name}");
        $this->line("📧 Email: {$user->email}");
        $this->line("🔑 Nueva contraseña: {$password}");

        return 0;
    }
}
