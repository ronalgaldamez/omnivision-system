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
                'name' => 'Grupo Q Elektra S.A. de C.V.',
                'contact_name' => 'Ricardo Quintanilla',
                'phones' => ['2210-5000', '2210-5001'],
                'nrc' => '101234-5',
                'nit' => '0614-120890-101-5',
                'email' => 'ventas@grupoqelektra.com.sv',
                'address' => 'Alameda Roosevelt y 49 Av. Sur, San Salvador',
                'bank_accounts' => [
                    ['bank_name' => 'Banco Agrícola', 'account_number' => '0123456789'],
                    ['bank_name' => 'Banco Cuscatlán', 'account_number' => '0234567890'],
                ],
            ],
            [
                'name' => 'Distribuidora Electro Industrial S.A.',
                'contact_name' => 'Karla Menjívar',
                'phones' => ['2248-9000', '2248-9001'],
                'nrc' => '102345-6',
                'nit' => '0614-150591-202-6',
                'email' => 'kmenjivar@deisa.com.sv',
                'address' => 'Calle San Antonio Abad, Colonia Roma, San Salvador',
                'bank_accounts' => [
                    ['bank_name' => 'Banco Agrícola', 'account_number' => '0345678901'],
                    ['bank_name' => 'Banco Promérica', 'account_number' => '0456789012'],
                ],
            ],
            [
                'name' => 'Cablevisión y Comunicaciones S.A.',
                'contact_name' => 'Mauricio Herrera',
                'phones' => ['2265-4321'],
                'nrc' => '103456-7',
                'nit' => '0614-180392-303-7',
                'email' => 'mauricio.herrera@cablecom.sv',
                'address' => 'Boulevard Los Próceres #1234, Antiguo Cuscatlán, La Libertad',
                'bank_accounts' => [
                    ['bank_name' => 'Banco Cuscatlán', 'account_number' => '0567890123'],
                ],
            ],
            [
                'name' => 'Redes y Sistemas S.A. de C.V.',
                'contact_name' => 'Alejandro Flores',
                'phones' => ['2257-8901'],
                'nrc' => '104567-8',
                'nit' => '0614-210493-404-8',
                'email' => 'ventas@redesysistemas.com.sv',
                'address' => 'Colonia San Benito, Calle La Mascota #45, San Salvador',
                'bank_accounts' => [
                    ['bank_name' => 'Banco Promérica', 'account_number' => '0678901234'],
                    ['bank_name' => 'Banco Hipotecario', 'account_number' => '0789012345'],
                ],
            ],
            [
                'name' => 'Importaciones Técnicas de El Salvador',
                'contact_name' => 'Sofía Renderos',
                'phones' => ['2278-3456'],
                'nrc' => '105678-9',
                'nit' => '0614-250594-505-9',
                'email' => 'srenderos@importec.sv',
                'address' => 'Avenida Jerusalén, Colonia Satélite, Santa Tecla, La Libertad',
                'bank_accounts' => [
                    ['bank_name' => 'Banco Agrícola', 'account_number' => '0890123456'],
                ],
            ],
            [
                'name' => 'Suministros Eléctricos de Oriente S.A.',
                'contact_name' => 'José Armando Pineda',
                'phones' => ['2661-2345', '2661-2346'],
                'nrc' => '106789-0',
                'nit' => '0614-300695-606-0',
                'email' => 'japineda@suministrosoriente.com.sv',
                'address' => '6a Calle Poniente #34, Barrio El Centro, San Miguel',
                'bank_accounts' => [
                    ['bank_name' => 'Banco Cuscatlán', 'account_number' => '0901234567'],
                    ['bank_name' => 'Banco Agrícola', 'account_number' => '1012345678'],
                ],
            ],
            [
                'name' => 'Tecnología Aplicada S.A. (TECAPSA)',
                'contact_name' => 'Ana Cecilia Turcios',
                'phones' => ['2442-5678'],
                'nrc' => '107890-1',
                'nit' => '0614-120796-707-1',
                'email' => 'acturcios@tecapsa.com.sv',
                'address' => 'Final Calle El Mirador, Colonia Escalón, San Salvador',
                'bank_accounts' => [
                    ['bank_name' => 'Banco Promérica', 'account_number' => '1123456789'],
                    ['bank_name' => 'Banco Hipotecario', 'account_number' => '1234567890'],
                ],
            ],
            [
                'name' => 'Materiales Eléctricos de Occidente',
                'contact_name' => 'Héctor Lara',
                'phones' => ['2453-7890'],
                'nrc' => '108901-2',
                'nit' => '0614-150897-808-2',
                'email' => 'hlara@meoccidente.com.sv',
                'address' => '4a Calle Oriente #12, Santa Ana',
                'bank_accounts' => [
                    ['bank_name' => 'Banco Agrícola', 'account_number' => '1345678901'],
                ],
            ],
            [
                'name' => 'Comercializadora Global Tech S.A.',
                'contact_name' => 'Fernando Aguilera',
                'phones' => ['2298-4567'],
                'nrc' => '109012-3',
                'nit' => '0614-010198-909-3',
                'email' => 'faguilera@globaltech.sv',
                'address' => 'Avenida Las Palmas #8, Colonia San Francisco, San Salvador',
                'bank_accounts' => [
                    ['bank_name' => 'Banco Cuscatlán', 'account_number' => '1456789012'],
                    ['bank_name' => 'Banco Promérica', 'account_number' => '1567890123'],
                ],
            ],
            [
                'name' => 'Proveedora de Cableados y Redes S.A.',
                'contact_name' => 'Mónica Zelaya',
                'phones' => ['2260-1234'],
                'nrc' => '109123-4',
                'nit' => '0614-200299-010-4',
                'email' => 'mzelaya@procable.com.sv',
                'address' => 'Calle El Pedregal, Urbanización Madresalva, Soyapango, San Salvador',
                'bank_accounts' => [
                    ['bank_name' => 'Banco Hipotecario', 'account_number' => '1678901234'],
                ],
            ],
        ];

        foreach ($suppliers as $supplier) {
            Supplier::updateOrCreate(['name' => $supplier['name']], $supplier);
        }

        $this->command->info('Proveedores salvadoreños actualizados.');
    }
}
