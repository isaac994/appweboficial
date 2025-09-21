<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Proveedor;

class ProveedorSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $proveedores = [
            [
                'nombre' => 'Distribuidora Central S.A.',
                'ci_nit' => '123456789',
                'telefono' => '+591 2 123456'
            ],
            [
                'nombre' => 'Importadora del Norte Ltda.',
                'ci_nit' => '987654321',
                'telefono' => '+591 3 654321'
            ],
            [
                'nombre' => 'Comercial Sur EIRL',
                'ci_nit' => '456789123',
                'telefono' => '+591 4 789123'
            ],
            [
                'nombre' => 'Mayorista Express',
                'ci_nit' => '789123456',
                'telefono' => '+591 7 123789'
            ],
            [
                'nombre' => 'Proveedora Nacional',
                'ci_nit' => '321654987',
                'telefono' => '+591 6 456987'
            ]
        ];

        foreach ($proveedores as $proveedor) {
            Proveedor::create($proveedor);
        }
    }
}
