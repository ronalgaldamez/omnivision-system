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
        $branchData = [
            ['name' => 'Casa Matriz Chalatenango', 'code' => 'MATRIZ', 'address' => 'Chalatenango'],
            ['name' => 'Sucursal Concepción Quezaltepeque', 'code' => 'CQ'],
            ['name' => 'Sucursal Amayo', 'code' => 'AMAYO'],
            ['name' => 'Sucursal Aguilares', 'code' => 'AGUILARES'],
            ['name' => 'Sucursal La Palma', 'code' => 'PALMA'],
            ['name' => 'Sucursal San Pablo Tacachico', 'code' => 'SMP'],
        ];

        $branches = [];
        foreach ($branchData as $data) {
            $branches[$data['code']] = Branch::firstOrCreate(
                ['code' => $data['code']],
                [
                    'name' => $data['name'],
                    'address' => $data['address'] ?? null,
                    'is_active' => true,
                ]
            );
        }

        // ─── Zonas ───
        // Estructura: [name, branch_code, children: [name, level, children: [name, level]]]
        $zoneGroups = [
            [
                'name' => 'Chalatenango S - Casa Matriz',
                'branch_code' => 'MATRIZ',
                'children' => [
                    [
                        'name' => 'Chalatenango Sur',
                        'level' => 'municipio',
                        'children' => [
                            ['name' => 'Arcatao', 'level' => 'distrito'],
                            ['name' => 'Azacualpa', 'level' => 'distrito'],
                            ['name' => 'Chalatenango (Cabecera departamental y municipal)', 'level' => 'distrito'],
                            ['name' => 'Comalapa', 'level' => 'distrito'],
                            ['name' => 'Concepción Quezaltepeque', 'level' => 'distrito'],
                            ['name' => 'El Carrizal', 'level' => 'distrito'],
                            ['name' => 'La Laguna', 'level' => 'distrito'],
                            ['name' => 'Las Flores (San José Las Flores)', 'level' => 'distrito'],
                            ['name' => 'Las Vueltas', 'level' => 'distrito'],
                            ['name' => 'Nombre de Jesús', 'level' => 'distrito'],
                            ['name' => 'Nueva Trinidad', 'level' => 'distrito'],
                            ['name' => 'Ojos de Agua', 'level' => 'distrito'],
                            ['name' => 'Potonico', 'level' => 'distrito'],
                            ['name' => 'San Antonio de la Cruz', 'level' => 'distrito'],
                            ['name' => 'San Antonio Los Ranchos', 'level' => 'distrito'],
                            ['name' => 'San Francisco Lempa', 'level' => 'distrito'],
                            ['name' => 'San Isidro Labrador', 'level' => 'distrito'],
                            ['name' => 'San José Cancasque', 'level' => 'distrito'],
                            ['name' => 'San Luis del Carmen', 'level' => 'distrito'],
                            ['name' => 'San Miguel de Mercedes', 'level' => 'distrito'],
                        ],
                    ],
                ],
            ],
            [
                'name' => 'San Salvador N - Aguilares',
                'branch_code' => 'AGUILARES',
                'children' => [
                    [
                        'name' => 'San Salvador Norte',
                        'level' => 'municipio',
                        'children' => [
                            ['name' => 'Aguilares', 'level' => 'distrito'],
                            ['name' => 'El Paisnal', 'level' => 'distrito'],
                            ['name' => 'Guazapa', 'level' => 'distrito'],
                        ],
                    ],
                ],
            ],
            [
                'name' => 'Chalatenango C - Amayo',
                'branch_code' => 'AMAYO',
                'children' => [
                    [
                        'name' => 'Chalatenango Centro',
                        'level' => 'municipio',
                        'children' => [
                            ['name' => 'Agua Caliente', 'level' => 'distrito'],
                            ['name' => 'Dulce Nombre de María', 'level' => 'distrito'],
                            ['name' => 'El Paraíso', 'level' => 'distrito'],
                            ['name' => 'La Reina', 'level' => 'distrito'],
                            ['name' => 'Nueva Concepción', 'level' => 'distrito'],
                            ['name' => 'San Fernando', 'level' => 'distrito'],
                            ['name' => 'San Francisco Morazán', 'level' => 'distrito'],
                            ['name' => 'San Rafael', 'level' => 'distrito'],
                            ['name' => 'Santa Rita', 'level' => 'distrito'],
                            ['name' => 'Tejutla', 'level' => 'distrito'],
                        ],
                    ],
                ],
            ],
            [
                'name' => 'Chalatenango S - Concepcion',
                'branch_code' => 'CQ',
                'children' => [
                    [
                        'name' => 'Chalatenango Sur',
                        'level' => 'municipio',
                        'children' => [],
                    ],
                ],
            ],
            [
                'name' => 'Chalatenango N - La Palma',
                'branch_code' => 'PALMA',
                'children' => [
                    [
                        'name' => 'Chalatenango Norte',
                        'level' => 'municipio',
                        'children' => [
                            ['name' => 'Citalá', 'level' => 'distrito'],
                            ['name' => 'La Palma', 'level' => 'distrito'],
                            ['name' => 'San Ignacio', 'level' => 'distrito'],
                        ],
                    ],
                ],
            ],
            [
                'name' => 'La Libertad N - San Pablo Tacachico',
                'branch_code' => 'SMP',
                'children' => [
                    [
                        'name' => 'La Libertad Norte',
                        'level' => 'municipio',
                        'children' => [
                            ['name' => 'Quezaltepeque (Cabecera municipal)', 'level' => 'distrito'],
                            ['name' => 'San Matías', 'level' => 'distrito'],
                            ['name' => 'San Pablo Tacachico', 'level' => 'distrito'],
                        ],
                    ],
                ],
            ],
        ];

        $isContainer = fn($l) => in_array($l, ['departamento', 'municipio']);

        foreach ($zoneGroups as $group) {
            $branch = $branches[$group['branch_code']] ?? null;
            if (!$branch) continue;

            $depto = Zone::firstOrCreate(
                ['name' => $group['name'], 'parent_id' => null],
                [
                    'branch_id' => $branch->id,
                    'level' => 'departamento',
                    'has_internet' => false,
                    'has_cable' => false,
                    'is_active' => true,
                ]
            );

            foreach ($group['children'] as $municipio) {
                $muni = Zone::firstOrCreate(
                    ['name' => $municipio['name'], 'parent_id' => $depto->id],
                    [
                        'branch_id' => $branch->id,
                        'level' => $municipio['level'],
                        'has_internet' => $isContainer($municipio['level']) ? false : true,
                        'has_cable' => $isContainer($municipio['level']) ? false : true,
                        'is_active' => true,
                    ]
                );

                foreach ($municipio['children'] as $distrito) {
                    Zone::firstOrCreate(
                        ['name' => $distrito['name'], 'parent_id' => $muni->id],
                        [
                            'branch_id' => $branch->id,
                            'level' => $distrito['level'],
                            'has_internet' => $isContainer($distrito['level']) ? false : true,
                            'has_cable' => $isContainer($distrito['level']) ? false : true,
                            'is_active' => true,
                        ]
                    );
                }
            }
        }
    }
}