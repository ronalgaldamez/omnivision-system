<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Spatie\Permission\Models\Role;

class SalesRepSeeder extends Seeder
{
    public function run()
    {
        $role = Role::where('name', 'sales_rep')->first();

        if (!$role) {
            $this->command->warn('El rol sales_rep no existe. Ejecutá primero RolesAndPermissionsSeeder.');
            return;
        }

        $users = [
            [
                'name' => 'Carlos Mendoza',
                'email' => 'carlos.mendoza@omnivision.test',
                'password' => bcrypt('password'),
            ],
            [
                'name' => 'Ana Lucía Ramírez',
                'email' => 'ana.ramirez@omnivision.test',
                'password' => bcrypt('password'),
            ],
            [
                'name' => 'Luis Fernando Gómez',
                'email' => 'luis.gomez@omnivision.test',
                'password' => bcrypt('password'),
            ],
        ];

        foreach ($users as $data) {
            $user = User::firstOrCreate(
                ['email' => $data['email']],
                [
                    'name' => $data['name'],
                    'password' => $data['password'],
                ]
            );

            // Asignar el rol sales_rep si no lo tiene
            if (!$user->hasRole('sales_rep')) {
                $user->assignRole('sales_rep');
            }
        }

        $this->command->info('3 usuarios Sales Rep creados correctamente.');
    }
}