<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Supplier;

class SuppliersSeeder extends Seeder
{
    public function run()
    {
        $suppliers = [
            [
                'name' => 'Electrónica del Norte',
                'contact_name' => 'Carlos Méndez',
                'phone' => '2233-4455',
                'email' => 'ventas@electronicadelnorte.com',
                'address' => 'Boulevard Los Próceres, San Salvador',
            ],
            [
                'name' => 'Distribuidora Técnica S.A.',
                'contact_name' => 'Ana Rodríguez',
                'phone' => '2266-7788',
                'email' => 'pedidos@distribuidoratecnica.com',
                'address' => 'Calle La Reforma, Santa Tecla',
            ],
            [
                'name' => 'Suministros Industriales',
                'contact_name' => 'Roberto Guevara',
                'phone' => '2299-0011',
                'email' => 'ventas@suministrosindustrial.com',
                'address' => 'Carretera a Sonsonate, km 12',
            ],
            [
                'name' => 'Cables y Conexiones',
                'contact_name' => 'Marta López',
                'phone' => '2244-5566',
                'email' => 'info@cablesyconexiones.com',
                'address' => 'Colonia Escalón, San Salvador',
            ],
            [
                'name' => 'TecnoRedes S.A. de C.V.',
                'contact_name' => 'Luis Hernández',
                'phone' => '2277-8899',
                'email' => 'ventas@tecnoredes.com',
                'address' => 'Zona Franca, San Miguel',
            ],
        ];

        foreach ($suppliers as $supplier) {
            Supplier::create($supplier);
        }

        $this->command->info('✅ Proveedores de ejemplo creados.');
    }
}