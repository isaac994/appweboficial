<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Producto;

class UpdateProductImageUrls extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'productos:update-image-urls';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Actualizar URLs de imágenes de productos para incluir el dominio completo';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Actualizando URLs de imágenes de productos...');

        $productos = Producto::whereNotNull('img_url')
            ->where('img_url', 'not like', 'http%')
            ->get();

        $updated = 0;

        foreach ($productos as $producto) {
            $oldUrl = $producto->img_url;
            $producto->img_url = url($producto->img_url);
            $producto->save();

            $this->line("Producto {$producto->nombre}: {$oldUrl} -> {$producto->img_url}");
            $updated++;
        }

        $this->info("Se actualizaron {$updated} productos.");

        return 0;
    }
}
