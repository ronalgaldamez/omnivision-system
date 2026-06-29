<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Supplier;

class SuppliersSeeder extends Seeder
{
    public function run()
    {
        $suppliers = [
            ['name' => 'Electrónica del Norte', 'contact_name' => 'Carlos Méndez', 'phones' => ['2233-4455'], 'nrc' => '12345678', 'nit' => '123456789', 'bank_accounts' => [['bank_name' => 'Banco Agrícola', 'account_number' => '001234567'], ['bank_name' => 'Banco Cuscatlán', 'account_number' => '002789012']], 'email' => 'ventas@electronicadelnorte.com', 'address' => 'Boulevard Los Próceres, San Salvador'],
            ['name' => 'Distribuidora Técnica S.A.', 'contact_name' => 'Ana Rodríguez', 'phones' => ['2266-7788'], 'nrc' => '23456789', 'nit' => '234567890', 'bank_accounts' => [['bank_name' => 'Banco Agrícola', 'account_number' => '003345678'], ['bank_name' => 'Banco Promérica', 'account_number' => '004567890']], 'email' => 'pedidos@distribuidoratecnica.com', 'address' => 'Calle La Reforma, Santa Tecla'],
            ['name' => 'Suministros Industriales', 'contact_name' => 'Roberto Guevara', 'phones' => ['2299-0011'], 'nrc' => '34567890', 'nit' => '345678901', 'bank_accounts' => [['bank_name' => 'Banco Cuscatlán', 'account_number' => '005678901']], 'email' => 'ventas@suministrosindustrial.com', 'address' => 'Carretera a Sonsonate, km 12'],
            ['name' => 'Cables y Conexiones', 'contact_name' => 'Marta López', 'phones' => ['2244-5566'], 'nrc' => '45678901', 'nit' => '456789012', 'bank_accounts' => [['bank_name' => 'Banco Agrícola', 'account_number' => '006789012'], ['bank_name' => 'Banco Hipotecario', 'account_number' => '007890123']], 'email' => 'info@cablesyconexiones.com', 'address' => 'Colonia Escalón, San Salvador'],
            ['name' => 'TecnoRedes S.A. de C.V.', 'contact_name' => 'Luis Hernández', 'phones' => ['2277-8899'], 'nrc' => '56789012', 'nit' => '567890123', 'bank_accounts' => [['bank_name' => 'Banco Promérica', 'account_number' => '008901234']], 'email' => 'ventas@tecnoredes.com', 'address' => 'Zona Franca, San Miguel'],
        ];

        foreach ($suppliers as $supplier) {
            Supplier::updateOrCreate(['name' => $supplier['name']], $supplier);
        }

        $this->command->info('Proveedores actualizados.');
    }
}
