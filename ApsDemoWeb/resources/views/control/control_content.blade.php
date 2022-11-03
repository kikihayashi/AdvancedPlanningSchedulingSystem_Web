@extends('layouts.sidebar_outer')
@section('outer_content')
<div class="wrapper">
    <nav id="sidebar">
        <ul class="list-unstyled components">
            <a id="sidebar-ul-a">{{$tableData['title']}}</a>
            <li>
                <a id="identityMenu-a" onclick="change('{{$openMenu??''}}');" href="#identityMenu" data-toggle="collapse"
                    aria-expanded="false" class="dropdown-toggle">身分識別管理</a>
                <div id="identityMenu" class="collapse">
                    <ul class="list-unstyled">
                        <li>
                            <a id="user" href="{{route('UserController.showUserPage')}}">使用者</a>
                        </li>
                        <li>
                            <a id="role" href="{{route('RoleController.showRolePage')}}">角色</a>
                        </li>
                        <li>
                            <a id="permission" href="{{route('PermissionController.showPermissionPage')}}">權限</a>
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