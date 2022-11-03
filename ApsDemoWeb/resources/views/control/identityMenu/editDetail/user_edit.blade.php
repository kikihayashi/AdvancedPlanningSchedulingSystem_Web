@extends('layouts.sidebar_outer')
@section('outer_content')
<div class="wrapper" style="background:#F0F2F5;">
    <div class="content-wrapper-identity">
        <div style="padding-left:5%;">
            <!--  資料操作結果的提示視窗 -->
            @include('layouts.prompt_layout')
        </div>
        <table style="margin-top:5%;width:92%;">
            <thead>
                <tr>
                    <th>
                        <div class="card" style="width:350px;border-color: #DFDFDF;border-style: solid;">
                            <div class="card-header text-white" style="background-color: #000000;">修改個人資料</div>
                            <div class="card-body">
                                <form method="POST"
                                    action="{{route('UserController.updateUser', ['id'=>$tableData['user']['id'], 'type'=>'SELF'])}}">
                                    @csrf
                                    @method('PUT')
                                    <!-- 姓名 -->
                                    <div class="txt_field">
                                        <input id="name" type="text" class="@error('name') is-invalid @enderror"
                                            name="name" value="{{$tableData['user']['name']}}" required
                                            autocomplete="off">
                                        <label for="name">姓名</label>
                                    </div>

                                    <!-- 帳號 -->
                                    <div class="txt_field">
                                        <input id="account" type="text" class="@error('account') is-invalid @enderror"
                                            name="account" value="{{$tableData['user']['account']}}" required
                                            autocomplete="off">
                                        <label for="account">帳號</label>
                                    </div>

                                    <!-- 密碼 -->
                                    <div class="txt_field">
                                        <input id="password" type="password" pattern=".{4,}" required title="最少4個字元"
                                            name="password" required autocomplete="new-password"
                                            class="@error('password') is-invalid @enderror">
                                        <label for="password">原密碼</label>
                                    </div>

                                    <!-- 新密碼 -->
                                    <div class="txt_field">
                                        <input id="new_password" type="password" pattern=".{4,}" required title="最少4個字元"
                                            name="new_password" required autocomplete="new-password"
                                            class="@error('password') is-invalid @enderror">
                                        <label for="password">新密碼(最少4個字元)</label>
                                    </div>

                                    <!-- 確認新密碼 -->
                                    <div class="txt_field">
                                        <input id="new_password-confirm" type="password" pattern=".{4,}" required
                                            title="最少4個字元" name="new_password_confirmation" required
                                            autocomplete="new-password">
                                        <label for="new_password-confirm">確認新密碼</label>
                                    </div>
                                    <input type="submit" value="儲存">
                                    <div class="back">
                                        <label><a href="javascript:history.go(-1)">回上一頁</a></label>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </th>
                </tr>
            </thead>
        </table>
    </div>
</div>
@endsection