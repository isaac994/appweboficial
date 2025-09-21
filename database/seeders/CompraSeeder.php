<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Compra;
use App\Models\DetalleCompra;
use App\Models\Producto;
use App\Models\Proveedor;
use App\Models\User;

class CompraSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Obtener usuarios, proveedores y productos existentes
        $usuarios = User::all();
        $proveedores = Proveedor::all();
        $productos = Producto::all();

        if ($usuarios->isEmpty() || $proveedores->isEmpty() || $productos->isEmpty()) {
            $this->command->warn('No se pueden crear compras sin usuarios, proveedores o productos.');
            return;
        }

        // Crear 10 compras de ejemplo
        for ($i = 0; $i < 10; $i++) {
            $proveedor = $proveedores->random();
            $usuario = $usuarios->random();
            $fecha = now()->subDays(rand(1, 30));

            // Crear la compra
            $compra = Compra::create([
                'id_proveedor' => $proveedor->id_proveedor,
                'id_usuario' => $usuario->id,
                'fecha' => $fecha
            ]);

            // Agregar 1-5 productos a la compra
            $numProductos = rand(1, 5);
            $productosCompra = $productos->random($numProductos);

            foreach ($productosCompra as $producto) {
                $cantidad = rand(1, 10);
                // Usar un precio base basado en el precio de venta (aproximadamente 70% del precio de venta)
                $precioBase = $producto->precio_venta * 0.7;
                $precioUnitario = $precioBase * (1 + (rand(-10, 20) / 100)); // Variación de ±10-20%

                DetalleCompra::create([
                    'id_compra' => $compra->id_compra,
                    'id_producto' => $producto->id_producto,
                    'cantidad' => $cantidad,
                    'precio_unitario' => round($precioUnitario, 2)
                ]);
            }


        }

        $this->command->info('Compras de ejemplo creadas exitosamente.');
    }
}
