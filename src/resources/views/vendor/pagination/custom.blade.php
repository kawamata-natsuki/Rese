@if ($paginator->hasPages())
<nav class="pagination">
  {{-- 前へ --}}
  @if ($paginator->onFirstPage())
  <span class="pagination__arrow pagination__arrow--disabled">&laquo;</span>
  @else
  <a href="{{ $paginator->previousPageUrl() }}" class="pagination__arrow">&laquo;</a>
  @endif

  {{-- ページ番号 --}}
  @foreach ($elements as $element)
  @if (is_string($element))
  <span class="pagination__dots">{{ $element }}</span>
  @endif

  @if (is_array($element))
  @foreach ($element as $page => $url)
  @if ($page == $paginator->currentPage())
  <span class="pagination__page pagination__page--active">{{ $page }}</span>
  @else
  <a href="{{ $url }}" class="pagination__page">{{ $page }}</a>
  @endif
  @endforeach
  @endif
  @endforeach

  {{-- 次へ --}}
  @if ($paginator->hasMorePages())
  <a href="{{ $paginator->nextPageUrl() }}" class="pagination__arrow">&raquo;</a>
  @else
  <span class="pagination__arrow pagination__arrow--disabled">&raquo;</span>
  @endif
</nav>
@endif