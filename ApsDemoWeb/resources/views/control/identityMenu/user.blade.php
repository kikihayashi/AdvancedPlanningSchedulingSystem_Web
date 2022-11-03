@extends('control.control_content')
@section('inner_content')
@include('layouts.script.jq_dialog_layout')
@include('layouts.script.jq_dataTable_layout')

<!-- 使用者 -->
<section id="user-section">
    @if($tableData['permission']['identity_crud']=='N')
    <h1>無權限檢視！</h1>
    @else
    <a id="user-btn-register" class="btn btn-primary" onclick="addUserBtn();"> +新使用者</a>
    @if(count($tableData['user']) == 0)
    <br>
    <span style="margin-top:10px;font-size:30px">尚無使用者</span>
    @else
    <table id="user-table" class="table table-bordered">
        <thead style="background-color:#F5F6FB">
            <tr>
                <th style="width:15%;">操作</th>
                <th style="width:20%;">使用者名稱</th>
                <th style="width:20%;">帳號</th>
                <th style="width:20%;">角色</th>
                <th style="width:25%;">最後更新時間</th>
            </tr>
        </thead>
    </table>

    <!-- 編輯對話框，預設不開啟 -->
    <div class="identity-form-div" id="dialog-form-user" style="visibility:hidden;">
        <form id="formUpdateUser" method="POST">
            @csrf
            @method('PUT')
            <fieldset>
                <div style="margin-top:10px">
                    <div id="div-fieldset">
                        <label style="margin-top: 5px;">名稱：</label>
                        <input type="text" name="name" id="name" class="input-large"
                            placeholder="請輸入姓名">
                    </div>
                    <div id="div-fieldset">
                        <label style="margin-top: 5px;">帳號：</label>
                        <input type="text" name="account" id="account" class="input-large"
                            placeholder="請輸入帳號">
                    </div>
                    <div id="div-fieldset">
                        <label for="work_type">角色：</label>
                        <select style="width:100px;" name="roleId" id="roleName">
                            <div>
                                <div>
                                    <!-- 預設為空，令value=0，讓BasicController判斷使用者是否有選東西 -->
                                    @foreach($tableData['role'] as $role)
                                    <option value="{{$role['id']}}">{{$role['name']}}</option>
                                    @endforeach
                                </div>
                            </div>
                        </select>
                    </div>
                </div>
            </fieldset>
        </form>
    </div>

    <div style="display:none;">
        <form id="formDeleteUser" method="POST">
            @csrf
            @method("DELETE")
        </form>
    </div>
    @endif
    @endif
</section>

<script>
let can_identity_crud = ("{{$tableData['permission']['identity_crud']}}" == 'Y');
let tableRoleMap = @json($tableData['roleMap']);
let columnIdArray = [
    "id",
    "name",
    "account",
    "roleName",
    "updated_at"
];

//下拉式選單，選擇的期別
function addUserBtn() {
    if (can_identity_crud) {
        var url = "{{route('UserController.addUserPage')}}";
        window.location.href = url; //導向使用者點選的期別頁面
    } else {
        alert('無權限可操作！');
        return;
    }
}

//DataTable設置
$jq_dataTable(document).ready(function() {
    //客製化按鈕、語系可參考：https://ithelp.ithome.com.tw/articles/10272813
    $jq_dataTable('#user-table').append(
        '<caption id="user-title">最後更新時間：{{date("Y-m-d H:i:s",(time()+8*3600))}}</caption>');
    var table = $jq_dataTable('#user-table').DataTable({
        data: @json($tableData['user']), //資料
        info: false, //關閉顯示搜尋結果
        ordering: true, //是否要做排序，不要做排序的話，controller裡面的排序資料要註解
        pageLength: 10, //初始每頁顯示幾筆資料
        pagingType: "simple_numbers", //'Next' and 'Last' buttons, page numbers
        bLengthChange: false, //關閉顯示幾項圖示
        processing: false, //顯示處理中，使用方法二時可開啟
        serverSide: false, //啟用ServerSide模式，使用方法二時要開啟
        bAutoWidth: false, //是否啟用自動適應列寬，針對有Tab的頁面，這樣<th>的設定，在轉換Tab時不會不一樣
        lengthChange: true, //是否啟動改變每頁顯示幾筆資料的功能
        lengthMenu: [ //改變每頁顯示幾筆資料設置
            [10, 25, 50, -1],
            [10, 25, 50, "All"]
        ],
        columnDefs: [{
                defaultContent: "--", //設定預設資料
                targets: "_all" //全部欄位套用
            },
            {
                orderable: false, //不排序
                targets: [0, 4] //指定哪些欄位不排序
            },
            {
                className: 'text-center', //資料對齊中間
                targets: [0, 3] //指定哪些欄位對齊中間
            },
            {
                targets: 0,
                render: function(data, type, row) {
                    var id = data;
                    var html = '';
                    html += '<button id="user-delete-btn" type="button" ';
                    html += 'class="btn btn-danger btn-sm">刪除</button> ';
                    html += '<button id="user-update-btn" type="button" ';
                    html += 'class="btn btn-primary btn-sm">編輯</button> ';
                    return html;
                }
            }
        ],
        language: {
            "lengthMenu": "顯示 _MENU_ 筆資料",
            "search": "", //不顯示搜尋兩個字
            "searchPlaceholder": "查詢使用者",
            "sProcessing": "<div class='loader'></div>", //客製處理中動畫
            "sZeroRecords": "没有資料",
            "oPaginate": {
                "sFirst": "<<",
                "sPrevious": "<",
                "sNext": ">",
                "sLast": ">>"
            },
        },
    });

    //刪除按鈕
    $jq_dataTable('#user-table').on('click', '#user-delete-btn', function() {
        if (can_identity_crud) {
            //取得欄位資料
            var columnData = table.row($jq_dataTable(this).parents('tr')).data();
            var id = columnData[0]; //user ID
            var name = columnData[1];
            var account = columnData[2];
            if (confirm('確定要刪除 ' + account + '(' + name + ') 嗎？')) {
                var url = "{{route('UserController.deleteUser', ':id')}}";
                url = url.replace(':id', id);
                document.getElementById('formDeleteUser').action = url;
                document.getElementById('formDeleteUser').submit();
            }
        } else {
            alert('無權限可操作！');
            return;
        }
    });

    //編輯按鈕
    $jq_dataTable('#user-table').on('click', '#user-update-btn', function() {
        if (can_identity_crud) {
            //取得欄位資料
            var columnData = table.row($jq_dataTable(this).parents('tr')).data();
            var id = columnData[0]; //user ID
            var name = columnData[1];
            var account = columnData[2];
            var roleName = columnData[3];
            var roleId = tableRoleMap[roleName];
            document.getElementById(columnIdArray[1]).value = name;
            document.getElementById(columnIdArray[2]).value = account;
            document.getElementById(columnIdArray[3]).value = roleId;
            //設定action
            var url = "{{route('UserController.updateUser', ['id'=>':id','type'=>'ADMIN'])}}";
            url = url.replace(':id', id);
            document.getElementById('formUpdateUser').action = url;
            //顯示對話框
            document.getElementById("dialog-form-user").style.visibility = "visible";
            $jq_dialog("#dialog-form-user").dialog('option', 'title', '修改資料');
            $jq_dialog("#dialog-form-user").dialog("open");
        } else {
            alert('無權限可操作！');
            return;
        }
    });
});

//對話框設置
$jq_dialog(function() {
    $jq_dialog("#dialog-form-user").dialog({
        autoOpen: false,
        height: 350,
        width: 450,
        modal: true,
        buttons: {
            "儲存": function() {
                //儲存資料，這個是底下form的id
                document.getElementById('formUpdateUser').submit();
                //關閉對話框
                $jq_dialog(this).dialog("close");
            },
        },
        close: function() {}
    }).prev(".ui-dialog-titlebar").css("background", "#00C1DE").css("color", "white");
});
</script>
@stop