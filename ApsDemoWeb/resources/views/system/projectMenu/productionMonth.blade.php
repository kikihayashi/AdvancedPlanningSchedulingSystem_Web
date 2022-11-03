@extends('system.system_content')
@section('inner_content')
<!-- dataTable -->
@include('layouts.script.jq_dataTable_layout')
@include('layouts.script.js_scaling_layout')
<!-- 月度生產計畫 -->
<script>
let selectId = "";
let selectItemCode = "";
let selectLotNo = "";
let thisTimeSelectTab = @json($tableData['selectTab']);
let tableEquipment = @json($tableData['equipment']);
let thisTimeDays = @json($tableData['thisTimeDays']);

document.addEventListener("DOMContentLoaded", function(event) {
    //選擇機種之後，帶出機種名稱
    $('#itemIndex').on('change', function() {
        var index = $(this).val();
        $('#itemCode').val(tableEquipment[index]['ItemCode']);
        $('#itemName').val(tableEquipment[index]['ItemName']);
        return false;
    });
});

//新增月度生產計劃
function saveProjectBtn() {
    var thisMonthNumber = Number(document.getElementById('thisMonthNumber').value);
    if (thisMonthNumber <= 0) {
        alert('本月計劃生產台數不可為0');
        return false;
    }
}

//清除月度生產計劃(當使用者點選清除時，將生產區間重置)
function clearProjectBtn() {
    for (day = 1; day <= thisTimeDays; day++) {
        $('#grid-' + day).css('background-color', '#FFFFFF');
        $('#checkbox-' + day).prop('checked', false);
    }
}

//刪除月度生產計劃
function deleteProjectBtn() {
    if (confirm("確定要刪除" + selectItemCode + " - Lot no : " + selectLotNo + " ?")) {
        var url = "{{route('ProductionMonthController.deleteProductionMonth', ':id')}}";
        url = url.replace(':id', selectId);
        document.getElementById('formDeleteProject').action = url;
        document.getElementById('formDeleteProject').submit();
    }
}

//修改月度生產計劃
function editProjectBtn() {
    var url = "{{route('ProductionMonthController.editProductionMonthPage', ':id')}}";
    url = url.replace(':id', selectId);
    location.href = url;
}

//上傳年度生產計劃到SAP
function uploadProjectBtn(version) {
    if (version == 0) {
        alert('當前版本為0，不可上傳至SAP！');
        return false;
    }
    return confirm("確定上傳至SAP嗎？");
}

//選擇生產日期
function selectDay(selectDay) {
    if ($('#checkbox-' + selectDay).is(":checked")) {
        $('#grid-' + selectDay).css('background-color', '#FFFFFF');
        $('#checkbox-' + selectDay).prop('checked', false);
    } else {
        $('#grid-' + selectDay).css('background-color', '#B3E5FC');
        $('#checkbox-' + selectDay).prop('checked', true);
    }
}

//選擇分頁(列表、新增)
function selectTab(selectTab) {
    thisTimeSelectTab = selectTab;
}

//下拉式選單，選擇的期別、月份
function selectPeriodMonth() {
    var selectPeriodBox = document.getElementById("select-period");
    var selectMonthBox = document.getElementById("select-month");
    var selectPeriodValue = selectPeriodBox.options[selectPeriodBox.selectedIndex].value;
    var selectMonthValue = selectMonthBox.options[selectMonthBox.selectedIndex].value;
    var url =
        "{{route('ProductionMonthController.showProductionMonthPage', ['period_tw' => ':selectPeriod', 'month' => ':selectMonth', 'selectTab' => ':selectTab'])}}";
    url = url.replace(':selectPeriod', selectPeriodValue);
    url = url.replace(':selectMonth', selectMonthValue);
    url = url.replace(':selectTab', thisTimeSelectTab);
    window.location.href = url; //導向使用者點選的期別頁面
}

//偵測使用者選擇的資料列(tr)
function selectTrContent(id, itemCode, lot_no, line) {
    selectId = id;
    selectItemCode = itemCode;
    selectLotNo = lot_no;
    resetTrContent("list-middle");
    focusTrContent("productionMonth-content-" + id);
    $('#project-btn-title').html('目前選擇項目：' + itemCode + " - Lot no : " + lot_no);
    $('#project-btn-title').css('color', '#000000').css("fontSize", 18);

    //顯示按鈕和按鈕標題
    $('#project-btn-div').show();
}

//取消所有Tr(背景條白)
function resetTrContent(id) {
    var elements = document.getElementById(id).getElementsByTagName('tr');
    for (i = 0; i < elements.length; i++) {
        elements[i].style.border = 'none';
        elements[i].style.background = '#FFFFFF';
        elements[i].classList.remove("focus");
    }
}

//指定選擇的Tr(加上邊框)
function focusTrContent(id) {
    document.getElementById(id).classList.add("focus");
}
</script>

<section>
    @php
    $projectType = 'PM';
    $period_tw = $tableData['thisTimePeriod']->period_tw;
    $month = $tableData['thisTimeMonth'];
    $version = $tableData['progress']['version'];
    $point = $tableData['progress']['progress_point'];
    $can_operation = $tableData['permission'][$point] == 'Y';
    $can_project_crud = $tableData['permission']['project_crud'] == 'Y';
    $can_create_project = $tableData['progress']['create_project'] == 'Y';
    $data_number = count($tableData['productionMonth']);
    $hasZeroLineNo = $tableData['hasZeroLineNo'];
    @endphp

    <!-- 計畫頁面右上角操作 -->
    @include('layouts.project.operation_layout')
    <!-- 進度條 -->
    <div id="menus">
        <!-- 當期資訊 -->
        @include('layouts.project.info_month_layout')
        <!-- 審核進度 -->
        @include('layouts.project.progressbar_layout')
    </div>
    <!-- 縮放進度條標誌 -->
    <div style="text-align:center;">
        <span class="with-line" style="--width: 42%;">
            <a id="fa-toggle" onclick='toggleMenu();' class="fa fa-angle-double-up"
                style="margin-right:10px;margin-left:10px;font-size:30px"></a>
        </span>
    </div>
</section>

<section>
    <div class="div-nav-tabs">
        <!-- TAB -->
        <ul class="nav nav-tabs">
            @include('layouts.project.tab_layout')
        </ul>

        <div class="tab-content">
            <!-- 月度生產計劃列表的內容 -->
            <div class="tab-pane fade in {{($tableData['selectTab']=='projectList')? 'active':''}}"
                id="projectList-page">
                @php
                $days = $tableData['thisTimeDays'];
                @endphp
                @if($data_number == 0)
                <br>
                <span style="margin-left:20px;font-size:30px">尚無計劃資料</span>
                @else
                <section style="padding-top:1.5%;display:flex;flex-direction:column">
                    <div>
                        <div style="margin-top:6px;float:left;display:flex;">
                            <span class="with-line-icon">&ensp;&ensp;&ensp;</span>
                            <span>：生產日期</span>
                            <span style='font-size:18px;color:#E1E6EB'>&ensp;|</span>
                            <span style='font-size:23px;color:#000000'>&ensp;*</span>
                            <span>：休假日</span>
                        </div>

                        <!-- 刪除、編輯按鈕 -->
                        @include('layouts.project.delete_edit_button_layout')
                        <form id="formDeleteProject" style="display:none;" method="POST">
                            @csrf
                            @method('DELETE')
                        </form>
                    </div>
                </section>

                <section class="productionMonth-list-section">
                    <!-- 列表頂端 -->
                    <div class="productionMonth-list-div-title">
                        <table class="table table-bordered" style="margin-bottom:0px;">
                            <thead style="background-color:#F5F6FB">
                                <tr>
                                    <th style="width: 8%;">機種</th>
                                    <th style="width:5%">結算SH</th>
                                    <th style="width:5%">製番</th>
                                    <th style="width:6%">前月迄完成數</th>
                                    <th style="width:6%">本月計劃<br>生產台數</th>
                                    @foreach(range(1, $days) as $day)
                                    <th style="width:2%">{{($tableData['isHolidayMap'][$day]=='Y')? '*' : $day}}</th>
                                    @endforeach
                                    <th style="width:{{8 + 2 * (31 - $days)}}%" rowspan="{{31 - $days + 1}}">完成工數</th>
                                </tr>
                            </thead>
                        </table>
                    </div>

                    <!-- 列表內容 -->
                    <div class="productionMonth-list-div-content">
                        <!-- 列表左側 -->
                        <table class="table productionMonth-list-table left">
                            <tbody>
                                @foreach($tableData['productionMonth'] as $productionMonth)
                                <tr>
                                    <td style="width:8%;" id="productionMonth-list-td-left">
                                        {{($productionMonth['line_no']==0)?'無線別':'Line '.$productionMonth['line_no']}}
                                        <br>
                                        {{$productionMonth['item_code']}}
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>

                        <!-- 列表中間、右側 -->
                        <table class="table productionMonth-list-table middle">
                            <tbody id="list-middle">
                                @php
                                $totalNumber = 0;
                                $totalHour = 0;
                                @endphp
                                @foreach($tableData['productionMonth'] as $productionMonth)
                                <tr id="productionMonth-content-{{$productionMonth['id']}}" onclick="selectTrContent(
                                            '{{$productionMonth['id']}}',
                                            '{{$productionMonth['item_code']}}',
                                            '{{$productionMonth['lot_no']}}',
                                            '{{$productionMonth['line_no']}}');">
                                    @php
                                    $index = 0;
                                    $start = $productionMonth['start'];
                                    $end = $productionMonth['end'];
                                    $totalNumber += floatval($productionMonth['this_month_number']);
                                    $totalHour += floatval($productionMonth['completeHour']);
                                    @endphp
                                    <td style="width:5%" id="productionMonth-list-td-right">
                                        {{($productionMonth['workHour'] == 0)? '-' : $productionMonth['workHour'].'H'}}
                                    </td>
                                    <td style="width:5%" id="productionMonth-list-td-middle">
                                        #{{$productionMonth['lot_no']}}
                                    </td>
                                    <td style="width:6%" id="productionMonth-list-td-middle">
                                        {{$productionMonth['previous_month_number']}}
                                    </td>
                                    <td style="width:6%" id="productionMonth-list-td-middle">
                                        {{$productionMonth['this_month_number']}}
                                    </td>
                                    @for($day = 1; $day <= $days; $day++) @if($index < count($start) &&
                                        $day==$start[$index]) <td id="productionMonth-list-td-middle"
                                        colspan="{{$end[$index]-$start[$index]+1}}"
                                        style="width:{{2*($end[$index]-$start[$index]+1)}}%;padding:0;">
                                        <br>
                                        <span class="with-line-icon">&ensp;</span>
                                        </td>
                                        @php
                                        $day = $end[$index];
                                        $index++;
                                        @endphp
                                        @else
                                        <td style="width:2%" id="productionMonth-list-td-middle"></td>
                                        @endif
                                        @endfor
                                        <td id="productionMonth-list-td-right" colspan="{{31 - $days + 1}}"
                                            style="width:{{8 + 2 * (31 - $days)}}%">
                                            {{($productionMonth['completeHour'] == 0)? '-' : $productionMonth['completeHour'].'H'}}
                                        </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <!-- 列表底端 -->
                    <div class="productionMonth-list-div-bottom">
                        <table class="table table-bordered" style="margin-bottom:0px;">
                            <thead style="background-color:#fbfbfb">
                                <tr>
                                    <td style="width:24%" colspan="4">合計</td>
                                    <td style="width:6%">{{number_format($totalNumber)}}</td>
                                    <td style="width:{{2*$days}}%" colspan="{{$days}}"></td>
                                    <td style="width:{{8 + 2 * (31 - $days)}}%" rowspan="{{31 - $days + 1}}"
                                        id="productionMonth-list-td-right">
                                        {{($totalHour == 0)? '-' : $totalHour.'H'}}</td>
                                </tr>
                            </thead>
                        </table>
                    </div>
                </section>
                @endif
            </div>

            <!-- 新增的內容 -->
            @if($point == 1 && $can_project_crud)
            <div class="tab-pane fade in {{($tableData['selectTab']=='projectCreate')? 'active':''}}"
                id="projectCreate-page">
                <section style="margin-bottom:22%;padding-top:1.5%;">
                    <form method="POST" id="project-form"
                        action="{{route('ProductionMonthController.createProductionMonth')}}">
                        <!-- 清除、儲存按鈕 -->
                        @include('layouts.project.clear_save_button_layout')
                        <input type="hidden" name="period_tw" value="{{$period_tw}}">
                        <input type="hidden" name="month" value="{{$month}}">
                        <input type="hidden" name="version" value="{{$version}}">

                        <!-- 計畫表單 -->
                        <div id="project-div">
                            <table class="table project-table">
                                <tbody>
                                    <tr>
                                        <td colspan="3"><span class="with-line"
                                                style="--width: 45%;">&ensp;基本資料&ensp;</span></td>
                                    </tr>
                                    <tr>
                                        <td class="right" style="width:25%"><span style="color:red">&#42;</span> 機種 :</td>
                                        <td style="width:30%">
                                            <input type="hidden" value="" id="itemCode" name="item_code">
                                            <input type="hidden" value="" id="itemName" name="item_name">
                                            <select required class="select-large" id="itemIndex" onblur="this.size=1;">
                                                <div>
                                                    <div>
                                                        <option disabled selected value>選擇機種</option>
                                                        @foreach(range(0, count($tableData['equipment']) - 1) as
                                                        $index);
                                                        @if($tableData['equipment'][$index]['ItemCode']=='無資料')
                                                        <option disabled>無資料</option>
                                                        @else
                                                        <option value="{{$index}}">
                                                            {{$tableData['equipment'][$index]['ItemCode']}}
                                                        </option>
                                                        @endif
                                                        @endforeach
                                                    </div>
                                                </div>
                                            </select>
                                        </td>
                                        <td style="width:45%;text-align:left;font-size:20px">
                                            生產日期：{{$tableData['thisTimePeriod']->years}}年{{$tableData['thisTimeMonth']}}月
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="right"><span style="color:red">&#42;</span> Lot No :</td>
                                        <td><input min="1" required type="number" name="lot_no" id="lot_no"
                                                class="input-small"></td>
                                        <td rowspan="3">
                                            <div class="grid-container">
                                                @foreach(range(1, $days) as $day)
                                                <input type="checkbox" style="display:none" id="checkbox-{{$day}}"
                                                    value="{{$day}}" name="productionDay[]">
                                                <div class="grid-item" id="grid-{{$day}}"
                                                    onclick="selectDay('{{$day}}');">
                                                    {{$day}}
                                                </div>
                                                @endforeach
                                            </div>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="right"><span style="color:red">&#42;</span> 前月迄完成數 :</td>
                                        <td><input min="0" required type="number" name="previousMonthNumber"
                                                id="previousMonthNumber" value="0"
                                                class="input-small">
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="right"><span style="color:red">&#42;</span> 本月計劃生產台數:</td>
                                        <td><input min="0" required type="number" name="thisMonthNumber"
                                                id="thisMonthNumber" value="0"
                                                class="input-small">
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </form>
                </section>
            </div>

            <!-- 轉入SAP -->
            @elseif($point == 0 && $can_project_crud)
            <div class="tab-pane fade in {{($tableData['selectTab']=='projectSap')? 'active':''}}" id="projectSap-page">
                @if(count($tableData['sapData']) == 0)
                <br>
                <span style="margin-left:20px;font-size:30px">尚無資料可上傳</span>
                @else
                <form method="POST" id="sap-form" action="{{route('ProductionMonthController.uploadProductionMonth', 
                        ['period_tw' => $period_tw,'month' => $month])}}">
                    @csrf
                    @method('POST')
                    <section style="margin-bottom:2%;padding-top:1.5%;">
                        <button type="submit" onclick="return uploadProjectBtn('{{$version}}');" class="btn btn-primary"
                            id="project-btn-sap">送出</button>
                    </section>
                </form>
                <table id="sap-table" class="table table-bordered">
                    <thead style="background-color:#F5F6FB">
                        <tr>
                            <th style="width:10%;">Lot</th>
                            <th style="width:25%;">機種</th>
                            <th style="width:20%;">單價</th>
                            <th style="width:20%;">數量</th>
                            <th style="width:25%;">出貨日期</th>
                        </tr>
                    </thead>
                </table>
                @endif
            </div>
            @endif
        </div>
    </div>
</section>

<script>
let columnIdArray = [
    "lot",
    "itemCode",
    "cost",
    "number",
    "shippingDate",
];

//DataTable設置
$jq_dataTable(document).ready(function() {
    //客製化按鈕、語系可參考：https://ithelp.ithome.com.tw/articles/10272813

    $jq_dataTable('#sap-table').append(
        '<caption id="sap-title">最後更新時間：{{date("Y-m-d H:i:s",(time()+8*3600))}}</caption>');
    var table = $jq_dataTable('#sap-table').DataTable({
        data: @json($tableData['sapData']), //資料
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
                targets: [2, 3, 4] //指定哪些欄位不排序
            },
            {
                className: 'text-center', //資料對齊中間
                targets: [0, 4] //指定哪些欄位對齊中間
            },
            {
                className: 'text-right', //資料對齊右邊
                targets: [2, 3] //指定哪些欄位對齊中間
            },
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
});
</script>
@stop