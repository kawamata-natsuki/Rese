<div class="header-nav__search">
  <!-- 検索フォーム -->
  <form id="search-form" class="search-form" method="GET" action="{{ route('shop.search') }}">

    <select name="area" id="area" class="search-form__select">
      <option value="all">All area</option>
      <option value="tokyo" {{ request('area') == 'tokyo' ? 'selected' : '' }}>東京都</option>
      <option value="osaka" {{ request('area') == 'osaka' ? 'selected' : '' }}>大阪府</option>
      <option value="fukuoka" {{ request('area') == 'fukuoka' ? 'selected' : '' }}>福岡県</option>
    </select>

    <select name="genre" id="genre" class="search-form__select">
      <option value="all">All genre</option>
      <option value="sushi" {{ request('genre') == 'sushi' ? 'selected' : '' }}>寿司</option>
      <option value="yakiniku" {{ request('genre') == 'yakiniku' ? 'selected' : '' }}>焼肉</option>
      <option value="izakaya" {{ request('genre') == 'izakaya' ? 'selected' : '' }}>居酒屋</option>
      <option value="italian" {{ request('genre') == 'italian' ? 'selected' : '' }}>イタリアン</option>
      <option value="ramen" {{ request('genre') == 'ramen' ? 'selected' : '' }}>ラーメン</option>
    </select>

    <input
      type="text"
      name="keyword"
      id="keyword"
      value="{{ request('keyword') }}"
      placeholder="Search..."
      class="search-form__input">
  </form>

  <!-- 検索結果表示エリア -->
  <div id="search-results" class="search-results">
    <!-- JavaScriptで動的に結果を追加します -->
  </div>
</div>