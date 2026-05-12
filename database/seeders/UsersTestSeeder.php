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
        // Nos aseguramos de que los roles existan primero
        $this->call(RolesAndPermissionsSeeder::class);

        // Secretaria
        $secretaria = User::firstOrCreate(
            ['email' => 'kenia.guille@omnivision.com'],
            [
                'name'              => 'Kenia Guille',
                'password'          => Hash::make('123456789'),
                'email_verified_at' => now(),
            ]
        );
        $secretaria->assignRole('secretary');

        // NOC
        $noc = User::firstOrCreate(
            ['email' => 'deivy.alas@omnivision.com'],
            [
                'name'              => 'Deivy Alas',
                'password'          => Hash::make('123456789'),
                'email_verified_at' => now(),
            ]
        );
        $noc->assignRole('noc');

        // Técnico 1
        $tecnico1 = User::firstOrCreate(
            ['email' => 'tecnico1@omnivision.com'],
            [
                'name'              => 'Técnico Uno',
                'password'          => Hash::make('123456789'),
                'email_verified_at' => now(),
            ]
        );
        $tecnico1->assignRole('technician');

        // Técnico 2
        $tecnico2 = User::firstOrCreate(
            ['email' => 'tecnico2@omnivision.com'],
            [
                'name'              => 'Técnico Dos',
                'password'          => Hash::make('123456789'),
                'email_verified_at' => now(),
            ]
        );
        $tecnico2->assignRole('technician');

        // Supervisor
        $supervisor = User::firstOrCreate(
            ['email' => 'supervisor@omnivision.com'],
            [
                'name'              => 'Supervisor Uno',
                'password'          => Hash::make('123456789'),
                'email_verified_at' => now(),
            ]
        );
        $supervisor->assignRole('supervisor');

        // Limpiar caché de permisos para que los roles se apliquen de inmediato
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        $this->command->info('Usuarios de prueba creados: secretaria, NOC, 2 técnicos y supervisor.');
    }
}