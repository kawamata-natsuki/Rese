<?php

namespace App\Http\Controllers\User;

use App\Enums\ReservationStatus;
use App\Http\Controllers\Controller;
use App\Models\Reservation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;

class NotificationController extends Controller
{
    public function index(Request $request)
    {
        $u = $request->user();

        // 1) 基本クエリを最初に1回だけ作る（来店済み・未レビュー・予約日時が現在以前）
        $baseQ = Reservation::query()
            ->where('user_id', $u->id)
            ->where('reservation_status', ReservationStatus::VISITED)
            ->whereDoesntHave('review')
            ->whereRaw("TIMESTAMP(reservation_date, IFNULL(TIME(reservation_time), '00:00:00')) <= ?", [now()]);

        // 2) 件数とリストを同じ条件で
        $pendingTotal = (clone $baseQ)->count();

        $reviewRoute = Route::has('user.reviews.create') ? 'user.reviews.create' : null;

        $pendingList = (clone $baseQ)
            ->with('shop')
            ->orderByDesc('reservation_date')
            ->orderByDesc('reservation_time')
            ->limit(10)
            ->get()
            ->map(function ($r) use ($reviewRoute) {
                $date = $r->reservation_date;             // cast: date
                $time = $r->reservation_time;             // cast: datetime|null
                $hms  = $time ? $time->format('H:i') : '00:00';
                $dt   = \Carbon\Carbon::parse($date->format('Y-m-d') . ' ' . $hms);

                return [
                    'id'         => 'pending:' . $r->id,
                    'title'      => 'レビューのお願い',
                    'message'    => ($r->shop->name ?? 'ご来店店') . 'のレビューをお願いします',
                    'url'        => $reviewRoute ? route($reviewRoute, ['reservation' => $r->id], false) : null,
                    'read_at'    => null, // 擬似通知なので常に未読扱い
                    'created_at' => $dt->diffForHumans(),
                    'time_text'  => $dt->format('Y/m/d H:i'),
                    'ts'         => $dt->timestamp,
                ];
            });

        // ※ ← ここで $pendingQ を作り直す必要はもう無い

        // 既存DB通知
        $dbLatest = $u->notifications()
            ->latest()->take(10)->get()
            ->map(function ($n) use ($u) {
                $url = $n->data['url'] ?? '#';

                // 絶対URL→相対
                if (\Illuminate\Support\Str::startsWith($url, ['http://', 'https://'])) {
                    $parts = parse_url($url);
                    $path  = $parts['path']  ?? '/';
                    $query = isset($parts['query']) ? ('?' . $parts['query']) : '';
                    $frag  = isset($parts['fragment']) ? ('#' . $parts['fragment']) : '';
                    $url   = $path . $query . $frag;
                }

                // 旧形式→現行
                if (preg_match('#^/reviews/(\d+)/create$#', $url, $m)) {
                    $url = route('user.reviews.create', ['reservation' => $m[1]], false);
                }

                // アクセス検証（所有・未レビュー・来店済み）
                if (preg_match('#^/user/reviews/(\d+)/create$#', $url, $m)) {
                    $rid = (int)$m[1];
                    $canAccess = Reservation::query()
                        ->where('id', $rid)
                        ->where('user_id', $u->id)
                        ->where('reservation_status', ReservationStatus::VISITED)
                        ->whereDoesntHave('review')
                        ->exists();

                    if (!$canAccess) {
                        return null; // 除外
                    }
                }

                return [
                    'id'         => $n->id,
                    'title'      => $n->data['title'] ?? '通知',
                    'message'    => $n->data['message'] ?? '',
                    'url'        => $url,
                    'read_at'    => $n->read_at,
                    'created_at' => $n->created_at->diffForHumans(),
                    'ts'         => $n->created_at->timestamp,
                ];
            })
            ->filter()
            ->values();

        $latest = $pendingList->concat($dbLatest)
            ->sortByDesc('ts')->values()
            ->map(fn($i) => collect($i)->except('ts'));

        $unread_count = $u->unreadNotifications()->count() + $pendingTotal;

        return response()->json([
            'unread_count' => $unread_count,
            'latest'       => $latest,
        ])->header('Cache-Control', 'no-store');
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
