<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>APS Demo</title>
    <!-- Fonts -->
    <!-- <link href="//fonts.gstatic.com" rel="dns-prefetch"> -->
    <!-- <link href="https://fonts.googleapis.com/css?family=Nunito" rel="stylesheet"> -->
    <!-- home -->

    <!-- <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css" rel="stylesheet"> -->
    <!-- sidebar_outer -->
    <!-- Table必要 -->
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.4.1/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://netdna.bootstrapcdn.com/bootstrap/3.0.0/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap -->
    <!-- <link href="css/bootstrap.min.css" rel="stylesheet"> -->
    <!-- <link href="{{ asset('css/app.css') }}" rel="stylesheet"> -->
    <!-- 引用內部css -->
    <link href="/css/tool.css" rel="stylesheet">
    <link href="/css/sidebar_outer.css" rel="stylesheet">
    <link href="/css/sidebar_inner.css" rel="stylesheet">
    <link href="/css/identity_menu.css" rel="stylesheet">
    <link href="/css/basic_menu.css" rel="stylesheet">
    <link href="/css/project_menu.css" rel="stylesheet">
    <link href="/css/maintain_menu.css" rel="stylesheet">
    <!-- <link href="/css/login.css" rel="stylesheet"> -->
    <!-- <link href="//apps.bdimg.com/libs/jqueryui/1.10.4/css/jquery-ui.min.css" rel="stylesheet"> -->
    <!-- 上下展開功能需要引用的元件 -->
    <!-- 最左邊邊框元件跳動效果 -->
    <!-- <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css" rel="stylesheet"> -->
</head>

<body>
    <!-- 如果路由名稱含有login、registerUser，不顯示左邊和上面外框 -->
    @if(Route::currentRouteName()!='login' && Route::currentRouteName()!='registerUser')
    <!-- 頂部外框 -->
    @include('layouts.sidebar_top')
    <!-- 左邊外框 -->
    @include('layouts.sidebar_left')
    @endif
    <!-- 中間內容 -->
    <div>
        <main style="margin-left:50px;margin-top:50px;">
            @include('layouts.script.js_sidebar_layout')
            @yield('outer_content')
        </main>
    </div>
</body>

</html>