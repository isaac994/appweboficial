<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Producto;

class ProductoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $productos = [
            [
                'nombre' => 'iPhone 15 Pro',
                'descripcion' => 'El último iPhone con características avanzadas',
                'precio_venta' => 1299.99,
                'id_categoria' => 1,
                'id_marca' => 1,
                'img_url' => null,
                'estado' => 'activo'
            ],
            [
                'nombre' => 'Samsung Galaxy S24',
                'descripcion' => 'Smartphone Android de alta gama',
                'precio_venta' => 1199.99,
                'id_categoria' => 1,
                'id_marca' => 2,
                'img_url' => null,
                'estado' => 'activo'
            ],
            [
                'nombre' => 'MacBook Air M2',
                'descripcion' => 'Laptop ultraportátil con chip M2',
                'precio_venta' => 999.99,
                'id_categoria' => 2,
                'id_marca' => 1,
                'img_url' => null,
                'estado' => 'activo'
            ],
            [
                'nombre' => 'Dell XPS 13',
                'descripcion' => 'Laptop premium con pantalla InfinityEdge',
                'precio_venta' => 1299.99,
                'id_categoria' => 2,
                'id_marca' => 3,
                'img_url' => null,
                'estado' => 'activo'
            ],
            [
                'nombre' => 'iPad Air',
                'descripcion' => 'Tablet versátil para trabajo y entretenimiento',
                'precio_venta' => 899.99,
                'id_categoria' => 3,
                'id_marca' => 1,
                'img_url' => null,
                'estado' => 'activo'
            ],
            [
                'nombre' => 'Samsung Galaxy Tab S9',
                'descripcion' => 'Tablet Android de alto rendimiento',
                'precio_venta' => 399.99,
                'id_categoria' => 3,
                'id_marca' => 2,
                'img_url' => null,
                'estado' => 'activo'
            ],
            [
                'nombre' => 'AirPods Pro',
                'descripcion' => 'Auriculares inalámbricos con cancelación de ruido',
                'precio_venta' => 299.99,
                'id_categoria' => 4,
                'id_marca' => 1,
                'img_url' => null,
                'estado' => 'activo'
            ],
            [
                'nombre' => 'Sony WH-1000XM5',
                'descripcion' => 'Auriculares over-ear con cancelación de ruido líder',
                'precio_venta' => 49.99,
                'id_categoria' => 4,
                'id_marca' => 4,
                'img_url' => null,
                'estado' => 'activo'
            ],
            [
                'nombre' => 'Cable USB-C',
                'descripcion' => 'Cable de carga y datos USB-C de alta calidad',
                'precio_venta' => 29.99,
                'id_categoria' => 5,
                'id_marca' => 5,
                'img_url' => null,
                'estado' => 'activo'
            ],
            [
                'nombre' => 'Cargador Inalámbrico',
                'descripcion' => 'Cargador inalámbrico de 15W para smartphones',
                'precio_venta' => 599.99,
                'id_categoria' => 5,
                'id_marca' => 6,
                'img_url' => null,
                'estado' => 'activo'
            ],
            [
                'nombre' => 'Monitor LG 27" 4K',
                'descripcion' => 'Monitor profesional con resolución 4K',
                'precio_venta' => 449.99,
                'id_categoria' => 6,
                'id_marca' => 7,
                'img_url' => null,
                'estado' => 'activo'
            ],
            [
                'nombre' => 'Teclado Mecánico RGB',
                'descripcion' => 'Teclado mecánico con switches Cherry MX y RGB',
                'precio_venta' => 399.99,
                'id_categoria' => 6,
                'id_marca' => 8,
                'img_url' => null,
                'estado' => 'activo'
            ],
            [
                'nombre' => 'Mouse Gaming',
                'descripcion' => 'Mouse gaming con sensor de alta precisión',
                'precio_venta' => 24.99,
                'id_categoria' => 6,
                'id_marca' => 9,
                'img_url' => null,
                'estado' => 'activo'
            ],
            [
                'nombre' => 'Webcam HD',
                'descripcion' => 'Webcam de alta definición para videoconferencias',
                'precio_venta' => 14.99,
                'id_categoria' => 6,
                'id_marca' => 10,
                'img_url' => null,
                'estado' => 'activo'
            ],
            [
                'nombre' => 'Disco Duro Externo 2TB',
                'descripcion' => 'Disco duro externo portátil de 2TB',
                'precio_venta' => 99.99,
                'id_categoria' => 7,
                'id_marca' => 11,
                'img_url' => null,
                'estado' => 'activo'
            ],
            [
                'nombre' => 'Memoria USB 128GB',
                'descripcion' => 'Memoria USB de alta velocidad 128GB',
                'precio_venta' => 189.99,
                'id_categoria' => 7,
                'id_marca' => 12,
                'img_url' => null,
                'estado' => 'activo'
            ]
        ];

        foreach ($productos as $producto) {
            Producto::create($producto);
        }
    }
}
