<?php

namespace Database\Seeders;

use App\Models\Branch;
use App\Models\Zone;
use Illuminate\Database\Seeder;

class ZonesAndBranchesSeeder extends Seeder
{
    public function run(): void
    {
        // ─── Sucursales ───
        $branches = [
            ['name' => 'Casa Matriz Chalatenango', 'code' => 'MATRIZ', 'address' => 'Chalatenango'],
            ['name' => 'Sucursal Concepción Quezaltepeque', 'code' => 'CQ'],
            ['name' => 'Sucursal Amayo', 'code' => 'AMAYO'],
            ['name' => 'Sucursal Aguilares', 'code' => 'AGUILARES'],
            ['name' => 'Sucursal La Palma', 'code' => 'PALMA'],
            ['name' => 'Sucursal San Pablo Tacachico', 'code' => 'SMP'],
        ];

        foreach ($branches as $data) {
            Branch::firstOrCreate(
                ['code' => $data['code']],
                [
                    'name' => $data['name'],
                    'address' => $data['address'] ?? null,
                    'is_active' => true,
                ]
            );
        }

        // ─── Zonas ───
        // Estructura: [name, branch_code, children: [name, level]]
        $zoneGroups = [
            [
                'name' => 'Chalatenango - Casa Matriz',
                'branch_code' => 'MATRIZ',
                'children' => [
                    ['name' => 'Chalatenango Sur', 'level' => 'municipio'],
                ],
            ],
            [
                'name' => 'San Salvador',
                'branch_code' => 'AGUILARES',
                'children' => [
                    ['name' => 'San Salvador Norte', 'level' => 'municipio'],
                ],
            ],
            [
                'name' => 'Chalatenango - Centro',
                'branch_code' => 'AMAYO',
                'children' => [
                    ['name' => 'Chalatenango Centro', 'level' => 'municipio'],
                ],
            ],
            [
                'name' => 'Chalatenango Sur - Concepcion',
                'branch_code' => 'CQ',
                'children' => [
                    ['name' => 'Chalatenango Sur', 'level' => 'municipio'],
                ],
            ],
            [
                'name' => 'Chalatenango Norte - La Palma',
                'branch_code' => 'PALMA',
                'children' => [
                    ['name' => 'Chalatenango Norte', 'level' => 'municipio'],
                ],
            ],
            [
                'name' => 'La Libertad',
                'branch_code' => 'SMP',
                'children' => [
                    ['name' => 'La Libertad Norte', 'level' => 'municipio'],
                ],
            ],
        ];

        foreach ($zoneGroups as $group) {
            $branch = Branch::where('code', $group['branch_code'])->first();
            if (!$branch) continue;

            $parent = Zone::firstOrCreate(
                ['name' => $group['name'], 'parent_id' => null],
                [
                    'branch_id' => $branch->id,
                    'level' => 'departamento',
                    'is_active' => true,
                ]
            );

            foreach ($group['children'] as $child) {
                Zone::firstOrCreate(
                    ['name' => $child['name'], 'parent_id' => $parent->id],
                    [
                        'branch_id' => $branch->id,
                        'level' => $child['level'],
                        'is_active' => true,
                    ]
                );
            }
        }
    }
}
