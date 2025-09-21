<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Cliente;

class ClienteSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $clientes = [
            [
                'nombre' => 'Juan Carlos Pérez',
                'telefono' => '+591 755-12345'
            ],
            [
                'nombre' => 'María González',
                'telefono' => '+591 755-23456'
            ],
            [
                'nombre' => 'Carlos Rodríguez',
                'telefono' => '+591 755-34567'
            ],
            [
                'nombre' => 'Ana Martínez',
                'telefono' => '+591 755-45678'
            ],
            [
                'nombre' => 'Luis Hernández',
                'telefono' => '+591 755-56789'
            ],
            [
                'nombre' => 'Sofia López',
                'telefono' => '+591 755-67890'
            ],
            [
                'nombre' => 'Roberto Silva',
                'telefono' => '+591 755-78901'
            ],
            [
                'nombre' => 'Carmen Torres',
                'telefono' => '+591 755-89012'
            ],
            [
                'nombre' => 'Miguel Castro',
                'telefono' => '+591 755-90123'
            ],
            [
                'nombre' => 'Isabella Morales',
                'telefono' => '+591 755-01234'
            ],
            [
                'nombre' => 'Diego Jiménez',
                'telefono' => '+591 755-12345'
            ],
            [
                'nombre' => 'Valentina Ruiz',
                'telefono' => '+591 755-23456'
            ],
            [
                'nombre' => 'Alejandro Vargas',
                'telefono' => '+591 755-34567'
            ],
            [
                'nombre' => 'Camila Herrera',
                'telefono' => '+591 755-45678'
            ],
            [
                'nombre' => 'Daniel Mendoza',
                'telefono' => '+591 755-56789'
            ]
        ];

        foreach ($clientes as $cliente) {
            Cliente::create($cliente);
        }
    }
}
