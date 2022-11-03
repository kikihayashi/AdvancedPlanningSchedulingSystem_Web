@extends('system.system_content')
@section('inner_content')
<!-- 對話框功能 -->
@include('layouts.script.jq_dialog_layout')
<!-- 縮放功能 -->
@include('layouts.script.js_scaling_layout')
<!-- 期別與仕切維護 -->
<script>
let can_basic_crud = ("{{$tableData['permission']['basic_crud']}}" == 'Y');
let can_period_delete = ("{{$tableData['permission']['period_delete']}}" == 'Y');
let tablePeriod = @json($tableData['period']);
//對話框設置
$jq_dialog(function() {
    $jq_dialog("#period-dialog-div").dialog({
        autoOpen: false,
        height: 300,
        width: 450,
        modal: true,
        buttons: {
            "儲存": function() {
                //送出資料，這個是底下form的id
                document.getElementById("period-form").submit();
                //關閉對話框
                $jq_dialog(this).dialog("close");
            },
        },
        close: function() {
            //重置預設值
        }
    }).prev(".ui-dialog-titlebar").css("background", "#00C1DE").css("color", "white");
});

//新增&編輯期別按鈕
function clickPeriodBtn(index) {
    if (can_basic_crud) {
        //建立
        var title = "新增資料";
        var period_tw = "";
        var start_date = "1";
        var url = "{{route('PeriodController.writePeriod')}}";
        //更新
        if (index >= 0) {
            title = "修改資料";
            period_tw = tablePeriod[index]['period_tw'];
            start_date = tablePeriod[index]['start_date'];
            url = "{{route('PeriodController.writePeriod', ':id')}}";
            url = url.replace(':id', tablePeriod[index]['id']);
        }
        $jq_dialog("#period-form").attr('action', url); //設置Action
        $jq_dialog("#period-period_tw").val(period_tw);
        $jq_dialog("#period-start_date").val(start_date);
        inputListener();
        $jq_dialog("#period-dialog-div").dialog('option', 'title', title);
        $jq_dialog("#period-dialog-div").dialog("open");
    } else {
        alert('無操作權限！');
        return;
    }
}

//刪除期別按鈕
function deletePeriodBtn(index) {
    //如果有刪除權限
    if (can_basic_crud && can_period_delete) {
        //如果目前期別大於1個
        if (tablePeriod.length > 1) {
            var period_tw = tablePeriod[index]['period_tw'];
            var period_id = tablePeriod[index]['id'];
            if (confirm('確定要刪除，台京期數第' + period_tw + '期？')) {
                document.getElementById('formDeletePeriod-' + period_id).submit();
            }
        } else {
            alert('不可刪除，期別至少要有一個！');
            return;
        }
    } else {
        alert('無操作權限！');
        return;
    }
}

//偵測輸入台京期數的值
function inputListener() {
    //獲取id
    var period_tw_id = "period-period_tw";
    var period_jp_id = "period-period_jp";
    var years_id = "period-years";
    //設定value
    var period_tw = document.getElementById(period_tw_id).value;
    var period_jp = (parseInt(period_tw) > 0) ? (parseInt(period_tw) + 105) : '';
    var years = (parseInt(period_tw) > 0) ? (parseInt(period_tw) + 1969) : '';

    document.getElementById(period_jp_id).value = period_jp;
    document.getElementById(years_id).value = years;
    document.getElementById(period_jp_id).style.color = '#696969';
    document.getElementById(years_id).style.color = '#696969';
    document.getElementById(period_jp_id).style.background = '#F5F5F5';
    document.getElementById(years_id).style.background = '#F5F5F5';
}
</script>

<!-- 期別與仕切維護內容 -->
<div>
    <main>
        <div class="main-flex">
            <button id="create-button-period" class="btn btn-info" style="margin-top:0px;margin-left:2%;"
                onclick="clickPeriodBtn(-1);">+建立</button>
            <!-- 期別與仕切維護的對話框 -->
            <div class="period-div" id="period-dialog-div" style="display:none;">
                <p class="validateTips"></p>
                <form id="period-form" method="POST">
                    @csrf
                    @method('POST')
                    <fieldset>
                        <div style="margin-top:10px">
                            <div id="div-fieldset" style="margin-left:2%">
                                <label style="margin-top:5px;" for="period_tw">台京期數 :</label>
                                <input style="width:100px;" type="number" min="1" name="period[]" id="period-period_tw"
                                    class="input-small" oninput="inputListener()">
                                <label class="label-not-used" for="period_jp">日京期數 :</label>
                                <input class="input-small not-used" type="text" name="period[]" id="period-period_jp">
                            </div>
                            <div id="div-fieldset" style="margin-left:2%">
                                <label style="margin-top:5px;" for="start_date">生產起始日 :</label>
                                <select style="width:85px;" name="period[]" id="period-start_date">
                                    <div>
                                        <div>
                                            <option value="1" selected>1</option>
                                            <!-- <option value="26">26</option> -->
                                        </div>
                                    </div>
                                </select>
                                <label class="label-not-used" for="years">年&emsp;分&emsp; :</label>
                                <input class="input-small not-used" type="text" name="period[]" id="period-years">
                            </div>
                        </div>
                    </fieldset>
                </form>
            </div>
            <!-- 縮放上、下半年提示框的按鈕 -->
            <a id="fa-toggle" onclick="toggleMenu();" class="fa fa-angle-double-down"
                style="margin-right:10px;margin-left:10px;font-size:24px">
            </a>
        </div>

        <!-- 上、下半年提示框 -->
        <div id="menus" style="display:none;margin-left:2%;margin-right:6%;margin-top:5px;">
            <ul id="ul-toggle">
                <strong><a>上半年：</a></strong>
                @foreach(range(0, 5) as $index)
                <button class="btn menus-button" style="pointer-events: none;">
                    {{$tableData['monthMaps'][$index]['page']}}月
                </button>
                @endforeach
                <br><br>
                <strong><a>下半年：</a></strong>
                @foreach(range(6, 11) as $index)
                <button class="btn menus-button" style="pointer-events: none;">
                    {{$tableData['monthMaps'][$index]['page']}}月
                </button>
                @endforeach
            </ul>
        </div>
    </main>

    <section style="margin-left:2%;margin-right:6%;padding-bottom:8%">
        <table class="table table-bordered">
            <thead style="background-color:#F5F6FB">
                <tr>
                    <th style="width: 30%">操作</th>
                    <th style="width: 17.5%">台京期數</th>
                    <th style="width: 17.5%">日京期數 </th>
                    <th style="width: 17.5%">年份</th>
                    <th style="width: 17.5%">生產起始日</th>
                </tr>
            </thead>

            <tbody class="period-tbody">
                @if($tableData['period']->count()==0)
                <tr>
                    <td colspan="5">
                        <span style="margin-top:10px;font-size:30px">尚無資料</span>
                    </td>
                </tr>
                @else
                @foreach(range(0, $tableData['period']->count() - 1) as $index)
                @php
                $period = $tableData['period']->get($index);
                @endphp
                <tr>
                    <td style="text-align:left">
                        <a id="edit-button-period" class="btn btn-primary btn-flat" style="color:white;"
                            onclick="clickPeriodBtn('{{$index}}');">編輯</a>

                        <a id="partition-button-{{$period->id}}" class="btn btn-secondary btn-flat" style="color:white;"
                            href="{{route('PeriodController.showPartitionPage', $period->period_tw)}}">結算表</a>

                        <a id="exchange-button-{{$period->id}}" class="btn btn-warning btn-flat" style="color:black;"
                            href="{{route('PeriodController.showExchangePage', $period->period_tw)}}">匯率</a>

                        <a style="color:white;" class="btn btn-danger btn-flat"
                            onclick="deletePeriodBtn('{{$index}}');">刪除</a>
                        <form id="formDeletePeriod-{{$period->id}}" style="display:none;" method="POST"
                            action="{{route('PeriodController.deletePeriod', $period->id)}}">
                            @csrf
                            @method('DELETE')
                            <!-- 如果需要傳多參數再用 -->
                            <!-- <input type="hidden" value="{{$period->id}}" name="periodID"> -->
                        </form>
                    </td>
                    <td>{{$period->period_tw}}</td>
                    <td>{{$period->period_jp}}</td>
                    <td>{{$period->years}}</td>
                    <td>{{$period->start_date}}</td>
                </tr>
                @endforeach
                @endif
            </tbody>
        </table>
    </section>
</div>
@stop