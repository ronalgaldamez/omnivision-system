<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Seeders base del sistema
        $this->call(RolesAndPermissionsSeeder::class);
        $this->call(AdminUserSeeder::class);
        $this->call(UsersTestSeeder::class);
        
        // Datos de demostración (marcas, modelos, categorías, productos)
        $this->call(DemoDataSeeder::class);
        $this->call(SuppliersSeeder::class);

    }
}