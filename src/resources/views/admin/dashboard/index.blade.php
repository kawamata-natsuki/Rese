@extends('admin.layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/components/admin/stat-card.css') }}">
<link rel="stylesheet" href="{{ asset('css/admin/dashboard/index.css') }}">
@endsection

@section('title', '管理者画面ダッシュボード')

@section('content')

<div class="admin-dashboard-page">

  {{-- Topbar は resources/views/admin/layouts/app.blade.php で定義 --}}
  {{-- Sidebar は resources/views/admin/layouts/app.blade.php で定義 --}}

  {{-- ===== Body ===== --}}
  <div class="admin-dashboard-page__container">

    {{-- Hero --}}
    @include('admin.dashboard.partials.hero')

    {{-- KPI 6枚 --}}
    @include('admin.dashboard.partials.stats')

    {{-- 折れ線 --}}
    @include('admin.dashboard.partials.linechart')

    {{-- 円グラフ --}}
    @include('admin.dashboard.partials.piechart')

    {{-- 右：Top / Inactive （上下2分割） --}}
    <section class="latest-panels">
      {{-- Top Shops (30D) --}}
      @include('admin.dashboard.partials.topshops')

      {{-- Inactive Shops (30D) --}}
      @include('admin.dashboard.partials.inactive')
    </section>

  </div>
</div>
@endsection

@section('js')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script defer src="{{ asset('js/admin/dashboard.js') }}"></script>
@endsection