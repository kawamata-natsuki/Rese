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
  // 検索実行関数
  function fetchShops() {
    const keyword = $('#keyword').val();
    const area = $('#area').val();
    const genre = $('#genre').val();

    $.ajax({
      url: "{{ route('shop.search.ajax') }}",
      type: 'GET',
      data: {
        keyword,
        area,
        genre
      },
      success: function(response) {
        $('#search-results').html(response.html);
      }
    });
  }

  // イベント登録
  $(document).ready(function() {
    $('#keyword').on('input', fetchShops);
    $('#area, #genre').on('change', fetchShops);

    // Enterキー無効化
    $('#search-form').on('keydown', function(e) {
      if (e.key === 'Enter') {
        e.preventDefault();
      }
    });

    // フォーム送信自体も無効化
    $('#search-form').on('submit', function(e) {
      e.preventDefault();
      fetchShops();
    });
  });
</script>
@endpush