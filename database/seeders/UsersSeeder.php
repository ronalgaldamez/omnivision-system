<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Branch;
use Illuminate\Support\Facades\Hash;

class UsersSeeder extends Seeder
{
    public function run(): void
    {
        $password = Hash::make('password');
        $branches = Branch::pluck('id', 'code');

        // ── Core (3 perfiles) ──
        $users = [
            ['name' => 'Administrador', 'email' => 'admin@omnivision.com', 'role' => 'admin', 'branch_code' => null],
            ['name' => 'Ronal Galdamez', 'email' => 'ronal@omnivision.com', 'role' => 'admin', 'branch_code' => null],
            ['name' => 'Walter Marín', 'email' => 'supervisor@omnivision.com', 'role' => 'field_supervisor', 'branch_code' => null],
            ['name' => 'Deyvi Alas', 'email' => 'noc@omnivision.com', 'role' => 'noc', 'branch_code' => null],
        ];

        // ── Técnicos — globales (branch_code = null) ──
        $tecnicos = [
            'KEVIN MAURICIO FUNES AQUINO',
            'OSCAR ORLANDO CRUZ MELARA',
            'ALDAIR HUMBERTO ESCOBAR CRUZ',
            'CARLOS AGUSTIN LOPEZ CHACON',
            'JHONATAN EDENILSON MARTÍNEZ CRUZ',
            'GIOVANY OLIVA',
            'RONALD AYALA',
            'LUIS MANUEL CARDOZA MARTINEZ',
            'MARVIN CASTRO',
            'JULIO CESAR GUERRA LINARES',
            'JULIO ANTONIO MOLINA SANTOS',
            'STANLEY PEREZ',
            'RAFAEL MENJIVAR',
            'JORGE LUIS CALLES CARBAJAL',
            'CRISTIAN ALEJANDRO RAMIREZ SANTOS',
            'MARIO OSCAR SORIANO SANCHEZ',
            'MARCOS TULIO RODRIGUEZ RODRIGUEZ',
            'LUIS ARMANDO HERNANDEZ LEON',
            'WILLIAM ERNESTO GUARDADO AREVALO',
            'ENRIQUE ALEXANDER VALLE MENJIVAR',
        ];

        foreach ($tecnicos as $nombre) {
            $nombre = mb_convert_case(mb_strtolower($nombre), MB_CASE_TITLE, 'UTF-8');
            $users[] = ['name' => $nombre, 'email' => $this->email($nombre, 'tech'), 'role' => 'technician', 'branch_code' => null];
        }

        // ── Ventas — globales ──
        $ventas = [
            'KARLA MARIBEL RAMIREZ TREJOS',
            'PATRICIA GUADALUPE GUARDADO MEJÍA',
            'WALTER ARMANDO MENJIVAR GRANDE',
        ];

        foreach ($ventas as $nombre) {
            $nombre = mb_convert_case(mb_strtolower($nombre), MB_CASE_TITLE, 'UTF-8');
            $users[] = ['name' => $nombre, 'email' => $this->email($nombre, 'sr'), 'role' => 'sales_rep', 'branch_code' => null];
        }

        // ── SAC por sucursal ──
        $sac = [
            ['Yancy Marleny Solis de Alvarado', 'MATRIZ'],
            ['Karen Lissette Lopez de Aguilar', 'MATRIZ'],
            ['Kenya Marcela Leiva Tejada', null],   // global — rota entre Amayo/Aguilares
            ['Vicky Dinora Gutierrez Salguero', null],   // global — rota entre La Palma/Amayo
            ['Elsy Hernández', null],   // global — rota entre Amayo/La Palma/Aguilares
            ['Andrea Ochoa', null],   // desconocida
            ['Susana Solis', null],   // desconocida
            ['Emelina Landaverde', null],   // desconocida
        ];

        foreach ($sac as [$nombre, $branchCode]) {
            $users[] = ['name' => $nombre, 'email' => $this->email($nombre, 'sac'), 'role' => 'atencion_al_cliente', 'branch_code' => $branchCode];
        }

        // ── Staff de Contratos (prueba) ──
        $users[] = ['name' => 'Staff Contratos', 'email' => 'staff@omnivision.com', 'role' => 'contracts_staff', 'branch_code' => 'MATRIZ'];

        foreach ($users as $data) {
            $branchId = $data['branch_code']
                ? ($branches[$data['branch_code']] ?? null)
                : null;

            $user = User::firstOrCreate(
                ['email' => $data['email']],
                [
                    'name' => $data['name'],
                    'password' => $password,
                    'branch_id' => $branchId,
                ]
            );
            $user->assignRole($data['role']);

            if ($user->branch_id !== $branchId) {
                $user->update(['branch_id' => $branchId]);
            }
        }

        $this->command->info('Usuarios creados: ' . count($users));
    }

    private function email(string $name, string $prefix = ''): string
    {
        $parts = explode(' ', strtolower($name));
        $first = $parts[0] ?? 'usuario';
        $last = $parts[count($parts) - 1] ?? $first;
        $prefix = $prefix ? $prefix . '.' : '';
        return $prefix . $first . '.' . $last . '@omnivision.com';
    }
}
