<!-- 検索フォーム -->
<div class="header__search">

  <form id="search-form" class="search-form">

    <!-- エリア検索 -->
    <div class="search-form__group   search-form__group--select">
      <div class="search-form__select-wrapper">
        <select name="area" id="area" class="search-form__select">
          <option value="all">すべてのエリア</option>
          <option value="1">東京都</option>
          <option value="2">大阪府</option>
          <option value="3">福岡県</option>
        </select>
        <span class="search-form__select-icon">
          <i class="fas fa-chevron-down"></i>
        </span>
      </div>
    </div>

    <!-- ジャンル検索 -->
    <div class="search-form__group search-form__group--select">
      <div class="search-form__select-wrapper">
        <select name="genre" id="genre" class="search-form__select">
          <option value="all">すべてのジャンル</option>
          <option value="1">寿司</option>
          <option value="2">焼肉</option>
          <option value="3">居酒屋</option>
          <option value="4">イタリアン</option>
          <option value="5">ラーメン</option>
        </select>
        <span class="search-form__select-icon">
          <i class="fas fa-chevron-down"></i>
        </span>
      </div>
    </div>

    <!-- キーワード検索 -->
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

    <!-- リセットボタン -->
    <div class="search-form__group search-form__group--reset">
      <button type="button" id="reset-button" class="search-form__reset-button">
        リセット
      </button>
    </div>

  </form>
</div>