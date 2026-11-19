<?php

namespace App\Console\Commands;

use App\Services\NotificationService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class DispatchNotifications extends Command
{
    protected $signature = 'notifications:dispatch';

    protected $description = 'Membuat pengingat deadline akademik H-1 dan aktivitas belajar 60 menit sebelum mulai';

    public function handle(NotificationService $notifications): int
    {
        try {
            $deadlines = $notifications->notifyAcademicTaskDeadlines();
            $studyReminders = $notifications->notifyStudyActivityReminders();
        } catch (\Throwable $exception) {
            Log::error('Notification dispatch failed.', [
                'exception' => $exception,
            ]);

            $this->components->error('Dispatch notifikasi gagal: '.$exception->getMessage());

            return self::FAILURE;
        }

        Log::info('Notification dispatch completed.', [
            'academic_task_deadlines' => $deadlines,
            'study_activity_reminders' => $studyReminders,
        ]);

        $this->components->info("Notifikasi dibuat: {$deadlines} pengingat deadline, {$studyReminders} pengingat belajar.");

        return self::SUCCESS;
    }
}
