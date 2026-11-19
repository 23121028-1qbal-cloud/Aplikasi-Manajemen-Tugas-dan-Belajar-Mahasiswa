<?php

namespace App\Policies;

use App\Models\Notifikasi;
use App\Models\User;

class NotifikasiPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Notifikasi $notification): bool
    {
        return $notification->id_user === $user->id_user;
    }

    public function markRead(User $user, Notifikasi $notification): bool
    {
        return $this->view($user, $notification);
    }

    public function markAllRead(User $user): bool
    {
        return true;
    }
}
