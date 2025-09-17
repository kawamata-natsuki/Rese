<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Notifications\DatabaseNotification;

class AdminNotificationController extends Controller
{
  public function unreadCount(Request $request)
  {
    $admin = $request->user('admin');
    $count = $admin?->unreadNotifications()->count() ?? 0;
    return response()->json(['unread' => $count]);
  }

  public function index(Request $request)
  {
    $admin = $request->user('admin');
    $paginator = $admin->notifications()
      ->orderByDesc('created_at')
      ->paginate(10);

    $items = collect($paginator->items())->map(function (DatabaseNotification $n) {
      $data = $n->data ?? [];
      return [
        'id'        => $n->id,
        'title'     => $data['title'] ?? ($data['type'] ?? 'Notification'),
        'message'   => $data['message'] ?? null,
        'url'       => $data['url'] ?? null,
        'read_at'   => optional($n->read_at)?->toISOString(),
        'created_at' => optional($n->created_at)?->toISOString(),
      ];
    });

    return response()->json([
      'data' => $items,
      'meta' => [
        'current_page' => $paginator->currentPage(),
        'last_page'    => $paginator->lastPage(),
        'total'        => $paginator->total(),
      ],
    ]);
  }

  public function markAllRead(Request $request)
  {
    $admin = $request->user('admin');
    $admin->unreadNotifications->markAsRead();
    return response()->json(['ok' => true]);
  }

  public function markRead(Request $request, DatabaseNotification $notification)
  {
    abort_unless($notification->notifiable_type === get_class($request->user('admin'))
      && $notification->notifiable_id === $request->user('admin')->id, 403);

    if (is_null($notification->read_at)) {
      $notification->markAsRead();
    }
    return response()->json(['ok' => true]);
  }
}
