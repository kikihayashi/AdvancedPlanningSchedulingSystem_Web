@extends('system.system_content')
@section('inner_content')
<!-- 對話框設置 -->
@include('layouts.script.jq_dialog_layout')
<!-- CSS轉圖片設置 -->
@include('layouts.script.jq_cssToImage_layout')
<script>
let can_maintain_crud = ("{{$tableData['permission']['maintain_crud']}}" == 'Y');
let tableSetting = @json($tableData['setting']);
//對話框設置
$jq_dialog(function() {
    $jq_dialog("#setting-dialog-div").dialog({
        autoOpen: false,
        height: 260,
        width: 450,
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

//新增&編輯參數設定按鈕
function clickSettingBtn(index) {
    if (can_maintain_crud) {
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

//預覽圖片
function previewSignImage(projectType) {
    if (can_maintain_crud) {
        var inputArray = document.getElementById(projectType + '-sign-table').getElementsByClassName('signData');
        var totalImageNumber = inputArray.length / 2;
        var previewIdArray = new Array();

        for (var i = 0; i < inputArray.length / 2; i++) {
            document.getElementById(projectType + '_department-' + i).innerHTML = inputArray[i].value;
            document.getElementById(projectType + '_user-' + i).innerHTML = inputArray[totalImageNumber + i].value;
            if (inputArray[i].value.length > 0 && inputArray[totalImageNumber + i].value.length > 0) {
                document.getElementById(projectType + '-sign-' + i).style.visibility = "visible";
                previewIdArray.push(i);
            } else {
                document.getElementById(projectType + '-sign-' + i).style.visibility = "hidden";
            }
        }
        if (previewIdArray.length == 0) {
            alert('作成、審查必須要有資料！');
            $('#' + projectType + '-btn-save').hide();
        } else {
            resetSignImage(projectType);
            for (var i = 0; i < previewIdArray.length; i++) {
                createSignImage(projectType, previewIdArray[i], previewIdArray.length, totalImageNumber);
            }
        }
    } else {
        alert('無操作權限！');
        return;
    }
}

//先重置預覽圖片
function resetSignImage(projectType) {
    var inputArray = document.getElementById(projectType + '-sign-table').getElementsByClassName('signData');
    var totalImageNumber = inputArray.length / 2;
    for (var i = 0; i < totalImageNumber; i++) {
        $('#' + projectType + '-signBase64-' + i).val("");
    }
    $('#' + projectType + '-btn-save').toggleClass("finish", false);
    $('#' + projectType + '-btn-save').toggleClass("yet");
    $('#' + projectType + '-btn-save').html('設定中請稍後...');
    $('#' + projectType + '-btn-save').show();
}

//產生預覽圖片
function createSignImage(projectType, i, previewNumber, totalImageNumber) {
    html2canvas(document.querySelector('#' + projectType + '-sign-' + i), {
        width: 100,
        height: 100
    }).then(function(canvas) {
        $('#' + projectType + '-signBase64-' + i).val(canvas.toDataURL("image/png"));
        checkSignImage(projectType, previewNumber, totalImageNumber);
    });
}

//驗證是否可以顯示儲存按鈕
function checkSignImage(projectType, previewNumber, totalImageNumber) {
    var validNumber = 0;
    for (var i = 0; i < totalImageNumber; i++) {
        if ($('#' + projectType + '-signBase64-' + i).val().length > 0) {
            validNumber++;
        }
    }
    if (validNumber == previewNumber) {
        $('#' + projectType + '-btn-save').toggleClass("yet", false);
        $('#' + projectType + '-btn-save').toggleClass("finish");
        $('#' + projectType + '-btn-save').html('儲存圖片');
    }
}

//儲存圖片
function saveSignImage(projectType) {
    if (can_maintain_crud) {
        if (confirm("確定要儲存當前預覽圖片嗎？")) {
            var inputArray = document.getElementById(projectType + '-sign-table').getElementsByClassName('signData');
            var totalImageNumber = inputArray.length / 2;
            var customNumber = (projectType == 'PM') ? 0 : 1;
            for (var i = 0; i < totalImageNumber - customNumber; i++) {
                if ($('#' + projectType + '-signBase64-' + i).val().length == 0) {
                    alert('作成、審查必須要有資料！');
                    return false;
                }
            }
            document.getElementById('selectSubPage').value = projectType;
        } else {
            return false;
        }
    } else {
        alert('無操作權限！');
        return;
    }
}
</script>
<!-- 此處用來判斷當前是點選哪個TAB，顯示對應的內容，預設是基本設定 -->
@php
$selectPage = session('selectPage') ?? 'setting';
$selectSubPage = session('selectSubPage') ?? 'PY';
@endphp
<div class="div-nav-tabs">
    <ul class="nav nav-tabs">
        <!-- 基本設定的TAB -->
        <li class="nav-item">
            <a id="nav-link-a" class="nav-link {{($selectPage=='setting')? 'active':''}}" data-toggle="tab"
                href="#setting-page">基本設定</a>
        </li>
        <!-- 電子章設定的TAB -->
        <li class="nav-item">
            <a id="nav-link-a" class="nav-link {{($selectPage=='sign')? 'active':''}}" data-toggle="tab"
                href="#sign-page">電子章設定</a>
        </li>
    </ul>

    <div class="tab-content">
        <!-- 基本設定的內容 -->
        <div class="tab-pane fade in {{($selectPage=='setting')? 'active':''}}" id="setting-page">
            <section style="margin-top:3%;margin-left:3%;margin-right:5%;padding-bottom:8%">
                <table class="table table-bordered">
                    <thead style="background-color:#F5F6FB">
                        <tr>
                            <th colspan="3">員工 ISO文件設定</th>
                        </tr>
                        <tr>
                            <th style="width: 40%">名稱</th>
                            <th style="width: 40%">設定值</th>
                            <th style="width: 20%">操作</th>
                        </tr>
                    </thead>
                    <tbody class="iso-tbody">
                        @foreach(range(8, 12) as $index)
                        @php
                        $setting = $tableData['setting'][$index];
                        @endphp
                        <tr>
                            <td>{{$setting['name']}}</td>
                            <td>{{$setting['setting_value']}}</td>
                            <td style="text-align:left">
                                <button id="edit-button-setting" class="btn btn-primary btn-flat" style="color:white;"
                                    onclick="clickSettingBtn('{{$index}}');">編輯</button>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>

                <!-- 參數設定的對話框 -->
                <div class="iso-form-div" id="setting-dialog-div" style="display:none">
                    <p class="validateTips"></p>
                    <form id="setting-form" method="POST">
                        @csrf
                        @method('PUT')
                        <fieldset>
                            <div style="margin-top:10px">
                                <div id="div-fieldset">
                                    <label style="margin-top: 5px;" for="setting_value">設定值 :</label>
                                    <input type="text" name="setting[]" id="setting-setting_value"
                                        class="input-large" placeholder="請輸入設定值">
                                    <input type="hidden" name="systemType" value="maintain">
                                </div>
                            </div>
                        </fieldset>
                    </form>
                </div>
            </section>
        </div>

        @php
        $projectTypeArray = $tableData['projectTypeArray'];
        @endphp
        <!-- 電子章設定的內容 -->
        <div class="tab-pane fade in {{($selectPage=='sign')? 'active':''}}" id="sign-page">
            <div style="margin-top:2%;" class="iso-div-nav-tabs">
                <ul class="nav nav-tabs">
                    @foreach($projectTypeArray as $projectType => $infoArray)
                    <li class="nav-item">
                        <a id="iso-nav-link-a" class="nav-link {{($selectSubPage == $projectType)? 'active':''}}"
                            data-toggle="tab" href="#{{$projectType}}-page">{{$infoArray['name']}}</a>
                    </li>
                    @endforeach
                </ul>

                <section style="margin-top:2%;padding-bottom:8%">
                    <form method="POST" id="sign-form" action="{{route('ContentController.writeContent')}}">
                        @csrf
                        @method('POST')
                        <input type="hidden" id="selectSubPage" name="selectSubPage">
                        <div class="tab-content">
                            @foreach($projectTypeArray as $projectType => $infoArray)
                            <div class="tab-pane fade in {{($selectSubPage == $projectType)? 'active':''}}"
                                id="{{$projectType}}-page">
                                <div style="float:left;margin-bottom:2%">
                                    <button type="button" class="btn btn-success maintain-btn-preview"
                                        id="{{$projectType}}-btn-preview"
                                        onclick="previewSignImage('{{$projectType}}');">預覽圖片</button>
                                    <button type="submit" class="btn btn-primary maintain-btn-save"
                                        id="{{$projectType}}-btn-save" style="display:none;"
                                        onclick="return saveSignImage('{{$projectType}}');">儲存圖片</button>
                                </div>
                                <table id="{{$projectType}}-sign-table" class="table table-bordered sign-table">
                                    <thead style="background-color:#F5F6FB">
                                        <tr>
                                            <th style="width:10%"></th>
                                            @foreach($infoArray['title'] as $title)
                                            <th>{{$title}}</th>
                                            @endforeach
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td style="background-color:#F5F6FB">部門</td>
                                            @foreach($infoArray['department'] as $department)
                                            <td>
                                                <input type="text" name="{{$projectType}}-department[]"
                                                    value="{{$department}}" placeholder="請輸入部門"
                                                    class="signData"
                                                    autocomplete="off">
                                            </td>
                                            @endforeach
                                        </tr>
                                        <tr>
                                            <td style="background-color:#F5F6FB">名字</td>
                                            @foreach($infoArray['user'] as $user)
                                            <td>
                                                <input type="text" name="{{$projectType}}-user[]" value="{{$user}}"
                                                    placeholder="請輸入名字"
                                                    class="signData"
                                                    autocomplete="off">
                                            </td>
                                            @endforeach
                                        </tr>
                                        <tr>
                                            <td style="background-color:#F5F6FB">預覽</td>
                                            @foreach(range(0, count($infoArray['title'])-1) as $i)
                                            <td>
                                                <input type="hidden" id="{{$projectType}}-signBase64-{{$i}}"
                                                    name="{{$projectType}}-signBase64[]">
                                                <div id="{{$projectType}}-sign-{{$i}}" class="sign-div"
                                                    style="visibility:hidden">
                                                    <div class="circle">
                                                        <span id="{{$projectType}}_department-{{$i}}"></span>
                                                        <div class="circle-line"></div>
                                                        <div class="circle-space"></div>
                                                        <div class="circle-line"></div>
                                                        <span id="{{$projectType}}_user-{{$i}}"></span>
                                                    </div>
                                                </div>
                                            </td>
                                            @endforeach
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                            @endforeach
                        </div>
                    </form>
                </section>
            </div>
        </div>
    </div>
</div>
@stop