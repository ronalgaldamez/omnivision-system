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

        // Limpiar caché de permisos para que los roles se apliquen de inmediato
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        $this->command->info('Usuarios de prueba creados: secretaria y NOC.');
    }
}