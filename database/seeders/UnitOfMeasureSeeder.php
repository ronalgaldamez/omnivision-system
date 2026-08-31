<?php

namespace Database\Seeders;

use App\Models\UnitOfMeasure;
use Illuminate\Database\Seeder;

class UnitOfMeasureSeeder extends Seeder
{
    public function run(): void
    {
        $units = [
            ['code' => 'unidad',    'name' => 'Unidad',     'symbol' => null,    'is_whole' => true],
            ['code' => 'pieza',     'name' => 'Pieza',      'symbol' => null,    'is_whole' => true],
            ['code' => 'par',       'name' => 'Par',        'symbol' => null,    'is_whole' => true],
            ['code' => 'caja',      'name' => 'Caja',       'symbol' => null,    'is_whole' => true],
            ['code' => 'metro',     'name' => 'Metro',      'symbol' => 'm',     'is_whole' => false],
            ['code' => 'litro',     'name' => 'Litro',      'symbol' => 'L',     'is_whole' => false],
            ['code' => 'kilogramo', 'name' => 'Kilogramo',  'symbol' => 'kg',    'is_whole' => false],
            ['code' => 'rollo',     'name' => 'Rollo',      'symbol' => null,    'is_whole' => false],
            ['code' => 'bobina',    'name' => 'Bobina',     'symbol' => null,    'is_whole' => false],
        ];

        foreach ($units as $unit) {
            UnitOfMeasure::firstOrCreate(['code' => $unit['code']], $unit);
        }
    }
}
