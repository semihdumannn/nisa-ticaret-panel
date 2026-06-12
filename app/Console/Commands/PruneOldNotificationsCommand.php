<?php

namespace App\Console\Commands;

use App\Models\Notification;
use Illuminate\Console\Command;

class PruneOldNotificationsCommand extends Command
{
    protected $signature = 'notifications:prune
                            {--days=3 : Bildirimlerin saklanacagi gun sayisi}';

    protected $description = 'Belirtilen gunden eski app_notifications kayitlarini siler (varsayilan: 3 gun).';

    public function handle(): int
    {
        $days = (int) $this->option('days');

        $deleted = Notification::where('created_at', '<', now()->subDays($days))->delete();

        $this->info("{$deleted} bildirim kaydi silindi (>{$days} gun).");

        return self::SUCCESS;
    }
}
