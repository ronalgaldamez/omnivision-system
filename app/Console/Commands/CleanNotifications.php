<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Notifications\DatabaseNotification;

class CleanNotifications extends Command
{
    protected $signature = 'notifications:clean {--days=7}';

    protected $description = 'Elimina notificaciones leídas antiguas y marca como leídas las no leídas vencidas (48h)';

    public function handle(): int
    {
        $days = (int) $this->option('days');

        // Eliminar notificaciones leídas con más de X días
        $deleted = DatabaseNotification::query()
            ->whereNotNull('read_at')
            ->where('read_at', '<', now()->subDays($days))
            ->delete();

        // Marcar como leídas las no leídas de más de 48 horas (el badge no debe acumular ruido)
        $autoRead = DatabaseNotification::query()
            ->whereNull('read_at')
            ->where('created_at', '<', now()->subHours(48))
            ->update(['read_at' => now()]);

        $this->info("Notificaciones leídas eliminadas: {$deleted}");
        $this->info("Notificaciones auto-marcadas como leídas: {$autoRead}");

        return self::SUCCESS;
    }
}
