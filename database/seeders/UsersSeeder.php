<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UsersSeeder extends Seeder
{
    public function run(): void
    {
        $password = Hash::make('password');

        $users = [
            // Admin
            ['name' => 'Administrador', 'email' => 'admin@omnivision.com', 'role' => 'admin'],
            ['name' => 'Admin Prueba',   'email' => 'admin@test.com',      'role' => 'admin'],

            // Atención al Cliente (SAC)
            ['name' => 'SAC Principal',  'email' => 'sac@omnivision.com',   'role' => 'atencion_al_cliente'],
            ['name' => 'SAC Soporte',    'email' => 'soporte@omnivision.com', 'role' => 'atencion_al_cliente'],

            // NOC
            ['name' => 'NOC Técnico',    'email' => 'noc@omnivision.com',  'role' => 'noc'],
            ['name' => 'NOC Supervisor', 'email' => 'noc2@omnivision.com', 'role' => 'noc'],

            // Técnicos de campo
            ['name' => 'Técnico 1',      'email' => 'tecnico1@omnivision.com', 'role' => 'technician'],
            ['name' => 'Técnico 2',      'email' => 'tecnico2@omnivision.com', 'role' => 'technician'],

            // Bodega
            ['name' => 'Bodeguero',      'email' => 'bodega@omnivision.com', 'role' => 'warehouse'],

            // Compras
            ['name' => 'Comprador',      'email' => 'compras@omnivision.com', 'role' => 'buyer'],

            // Contabilidad
            ['name' => 'Contador',       'email' => 'contabilidad@omnivision.com', 'role' => 'accountant'],

            // Supervisor de campo
            ['name' => 'Supervisor',     'email' => 'supervisor@omnivision.com', 'role' => 'field_supervisor'],

            // Vendedores (Sales Rep)
            ['name' => 'Vendedor 1',     'email' => 'vendedor1@omnivision.com', 'role' => 'sales_rep'],
            ['name' => 'Vendedor 2',     'email' => 'vendedor2@omnivision.com', 'role' => 'sales_rep'],
            ['name' => 'Vendedor 3',     'email' => 'vendedor3@omnivision.com', 'role' => 'sales_rep'],
        ];

        foreach ($users as $data) {
            $user = User::firstOrCreate(
                ['email' => $data['email']],
                [
                    'name' => $data['name'],
                    'password' => $password,
                ]
            );
            $user->assignRole($data['role']);
        }

        $this->command->info('Usuarios creados: ' . count($users));
    }
}
