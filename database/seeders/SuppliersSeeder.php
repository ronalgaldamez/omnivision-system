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
                'name'          => 'Electrónica del Norte',
                'contact_name'  => 'Carlos Méndez',
                'phone'         => '2233-4455',
                'nrc'           => 'NRC-123456-7',
                'nit'           => '0614-010203-104-5',
                'bank_accounts' => "Banco Agrícola: 001-23456-7\nBanco Cuscatlán: 002-78901-2",
                'email'         => 'ventas@electronicadelnorte.com',
                'address'       => 'Boulevard Los Próceres, San Salvador',
            ],
            [
                'name'          => 'Distribuidora Técnica S.A.',
                'contact_name'  => 'Ana Rodríguez',
                'phone'         => '2266-7788',
                'nrc'           => 'NRC-234567-8',
                'nit'           => '0614-020304-105-6',
                'bank_accounts' => "Banco Agrícola: 003-34567-8\nBanco Promérica: 004-56789-0",
                'email'         => 'pedidos@distribuidoratecnica.com',
                'address'       => 'Calle La Reforma, Santa Tecla',
            ],
            [
                'name'          => 'Suministros Industriales',
                'contact_name'  => 'Roberto Guevara',
                'phone'         => '2299-0011',
                'nrc'           => 'NRC-345678-9',
                'nit'           => '0614-030405-106-7',
                'bank_accounts' => "Banco Cuscatlán: 005-67890-1",
                'email'         => 'ventas@suministrosindustrial.com',
                'address'       => 'Carretera a Sonsonate, km 12',
            ],
            [
                'name'          => 'Cables y Conexiones',
                'contact_name'  => 'Marta López',
                'phone'         => '2244-5566',
                'nrc'           => 'NRC-456789-0',
                'nit'           => '0614-040506-107-8',
                'bank_accounts' => "Banco Agrícola: 006-78901-2\nBanco Hipotecario: 007-89012-3",
                'email'         => 'info@cablesyconexiones.com',
                'address'       => 'Colonia Escalón, San Salvador',
            ],
            [
                'name'          => 'TecnoRedes S.A. de C.V.',
                'contact_name'  => 'Luis Hernández',
                'phone'         => '2277-8899',
                'nrc'           => 'NRC-567890-1',
                'nit'           => '0614-050607-108-9',
                'bank_accounts' => "Banco Promérica: 008-90123-4",
                'email'         => 'ventas@tecnoredes.com',
                'address'       => 'Zona Franca, San Miguel',
            ],
        ];

        foreach ($suppliers as $supplier) {
            Supplier::updateOrCreate(
                ['name' => $supplier['name']], // busca por nombre
                $supplier                      // si no existe, lo crea con estos datos
            );
        }

        $this->command->info('✅ Proveedores actualizados con NRC, NIT y cuentas bancarias sin duplicados.');
    }
}