@extends('system.system_content')
@section('inner_content')
<!-- 對話框設置 -->
@include('layouts.script.jq_dialog_layout')
<!-- dataTable設置 -->
@include('layouts.script.jq_dataTable_layout')
<script>
let can_maintain_crud = ("{{$tableData['permission']['maintain_crud']}}" == 'Y');

//對話框設置
$jq_dialog(function() {
    $jq_dialog("#upload-dialog-div").dialog({
        autoOpen: false,
        height: 300,
        width: 430,
        modal: true,
        buttons: {
            "上傳": function() {
                //送出資料，這個是底下form的id
                document.getElementById("upload-form").submit();
                //關閉對話框
                $jq_dialog(this).dialog("close");
            },
        },
        close: function() {
            //重置預設值
        }
    }).prev(".ui-dialog-titlebar").css("background", "#00C1DE").css("color", "white");
});

//上傳按鈕
function uploadFile() {
    if (can_maintain_crud) {
        var title = "上傳檔案";
        var url = "{{route('FileController.uploadFile')}}";
        $jq_dialog("#upload-form").attr('action', url); //設置Action
        $jq_dialog("#upload-dialog-div").dialog('option', 'title', title);
        $jq_dialog("#upload-dialog-div").dialog("open");
    } else {
        alert('無操作權限！');
        return;
    }
}

//整理上傳列表
arrangeFileList = function() {
    var input = document.getElementById('files');
    var output = document.getElementById('fileList');
    var children = "";
    for (var i = 0; i < input.files.length; ++i) {
        children += '<li>' + input.files.item(i).name + '</li>';
    }
    output.innerHTML = '<ul>' + children + '</ul>';
}
</script>

<section style="margin-left:3%;margin-right:8%;padding-bottom:8%">
    <div style="padding-bottom:5%">
        <button type="button" class="btn btn-primary maintain-btn-upload" onclick="uploadFile();">上傳檔案</button>
    </div>

    <table id="file-table" class="table table-bordered">
        <thead style="background-color:#F5F6FB">
            <tr>
                <th style="width: 20%">操作</th>
                <th style="width: 10%">No.</th>
                <th style="width: 25%">檔名</th>
                <th style="width: 25%">上傳時間</th>
                <th style="width: 20%">檔案大小</th>
            </tr>
        </thead>
    </table>

    <form id="formDeleteFile" style="display:none;" method="POST">
        @csrf
        @method('DELETE')
    </form>

    <form id="formDownloadFile" style="display:none;" method="POST">
        @csrf
        @method('POST')
    </form>
</section>

<!-- 上傳檔案的對話框 -->
<div id="upload-dialog-div" style="display:none">
    <form id="upload-form" method="POST" enctype="multipart/form-data">
        @csrf
        @method('POST')
        <fieldset style="margin-top:10px;">
            <div>
                <div id="file-div-fieldset">
                    <div>
                        <label for="files" class="btn btn-light maintain-btn-select">選擇檔案</label>
                        <input type="file" style="height:10px;visibility:hidden;" multiple name="excelFiles[]"
                            id="files" onchange="javascript:arrangeFileList()" />
                        <div class="div-fileList" id="fileList"></div>
                    </div>
                </div>
            </div>
        </fieldset>
    </form>
</div>

<script>
let columnIdArray = [
    "fileFullName",
    "fileNo",
    "fileName",
    "fileUpdateTime",
    "fileSize"
];

//DataTable設置
$jq_dataTable(document).ready(function() {
    //客製化按鈕、語系可參考：https://ithelp.ithome.com.tw/articles/10272813
    $jq_dataTable('#file-table').append(
        '<caption id="file-title">最後更新時間：{{date("Y-m-d H:i:s",(time()+8*3600))}}</caption>');
    var table = $jq_dataTable('#file-table').DataTable({
        data: @json($tableData['file']), //資料
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
                targets: 0 //指定哪些欄位不排序
            },
            {
                className: 'text-center', //資料對齊中間
                targets: [0, 1] //指定哪些欄位對齊中間
            },
            {
                className: 'text-right', //資料對齊右邊
                targets: 4 //指定哪些欄位對齊中間
            },
            {
                targets: 0,
                render: function(data, type, row) {
                    var id = data;
                    var html = '';
                    html += '<button id="file-delete-btn" type="button" class="btn btn-danger ';
                    html += 'maintain-btn-delete">刪除</button>';
                    html +=
                        '<button id="file-download-btn" type="button" class="btn btn-success ';
                    html += 'maintain-btn-download">下載</button>';
                    return html;
                }
            }
        ],
        language: {
            "lengthMenu": "顯示 _MENU_ 筆資料",
            "search": "", //不顯示搜尋兩個字
            "searchPlaceholder": "查詢資料",
            "sProcessing": "<div class='loader'></div>", //客製處理中動畫
            "sZeroRecords": "尚無檔案",
            "oPaginate": {
                "sFirst": "<<",
                "sPrevious": "<",
                "sNext": ">",
                "sLast": ">>"
            },
        },
    });

    //刪除按鈕
    $jq_dataTable('#file-table').on('click', '#file-delete-btn', function() {
        //取得欄位資料
        var columnData = table.row($jq_dataTable(this).parents('tr')).data();
        var fileFullName = columnData[0];
        var fileNo = columnData[1];

        if (can_maintain_crud) {
            if (confirm('確定要刪除【No. ' + fileNo + '】的檔案嗎？')) {
                var url = "{{route('FileController.deleteFile', ':fullName')}}";
                url = url.replace(':fullName', fileFullName);
                document.getElementById('formDeleteFile').action = url;
                document.getElementById('formDeleteFile').submit();
            }
        } else {
            alert('無操作權限！');
            return;
        }
    });

    //下載按鈕
    $jq_dataTable('#file-table').on('click', '#file-download-btn', function() {
        //取得欄位資料
        var columnData = table.row($jq_dataTable(this).parents('tr')).data();
        var fileFullName = columnData[0];
        var url = "{{route('FileController.downloadFile', ':fullName')}}";
        url = url.replace(':fullName', fileFullName);
        document.getElementById('formDownloadFile').action = url;
        document.getElementById("formDownloadFile").submit();
    });
});
</script>
@stop