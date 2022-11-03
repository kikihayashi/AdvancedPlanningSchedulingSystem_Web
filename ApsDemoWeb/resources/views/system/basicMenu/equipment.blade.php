@extends('system.system_content')
@section('inner_content')
<!-- 對話框設置 -->
@include('layouts.script.jq_dialog_layout')
<!-- dataTable設置 -->
@include('layouts.script.jq_dataTable_layout')
<!-- 機種清單 -->
<section id="equipment-section">
    <div id="equipment-div">
        <table id="equipment-table" class="table table-bordered">
            <thead style="background-color:#F5F6FB">
                <tr>
                    <th style="text-align:center">操作</th>
                    <th style="text-align:center">機種名稱</th>
                    <th style="text-align:center">永久品番</th>
                    <th style="text-align:center">機種日文名稱</th>
                    <th style="text-align:center">機種英文名稱</th>
                    <th style="text-align:center">在庫品番</th>
                    <th style="text-align:center">圖番</th>
                    <th style="text-align:center">Line</th>
                    <th style="text-align:center">是否內藏</th>
                </tr>
            </thead>
        </table>
    </div>

    <!-- 編輯對話框，預設不開啟 -->
    <div class="equipment-form-div" id="dialog-form-equipment" style="visibility:hidden;">
        <form id="update-equipment" method="POST" action="{{route('EquipmentController.updateEquipment')}}">
            @csrf
            @method('POST')
            <fieldset>
                <div style="margin-top:10px">
                    <input type="text" name="equipment[]" id="itemCode" style="display:none">
                    <div id="equipment-div-fieldset">
                        <label class="equipment_label-used" style="margin-top: 5px;"
                            for="lineValue">&ensp;線別(Line) :</label>
                        <input type="number" min="1" max="3" name="equipment[]" id="lineValue"
                            class="equipment_input-used" placeholder="請輸入線別">
                        <label class="equipment_label-not-used" style="margin-top: 5px;"
                            for="itemName">&emsp;&emsp;&emsp;&emsp;機種 :</label>
                        <input type="text" name="equipment[]" id="itemName"
                            class="equipment_input-not-used" readonly="readonly">
                    </div>

                    <div id="equipment-div-fieldset">
                        <label class="equipment_label-not-used" style="margin-top: 5px;" for="itemNameJp">機種日文名稱
                            :</label>
                        <input type="text" name="equipment[]" id="itemNameJp"
                            class="equipment_input-not-used" readonly="readonly">
                        <label class="equipment_label-not-used" style="margin-top: 5px;" for="itemNameEn">機種英文名稱
                            :</label>
                        <input type="text" name="equipment[]" id="itemNameEn"
                            class="equipment_input-not-used" readonly="readonly">
                    </div>

                    <div id="equipment-div-fieldset">
                        <label class="equipment_label-not-used" style="margin-top: 5px;"
                            for="eternalCode">&emsp;&emsp;永久品番
                            :</label>
                        <input type="text" name="equipment[]" id="eternalCode"
                            class="equipment_input-not-used" readonly="readonly">
                        <label class="equipment_label-not-used" style="margin-top: 5px;"
                            for="stockCode">&emsp;&emsp;在庫品番
                            :</label>
                        <input type="text" name="equipment[]" id="stockCode"
                            class="equipment_input-not-used" readonly="readonly">
                    </div>

                    <div id="equipment-div-fieldset">
                        <label class="equipment_label-not-used" style="margin-top: 5px;"
                            for="imageCode">&emsp;&emsp;&emsp;&emsp;圖番
                            :</label>
                        <input type="text" name="equipment[]" id="imageCode"
                            class="equipment_input-not-used" readonly="readonly">
                        <label class="equipment_label-not-used" style="margin-top: 5px;" for="isHidden">&emsp;&emsp;是否內藏
                            :</label>
                        <input type="text" name="equipment[]" id="isHidden"
                            class="equipment_input-not-used" readonly="readonly">
                    </div>
                </div>
            </fieldset>
        </form>
    </div>
</section>

<script>
let can_basic_crud = ("{{$tableData['permission']['basic_crud']}}" == 'Y');
let columnIdArray = [
    "itemName",
    "itemCode",
    "eternalCode",
    "itemNameJp",
    "itemNameEn",
    "stockCode",
    "imageCode",
    "lineValue",
    "isHidden"
];

//DataTable設置
$jq_dataTable(document).ready(function() {
    //客製化按鈕、語系可參考：https://ithelp.ithome.com.tw/articles/10272813

    $jq_dataTable('#equipment-table').append(
        '<caption id="equipment-title">最後更新時間：{{date("Y-m-d H:i:s",(time()+8*3600))}}</caption>');
    var table = $jq_dataTable('#equipment-table').DataTable({
        data: @json($tableData['equipment']), //資料
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
                targets: [0, 2, 3, 4, 5, 6, 7, 8] //指定哪些欄位不排序
            },
            {
                className: 'text-center', //資料對齊中間
                orderable: false, //不排序
                targets: [0, 6, 7, 8] //指定哪些欄位對齊中間
            },
            {
                targets: 0,
                render: function(data, type, row) {
                    return '<button id="update-btn" type="button" class="btn btn-primary btn-sm">編輯</button> '
                }
            }
        ],
        language: {
            "lengthMenu": "顯示 _MENU_ 筆資料",
            "search": "", //不顯示搜尋兩個字
            "searchPlaceholder": "查詢機種",
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

    //開啟對話框編輯資料
    $jq_dataTable('#equipment-table').on('click', '#update-btn', function() {
        if (can_basic_crud) {
            //取得欄位資料
            var columnData = table.row($jq_dataTable(this).parents('tr')).data();
            for (i = 0; i < columnIdArray.length; i++) {
                //將資料代入至對話框
                document.getElementById(columnIdArray[i]).value = columnData[i];
                //如果i不是7(lineValue)，input背景顏色要調整
                if (i != 7) {
                    document.getElementById(columnIdArray[i]).style.color = '#696969';
                    document.getElementById(columnIdArray[i]).style.background = '#F5F5F5';
                }
            }
            document.getElementById("dialog-form-equipment").style.visibility = "visible";
            $jq_dialog("#dialog-form-equipment").dialog('option', 'title', '修改線別');
            $jq_dialog("#dialog-form-equipment").dialog('option', 'position', [250, 80]);
            $jq_dialog("#dialog-form-equipment").dialog("open");
        } else {
            alert('無操作權限！');
            return;
        }
    });
});

//對話框設置
$jq_dialog(function() {
    $jq_dialog("#dialog-form-equipment").dialog({
        autoOpen: false,
        width: 750,
        height: 370,
        modal: true,
        buttons: {
            "儲存": function() {
                //儲存資料，這個是底下form的id
                document.getElementById('update-equipment')
                    .submit();
                //關閉對話框
                $jq_dialog(this).dialog("close");
            },
        },
        close: function() {
            //重置預設值
            $jq_dialog("#lineValue").val("");
        }
    }).prev(".ui-dialog-titlebar").css("background", "#00C1DE").css("color", "white");
});
</script>
@stop