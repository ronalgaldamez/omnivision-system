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

        $users = [
            // ── Superadmins (globales) ──
            ['name' => 'Administrador',  'email' => 'admin@omnivision.com',   'role' => 'admin',          'branch_code' => null],
            ['name' => 'Admin Respaldo', 'email' => 'admin@test.com',          'role' => 'admin',          'branch_code' => null],
            ['name' => 'Contador',       'email' => 'contabilidad@omnivision.com', 'role' => 'accountant', 'branch_code' => null],
            ['name' => 'Supervisor',     'email' => 'supervisor@omnivision.com', 'role' => 'field_supervisor', 'branch_code' => null],

            // ── Casa Matriz Chalatenango ──
            ['name' => 'Bodeguero Matriz', 'email' => 'bodega@omnivision.com',   'role' => 'warehouse', 'branch_code' => 'MATRIZ'],
            ['name' => 'Comprador Matriz', 'email' => 'compras@omnivision.com',   'role' => 'buyer',     'branch_code' => 'MATRIZ'],
            ['name' => 'SAC Matriz',       'email' => 'sac@omnivision.com',       'role' => 'atencion_al_cliente', 'branch_code' => 'MATRIZ'],
            ['name' => 'NOC Matriz',       'email' => 'noc@omnivision.com',       'role' => 'noc',       'branch_code' => 'MATRIZ'],
            ['name' => 'Técnico Matriz',   'email' => 'tecnico1@omnivision.com',  'role' => 'technician', 'branch_code' => 'MATRIZ'],
            ['name' => 'Vendedor Matriz',  'email' => 'vendedor1@omnivision.com', 'role' => 'sales_rep',  'branch_code' => 'MATRIZ'],

            // ── Sucursal Concepción Quezaltepeque ──
            ['name' => 'SAC CQ',         'email' => 'sac_cq@omnivision.com',       'role' => 'atencion_al_cliente', 'branch_code' => 'CQ'],
            ['name' => 'Técnico CQ',     'email' => 'tecnico_cq@omnivision.com',   'role' => 'technician', 'branch_code' => 'CQ'],
            ['name' => 'Vendedor CQ',    'email' => 'vendedor_cq@omnivision.com',  'role' => 'sales_rep',  'branch_code' => 'CQ'],

            // ── Sucursal Amayo ──
            ['name' => 'SAC Amayo',      'email' => 'sac_amayo@omnivision.com',      'role' => 'atencion_al_cliente', 'branch_code' => 'AMAYO'],
            ['name' => 'NOC Amayo',      'email' => 'noc_amayo@omnivision.com',      'role' => 'noc',       'branch_code' => 'AMAYO'],
            ['name' => 'Técnico Amayo',  'email' => 'tecnico_amayo@omnivision.com',  'role' => 'technician', 'branch_code' => 'AMAYO'],
            ['name' => 'Vendedor Amayo', 'email' => 'vendedor_amayo@omnivision.com', 'role' => 'sales_rep',  'branch_code' => 'AMAYO'],

            // ── Sucursal Aguilares ──
            ['name' => 'SAC Aguilares',      'email' => 'sac_aguilares@omnivision.com',      'role' => 'atencion_al_cliente', 'branch_code' => 'AGUILARES'],
            ['name' => 'Técnico Aguilares',  'email' => 'tecnico_aguilares@omnivision.com',  'role' => 'technician', 'branch_code' => 'AGUILARES'],
            ['name' => 'Vendedor Aguilares', 'email' => 'vendedor_aguilares@omnivision.com', 'role' => 'sales_rep',  'branch_code' => 'AGUILARES'],

            // ── Sucursal La Palma ──
            ['name' => 'Admin La Palma',     'email' => 'admin_lapalma@omnivision.com',  'role' => 'branch_admin', 'branch_code' => 'PALMA'],
            ['name' => 'SAC La Palma',       'email' => 'sac_lapalma@omnivision.com',    'role' => 'atencion_al_cliente', 'branch_code' => 'PALMA'],
            ['name' => 'Técnico La Palma',   'email' => 'tecnico_lapalma@omnivision.com','role' => 'technician', 'branch_code' => 'PALMA'],
            ['name' => 'Vendedor La Palma',  'email' => 'vendedor_lapalma@omnivision.com','role' => 'sales_rep', 'branch_code' => 'PALMA'],

            // ── Sucursal San Pablo Tacachico ──
            ['name' => 'SAC SPT',           'email' => 'sac_spt@omnivision.com',         'role' => 'atencion_al_cliente', 'branch_code' => 'SMP'],
            ['name' => 'Técnico SPT',       'email' => 'tecnico_spt@omnivision.com',     'role' => 'technician', 'branch_code' => 'SMP'],
            ['name' => 'Vendedor SPT',      'email' => 'vendedor_spt@omnivision.com',    'role' => 'sales_rep',  'branch_code' => 'SMP'],

            // ── Globales (sin sucursal fija) ──
            ['name' => 'SAC Soporte',       'email' => 'soporte@omnivision.com',   'role' => 'atencion_al_cliente', 'branch_code' => null],
            ['name' => 'NOC Supervisor',    'email' => 'noc2@omnivision.com',      'role' => 'noc',       'branch_code' => null],
            ['name' => 'Técnico Flotante',  'email' => 'tecnico2@omnivision.com',  'role' => 'technician', 'branch_code' => null],
            ['name' => 'Vendedor Flotante', 'email' => 'vendedor2@omnivision.com', 'role' => 'sales_rep',  'branch_code' => null],
            ['name' => 'Vendedor Flotante 2','email' => 'vendedor3@omnivision.com','role' => 'sales_rep',  'branch_code' => null],
        ];

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
}
