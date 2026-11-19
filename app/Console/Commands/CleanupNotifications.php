<?php

namespace App\Console\Commands;

use App\Services\NotificationService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class CleanupNotifications extends Command
{
    protected $signature = 'notifications:cleanup';

    protected $description = 'Menghapus notifikasi yang lebih lama dari retensi 90 hari';

    public function handle(NotificationService $notifications): int
    {
        try {
            $deleted = $notifications->cleanup();
        } catch (\Throwable $exception) {
            Log::error('Notification cleanup failed.', [
                'exception' => $exception,
            ]);

            $this->components->error('Cleanup notifikasi gagal: '.$exception->getMessage());

            return self::FAILURE;
        }

        Log::info('Notification cleanup completed.', [
            'deleted' => $deleted,
        ]);

        $this->components->info("{$deleted} notifikasi lama dihapus.");

        return self::SUCCESS;
    }
}
