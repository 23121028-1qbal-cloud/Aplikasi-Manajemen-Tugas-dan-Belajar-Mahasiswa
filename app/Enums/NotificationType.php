<?php

namespace App\Enums;

enum NotificationType: string
{
    case AcademicTaskAssigned = 'academic_task_assigned';
    case AcademicTaskDeadline = 'academic_task_deadline';
    case StudyActivityReminder = 'study_activity_reminder';

    public function label(): string
    {
        return match ($this) {
            self::AcademicTaskAssigned => 'Penugasan akademik',
            self::AcademicTaskDeadline => 'Pengingat deadline',
            self::StudyActivityReminder => 'Pengingat belajar',
        };
    }
}
