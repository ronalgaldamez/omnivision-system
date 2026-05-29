<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\PermissionRegistrar;

class UsersTestSeeder extends Seeder
{
    public function run()
    {
        // Asegurar roles
        $this->call(RolesAndPermissionsSeeder::class);

        // Atención al cliente (Kenia)
        $atencion = User::firstOrCreate(
            ['email' => 'kenia.guille@omnivision.com'],
            [
                'name' => 'Kenia Guille',
                'password' => Hash::make('123456789'),
                'email_verified_at' => now(),
            ]
        );
        $atencion->assignRole('atencion_al_cliente');

        // NOC
        $noc = User::firstOrCreate(
            ['email' => 'deivy.alas@omnivision.com'],
            [
                'name' => 'Deivy Alas',
                'password' => Hash::make('123456789'),
                'email_verified_at' => now(),
            ]
        );
        $noc->assignRole('noc');

        // Técnico 1
        User::firstOrCreate(
            ['email' => 'tecnico1@omnivision.com'],
            ['name' => 'Técnico Uno', 'password' => Hash::make('123456789'), 'email_verified_at' => now()]
        )->assignRole('technician');

        // Técnico 2
        User::firstOrCreate(
            ['email' => 'tecnico2@omnivision.com'],
            ['name' => 'Técnico Dos', 'password' => Hash::make('123456789'), 'email_verified_at' => now()]
        )->assignRole('technician');

        // Supervisor
        User::firstOrCreate(
            ['email' => 'supervisor@omnivision.com'],
            ['name' => 'Supervisor Uno', 'password' => Hash::make('123456789'), 'email_verified_at' => now()]
        )->assignRole('supervisor');

        app()[PermissionRegistrar::class]->forgetCachedPermissions();
        $this->command->info('Usuarios de prueba creados: Atención al cliente, NOC, 2 técnicos y supervisor.');
    }
}