@extends('system.system_content')
@section('inner_content')
<!-- 對話框設置 -->
@include('layouts.script.jq_dialog_layout')
<!-- 參數設定 -->
<script>
let can_basic_crud = ("{{$tableData['permission']['basic_crud']}}" == 'Y');
let tableCalendar = @json($tableData['calendar']);
let tableTransport = @json($tableData['transport']);
let tableSetting = @json($tableData['setting']);

//對話框設置(初始化)
$jq_dialog(function() {
    //註：id必須已存在，如果是透過動態產生append('...')
    //初始化會錯誤，因為還沒產生
    $jq_dialog("#calender-dialog-div").dialog({
        autoOpen: false,
        width: 450,
        height: 300,
        modal: true,
        buttons: {
            "儲存": function() {
                //送出資料，這個是底下form的id
                document.getElementById("calendar-form").submit();
                //關閉對話框
                $jq_dialog(this).dialog("close");
            },
        },
        close: function() {
            //重置預設值
        }
    }).prev(".ui-dialog-titlebar").css("background", "#00C1DE").css("color", "white");

    $jq_dialog("#transport-dialog-div").dialog({
        autoOpen: false,
        width: 450,
        height: 350,
        modal: true,
        buttons: {
            "儲存": function() {
                //送出資料，這個是底下form的id
                document.getElementById("transport-form").submit();
                //關閉對話框
                $jq_dialog(this).dialog("close");
            },
        },
        close: function() {
            //重置預設值
        }
    }).prev(".ui-dialog-titlebar").css("background", "#00C1DE").css("color", "white");

    $jq_dialog("#setting-dialog-div").dialog({
        autoOpen: false,
        width: 450,
        height: 230,
        modal: true,
        buttons: {
            "儲存": function() {
                //送出資料，這個是底下form的id
                document.getElementById("setting-form").submit();
                //關閉對話框
                $jq_dialog(this).dialog("close");
            },
        },
        close: function() {
            //重置預設值
        }
    }).prev(".ui-dialog-titlebar").css("background", "#00C1DE").css("color", "white");
});

//新增&編輯日曆類型按鈕
function clickCalendarBtn(index) {
    if (can_basic_crud) {
        //建立
        var title = "新增資料";
        var name = "";
        var is_checked = false;
        var url = "{{route('ParameterController.writeCalendarType')}}";
        //更新
        if (index >= 0) {
            title = "修改資料";
            name = tableCalendar[index]['name'];
            is_checked = tableCalendar[index]['is_holiday'] == 'Y';
            url = "{{route('ParameterController.writeCalendarType', ':id')}}";
            url = url.replace(':id', tableCalendar[index]['id']);
        }
        $jq_dialog("#calendar-form").attr('action', url); //設置Action
        $jq_dialog("#calendar-name").val(name);
        $jq_dialog("#calendar-is_holiday").prop('checked', is_checked);
        $jq_dialog("#calender-dialog-div").dialog('option', 'title', title);
        $jq_dialog("#calender-dialog-div").dialog("open");
    } else {
        alert('無操作權限！');
        return;
    }
}

//新增&編輯運輸類型按鈕
function clickTransportBtn(index) {
    if (can_basic_crud) {
        //建立
        var title = "新增資料";
        var name = "";
        var abbreviation = "";
        var is_checked = false;
        var url = "{{route('ParameterController.writeTransportType')}}";
        //更新
        if (index >= 0) {
            title = "修改資料";
            name = tableTransport[index]['name'];
            abbreviation = tableTransport[index]['abbreviation'];
            is_checked = tableTransport[index]['is_remark'] == 'Y';
            url = "{{route('ParameterController.writeTransportType', ':id')}}";
            url = url.replace(':id', tableTransport[index]['id']);
        }
        $jq_dialog("#transport-form").attr('action', url); //設置Action
        $jq_dialog("#transport-name").val(name);
        $jq_dialog("#transport-abbreviation").val(abbreviation);
        $jq_dialog("#transport-is_remark").prop('checked', is_checked);
        $jq_dialog("#transport-dialog-div").dialog('option', 'title', title);
        $jq_dialog("#transport-dialog-div").dialog("open");
    } else {
        alert('無操作權限！');
        return;
    }
}

//新增&編輯參數設定按鈕
function clickSettingBtn(index) {
    if (can_basic_crud) {
        //更新
        var title = "修改資料";
        var setting_value = tableSetting[index]['setting_value'];
        var url = "{{route('ParameterController.updateParameterSetting', ':id')}}";
        url = url.replace(':id', tableSetting[index]['id']);

        $jq_dialog("#setting-form").attr('action', url); //設置Action
        $jq_dialog("#setting-setting_value").val(setting_value);
        $jq_dialog("#setting-dialog-div").dialog('option', 'title', title);
        $jq_dialog("#setting-dialog-div").dialog("open");
    } else {
        alert('無操作權限！');
        return;
    }
}
</script>

<!-- 此處用來判斷當前是點選哪個TAB，顯示對應的內容，預設是日曆類型 -->
@php
$selectPage = session('selectPage') ?? 'calendar';
@endphp
<div class="div-nav-tabs">
    <ul class="nav nav-tabs">
        <!-- 日曆類型的TAB -->
        <li class="nav-item">
            <a id="nav-link-a" class="nav-link {{($selectPage=='calendar')? 'active':''}}" data-toggle="tab"
                href="#calendar-page">日曆類型</a>
        </li>
        <!-- 運輸類型的TAB -->
        <li class="nav-item">
            <a id="nav-link-a" class="nav-link {{($selectPage=='transport')? 'active':''}}" data-toggle="tab"
                href="#transport-page">運送類型</a>
        </li>
        <!-- 參數設定的TAB -->
        <li class="nav-item">
            <a id="nav-link-a" class="nav-link {{($selectPage=='setting')? 'active':''}}" data-toggle="tab"
                href="#setting-page">參數設定</a>
        </li>
    </ul>

    <div class="tab-content">
        <!-- 日曆類型的內容 -->
        <div class="tab-pane fade in {{($selectPage=='calendar')? 'active':''}}" id="calendar-page">
            <button id="create-button-calendar" class="btn btn-info" style="margin-top:20px;margin-left:20px;"
                onclick="clickCalendarBtn(-1);">+建立</button>

            <section style="margin:20px;padding-bottom:2%">
                <table class="table table-bordered">
                    <thead style="background-color:#F5F6FB">
                        <tr>
                            <th style="width: 40%">名稱</th>
                            <th style="width: 40%">是否為假日</th>
                            <th style="width: 20%">操作</th>
                        </tr>
                    </thead>
                    <tbody class="parameter-tbody">
                        @foreach(range(0, $tableData['calendar']->count()-1) as $index)
                        @php
                        $calendar = $tableData['calendar']->get($index);
                        @endphp
                        <tr>
                            <td>{{$calendar->name}}</td>
                            <td>{{(strcmp($calendar->is_holiday,'Y')==0)?'是':'否'}}</td>
                            <td style="text-align:left">
                                <button id="edit-button-calendar" class="btn btn-primary btn-flat" style="color:white;"
                                    onclick="clickCalendarBtn('{{$index}}');">編輯</button>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </section>

            <!-- 日曆類型的對話框 -->
            <div class="parameter-form-div" id="calender-dialog-div" style="display:none">
                <p class="validateTips"></p>
                <form id="calendar-form" method="POST">
                    @csrf
                    @method('POST')
                    <fieldset>
                        <div>
                            <div id="div-fieldset">
                                <label style="margin-top: 5px;" for="name">名稱 :</label>
                                <input type="text" name="calendar[]" id="calendar-name"
                                    class="input-large" placeholder="請輸入名稱">
                            </div>
                            <div id="div-fieldset">
                                <label style="margin-top: 8px;" for="is_holiday">是否為假日 :</label>
                                <input type="checkbox" name="calendar[]" id="calendar-is_holiday"
                                    style="transform: scale(1.5);" />
                            </div>
                        </div>
                    </fieldset>
                </form>
            </div>
        </div>

        <!-- 運輸類型的內容 -->
        <div class="tab-pane fade in {{($selectPage=='transport')? 'active':''}}" id="transport-page">
            <button id="create-button-transport" class="btn btn-info" style="margin-top:20px;margin-left:20px;"
                onclick="clickTransportBtn(-1);">+建立</button>

            <section style="margin:20px;padding-bottom:2%">
                <table class="table table-bordered">
                    <thead style="background-color:#F5F6FB">
                        <tr>
                            <th style="width: 30%">名稱</th>
                            <th style="width: 20%">是否為備註</th>
                            <th style="width: 30%">縮寫</th>
                            <th style="width: 20%">操作</th>
                        </tr>
                    </thead>
                    <tbody class="parameter-tbody">
                        @foreach(range(0, $tableData['transport']->count()-1) as $index)
                        @php
                        $transport = $tableData['transport']->get($index);
                        @endphp
                        <tr>
                            <td>{{$transport->name}}</td>
                            <td>{{(strcmp($transport->is_remark,'Y')==0)?'是':'否'}}</td>
                            <td>{{$transport->abbreviation}}</td>
                            <td style="text-align:left">
                                <button id="edit-button-transport" class="btn btn-primary btn-flat" style="color:white;"
                                    onclick="clickTransportBtn('{{$index}}');">編輯</button>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </section>

            <!-- 運輸類型的對話框 -->
            <div class="parameter-form-div" id="transport-dialog-div" style="display:none">
                <p class="validateTips"></p>
                <form id="transport-form" method="POST">
                    @csrf
                    @method('POST')
                    <fieldset>
                        <div>
                            <div id="div-fieldset">
                                <label style="margin-top: 5px;" for="name">名稱 :</label>
                                <input type="text" name="transport[]" id="transport-name"
                                    class="input-large" placeholder="請輸入名稱">
                            </div>
                            <div id="div-fieldset">
                                <label style="margin-top: 5px;" for="abbreviation">縮寫 :</label>
                                <input type="text" name="transport[]" id="transport-abbreviation"
                                    class="input-large" placeholder="請輸入縮寫">
                            </div>
                            <div id="div-fieldset">
                                <label style="margin-top: 8px;" for="is_remark">是否為備註 :</label>
                                <input type="checkbox" name="transport[]" id="transport-is_remark"
                                    style="transform: scale(1.5);">
                            </div>
                        </div>
                    </fieldset>
                </form>
            </div>

        </div>

        <!-- 參數設定的內容 -->
        <div class="tab-pane fade in {{($selectPage=='setting')? 'active':''}}" id="setting-page">
            <section style="margin:20px;padding-bottom:8%">
                <table class="table table-bordered">
                    <thead style="background-color:#F5F6FB">
                        <tr>
                            <th style="width: 40%">名稱</th>
                            <th style="width: 40%">設定值</th>
                            <th style="width: 20%">操作</th>
                        </tr>
                    </thead>
                    <tbody class="parameter-tbody">
                        @foreach(range(0, $tableData['setting']->count()-1) as $index)
                        @php
                        $setting = $tableData['setting']->get($index);
                        @endphp
                        <tr>
                            <td>{{$setting->name}}</td>
                            <td>{{$setting->setting_value}}</td>
                            <td style="text-align:left">
                                <button id="edit-button-setting" class="btn btn-primary btn-flat" style="color:white;"
                                    onclick="clickSettingBtn('{{$index}}');">編輯</button>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </section>

            <!-- 參數設定的對話框 -->
            <div class="parameter-form-div" id="setting-dialog-div" style="display:none">
                <p class="validateTips"></p>
                <form id="setting-form" method="POST">
                    @csrf
                    @method('PUT')
                    <fieldset>
                        <div>
                            <div id="div-fieldset">
                                <label style="margin-top: 5px;" for="setting_value">設定值 :</label>
                                <input type="text" name="setting[]" id="setting-setting_value"
                                    class="input-large" placeholder="請輸入設定值">
                                <input type="hidden" name="systemType" value="basic">
                            </div>
                        </div>
                    </fieldset>
                </form>
            </div>
        </div>
    </div>
</div>
@stop