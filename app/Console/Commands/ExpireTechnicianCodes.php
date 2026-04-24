<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\TechnicianRequest;

class ExpireTechnicianCodes extends Command
{
    protected $signature = 'technicians:expire-codes';
    protected $description = 'Expirar códigos de retiro que hayan pasado su fecha de expiración';

    public function handle()
    {
        $count = TechnicianRequest::where('status', 'approved')
            ->whereNotNull('code_expires_at')
            ->where('code_expires_at', '<', now())
            ->update(['status' => 'expired']);

        $this->info("Se expiraron {$count} códigos.");
    }
}