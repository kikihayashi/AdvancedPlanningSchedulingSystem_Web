@extends('layouts.sidebar_outer')
@section('outer_content')
<div style="padding-top:14px;">
    <!-- 標題 -->
    <span id="title-span" style="font-size:20px;margin-left:2%">{{$tableData['title']}}</span>
    <!-- 分隔線 -->
    <div class="line"></div>
</div>
<table style="margin:2%;width:80%;" class="table table-bordered">
    <thead style="background:#F5F5F6">
        <tr>
            <th style="font-size:15px">歡迎</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td>
                <img class="logo-img" src="../img/logo.png" alt="圖片已失效">
                <span class="logo-title">
                    <a href="https://github.com/kikihayashi">
                        About Me
                    </a>
                </span>
            </td>
        </tr>
    </tbody>
</table>
@endsection