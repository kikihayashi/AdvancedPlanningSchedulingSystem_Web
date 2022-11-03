@extends('layouts.sidebar_outer')
@section('outer_content')
<!-- 內框 -->
<div class="wrapper">
    <!-- 左框內容 -->
    <nav id="sidebar">
        <ul class="list-unstyled components">
            <a id="sidebar-ul-a">{{$tableData['title']}}</a>
            <!-- 基本資料維護 -->
            <li>
                <a id="basicMenu-a" onclick="change('{{$openMenu??''}}');" href="#basicMenu" data-toggle="collapse" aria-expanded="false"
                    class="dropdown-toggle">基本資料維護</a>
                <div id="basicMenu" class="collapse">
                    <ul class="list-unstyled">
                        <li>
                            <a id="parameter" href="{{route('ParameterController.showParameterPage')}}">參數設定</a>
                        </li>
                        <li>
                            <a id="equipment" href="{{route('EquipmentController.showEquipmentPage')}}">機種清單</a>
                        </li>
                        <li>
                            <a id="period" href="{{route('PeriodController.showPeriodPage')}}">期別與結算</a>
                        </li>
                        <li>
                            <a id="schedule" href="{{route('ScheduleController.showSchedulePage')}}">行事曆</a>
                        </li>
                    </ul>
                </div>
            </li>

            <!-- 大計畫資料維護 -->
            <li>
                <a id="projectMenu-a" onclick="change('{{$openMenu??''}}');" href="#projectMenu" data-toggle="collapse"
                    aria-expanded="false" class="dropdown-toggle">大計畫資料維護</a>
                <div id="projectMenu" class="collapse">
                    <ul class="list-unstyled">
                        <li>
                            <a id="management" href="{{route('ManagementController.showManagementPage')}}">大計畫規劃管理</a>
                        </li>
                        <li>
                            <a id="productionYear"
                                href="{{route('ProductionYearController.showProductionYearPage')}}">年度生產計畫</a>
                        </li>
                        <li>
                            <a id="shippingYear"
                                href="{{route('ShippingYearController.showShippingYearPage')}}">年度出荷計畫</a>
                        </li>
                        <li>
                            <a id="productionMonth"
                                href="{{route('ProductionMonthController.showProductionMonthPage')}}">月度生產計畫</a>
                        </li>
                        <li>
                            <a id="shippingMonth"
                                href="{{route('ShippingMonthController.showShippingMonthPage')}}">月度出荷計畫</a>
                        </li>
                    </ul>
                </div>
            </li>

            <!-- 資料維護 -->
            <li>
                <a id="maintainMenu-a" onclick="change('{{$openMenu??''}}');" href="#maintainMenu" data-toggle="collapse"
                    aria-expanded="false" class="dropdown-toggle">資料維護</a>
                <div id="maintainMenu" class="collapse">
                    <ul class="list-unstyled">
                        <li>
                            <a id="file" href="{{route('FileController.showFilePage')}}">檔案管理</a>
                        </li>
                        <li>
                            <a id="record" href="{{route('RecordController.showRecordPage')}}">變更記錄管理</a>
                        </li>
                        <li>
                            <a id="contents" href="{{route('ContentController.showContentPage')}}">報表內容管理</a>
                        </li>
                    </ul>
                </div>
            </li>
        </ul>
    </nav>

    <!-- 中間內容 -->
    <div id="content-main" class="content-wrapper">
        <!--  資料操作結果的提示視窗 -->
        @include('layouts.prompt_layout')
        <!-- 左外框縮放按鈕 -->
        <i type="button" id="sidebarCollapse" class="fa fa-bars"></i>
        <!-- 標題 -->
        <span id="title-span">{{$tableData['title']}}</span>
        <!-- 分隔線 -->
        <div class="line"></div>
        <!-- 主要畫面 -->
        @yield('inner_content')
    </div>
</div>
@stop