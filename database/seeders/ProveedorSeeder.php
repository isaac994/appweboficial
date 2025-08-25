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
                'nombre' => 'TechSupply Pro',
                'telefono' => '+1 (555) 123-4567',
                'direccion' => '123 Tech Street, Silicon Valley, CA 94025',
                'correo' => 'contact@techsupplypro.com'
            ],
            [
                'nombre' => 'Mobile World Distributors',
                'telefono' => '+1 (555) 987-6543',
                'direccion' => '456 Mobile Ave, Los Angeles, CA 90210',
                'correo' => 'sales@mobileworld.com'
            ],
            [
                'nombre' => 'Digital Devices Inc',
                'telefono' => '+1 (555) 456-7890',
                'direccion' => '789 Digital Blvd, San Francisco, CA 94102',
                'correo' => 'info@digitaldevices.com'
            ],
            [
                'nombre' => 'Smart Gadgets Co',
                'telefono' => '+1 (555) 321-0987',
                'direccion' => '321 Smart Way, San Diego, CA 92101',
                'correo' => 'hello@smartgadgets.com'
            ],
            [
                'nombre' => 'Electronics Plus',
                'telefono' => '+1 (555) 654-3210',
                'direccion' => '654 Electronics Rd, Oakland, CA 94601',
                'correo' => 'sales@electronicsplus.com'
            ],
            [
                'nombre' => 'Phone Accessories Ltd',
                'telefono' => '+1 (555) 789-0123',
                'direccion' => '789 Accessories St, San Jose, CA 95112',
                'correo' => 'contact@phoneaccessories.com'
            ],
            [
                'nombre' => 'Mobile Solutions',
                'telefono' => '+1 (555) 012-3456',
                'direccion' => '012 Solutions Ave, Fresno, CA 93721',
                'correo' => 'info@mobilesolutions.com'
            ],
            [
                'nombre' => 'Tech Importers',
                'telefono' => '+1 (555) 345-6789',
                'direccion' => '345 Import St, Sacramento, CA 95814',
                'correo' => 'sales@techimporters.com'
            ]
        ];

        foreach ($proveedores as $proveedor) {
            Proveedor::create($proveedor);
        }
    }
}
