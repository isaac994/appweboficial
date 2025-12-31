<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use App\Models\Compra;
use App\Models\Producto;

class RestaurarComprasProductos extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'compras:restaurar-productos {--productos=*}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Restaura compras eliminadas de productos específicos que tienen ventas asociadas';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Buscando compras eliminadas de productos con ventas...');

        // Buscar productos por nombre/modelo
        $productosBuscar = [
            'Redmi Note 13 Pro+ 5G',
            'Galaxy A55'
        ];

        // Si se pasaron productos como argumento, usarlos
        if ($this->option('productos')) {
            $productosBuscar = $this->option('productos');
        }

        $productosIds = [];
        foreach ($productosBuscar as $nombreProducto) {
            // Buscar por modelo o descripción
            $producto = Producto::whereHas('modelo', function($q) use ($nombreProducto) {
                $q->where('nombre', 'like', '%' . $nombreProducto . '%');
            })->orWhere('descripcion', 'like', '%' . $nombreProducto . '%')
            ->first();

            if ($producto) {
                $productosIds[] = $producto->id_producto;
                $this->info("Producto encontrado: {$nombreProducto} (ID: {$producto->id_producto})");
            } else {
                $this->warn("Producto no encontrado: {$nombreProducto}");
            }
        }

        if (empty($productosIds)) {
            $this->error('No se encontraron los productos especificados.');
            return 1;
        }

        // Buscar compras eliminadas que contengan estos productos
        $comprasEliminadas = Compra::where('estado', true)
            ->whereHas('detalles', function($q) use ($productosIds) {
                $q->whereIn('id_producto', $productosIds);
            })
            ->with(['detalles.producto.modelo', 'detalles.producto.marca'])
            ->get();

        if ($comprasEliminadas->isEmpty()) {
            $this->info('No se encontraron compras eliminadas para estos productos.');
            return 0;
        }

        $this->info("\nEncontradas " . $comprasEliminadas->count() . " compra(s) eliminada(s):");
        foreach ($comprasEliminadas as $compra) {
            $productos = $compra->detalles->map(function($detalle) {
                return ($detalle->producto->modelo->nombre ?? $detalle->producto->descripcion ?? 'Sin nombre') .
                       " (Cantidad: {$detalle->cantidad})";
            })->implode(', ');

            $this->line("  - Compra #{$compra->id_compra} - Productos: {$productos}");
        }

        // Verificar si estas compras tienen ventas asociadas
        $comprasConVentas = [];
        foreach ($comprasEliminadas as $compra) {
            foreach ($compra->detalles as $detalle) {
                $ventasActivas = DB::table('detalle_ventas')
                    ->join('ventas', 'detalle_ventas.id_venta', '=', 'ventas.id_venta')
                    ->where('detalle_ventas.id_producto', $detalle->id_producto)
                    ->where('ventas.estado', false)
                    ->sum('detalle_ventas.cantidad');

                if ($ventasActivas > 0 && in_array($detalle->producto->modelo->nombre ?? '', $productosBuscar)) {
                    if (!isset($comprasConVentas[$compra->id_compra])) {
                        $comprasConVentas[$compra->id_compra] = $compra;
                    }
                }
            }
        }

        if (empty($comprasConVentas)) {
            $this->warn('Las compras encontradas no tienen ventas activas asociadas a estos productos.');
            return 0;
        }

        $this->info("\nCompras que serán restauradas (tienen ventas asociadas):");
        foreach ($comprasConVentas as $compra) {
            $productos = $compra->detalles->map(function($detalle) {
                return ($detalle->producto->modelo->nombre ?? $detalle->producto->descripcion ?? 'Sin nombre');
            })->implode(', ');

            $this->line("  - Compra #{$compra->id_compra}: {$productos}");
        }

        if (!$this->confirm('¿Deseas restaurar estas compras?', true)) {
            $this->info('Operación cancelada.');
            return 0;
        }

        // Restaurar las compras
        DB::beginTransaction();
        try {
            $restauradas = 0;
            foreach ($comprasConVentas as $compra) {
                $compra->update([
                    'estado' => false,
                    'fecha_eliminacion' => null
                ]);
                $restauradas++;
                $this->info("✓ Compra #{$compra->id_compra} restaurada exitosamente.");
            }

            DB::commit();
            $this->info("\n✓ Se restauraron {$restauradas} compra(s) exitosamente.");
            return 0;

        } catch (\Exception $e) {
            DB::rollBack();
            $this->error('Error al restaurar las compras: ' . $e->getMessage());
            return 1;
        }
    }
}

















