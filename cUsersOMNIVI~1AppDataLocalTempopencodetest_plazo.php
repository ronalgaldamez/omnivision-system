<?php
require __DIR__ . '/../../../vendor/autoload.php';
$app = require __DIR__ . '/../../../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$wf = new \App\Livewire\Contracts\ContractWorkflow(\App\Models\Ticket::find(1));
$plan = \App\Models\Plan::where('name','Combo Total HD')->first();
$wf->plan_id = $plan->id; $wf->term_months = 24; $wf->zone_id = 39;
$wf->apply_plazo = true; $wf->refreshPromotions();
echo "CON_PLAZO benefit=[{$wf->benefit}] doble=" . var_export($wf->promo_double_speed,true) . "\n";
$wf->apply_plazo = false; $wf->updatedApplyPlazo(false);
echo "SIN_PLAZO benefit=[{$wf->benefit}] doble=" . var_export($wf->promo_double_speed,true) . "\n";
