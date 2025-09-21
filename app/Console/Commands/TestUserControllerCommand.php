<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Http\Controllers\UserController;
use App\Models\User;
use Spatie\Permission\Models\Role;

class TestUserControllerCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test:user-controller';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test UserController functionality';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🧪 Probando UserController...');
        $this->newLine();

        // Simular request
        request()->merge([
            'search' => '',
            'estado' => '',
            'rol' => ''
        ]);

        $controller = new UserController();

        try {
            $result = $controller->index();
            $this->info('✅ Controlador ejecutado exitosamente');

            // Verificar datos
            $users = User::with('roles')->paginate(10);
            $this->line("   👥 Usuarios paginados: " . $users->count());
            $this->line("   📄 Total páginas: " . $users->lastPage());
            $this->line("   📊 Total usuarios: " . $users->total());

            // Verificar enlaces de paginación
            $links = $users->linkCollection();
            $this->line("   🔗 Enlaces de paginación: " . $links->count());

            foreach ($links as $link) {
                $this->line("   🔗 Enlace: {$link['label']} - URL: " . ($link['url'] ?? 'NULL'));
            }

        } catch (\Exception $e) {
            $this->error('❌ Error en controlador: ' . $e->getMessage());
            $this->line('   Archivo: ' . $e->getFile());
            $this->line('   Línea: ' . $e->getLine());
        }
    }
}
