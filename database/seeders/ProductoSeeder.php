<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
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
                'nombre' => 'iPhone 15 Pro Max',
                'descripcion' => 'El iPhone más avanzado con chip A17 Pro, cámara de 48MP y pantalla de 6.7"',
                'precio_compra' => 899.99,
                'precio_venta' => 1199.99,
                'id_categoria' => 1, // Smartphones
                'id_marca' => 1, // Apple
                'id_proveedor' => 1, // TechSupply Pro
                'img_url' => 'https://images.unsplash.com/photo-1592750475338-74b7b21085ab?w=400'
            ],
            [
                'nombre' => 'Samsung Galaxy S24 Ultra',
                'descripcion' => 'Flagship de Samsung con S Pen integrado y cámara de 200MP',
                'precio_compra' => 799.99,
                'precio_venta' => 1099.99,
                'id_categoria' => 1, // Smartphones
                'id_marca' => 2, // Samsung
                'id_proveedor' => 2, // Mobile World Distributors
                'img_url' => 'https://images.unsplash.com/photo-1610945265064-0e34e5519bbf?w=400'
            ],
            [
                'nombre' => 'Xiaomi 14 Pro',
                'descripcion' => 'Potente smartphone con Leica Optics y Snapdragon 8 Gen 3',
                'precio_compra' => 599.99,
                'precio_venta' => 799.99,
                'id_categoria' => 1, // Smartphones
                'id_marca' => 3, // Xiaomi
                'id_proveedor' => 3, // Digital Devices Inc
                'img_url' => 'https://images.unsplash.com/photo-1592899677977-9c10ca588bbd?w=400'
            ],
            [
                'nombre' => 'iPad Pro 12.9"',
                'descripcion' => 'Tablet profesional con chip M2 y pantalla Liquid Retina XDR',
                'precio_compra' => 899.99,
                'precio_venta' => 1199.99,
                'id_categoria' => 2, // Tablets
                'id_marca' => 1, // Apple
                'id_proveedor' => 1, // TechSupply Pro
                'img_url' => 'https://images.unsplash.com/photo-1544244015-0df4b3ffc6b0?w=400'
            ],
            [
                'nombre' => 'Samsung Galaxy Tab S9 Ultra',
                'descripcion' => 'Tablet Android premium con S Pen y pantalla de 14.6"',
                'precio_compra' => 699.99,
                'precio_venta' => 899.99,
                'id_categoria' => 2, // Tablets
                'id_marca' => 2, // Samsung
                'id_proveedor' => 2, // Mobile World Distributors
                'img_url' => 'https://images.unsplash.com/photo-1585790050237-1f3b9d6d1b2a?w=400'
            ],
            [
                'nombre' => 'AirPods Pro 2',
                'descripcion' => 'Auriculares inalámbricos con cancelación de ruido activa',
                'precio_compra' => 199.99,
                'precio_venta' => 249.99,
                'id_categoria' => 4, // Auriculares
                'id_marca' => 1, // Apple
                'id_proveedor' => 1, // TechSupply Pro
                'img_url' => 'https://images.unsplash.com/photo-1606220588913-b3aacb4d2f46?w=400'
            ],
            [
                'nombre' => 'Samsung Galaxy Buds2 Pro',
                'descripcion' => 'Auriculares inalámbricos con sonido Hi-Fi 24bit',
                'precio_compra' => 149.99,
                'precio_venta' => 199.99,
                'id_categoria' => 4, // Auriculares
                'id_marca' => 2, // Samsung
                'id_proveedor' => 2, // Mobile World Distributors
                'img_url' => 'https://images.unsplash.com/photo-1590658268037-6bf12165a8df?w=400'
            ],
            [
                'nombre' => 'Cargador USB-C 65W',
                'descripcion' => 'Cargador rápido compatible con múltiples dispositivos',
                'precio_compra' => 19.99,
                'precio_venta' => 29.99,
                'id_categoria' => 5, // Cargadores
                'id_marca' => null,
                'id_proveedor' => 4, // Smart Gadgets Co
                'img_url' => 'https://images.unsplash.com/photo-1609592806598-ef155da6d42a?w=400'
            ],
            [
                'nombre' => 'Cable Lightning Premium',
                'descripcion' => 'Cable de carga rápida para dispositivos Apple',
                'precio_compra' => 9.99,
                'precio_venta' => 19.99,
                'id_categoria' => 5, // Cargadores
                'id_marca' => 1, // Apple
                'id_proveedor' => 1, // TechSupply Pro
                'img_url' => 'https://images.unsplash.com/photo-1586953208448-b95a79798f07?w=400'
            ],
            [
                'nombre' => 'Apple Watch Series 9',
                'descripcion' => 'Reloj inteligente con monitor cardíaco y GPS',
                'precio_compra' => 299.99,
                'precio_venta' => 399.99,
                'id_categoria' => 6, // Smartwatches
                'id_marca' => 1, // Apple
                'id_proveedor' => 1, // TechSupply Pro
                'img_url' => 'https://images.unsplash.com/photo-1544117519-31a4b719223d?w=400'
            ],
            [
                'nombre' => 'Samsung Galaxy Watch 6',
                'descripcion' => 'Smartwatch Android con Wear OS y monitor de salud',
                'precio_compra' => 249.99,
                'precio_venta' => 349.99,
                'id_categoria' => 6, // Smartwatches
                'id_marca' => 2, // Samsung
                'id_proveedor' => 2, // Mobile World Distributors
                'img_url' => 'https://images.unsplash.com/photo-1579586337278-3befd40fd17a?w=400'
            ],
            [
                'nombre' => 'Carcasa iPhone 15 Pro',
                'descripcion' => 'Carcasa protectora de silicona para iPhone 15 Pro',
                'precio_compra' => 14.99,
                'precio_venta' => 29.99,
                'id_categoria' => 3, // Accesorios
                'id_marca' => 1, // Apple
                'id_proveedor' => 6, // Phone Accessories Ltd
                'img_url' => 'https://images.unsplash.com/photo-1603313014512-71c1c8b1e3b9?w=400'
            ],
            [
                'nombre' => 'Protector de Pantalla Galaxy S24',
                'descripcion' => 'Protector de cristal templado para Samsung Galaxy S24',
                'precio_compra' => 4.99,
                'precio_venta' => 12.99,
                'id_categoria' => 3, // Accesorios
                'id_marca' => 2, // Samsung
                'id_proveedor' => 6, // Phone Accessories Ltd
                'img_url' => 'https://images.unsplash.com/photo-1586953208448-b95a79798f07?w=400'
            ],
            [
                'nombre' => 'Batería iPhone 14 Pro',
                'descripcion' => 'Batería de reemplazo original para iPhone 14 Pro',
                'precio_compra' => 49.99,
                'precio_venta' => 79.99,
                'id_categoria' => 7, // Repuestos
                'id_marca' => 1, // Apple
                'id_proveedor' => 7, // Mobile Solutions
                'img_url' => 'https://images.unsplash.com/photo-1609592806598-ef155da6d42a?w=400'
            ],
            [
                'nombre' => 'Pantalla Samsung Galaxy S23',
                'descripcion' => 'Pantalla de reemplazo para Samsung Galaxy S23',
                'precio_compra' => 89.99,
                'precio_venta' => 149.99,
                'id_categoria' => 7, // Repuestos
                'id_marca' => 2, // Samsung
                'id_proveedor' => 7, // Mobile Solutions
                'img_url' => 'https://images.unsplash.com/photo-1586953208448-b95a79798f07?w=400'
            ]
        ];

        foreach ($productos as $producto) {
            Producto::create($producto);
        }
    }
}
