<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Reservation;
use App\Models\Review;
use App\Models\Shop;
use App\Models\ShopOwner;
use App\Models\User;
use Illuminate\Http\Request;

class AdminGlobalSearchController extends Controller
{
  /**
   * シンプルなDB集約クエリで横断検索（各5件）
   */
  public function __invoke(Request $request)
  {
    $q = trim((string)$request->get('q', ''));
    $limit = (int)($request->get('limit', 5));
    $limit = max(1, min($limit, 10));

    if ($q === '') {
      return response()->json([
        'shops' => [],
        'owners' => [],
        'users' => [],
        'reservations' => [],
        'reviews' => []
      ]);
    }

    // shops: name 部分一致
    $shops = Shop::query()
      ->where('name', 'like', "%{$q}%")
      ->orderByDesc('id')
      ->limit($limit)
      ->get(['id', 'name'])
      ->map(fn($s) => [
        'id' => $s->id,
        'label' => $s->name,
        'sub' => 'Shop',
        'url' => route('admin.shops.index') . '?id=' . $s->id,
        'icon' => 'fas fa-store',
      ]);

    // owners: name/email 部分一致
    $owners = ShopOwner::query()
      ->where(function ($w) use ($q) {
        $w->where('name', 'like', "%{$q}%")
          ->orWhere('email', 'like', "%{$q}%");
      })
      ->orderByDesc('id')
      ->limit($limit)
      ->get(['id', 'name', 'email'])
      ->map(fn($o) => [
        'id' => $o->id,
        'label' => $o->name,
        'sub' => $o->email,
        'url' => route('admin.shop-owners.index') . '?id=' . $o->id,
        'icon' => 'fas fa-user-tie',
      ]);

    // users: name/email 部分一致
    $users = User::query()
      ->where(function ($w) use ($q) {
        $w->where('name', 'like', "%{$q}%")
          ->orWhere('email', 'like', "%{$q}%");
      })
      ->orderByDesc('id')
      ->limit($limit)
      ->get(['id', 'name', 'email'])
      ->map(fn($u) => [
        'id' => $u->id,
        'label' => $u->name,
        'sub' => $u->email,
        'url' => '#',
        'icon' => 'fas fa-user',
      ]);

    // reservations: id一致/メモ
    $reservations = Reservation::query()
      ->where('id', (int)$q)
      ->orWhere('note', 'like', "%{$q}%")
      ->orderByDesc('id')
      ->limit($limit)
      ->get(['id'])
      ->map(fn($r) => [
        'id' => $r->id,
        'label' => 'Reservation #' . $r->id,
        'sub' => '',
        'url' => '#',
        'icon' => 'fas fa-calendar-check',
      ]);

    // reviews: 本文
    $reviews = Review::query()
      ->where('comment', 'like', "%{$q}%")
      ->orderByDesc('id')
      ->limit($limit)
      ->get(['id', 'rating'])
      ->map(fn($rv) => [
        'id' => $rv->id,
        'label' => 'Review #' . $rv->id,
        'sub' => 'Rating ' . number_format((float)$rv->rating, 1),
        'url' => '#',
        'icon' => 'fas fa-star',
      ]);

    return response()->json([
      'shops' => $shops,
      'owners' => $owners,
      'users' => $users,
      'reservations' => $reservations,
      'reviews' => $reviews,
    ]);
  }
}

