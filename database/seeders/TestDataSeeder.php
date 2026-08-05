<?php

namespace Database\Seeders;

use App\Models\Client;
use App\Models\Movement;
use App\Models\Product;
use App\Models\SlaGoal;
use App\Models\Ticket;
use App\Models\User;
use App\Models\WorkOrder;
use Illuminate\Database\Seeder;

class TestDataSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::whereHas('roles', fn($q) => $q->where('name', 'admin'))->first() ?? User::first();
        $technicians = User::role('technician')->pluck('id')->toArray();
        $slaGoals = SlaGoal::pluck('id')->toArray();
        $clientId = Client::first()?->id ?? Client::create(['name' => 'Cliente Demo', 'phone' => '70000000'])->id;

        // ── 1. Productos ──
        $names = ['Router WiFi 5', 'Router WiFi 6', 'Cable coaxial 50m', 'Conector F', 'Splitter 2 vías', 'Amplificador', 'Antena 2.4GHz', 'Fuente 12V', 'Módem ONT', 'Switch 8 puertos'];
        $products = [];
        foreach ($names as $i => $name) {
            $p = Product::create([
                'name' => $name,
                'sku' => 'TEST-' . str_pad($i + 1, 3, '0', STR_PAD_LEFT),
                'current_stock' => rand(10, 60),
                'stock_min' => 8,
                'stock_max' => 100,
                'average_cost' => rand(5, 120),
            ]);
            $p->update(['total_value' => $p->current_stock * $p->average_cost]);
            $products[] = $p->id;
        }

        // ── 2. Movimientos (80, últimos 12 meses) ──
        $types = ['entry', 'entry', 'exit', 'exit', 'technician_out', 'technician_return', 'requisition_out', 'branch_allocation'];
        for ($i = 0; $i < 80; $i++) {
            $type = $types[array_rand($types)];
            $qty = rand(1, 15);
            $cost = rand(5, 100);
            $productId = $products[array_rand($products)];
            $date = now()->subDays(rand(0, 360))->setTime(rand(8, 18), rand(0, 59));

            $m = Movement::create([
                'product_id' => $productId,
                'type' => $type,
                'quantity' => $qty,
                'unit_cost' => $cost,
                'total_value' => $qty * $cost,
                'description' => 'Dato de prueba (' . $type . ')',
                'user_id' => $admin->id,
            ]);
            $m->update(['created_at' => $date]);
        }

        // ── 3. Órdenes de trabajo (30, últimos 6 meses) ──
        $statuses = ['pending', 'in_progress', 'completed', 'completed', 'completed', 'cancelled'];
        $services = ['instalacion', 'traslado', 'soporte_tecnico', 'reconexion', 'verificacion_instalacion', 'cambio_plan'];
        for ($i = 0; $i < 30; $i++) {
            $date = now()->subDays(rand(0, 175))->setTime(rand(7, 19), rand(0, 59));
            $status = $statuses[array_rand($statuses)];

            $wo = WorkOrder::create([
                'client_id' => $clientId,
                'technician_id' => $technicians ? $technicians[array_rand($technicians)] : null,
                'status' => $status,
                'service_type' => $services[array_rand($services)],
                'description' => 'OT de prueba',
                'code' => 'OT-TEST-' . strtoupper(substr(md5((string) rand()), 0, 6)),
                'created_by' => $admin->id,
            ]);
            $wo->update(['created_at' => $date]);
        }

        // ── 4. Tickets (40, últimos 12 meses) ──
        $priorities = ['P1', 'P2', 'P3', 'P4', 'P3'];
        $ticketStatuses = ['pending', 'in_progress', 'resolved', 'resolved', 'closed'];
        $ticketServices = ['instalacion', 'soporte_tecnico', 'revision', 'traslado', 'habilitacion', 'cambio_plan'];
        for ($i = 0; $i < 40; $i++) {
            $date = now()->subDays(rand(0, 360))->setTime(rand(8, 18), rand(0, 59));
            $status = $ticketStatuses[array_rand($ticketStatuses)];
            $evaluated = in_array($status, ['resolved', 'closed']);

            $t = Ticket::create([
                'client_id' => $clientId,
                'description' => 'Ticket de prueba',
                'service_type' => $ticketServices[array_rand($ticketServices)],
                'priority' => $priorities[array_rand($priorities)],
                'origin' => 'phone',
                'status' => $status,
                'created_by' => $admin->id,
                'sla_goal_id' => $slaGoals ? $slaGoals[array_rand($slaGoals)] : null,
                'sla_met' => $evaluated ? (bool) rand(0, 1) : null,
                'sla_evaluated_at' => $evaluated ? $date->copy()->addHours(rand(1, 72)) : null,
            ]);
            $t->update(['created_at' => $date]);
        }

        $this->command?->info('Datos de prueba generados correctamente.');
    }
}
