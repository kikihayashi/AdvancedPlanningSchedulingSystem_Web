@extends('layouts.sidebar_outer')
@if(Route::currentRouteName() == 'registerUser')
<img style="background:#F0F2F5;position:fixed;top:0;width:100%;height:100%" src="../img/bg.png" alt="圖片已失效">
@endif
@section('outer_content')
@php
switch($type){
case 'HOME':
$title = '註冊';
$buttonName = '註冊';
$backgroundColor = '';
$marginTop = 30;
break;
case 'IDENTITY':
$title = '新增使用者';
$buttonName = '新增';
$backgroundColor = '#F0F2F5';
$marginTop = 60;
break;
}
@endphp
<div class="wrapper" style="background:{{$backgroundColor}};">
    <div class="content-wrapper-identity">
        <div style="position:fixed;top:0;margin-top:2%;padding-left:5%;">
            <!--  資料操作結果的提示視窗-->
            @include('layouts.prompt_layout')
        </div>
        <table style="margin-top:1%;width:92%;">
            <thead>
                <tr>
                    <th>
                        @if(Route::currentRouteName() == 'registerUser')
                        <h1 style="margin-top:-30px;font-size:32px;color:#000000"><strong>APS管理系統</strong></h1>
                        @endif
                        <div class="card" style="margin-top:{{$marginTop}}px;width:350px;">
                            <div class="card-header text-white" style="background-color: #000000;">{{$title}}</div>

                            <div class="card-body">
                                <form method="POST" action="{{route('UserController.createUser', $type)}}">
                                    @csrf
                                    <!-- 姓名 -->
                                    <div class="txt_field">
                                        <input id="name" type="text" class="@error('name') is-invalid @enderror"
                                            name="name" value="{{ old('name') }}" required autocomplete="off">
                                        @error('name')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                        @enderror
                                        <label for="name">姓名</label>
                                    </div>

                                    <!-- 帳號 -->
                                    <div class="txt_field">
                                        <input id="account" type="text" class="@error('account') is-invalid @enderror"
                                            name="account" value="{{ old('account') }}" required autocomplete="off">
                                        @error('account')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                        @enderror
                                        <label for="account">帳號</label>
                                    </div>

                                    <!-- 密碼 -->
                                    <div class="txt_field">
                                        <input id="password" type="password" pattern=".{4,}" required title="最少4個字元"
                                            name="password" required autocomplete="new-password"
                                            class="@error('password') is-invalid @enderror">

                                        @error('password')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                        @enderror
                                        <label for="password">{{ __('密碼(最少4個字元)') }}</label>
                                    </div>

                                    <!-- 確認密碼 -->
                                    <div class="txt_field">
                                        <input id="password-confirm" type="password" pattern=".{4,}" required
                                            title="最少4個字元" name="password_confirmation" required
                                            autocomplete="new-password">
                                        <label for="password-confirm">{{ __('確認密碼') }}</label>
                                    </div>

                                    <input type="submit" value="{{$buttonName}}">
                                    <div class="back">
                                        <label><a href="javascript:history.go(-1)">回上一頁</a></label>
                                    </div>
                                </form>
                            </div>
                        </div>
                        @if(Route::currentRouteName() == 'registerUser')
                        <p class="register-bottom-title">Created By kikihayashi
                            2022</p>
                        @endif
                    </th>
                </tr>

            </thead>

        </table>
    </div>
</div>
@endsection