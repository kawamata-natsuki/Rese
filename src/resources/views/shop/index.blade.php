@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/components/shop-card.css') }}">
<link rel="stylesheet" href="{{ asset('css/shop/index.css') }}">
@endsection

@section('title', '飲食店一覧')

@section('content')
<div class="shop-index-page">
  <div class="shop-index-page__container">

    <div id="search-results" class="shop-index-page__results">
      @include('components.shop-cards', ['shops' => $shops])
    </div>

  </div>
</div>
@endsection

@push('scripts')
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
  // 検索+ページング
  function fetchShops(page = 1) {
    const keyword = $('#keyword').val();
    const area = $('#area').val();
    const genre = $('#genre').val();

    $.ajax({
      url: "{{ route('shop.search.ajax') }}",
      type: 'GET',
      data: {
        keyword,
        area,
        genre,
        page
      },
      success: function(response) {
        // レンダリング済みHTMLを返す想定（render()->toHtml()）
        $('#search-results').html(response.html || response);
        // スクロール位置を少し戻す（任意）
        window.scrollTo({
          top: 0,
          behavior: 'smooth'
        });
      }
    });
  }

  // リセット
  $('#reset-button').on('click', function() {
    $('#area').val('all');
    $('#genre').val('all');
    $('#keyword').val('');
    fetchShops(1);
  });

  // 初期化
  $(document).ready(function() {
    $('#keyword').on('input', () => fetchShops(1));
    $('#area, #genre').on('change', () => fetchShops(1));

    // Enterでのsubmit抑制
    $('#search-form').on('keydown', function(e) {
      if (e.key === 'Enter') e.preventDefault();
    }).on('submit', function(e) {
      e.preventDefault();
      fetchShops(1);
    });
  });

  // ページネーションをAJAXで置き換え（委譲）
  $(document).on('click', '.pagination a', function(e) {
    e.preventDefault();
    const url = new URL($(this).attr('href'), window.location.origin);
    const page = url.searchParams.get('page') || 1;
    fetchShops(page);
  });
</script>
@endpush