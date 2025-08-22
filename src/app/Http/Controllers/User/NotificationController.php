<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function index(Request $request)
    {
        $u = $request->user();

        return response()->json([
            'unread_count' => $u->unreadNotifications()->count(),
            'latest' => $u->notifications()
                ->latest()
                ->take(10)
                ->get()
                ->map(fn($n) => [
                    'id' => $n->id,
                    'title' => $n->data['title'] ?? '通知',
                    'message' => $n->data['message'] ?? '',
                    'url' => $n->data['url'] ?? '#',
                    'read_at' => $n->read_at,
                    'created_at' => $n->created_at->diffForHumans(),
                ]),
        ]);
    }

    public function markAsRead(Request $request)
    {
        $data = $request->validate([
            'id' => ['nullable', 'string'],
        ]);
        $u = $request->user();

        if (!empty($data['id'])) {
            $n = $u->notifications()->whereKey($data['id'])->first();
            if ($n && is_null($n->read_at)) $n->markAsRead();
        } else {
            $u->unreadNotifications->markAsRead(); // 全既読
        }

        return response()->json(['ok' => true]);
    }
}
