@extends('layouts.sidebar_outer')
@section('outer_content')
<!-- 內框 -->
<div class="wrapper">
    <!-- 左框內容 -->
    <nav id="sidebar">
        <ul class="list-unstyled components">
            <a id="sidebar-ul-a">期別與結算</a>
            <ul class="list-unstyled">
                <li>
                    <a id="partition" href="{{route('PeriodController.showPartitionPage', $period_tw)}}">結算維護</a>
                </li>
                <li>
                    <a id="exchange" href="{{route('PeriodController.showExchangePage', $period_tw)}}">匯率設定</a>
                </li>
            </ul>
        </ul>
    </nav>

    <!-- 中間內容 -->
    <div id="content-main" class="content-wrapper">
        <!-- 返回 -->
        <a href="{{route('PeriodController.showPeriodPage')}}" class="period-title-a btn btn-secondary">返回</a>
        <!-- 左外框縮放按鈕 -->
        <i type="button" id="sidebarCollapse" class="fa fa-bars"></i>
        <!-- 標題 -->
        <span id="title-span">台京第{{$period_tw}}期</span>
        <!-- 分隔線 -->
        <div class="line" style="margin:9px 0;"></div>
        <!-- 主要畫面 -->
        @yield('inner_content')
    </div>
</div>
@stop