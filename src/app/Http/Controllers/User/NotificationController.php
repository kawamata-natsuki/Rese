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

        // 予約日時を過ぎた & レビュー未投稿（visited_at使うなら前の条件でもOK）
        $pendingQ = Reservation::query()
            ->where('user_id', $u->id)
            ->where('reservation_status', ReservationStatus::VISITED) // ★来店済みだけ
            ->whereDoesntHave('review');

        $pendingTotal = (clone $pendingQ)->count();

        // ★ 固定でこれでOK（存在チェック付き）
        $reviewRoute = Route::has('user.reviews.create') ? 'user.reviews.create' : null;

        $pendingList = (clone $pendingQ)
            ->with('shop')
            ->orderByDesc('reservation_date')
            ->orderByDesc('reservation_time')
            ->limit(10)
            ->get()
            ->map(function ($r) {
                $date = $r->reservation_date; // cast: date
                $time = $r->reservation_time; // cast: datetime|null
                $hms  = $time ? $time->format('H:i') : '00:00';
                $dt   = \Carbon\Carbon::parse($date->format('Y-m-d') . ' ' . $hms);

                return [
                    'id'         => 'pending:' . $r->id,
                    'title'      => 'レビューのお願い',
                    'message'    => ($r->shop->name ?? 'ご来店店') . 'のレビューをお願いします',
                    'url'        => route('user.reviews.create', ['reservation' => $r->id], false),
                    'read_at'    => null,
                    'created_at' => $dt->diffForHumans(),     // 相対時間（例: 6日前）
                    'time_text'  => $dt->format('Y/m/d H:i'), // 予約日時（例: 2025/08/23 18:00）
                    'ts'         => $dt->timestamp,
                ];
            });

        // 既存DB通知
        $dbLatest = $u->notifications()
            ->latest()->take(10)->get()
            ->map(function ($n) {
                $url = $n->data['url'] ?? '#';

                // 1) 絶対URL→相対へ
                if (Str::startsWith($url, ['http://', 'https://'])) {
                    $parts = parse_url($url);
                    $path  = $parts['path']  ?? '/';
                    $query = isset($parts['query']) ? ('?' . $parts['query']) : '';
                    $frag  = isset($parts['fragment']) ? ('#' . $parts['fragment']) : '';
                    $url   = $path . $query . $frag;
                }

                // 2) 旧形式 `/reviews/{id}/create` → 現行にリライト
                if (preg_match('#^/reviews/(\d+)/create$#', $url, $m)) {
                    $url = route('user.reviews.create', ['reservation' => $m[1]], false);
                }

                // 3) 「レビュー作成URL」ならアクセス可能か検証（予約の所有・未レビュー・来店済み）
                if (preg_match('#^/user/reviews/(\d+)/create$#', $url, $m)) {
                    $rid = (int)$m[1];
                    $canAccess = Reservation::query()
                        ->where('id', $rid)
                        ->where('user_id', $u->id)
                        ->where('reservation_status', \App\Enums\ReservationStatus::VISITED)
                        ->whereDoesntHave('review')
                        ->exists();

                    if (!$canAccess) {
                        // 403になるリンクは無効化（候補: 店舗詳細などに差し替え/除外）
                        // ここでは "除外" するために null を返す
                        return null;
                        // 置き換えたい場合は:
                        // $url = url('/'); // or 任意の安全なページ
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
            ->filter()           // null(=除外) を落とす
            ->values();

        $latest = $pendingList->concat($dbLatest)
            ->sortByDesc('ts')->values()
            ->map(fn($i) => collect($i)->except('ts'));

        $unread_count = $u->unreadNotifications()->count() + $pendingTotal;

        return response()->json([
            'unread_count' => $unread_count,
            'latest' => $latest,
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
