<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Categoria;

class CategoriaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categorias = [
            [
                'nombre' => 'Smartphones',
                'descripcion' => 'Teléfonos inteligentes de última generación'
            ],
            [
                'nombre' => 'Tablets',
                'descripcion' => 'Tablets y dispositivos táctiles'
            ],
            [
                'nombre' => 'Accesorios',
                'descripcion' => 'Carcasas, protectores y accesorios varios'
            ],
            [
                'nombre' => 'Auriculares',
                'descripcion' => 'Auriculares inalámbricos y con cable'
            ],
            [
                'nombre' => 'Cargadores',
                'descripcion' => 'Cargadores, cables y adaptadores'
            ],
            [
                'nombre' => 'Smartwatches',
                'descripcion' => 'Relojes inteligentes y wearables'
            ],
            [
                'nombre' => 'Repuestos',
                'descripcion' => 'Pantallas, baterías y repuestos'
            ],
            [
                'nombre' => 'Gaming',
                'descripcion' => 'Accesorios para gaming móvil'
            ]
        ];

        foreach ($categorias as $categoria) {
            Categoria::create($categoria);
        }
    }
}
