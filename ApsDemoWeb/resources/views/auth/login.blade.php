@extends('layouts.sidebar_outer')
<img style="background:#F0F2F5;position:fixed;top:0;width:100%;height:100%" src="../img/bg.png" alt="圖片已失效">
@section('outer_content')
<div class="wrapper">
    <div class="content-wrapper-login">
        <table style="border:none;width:92%;margin-top: 1%;margin-bottom: 8%;">
            <thead>
                <tr>
                    <th>
                        <h1 style="margin-top:-30px;font-size:32px;color:#000000"><strong>APS管理系統</strong></h1>
                        <div class="card" style="width:350px;margin-top:60px">
                            <div class="card-header text-white" style="background-color: #000000;">
                                {{ __('登入') }}
                            </div>
                            <div class="card-body">
                                <form method="POST" action="{{ route('login') }}">
                                    @csrf
                                    <div class="txt_field">
                                        <input id="account" type="text"
                                            class="{{ $errors->has('account') ? ' is-invalid' : '' }}" name="account"
                                            value="{{ old('account') }}" autocomplete="off" required>

                                        @error('account')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                        @enderror
                                        <label>帳號</label>
                                    </div>

                                    <div class="txt_field">
                                        <input id="password" type="password"
                                            class="@error('password') is-invalid @enderror" name="password" required>

                                        @error('password')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                        @enderror
                                        <label>密碼</label>
                                    </div>
                                    <input type="submit" value="登入">
                                    <hr style="border-top: 1px solid #BFC0C2;">
                                    <a class="register-btn btn" href="{{route('registerUser')}}">註冊</a>
                                </form>
                            </div>
                        </div>
                        <p class="login-bottom-title">Created By kikihayashi
                            2022</p>
                    </th>
                </tr>
            </thead>
        </table>
    </div>
</div>
@endsection