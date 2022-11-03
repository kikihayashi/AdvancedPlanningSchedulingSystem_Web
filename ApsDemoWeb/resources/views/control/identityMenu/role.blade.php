@extends('control.control_content')
@section('inner_content')
@include('layouts.script.jq_dialog_layout')
<script>
let can_identity_crud = ("{{$tableData['permission']['identity_crud']}}" == 'Y');
let tableRole = @json($tableData['role']);
//對話框設置
$jq_dialog(function() {
    $jq_dialog("#role-dialog-div").dialog({
        autoOpen: false,
        height: 300,
        width: 450,
        modal: true,
        buttons: {
            "儲存": function() {
                //送出資料，這個是底下form的id
                document.getElementById("role-form").submit();
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
function writeRoleBtn(index) {
    if (can_identity_crud) {
        //建立
        var title = "新增資料";
        var name = "";
        var code = tableRole[0]['permission_code'];
        var url = "{{route('RoleController.writeRole')}}";
        //更新
        if (index >= 0) {
            title = "修改資料";
            name = tableRole[index]['name'];
            code = tableRole[index]['permission_code'];

            url = "{{route('RoleController.writeRole', ':id')}}";
            url = url.replace(':id', tableRole[index]['id']);
        }
        $jq_dialog("#role-form").attr('action', url); //設置Action
        $jq_dialog("#role-name").val(name);
        $jq_dialog("#role-code").val(code);
        $jq_dialog("#role-dialog-div").dialog('option', 'title', title);
        $jq_dialog("#role-dialog-div").dialog("open");
    } else {
        alert('無權限可操作！');
        return;
    }
}

//刪除角色按鈕
function deleteRoleBtn(id, name) {
    if (confirm('確定要刪除角色 :【' + name + '】？')) {
        var url = "{{route('RoleController.deleteRole', ':id')}}";
        url = url.replace(':id', id);
        document.getElementById('role-deleteForm').action = url;
        document.getElementById('role-deleteForm').submit();
    } else {
        return;
    }
}
</script>

<!-- 角色 -->
<section style="margin-top:20px;margin-left:4%;margin-right:8%;padding-bottom:8%">
    @if($tableData['permission']['identity_crud']=='N')
    <h1>無權限檢視！</h1>
    @else
    <button id="create-button-role" class="btn btn-info" style="margin-bottom:20px;"
        onclick="writeRoleBtn(-1);">+新角色</button>

    <table id="role-table" class="table table-bordered">
        <thead style="background-color:#F5F6FB">
            <tr>
                <th style="width: 15%">操作</th>
                <th style="width: 18%">角色名稱</th>
                <th style="width: 22%">權限代號</th>
                <th style="width: 45%">更新時間</th>
            </tr>
        </thead>
        <tbody>
            <form id="role-deleteForm" method="POST">
                @csrf
                @method('DELETE')
            </form>
            @foreach(range(0, count($tableData['role']) - 1) as $index)
            @php
            $role = $tableData['role'][$index];
            @endphp
            <tr>
                <td style="text-align: center">
                    @if(
                    ($role['permission_code']=='A' && $role['name']=='系統管理員')||
                    ($role['permission_code']=='W' && $role['name']=='員工')||
                    ($role['permission_code']=='S' && $role['name']=='課長')||
                    ($role['permission_code']=='M' && $role['name']=='經副理')||
                    ($role['permission_code']=='D' && $role['name']=='執行董事')||
                    ($role['permission_code']=='N' && $role['name']=='無權限'))
                    <button id="edit-button-role-not-used" class="btn btn-flat">唯讀</button>
                    @else
                    <div style="display:flex;justify-content: space-around">
                        <button class="btn btn-danger btn-flat" style="color:white;"
                            onclick="deleteRoleBtn('{{$role['id']}}','{{$role['name']}}');">刪除
                        </button>
                        <button class="btn btn-primary btn-flat" style="color:white;"
                            onclick="writeRoleBtn('{{$index}}');">編輯
                        </button>
                    </div>

                    @endif
                </td>
                <td>
                    {{$role['name']}}
                </td>
                <td>
                    {{$role['permission_code']}} : {{$tableData['permissionMap'][$role['permission_code']]['remark']}}
                </td>
                <td>
                    {{$role['updated_at_tw']}}
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <!-- 角色的對話框 -->
    <div class="identity-form-div" id="role-dialog-div" style="display:none;">
        <form id="role-form" method="POST">
            @csrf
            @method('POST')
            <fieldset>
                <div style="margin-top:10px">
                    <div id="div-fieldset">
                        <label style="margin-top: 5px;" for="name">角色名稱 :</label>
                        <input type="text" name="name" id="role-name" class="input-large" placeholder="請輸入角色名稱">
                    </div>
                    <div id="div-fieldset">
                        <label for="permission_code">權限代號 :</label>
                        <select style="width:100px;" name="permissionCode" id="role-code">
                            <div>
                                <div>
                                    <!-- 預設為空，令value=0，讓BasicController判斷使用者是否有選東西 -->
                                    @foreach($tableData['permissionArray'] as $permission)
                                    @if($permission['code']!='A')
                                    <option value="{{$permission['code']}}">{{$permission['code']}}</option>
                                    @endif
                                    @endforeach
                                </div>
                            </div>
                        </select>
                    </div>
                </div>
            </fieldset>
        </form>
    </div>
    @endif
</section>
@stop