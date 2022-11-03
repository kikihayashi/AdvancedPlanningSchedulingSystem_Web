@extends('control.control_content')
@section('inner_content')
@include('layouts.script.jq_dialog_layout')
<script>
let can_identity_crud = ("{{$tableData['permission']['identity_crud']}}" == 'Y');
let permissionInfoArray = @json($tableData['permissionInfoArray']);
let permissionArray = @json($tableData['permissionArray']);

//對話框設置
$jq_dialog(function() {
    $jq_dialog("#permissionCreate-dialog-div").dialog({
        autoOpen: false,
        width: 450,
        height: 450,
        modal: true,
        buttons: {
            "儲存": function() {
                //送出資料，這個是底下form的id
                document.getElementById("permissionCreate-form").submit();
                //關閉對話框
                $jq_dialog(this).dialog("close");
            },
        },
        close: function() {
            //重置預設值
        }
    }).prev(".ui-dialog-titlebar").css("background", "#00C1DE").css("color", "white");

    $jq_dialog("#permission-dialog-div").dialog({
        autoOpen: false,
        width: 380,
        height: 200,
        modal: true,
        buttons: {
            "儲存": function() {
                //送出資料，這個是底下form的id
                document.getElementById("permission-form").submit();
                //關閉對話框
                $jq_dialog(this).dialog("close");
            },
        },
        close: function() {
            //重置預設值
        }
    }).prev(".ui-dialog-titlebar").css("background", "#00C1DE").css("color", "white");
});

//偵測使用者選擇的資料項(td)
function selectTdContent(id, name, operation) {
    if (can_identity_crud) {
        for (var i = 0; i < permissionArray.length; i++) {
            if (permissionArray[i]['code'] != 'A' && permissionArray[i]['code'] != 'N') {
                for (var j = 0; j < permissionInfoArray.length; j++) {
                    var elementId = 'td-' + permissionArray[i]['id'] + '-' + permissionInfoArray[j]['name'];
                    if (permissionArray[i]['id'] == id && permissionInfoArray[j]['name'] == name) {
                        //加上邊框
                        document.getElementById(elementId).classList.add("focus");
                        //開啟對話框
                        var title = permissionArray[i]['code'] + '：' + permissionArray[i]['remark'];
                        var operation = operation.replace('<br>', '');
                        var is_checked = permissionArray[i][name] == 'Y';
                        var url = "{{route('PermissionController.updatePermission', ':id')}}";
                        url = url.replace(':id', id);
                        $jq_dialog("#permission-form").attr('action', url); //設置Action
                        $jq_dialog("#permission-codeName").html('是否可操作【' + operation + '】');
                        $jq_dialog("#permission-name").val(name);
                        $jq_dialog("#permission-allow").prop('checked', is_checked);
                        $jq_dialog("#permission-dialog-div").dialog('option', 'title', title);
                        $jq_dialog("#permission-dialog-div").dialog("open");
                    } else {
                        //去除邊框
                        document.getElementById(elementId).classList.remove("focus");
                    }
                }
            }
        }
    } else {
        alert('無權限可操作！');
        return;
    }
}

//新增權限按鈕
function createPermissionBtn() {
    if (can_identity_crud) {
        var title = "新增權限種類";
        $jq_dialog("#permissionCreate-dialog-div").dialog('option', 'title', title);
        $jq_dialog("#permissionCreate-dialog-div").dialog("open");
    } else {
        alert('無權限可操作！');
        return;
    }
}

//刪除權限按鈕
function deletePermissionBtn(code) {
    if (confirm('確定要刪除權限代號 :【' + code + '】？')) {
        var url = "{{route('PermissionController.deletePermission', ':code')}}";
        url = url.replace(':code', code);
        document.getElementById('permissionDelete-form').action = url;
        document.getElementById('permissionDelete-form').submit();
    } else {
        return;
    }
}
</script>

<!-- 權限 -->
<!-- <section style="margin-top:20px;margin-left:4%;margin-right:8%;padding-bottom:8%"> -->
<section id="permission-section">
    <div id="permission-div">
        @if($tableData['permission']['identity_crud']=='N')
        <h1>無權限檢視！</h1>
        @else
        <button id="create-button-permission" class="btn btn-info" style="margin-bottom:20px;"
            onclick="createPermissionBtn();">+新權限</button>
        <table id="permission-table" class="table table-bordered">
            <thead style="background-color:#F5F6FB">
                <tr>
                    <th style="width: 10%">操作</th>
                    <th style="width: {{90-8.5*(count($tableData['permissionInfoArray']))}}%">權限代號 & 說明</th>
                    @foreach($tableData['permissionInfoArray'] as $permissionInfo)
                    <th style="width: 8.5%">{!!$permissionInfo['operation']!!}</th>
                    @endforeach
                </tr>
            </thead>
            <tbody>
                <form id="permissionDelete-form" method="POST">
                    @csrf
                    @method('DELETE')
                </form>
                @foreach($tableData['permissionArray'] as $permission)
                <tr>
                    <td class="permission-td">
                        @if(in_array($permission['code'], $tableData['initPermissionArray']))
                        <button id="edit-button-permission-not-used" class="btn btn-flat">唯讀</button>
                        @else
                        <button class="btn btn-danger" style="color:white;"
                            onclick="deletePermissionBtn('{{$permission['code']}}');">刪除</button>
                        @endif
                    </td>
                    <td style="text-align:left">{{$permission['code']}} :
                        {{($permission['remark']=='')? '無說明' : $permission['remark']}}</td>
                    @foreach($tableData['permissionInfoArray'] as $permissionInfo)
                    <td id="td-{{$permission['id']}}-{{$permissionInfo['name']}}" onclick="selectTdContent(
                    '{{$permission['id']}}',
                    '{{$permissionInfo['name']}}',
                    '{{$permissionInfo['operation']}}');">
                        @if($permission[$permissionInfo['name']]=='Y')
                        <span style="color:#419E2A;font-family:Segoe UI Emoji">&#10004;</span>
                        @else
                        <span>&#10060;</span>
                        @endif
                    </td>
                    @endforeach
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <!-- 新增權限的對話框 -->
    <div class="identity-form-div" id="permissionCreate-dialog-div" style="display:none;">
        <form id="permissionCreate-form" method="POST" action="{{route('PermissionController.createPermission')}}">
            @csrf
            @method('POST')
            <fieldset>
                <div style="margin-top:10px">
                    <div id="div-fieldset-permission">
                        <label style="margin-top: 5px;">權限代號 ：</label>
                        <input type="text" name="code" id="permission-code" class="input-large" placeholder="請輸入權限代號"
                            autocomplete="off" />
                    </div>
                    <div id="div-fieldset-permission">
                        <label style="margin-top: 5px;">權限說明 ：</label>
                        <input type="text" name="remark" id="permission-remark" class="input-large"
                            placeholder="請輸入權限說明" autocomplete="off" />
                    </div>
                    @foreach($tableData['permissionInfoArray'] as $permissionInfo)
                    <div id="div-fieldset-permission">
                        <input type="checkbox" name="{{$permissionInfo['name']}}"
                            style="transform: scale(1.5);" />&emsp;
                        <label style="margin-top: 10px;">{{str_replace('<br>','',$permissionInfo['operation'])}}</label>
                    </div>
                    @endforeach
                </div>
            </fieldset>
        </form>
    </div>

    <!-- 更新權限的對話框 -->
    <div id="permission-dialog-div" style="display:none;">
        <form id="permission-form" method="POST">
            @csrf
            @method('PUT')
            <fieldset>
                <div style="margin-top:10px">
                    <div id="div-fieldset-permission">
                        <input type="checkbox" name="allow" id="permission-allow"
                            style="transform: scale(1.5);" />&emsp;
                        <label id="permission-codeName" style="margin-top: 10px;"></label>
                        <input type="hidden" name="name" id="permission-name" />
                    </div>
                </div>
            </fieldset>
        </form>
    </div>
    @endif
</section>
@stop