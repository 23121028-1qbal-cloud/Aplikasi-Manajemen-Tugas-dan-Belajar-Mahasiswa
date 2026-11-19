<?php

namespace App\Http\Controllers;

use App\Models\Notifikasi;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class NotificationController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorize('viewAny', Notifikasi::class);

        $user = $request->user();

        $notifications = Notifikasi::query()
            ->where('id_user', $user->id_user)
            ->orderByDesc('tanggal_dibuat')
            ->orderByDesc('id_notifikasi')
            ->paginate(20);

        return view('pages.notifications.index', [
            'notifications' => $notifications,
            'unreadCount' => Notifikasi::query()
                ->where('id_user', $user->id_user)
                ->unread()
                ->count(),
            'resourceUrls' => $notifications->getCollection()
                ->mapWithKeys(fn (Notifikasi $notification) => [
                    $notification->id_notifikasi => $this->resourceUrl($notification, $user),
                ]),
        ]);
    }

    public function markRead(Request $request, Notifikasi $notification): RedirectResponse
    {
        abort_unless($request->user()->can('markRead', $notification), 404);

        if (! $notification->is_read) {
            $notification->forceFill([
                'is_read' => true,
                'read_at' => now(),
            ])->save();
        }

        return back()->with('status', 'Notifikasi ditandai sudah dibaca.');
    }

    public function markAllRead(Request $request): RedirectResponse
    {
        $this->authorize('markAllRead', Notifikasi::class);

        Notifikasi::query()
            ->where('id_user', $request->user()->id_user)
            ->unread()
            ->update([
                'is_read' => true,
                'read_at' => now(),
            ]);

        return back()->with('status', 'Semua notifikasi ditandai sudah dibaca.');
    }

    private function resourceUrl(Notifikasi $notification, User $user): ?string
    {
        if ($notification->resource_id === null) {
            return null;
        }

        if ($notification->resource_type === 'tugas') {
            if ($user->hasRole('mahasiswa')) {
                return route('mahasiswa.academic-tasks.show', $notification->resource_id);
            }

            if ($user->hasRole('guru_dosen')) {
                return route('lecturer.academic-tasks.show', $notification->resource_id);
            }

            return null;
        }

        if ($notification->resource_type === 'aktivitas_belajar' && $user->hasRole('mahasiswa')) {
            return route('mahasiswa.study-activities.show', $notification->resource_id);
        }

        return null;
    }
}
