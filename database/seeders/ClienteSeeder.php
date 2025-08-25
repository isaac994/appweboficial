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
                'telefono' => '+1 (555) 123-4567',
                'direccion' => '123 Main Street, Los Angeles, CA 90210',
                'correo_electronico' => 'juan.perez@email.com',
                'fecha_nacimiento' => '1985-03-15',
                'genero' => 'M'
            ],
            [
                'nombre' => 'María González',
                'telefono' => '+1 (555) 234-5678',
                'direccion' => '456 Oak Avenue, San Francisco, CA 94102',
                'correo_electronico' => 'maria.gonzalez@email.com',
                'fecha_nacimiento' => '1990-07-22',
                'genero' => 'F'
            ],
            [
                'nombre' => 'Carlos Rodríguez',
                'telefono' => '+1 (555) 345-6789',
                'direccion' => '789 Pine Street, San Diego, CA 92101',
                'correo_electronico' => 'carlos.rodriguez@email.com',
                'fecha_nacimiento' => '1988-11-08',
                'genero' => 'M'
            ],
            [
                'nombre' => 'Ana Martínez',
                'telefono' => '+1 (555) 456-7890',
                'direccion' => '321 Elm Street, Oakland, CA 94601',
                'correo_electronico' => 'ana.martinez@email.com',
                'fecha_nacimiento' => '1992-04-12',
                'genero' => 'F'
            ],
            [
                'nombre' => 'Luis Hernández',
                'telefono' => '+1 (555) 567-8901',
                'direccion' => '654 Maple Drive, San Jose, CA 95112',
                'correo_electronico' => 'luis.hernandez@email.com',
                'fecha_nacimiento' => '1987-09-30',
                'genero' => 'M'
            ],
            [
                'nombre' => 'Sofia López',
                'telefono' => '+1 (555) 678-9012',
                'direccion' => '987 Cedar Lane, Fresno, CA 93721',
                'correo_electronico' => 'sofia.lopez@email.com',
                'fecha_nacimiento' => '1995-01-18',
                'genero' => 'F'
            ],
            [
                'nombre' => 'Roberto Silva',
                'telefono' => '+1 (555) 789-0123',
                'direccion' => '147 Birch Road, Sacramento, CA 95814',
                'correo_electronico' => 'roberto.silva@email.com',
                'fecha_nacimiento' => '1983-12-05',
                'genero' => 'M'
            ],
            [
                'nombre' => 'Carmen Torres',
                'telefono' => '+1 (555) 890-1234',
                'direccion' => '258 Spruce Court, Long Beach, CA 90802',
                'correo_electronico' => 'carmen.torres@email.com',
                'fecha_nacimiento' => '1989-06-14',
                'genero' => 'F'
            ],
            [
                'nombre' => 'Miguel Castro',
                'telefono' => '+1 (555) 901-2345',
                'direccion' => '369 Willow Way, Santa Ana, CA 92701',
                'correo_electronico' => 'miguel.castro@email.com',
                'fecha_nacimiento' => '1991-08-25',
                'genero' => 'M'
            ],
            [
                'nombre' => 'Isabella Morales',
                'telefono' => '+1 (555) 012-3456',
                'direccion' => '741 Aspen Place, Anaheim, CA 92801',
                'correo_electronico' => 'isabella.morales@email.com',
                'fecha_nacimiento' => '1993-02-28',
                'genero' => 'F'
            ],
            [
                'nombre' => 'Diego Jiménez',
                'telefono' => '+1 (555) 123-4567',
                'direccion' => '852 Poplar Street, Riverside, CA 92501',
                'correo_electronico' => 'diego.jimenez@email.com',
                'fecha_nacimiento' => '1986-05-10',
                'genero' => 'M'
            ],
            [
                'nombre' => 'Valentina Ruiz',
                'telefono' => '+1 (555) 234-5678',
                'direccion' => '963 Sycamore Avenue, Stockton, CA 95202',
                'correo_electronico' => 'valentina.ruiz@email.com',
                'fecha_nacimiento' => '1994-10-03',
                'genero' => 'F'
            ],
            [
                'nombre' => 'Alejandro Vargas',
                'telefono' => '+1 (555) 345-6789',
                'direccion' => '159 Magnolia Drive, Bakersfield, CA 93301',
                'correo_electronico' => 'alejandro.vargas@email.com',
                'fecha_nacimiento' => '1984-07-17',
                'genero' => 'M'
            ],
            [
                'nombre' => 'Camila Herrera',
                'telefono' => '+1 (555) 456-7890',
                'direccion' => '753 Cypress Lane, Irvine, CA 92602',
                'correo_electronico' => 'camila.herrera@email.com',
                'fecha_nacimiento' => '1996-12-20',
                'genero' => 'F'
            ],
            [
                'nombre' => 'Daniel Mendoza',
                'telefono' => '+1 (555) 567-8901',
                'direccion' => '486 Juniper Road, Modesto, CA 95350',
                'correo_electronico' => 'daniel.mendoza@email.com',
                'fecha_nacimiento' => '1982-11-11',
                'genero' => 'M'
            ]
        ];

        foreach ($clientes as $cliente) {
            Cliente::create($cliente);
        }
    }
}
