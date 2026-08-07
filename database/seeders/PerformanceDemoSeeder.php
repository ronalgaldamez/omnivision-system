<?php

namespace Database\Seeders;

use App\Models\Client;
use App\Models\Contract;
use App\Models\Plan;
use App\Models\Requisition;
use App\Models\SlaGoal;
use App\Models\TechnicianReturn;
use App\Models\Ticket;
use App\Models\User;
use App\Models\WorkOrder;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Demo de datos para poblar la vista /reports/performance.
 *
 * IDEMPOTENTE: borra los registros marcados con "DEMO RENDIMIENTO" antes de recrearlos.
 * Solo usar en entornos de desarrollo — nunca en producción.
 */
class PerformanceDemoSeeder extends Seeder
{
    private const MARKER = 'DEMO RENDIMIENTO';

    /**
     * Distribuye fechas para que el mes actual tenga volumen visible:
     * 40% en los últimos 7 días (mes actual), 30% el mes anterior, 30% el resto del año.
     */
    private function demoDate(): \Carbon\Carbon
    {
        $roll = rand(1, 100);

        if ($roll <= 40) {
            return now()->subDays(rand(0, 6));
        }
        if ($roll <= 70) {
            return now()->subDays(rand(7, 29));
        }

        return now()->subDays(rand(30, 360));
    }

    public function run(): void
    {
        // 🚨 GUARDA DE SEGURIDAD: este seeder borra registros por marcador y crea datos
        // de demostración. Está terminantemente prohibido ejecutarlo en producción.
        if (app()->environment('production')) {
            $this->command?->error('PerformanceDemoSeeder está bloqueado en producción. No se ejecutó nada.');
            return;
        }

        // ── Limpieza idempotente (hijos primero por FKs) ──
        Requisition::where('notes', self::MARKER)->delete();
        TechnicianReturn::where('notes', self::MARKER)->delete();
        Contract::where('contract_terms', self::MARKER)->delete();
        WorkOrder::where('code', 'like', 'OT-PERF-%')->delete();
        Ticket::where('description', self::MARKER)->delete();


        // ── Referencias ──
        $sellers = User::role('sales_rep')->orderBy('id')->get();
        $sellers = $sellers->isEmpty() ? User::orderBy('id')->limit(2)->get() : $sellers;

        $techs = User::role('technician')->orderBy('id')->get();
        $techs = $techs->isEmpty() ? User::orderBy('id')->limit(2)->get() : $techs;

        $plans = Plan::where('is_active', true)->get();
        $slaGoals = SlaGoal::pluck('id')->toArray();

        $clients = Client::orderBy('id')->take(8)->get();
        if ($clients->isEmpty()) {
            for ($i = 1; $i <= 5; $i++) {
                $clients->push(Client::create([
                    'name' => "DEMO-PERF-Cliente {$i}",
                    'phone' => '7' . str_pad((string) rand(0, 9999999), 7, '0', STR_PAD_LEFT),
                    'departamento' => collect(['San Salvador', 'La Libertad', 'San Miguel', 'Santa Ana', 'Cuscatlán'])->random(),
                    'municipio' => 'Municipio',
                    'distrito' => 'Distrito',
                    'address' => 'Dirección demo',
                ]));
            }
        }

        $products = \App\Models\Product::orderBy('id')->take(10)->get();
        if ($products->isEmpty()) {
            $this->command?->warn('No hay productos: se omite el demo de devoluciones.');
        }

        $admin = User::whereHas('roles', fn($q) => $q->where('name', 'admin'))->first() ?? User::first();

        // ══════════════════ 1. CONTRATOS (ventas) — 60, últimos 12 meses ══════════════════
        for ($i = 0; $i < 60; $i++) {
            $date = $this->demoDate()->startOfDay()->addHours(rand(8, 18));
            $plan = $plans->random();
            $price = $plan ? round((float) $plan->base_price + rand(-5, 10), 2) : round(rand(25, 60), 2);

            Contract::create([
                'client_id' => $clients->random()->id,
                'plan_id' => $plan?->id,
                'service_type' => 'instalacion',
                'price' => max(10, $price),
                'installation_cost' => 0,
                'status' => collect(['active', 'active', 'active', 'active', 'cancelled'])->random(),
                'contract_date' => $date->toDateString(),
                'contract_terms' => self::MARKER,
                'installation_address' => 'Dirección de instalación demo',
                'created_by' => $sellers->random()->id,
            ]);
        }

        // ══════════════════ 2. OTs DE INSTALACIÓN — 45, últimos 12 meses ══════════════════
        for ($i = 0; $i < 45; $i++) {
            $created = $this->demoDate()->setTime(rand(7, 19), rand(0, 59));
            $tech = $techs->random();
            $status = collect(['completed', 'completed', 'completed', 'pending', 'in_progress'])->random();

            $wo = WorkOrder::create([
                'client_id' => $clients->random()->id,
                'plan_id' => $plans->random()?->id,
                'technician_id' => $tech->id,
                'service_type' => 'instalacion',
                'status' => $status,
                'description' => self::MARKER,
                'code' => 'OT-PERF-' . strtoupper(Str::random(8)),
                'created_by' => $admin->id,
                'assigned_at' => $created->copy()->addHours(rand(2, 48)),
            ]);

            if ($status === 'completed') {
                $wo->completed_date = $created->copy()->addDays(rand(1, 5));
            }
            $wo->created_at = $created;
            $wo->save();
        }

        // ══════════════════ 3. TICKETS (fallos) — 70, últimos 12 meses ══════════════════
        $resolverPool = $sellers->merge($techs)->merge(collect([$admin]))->unique('id');
        $priorities = ['P1', 'P2', 'P2', 'P3', 'P3', 'P4'];
        $services = ['soporte_tecnico', 'revision', 'reconexion', 'habilitacion', 'traslado', 'cambio_plan', 'instalacion'];

        for ($i = 0; $i < 70; $i++) {
            $created = $this->demoDate()->setTime(rand(8, 18), rand(0, 59));
            $resolved = (bool) rand(0, 1);
            $requiresNoc = (bool) rand(0, 1);
            $slaGoalId = $slaGoals ? $slaGoals[array_rand($slaGoals)] : null;

            $t = Ticket::create([
                'client_id' => $clients->random()->id,
                'description' => self::MARKER,
                'service_type' => $services[array_rand($services)],
                'priority' => $priorities[array_rand($priorities)],
                'origin' => collect(['Llamada Telefónica', 'SMS WhatsApp', 'Presencial', 'Facebook Messenger'])->random(),
                'requires_noc' => $requiresNoc,
                'status' => $resolved ? 'resolved' : collect(['pending', 'in_progress', 'pending'])->random(),
                'created_by' => $resolverPool->random()->id,
                'sla_goal_id' => $slaGoalId,
            ]);

            if ($resolved) {
                $resolvedAt = $created->copy()->addHours(rand(2, 48));
                $t->resolved_at = $resolvedAt;
                $t->resolved_by = $resolverPool->random()->id;
                $t->sla_evaluated_at = $resolvedAt;
                $t->sla_met = (bool) rand(0, 1);
            }
            $t->created_at = $created;
            $t->save();
        }

        // ══════════════════ 4. REQUISICIONES APROBADAS — 18 ══════════════════
        for ($i = 0; $i < 18; $i++) {
            $date = $this->demoDate()->setTime(rand(8, 18), rand(0, 59));

            $r = Requisition::create([
                'technician_id' => $techs->random()->id,
                'status' => 'approved',
                'notes' => self::MARKER,
                'approved_by' => $admin->id,
                'approved_at' => $date->copy()->addHours(rand(2, 24)),
            ]);
            $r->created_at = $date;
            $r->save();
        }

        // ══════════════════ 5. DEVOLUCIONES DE TÉCNICO — 26 ══════════════════
        if ($products->isNotEmpty()) {
            for ($i = 0; $i < 26; $i++) {
                $date = $this->demoDate()->setTime(rand(8, 18), rand(0, 59));

                $ret = TechnicianReturn::create([
                    'user_id' => $techs->random()->id,
                    'product_id' => $products->random()->id,
                    'quantity' => rand(1, 3),
                    'type' => collect(['surplus', 'surplus', 'damage'])->random(),
                    'notes' => self::MARKER,
                ]);
                $ret->created_at = $date;
                $ret->save();
            }
        }

        // Limpiar caché del reporte para que la vista se regenere con los datos nuevos
        Cache::flush();

        $this->command?->info('Demo de rendimiento generado (60 contratos, 45 OTs, 70 tickets). Caché limpiada.');
    }
}
