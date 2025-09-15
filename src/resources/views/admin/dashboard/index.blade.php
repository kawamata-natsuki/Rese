@extends('admin.layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/components/admin/stat-card.css') }}">
<link rel="stylesheet" href="{{ asset('css/admin/dashboard/index.css') }}">
@endsection

@section('title', '管理者画面ダッシュボード')

@section('content')
@php
$admin = auth('admin')->user();
$unreadCount = $unreadCount ?? 0;
$adminName = $admin->name ?? 'Admin';
@endphp

<div class="admin-dashboard-page">
    {{-- トップバー --}}
    <x-admin.topbar :adminName="$adminName" :unreadCount="$unreadCount" />

    {{-- メインコンテンツ --}}
    <div class="admin-dashboard-page__container">
        {{-- ヒーローセクション --}}
        <x-admin.hero-section :adminName="$adminName" />

        {{-- 統計グリッド --}}
        <x-admin.stats-grid 
            :users30d="$users30d"
            :reservations30d="$reservations30d"
            :reviews30d="$reviews30d"
            :avgRating30d="$avgRating30d"
            :activeRate="$activeRate"
            :cancellationRate="$cancellationRate" />

        {{-- 時系列チャート --}}
        <x-admin.chart-section 
            :charts="$charts"
            type="line"
            title="Reservations & Users – Last 30 Days"
            canvasId="line-trend" />

        {{-- 円グラフ --}}
        <x-admin.chart-section 
            :charts="$charts"
            type="pie"
            title="Shops by Area"
            canvasId="pie-area" />

        {{-- サイドパネル --}}
        <x-admin.side-panels 
            :topShops30d="$topShops30d ?? collect()"
            :inactiveShops30d="$inactiveShops30d ?? 0" />
    </div>
</div>
@endsection

@section('js')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script defer src="{{ asset('js/admin/dashboard.js') }}"></script>
@endsection