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
        $this->call(UsersSeeder::class);
        $this->call(PackagingTypesSeeder::class);
        $this->call(MovementTypeSeeder::class);
        // $this->call(ServiceTypeSeeder::class);
        // $this->call(KnowledgeBaseSeeder::class);
        
        // Datos de demostración (marcas, modelos, categorías, productos)
        // $this->call(DemoDataSeeder::class);
        $this->call(SuppliersSeeder::class);
    }
}