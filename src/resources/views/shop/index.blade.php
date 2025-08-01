@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/components/shop-card.css') }}">
<link rel="stylesheet" href="{{ asset('css/shop/index.css') }}">
@endsection

@section('title', '飲食店一覧')

@section('content')
<div class="shop-index-page">
  <div class="shop-index-page__container">

    <div class="shop-index-pages__grid">
      @foreach ($shops as $shop)
      <x-shop-card :shop="$shop" />
      @endforeach
    </div>

  </div>

</div>



@endsection
<!--  
@push('scripts')
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
  function fetchShops() {
    const keyword = $('#keyword').val();
    const area = $('#area').val();
    const genre = $('#genre').val();

    $.ajax({
      url: "{{ route('shop.search.ajax') }}",
      type: 'GET',
      data: {
        keyword: keyword,
        area: area,
        genre: genre
      },
      success: function(shops) {
        let html = '';
        if (shops.length === 0) {
          html = '<p>該当する店舗が見つかりませんでした。</p>';
        } else {
          const showMore = shops.length > 10;
          const displayShops = showMore ? shops.slice(0, 10) : shops;

          displayShops.forEach(shop => {
            html += `
              <div class="search-result__item">
                <h3>${shop.name}</h3>
                <p>エリア: ${shop.area} / ジャンル: ${shop.genre}</p>
                <a href="/shops/${shop.id}">詳細を見る</a>
              </div>
            `;
          });

          if (showMore) {
            html += `<a href="{{ route('shop.search') }}?keyword=${keyword}&area=${area}&genre=${genre}" class="search-result__more">もっと見る</a>`;
          }
        }

        $('#search-results').html(html);
      },
      error: function() {
        $('#search-results').html('<p>検索中にエラーが発生しました。</p>');
      }
    });
  }

  $(document).ready(function() {
    $('#keyword').on('input', fetchShops);
    $('#area, #genre').on('change', fetchShops);
  });
</script>
@endpush
-->