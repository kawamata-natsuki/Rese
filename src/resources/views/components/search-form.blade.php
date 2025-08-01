<!-- 検索フォーム -->
<div class="header__search">

  <form id="search-form" class="search-form" method="GET" action="{{ route('shop.search') }}">

    <div class="search-form__group   search-form__group--select">
      <div class="search-form__select-wrapper">
        <select name="area" id="area" class="search-form__select">
          <option value="all" {{ request('area', 'all') == 'all' ? 'selected' : '' }}>すべてのエリア</option>
          <option value="tokyo" {{ request('area') == 'tokyo' ? 'selected' : '' }}>東京都</option>
          <option value="osaka" {{ request('area') == 'osaka' ? 'selected' : '' }}>大阪府</option>
          <option value="fukuoka" {{ request('area') == 'fukuoka' ? 'selected' : '' }}>福岡県</option>
        </select>
        <span class="search-form__select-icon">
          <i class="fas fa-chevron-down"></i>
        </span>
      </div>
    </div>

    <div class="search-form__group search-form__group--select">
      <div class="search-form__select-wrapper">
        <select name="genre" id="genre" class="search-form__select">
          <option value="all">すべてのジャンル</option>
          <option value="sushi" {{ request('genre') == 'sushi' ? 'selected' : '' }}>寿司</option>
          <option value="yakiniku" {{ request('genre') == 'yakiniku' ? 'selected' : '' }}>焼肉</option>
          <option value="izakaya" {{ request('genre') == 'izakaya' ? 'selected' : '' }}>居酒屋</option>
          <option value="italian" {{ request('genre') == 'italian' ? 'selected' : '' }}>イタリアン</option>
          <option value="ramen" {{ request('genre') == 'ramen' ? 'selected' : '' }}>ラーメン</option>
        </select>
        <span class="search-form__select-icon">
          <i class="fas fa-chevron-down"></i>
        </span>
      </div>
    </div>

    <div class="search-form__group search-form__group--input">
      <div class="search-form__input-wrapper">
        <span class="search-form__search-icon">
          <i class="fas fa-search"></i>
        </span>
        <input
          type="text"
          name="keyword"
          id="keyword"
          value="{{ request('keyword') }}"
          placeholder="Search..."
          class="search-form__input">
      </div>
    </div>

  </form>

  {{--
  <!-- 検索結果表示エリア -->
  <div id="search-results" class="search-results">
    <!-- JavaScriptで動的に結果を追加します    -->
    @foreach ($shops as $shop)
    @include('components.shop-card', ['shop' => $shop])
    @endforeach 
  --}}
</div>

</div>