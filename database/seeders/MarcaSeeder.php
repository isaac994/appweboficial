<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Marca;

class MarcaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $marcas = [
            [
                'nombre' => 'Apple',
                'pais_origen' => 'Estados Unidos'
            ],
            [
                'nombre' => 'Samsung',
                'pais_origen' => 'Corea del Sur'
            ],
            [
                'nombre' => 'Xiaomi',
                'pais_origen' => 'China'
            ],
            [
                'nombre' => 'Huawei',
                'pais_origen' => 'China'
            ],
            [
                'nombre' => 'OnePlus',
                'pais_origen' => 'China'
            ],
            [
                'nombre' => 'Google',
                'pais_origen' => 'Estados Unidos'
            ],
            [
                'nombre' => 'Sony',
                'pais_origen' => 'Japón'
            ],
            [
                'nombre' => 'LG',
                'pais_origen' => 'Corea del Sur'
            ],
            [
                'nombre' => 'Motorola',
                'pais_origen' => 'Estados Unidos'
            ],
            [
                'nombre' => 'Nokia',
                'pais_origen' => 'Finlandia'
            ],
            [
                'nombre' => 'Realme',
                'pais_origen' => 'China'
            ],
            [
                'nombre' => 'Oppo',
                'pais_origen' => 'China'
            ],
            [
                'nombre' => 'Vivo',
                'pais_origen' => 'China'
            ],
            [
                'nombre' => 'Honor',
                'pais_origen' => 'China'
            ],
            [
                'nombre' => 'ASUS',
                'pais_origen' => 'Taiwán'
            ]
        ];

        foreach ($marcas as $marca) {
            Marca::create($marca);
        }
    }
}
