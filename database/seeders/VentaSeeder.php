<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Cliente;
use App\Models\Producto;
use App\Models\Venta;
use App\Models\DetalleVenta;

class VentaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $clientes = Cliente::all();
        $productos = Producto::all();

        if ($clientes->isEmpty() || $productos->isEmpty()) {
            return;
        }

        // Crear algunas ventas de ejemplo
        for ($i = 0; $i < 15; $i++) {
            $cliente = $clientes->random();
            $estados = ['pendiente', 'procesado', 'enviado', 'entregado'];
            $estado = $estados[array_rand($estados)];

            $venta = Venta::create([
                'id_cliente' => $cliente->id_cliente,
                'estado' => $estado,
                'notas' => 'Venta de ejemplo generada por el seeder',
                'created_at' => now()->subDays(rand(1, 30))
            ]);

            // Agregar productos a la venta
            $numProductos = rand(1, 3);

            for ($j = 0; $j < $numProductos; $j++) {
                $producto = $productos->random();
                $cantidad = rand(1, 2);
                $precioUnitario = $producto->precio_venta;

                DetalleVenta::create([
                    'id_venta' => $venta->id_venta,
                    'id_producto' => $producto->id_producto,
                    'cantidad' => $cantidad,
                    'precio_unitario' => $precioUnitario
                ]);

                $totalVenta += $subtotal;
            }


        }
    }
}
