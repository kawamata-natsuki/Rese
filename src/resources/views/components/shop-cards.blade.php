<!-- 検索結果表示用、複数のカードを表示 -->

@if ($shops->isEmpty())
<div class="shop-index-page__no-result">
  <p>該当する店舗が見つかりませんでした。</p>
</div>
@else
<div class="shop-index-pages__grid">
  @foreach ($shops as $shop)
  <x-shop-card :shop="$shop" />
  @endforeach
</div>

{{-- ページネーション --}}
@if ($shops->hasPages())
<div class="pagination-wrapper">
  {{-- 端のページのリンク数を少なめにするなら onEachSide(1) などもOK --}}
  {{ $shops->onEachSide(1)->links('vendor.pagination.custom') }}
</div>
@endif
@endif