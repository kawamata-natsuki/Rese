<?php

namespace App\Http\Controllers\User;

use App\Enums\ReservationStatus;
use App\Http\Controllers\Controller;
use App\Models\Reservation;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;

class NotificationController extends Controller
{
    public function index(Request $request)
    {
        $u = $request->user();

        /**
         * 「いま開いてるレビュー予約ID」を取得する。
         * - 推奨: フロントから ?current_rid=123 を付けて叩く
         * - 代替: Referer ヘッダから /user/reviews/{id}/create を抜く
         */
        $currentReviewRid = (int) $request->input('current_rid', 0);
        if (!$currentReviewRid) {
            $ref = (string) $request->headers->get('referer', '');
            if ($ref && preg_match('#/user/reviews/(\d+)/create#', $ref, $m)) {
                $currentReviewRid = (int) $m[1];
            }
        }

        // 来店済み・未レビュー・予約日時が現在以前（ベースクエリは1回だけ作成）
        $baseQ = Reservation::query()
            ->where('user_id', $u->id)
            ->where('reservation_status', ReservationStatus::VISITED)
            ->whereDoesntHave('review')
            ->whereRaw("TIMESTAMP(reservation_date, IFNULL(TIME(reservation_time), '00:00:00')) <= ?", [now()]);

        // ① 未読数用の全件カウント（除外なし）
        $pendingTotalAll = (clone $baseQ)->count();

        $reviewRoute = Route::has('user.reviews.create') ? 'user.reviews.create' : null;

        // ② リストだけ、いま開いてる予約を一時的に除外（あれば）
        $listQ = (clone $baseQ);
        if ($currentReviewRid > 0) {
            $listQ->where('id', '!=', $currentReviewRid);
        }

        $pendingList = $listQ
            ->with('shop')
            ->orderByDesc('reservation_date')
            ->orderByDesc('reservation_time')
            ->limit(10)
            ->get()
            ->map(function ($r) use ($reviewRoute) {
                $date = $r->reservation_date; // cast: date
                $time = $r->reservation_time; // cast: datetime|null
                $hms  = $time ? $time->format('H:i') : '00:00';
                $dt   = Carbon::parse($date->format('Y-m-d') . ' ' . $hms);

                return [
                    'id'         => 'pending:' . $r->id,
                    'title'      => 'レビューのお願い',
                    'message'    => ($r->shop->name ?? 'ご来店店') . 'のレビューをお願いします',
                    'url'        => $reviewRoute ? route($reviewRoute, ['reservation' => $r->id], false) : null,
                    'read_at'    => null, // 擬似通知は常に未読扱い
                    'created_at' => $dt->diffForHumans(),
                    'time_text'  => $dt->format('Y/m/d H:i'),
                    'ts'         => $dt->timestamp,
                ];
            });

        // 既存DB通知（URLの正規化とアクセス検証を含む）
        $dbLatest = $u->notifications()
            ->latest()->take(10)->get()
            ->map(function ($n) use ($u) {
                $url = $n->data['url'] ?? '#';

                // 絶対URL → 相対URL化
                if (Str::startsWith($url, ['http://', 'https://'])) {
                    $parts = parse_url($url);
                    $path  = $parts['path']  ?? '/';
                    $query = isset($parts['query']) ? ('?' . $parts['query']) : '';
                    $frag  = isset($parts['fragment']) ? ('#' . $parts['fragment']) : '';
                    $url   = $path . $query . $frag; // ← 「+」ではなく「.」で連結
                }

                // 旧形式 → 現行ルートへ
                if (preg_match('#^/reviews/(\d+)/create$#', $url, $m)) {
                    $url = route('user.reviews.create', ['reservation' => $m[1]], false);
                }

                // レビュー作成URLの場合はアクセス検証（所有・未レビュー・来店済み）
                if (preg_match('#^/user/reviews/(\d+)/create$#', $url, $m)) {
                    $rid = (int) $m[1];
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

        // 擬似通知 + DB通知 を時系列でマージ
        $latest = $pendingList->concat($dbLatest)
            ->sortByDesc('ts')->values()
            ->map(fn($i) => collect($i)->except('ts'));

        // ③ 未読数は「DB未読 + 擬似通知の全件」（＝画面によってブレない）
        $unread_count = (int) $u->unreadNotifications()->count() + (int) $pendingTotalAll;

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
            // DB通知のみ対象（擬似通知はDBに存在しない）
            $n = $u->notifications()->whereKey($data['id'])->first();
            if ($n && is_null($n->read_at)) {
                $n->markAsRead();
            }
        } else {
            $u->unreadNotifications->markAsRead(); // 全既読
        }

        return response()->json(['ok' => true]);
    }
}
