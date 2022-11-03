@extends('system.system_content')
@section('inner_content')
<!-- 對話框設置 -->
@include('layouts.script.jq_dialog_layout')
@include('layouts.script.js_scaling_layout')
<!-- 年度出荷計畫 -->
<script>
let selectItemCode = "";
let thisTimeSelectTab = @json($tableData['selectTab']);
let tableEquipment = @json($tableData['equipment']);
let tableTransport = @json($tableData['transport']);
let tableShippingYear = @json($tableData['shippingYear']);
let tableInputForm = @json($tableData['htmlInputForm']);

document.addEventListener("DOMContentLoaded", function(event) {
    //選擇機種之後，帶出機種名稱
    $('#itemIndex').on('change', function() {
        var index = $(this).val();
        $('#itemCode').val(tableEquipment[index]['ItemCode']);
        $('#itemName').val(tableEquipment[index]['ItemName']);
        return false;
    });

    //移除表單
    $(document).on('click', '.removeLot', function() {
        $(this).closest('.inputForm').remove();
    });
});

//新增年度出荷計劃
function saveProjectBtn() {
    //檢查是否輸入重複的製番-出荷方式
    var set = new Set();
    var lotArray = document.getElementsByClassName('checkLot');
    var transportArray = document.getElementsByClassName('checkTransport');

    if (lotArray.length == 0) {
        alert('錯誤，機種資料尚未新增！');
        return false;
    } else {
        for (var i = 0; i < lotArray.length; i++) {
            set.add(lotArray[i].value + '-' + transportArray[i].value);
        }
        if (set.size != lotArray.length) {
            alert('錯誤，製番與出荷方式不可重複輸入！');
            return false;
        }
    }
}

//清除年度出荷計劃
function clearProjectBtn() {
    $('.inputForm').remove();
}

//刪除年度出荷計劃
function deleteProjectBtn() {
    if (confirm("確定要刪除" + selectItemCode + "?")) {
        var url = "{{route('ShippingYearController.deleteShippingYear', 'SINGLE')}}";
        document.getElementById('formDeleteProject').action = url;
        document.getElementById('formDeleteProject').submit();
    }
}

//編輯年度出荷計劃
function editProjectBtn() {
    var url =
        "{{route('ShippingYearController.editShippingYearPage', ['itemCode'=>':itemCode','period_tw'=> ':period_tw','version'=>':version'])}}";
    url = url.replace(':itemCode', selectItemCode);
    url = url.replace(':period_tw', '{{$tableData["thisTimePeriod"]->period_tw}}');
    url = url.replace(':version', '{{$tableData["progress"]["version"]}}');
    location.href = url;
}

//選擇分頁(列表、新增)
function selectTab(selectTab) {
    thisTimeSelectTab = selectTab;
}

//下拉式選單，選擇的期別
function selectPeriod() {
    var selectBox = document.getElementById("select-period");
    var selectPeriodValue = selectBox.options[selectBox.selectedIndex].value;
    var url =
        "{{route('ShippingYearController.showShippingYearPage',  ['period_tw' => ':selectPeriod', 'selectTab' => ':selectTab'])}}";
    url = url.replace(':selectPeriod', selectPeriodValue);
    url = url.replace(':selectTab', thisTimeSelectTab);
    window.location.href = url; //導向使用者點選的期別頁面
}

//偵測使用者選擇的資料列(tr)
function selectTrContent(itemCode) {
    selectItemCode = itemCode;
    $("#itemCodeForDelete").val(itemCode);
    resetTrContent("list-middle");
    focusTrContent("shippingYear-content-" + itemCode);
    $('#project-btn-title').html('目前選擇項目：' + itemCode);
    $('#project-btn-title').css('color', '#000000').css("fontSize", 18);

    //顯示按鈕和按鈕標題
    $('#project-btn-div').show();
}

//取消所有Tr、Td(背景條白)
function resetTrContent(id) {
    var tr_elements = document.getElementById(id).getElementsByTagName('tr');
    for (i = 0; i < tr_elements.length; i++) {
        tr_elements[i].style.border = 'none';
        tr_elements[i].style.background = '#FFFFFF';
        tr_elements[i].classList.remove("focus");
    }
    var td_elements = document.getElementById(id).getElementsByTagName('td');
    for (i = 0; i < td_elements.length; i++) {
        td_elements[i].classList.remove("focus");
    }
}

//指定選擇的Tr(加上邊框)
function focusTrContent(id) {
    document.getElementById(id).classList.add("focus");
}

//偵測使用者選擇的資料項(td)
function selectTdContent(itemCode, month) {
    for (i = 1; i <= 12; i++) {
        var elementId = 'td-' + itemCode + '-' + i;
        if (i == month) {
            //加上邊框
            document.getElementById(elementId).classList.add("focus");
        } else {
            //去除邊框
            document.getElementById(elementId).classList.remove("focus");
        }
    }
    openDialog(itemCode, month);
}

//開啟對話框
function openDialog(itemCode, month) {
    //動態產生對話框表單，這樣才有id：lot-dialog-div
    //設置對話框時才不會錯誤
    createLotForm(itemCode, month);
    //對話框設置
    var title = itemCode + "：" + month + "月";
    var height = 250 + 30 * (tableShippingYear[itemCode].length - 1);
    var url = "{{route('ShippingYearController.updateShippingYear', 'SINGLE')}}";
    $jq_dialog("#lot-form").attr('action', url); //設置Action
    $jq_dialog("#lot-dialog-div").dialog({
        title: title,
        autoOpen: false,
        width: 450,
        height: height,
        modal: true,
        buttons: {
            "儲存": function() {
                //送出資料，這個是底下form的id
                document.getElementById("lot-form").submit();
                //關閉對話框
                $jq_dialog(this).dialog("close");
            },
        },
        close: function() {
            //重置預設值
            $(this).closest('.lotForm').remove();
        }
    }).prev(".ui-dialog-titlebar").css("background", "#00C1DE").css("color", "white");
    $jq_dialog("#lot-dialog-div").dialog("open");
}

//點選單個月分來產生表單
function createLotForm(itemCode, month) {
    var html = '';
    html += '<div id="lot-dialog-div" class="lotForm">';
    html += '<form id="lot-form" method="POST">';
    html += '@csrf';
    html += '@method("PUT")';
    html += '<fieldset>';
    html += '<div style="margin-top:10px">';
    html += '<input type="hidden" name="item_code" value=' + itemCode + '>';
    html += '<input type="hidden" name="month" value=' + month + '>';
    html += '<input type="hidden" name="period_tw" value="{{$tableData["thisTimePeriod"]->period_tw}}">';
    html += '<input type="hidden" name="version" value="{{$tableData["progress"]["version"]}}">';
    html += '<input type="hidden" name="type" value="Single">';
    for (i = 0; i < tableShippingYear[itemCode].length; i++) {
        var lot_no = tableShippingYear[itemCode][i]['lot_no'];
        var lotNumber = tableShippingYear[itemCode][i]['month_' + month];
        var transportId = tableShippingYear[itemCode][i]['transport_id'];
        var transportName = tableTransport[tableShippingYear[itemCode][i]['transport_id']]['name'];
        html += '<div id="div-fieldset-shippingYear">';
        html += '<label class="lotLabel">';
        html += '<span style="color:red">&#42;&ensp;</span>#' + lot_no + ' :&ensp;';
        html += '</label>';
        html += '<input type="number" name="lotNumber[]" value=' + lotNumber + ' min="0"';
        html += 'class="input-large" placeholder="請輸入數量">&ensp;';
        html += '<input type="hidden" name="lot_no[]" value=' + lot_no + '>';
        html += '<input type="hidden" name="transport_id[]" value=' + transportId + '>';
        html += '<label class="lotLabel">(' + transportName + ')</label>';
        html += '</div>';
    }
    html += '</div>';
    html += '</fieldset>';
    html += '</form>';
    html += '</div>';
    $('#newLotForm').append(html);
}

//新增Lot表單
function createInputForm(id) {
    $('#' + id).append(tableInputForm);
}
</script>

<section>
    @php
    $projectType = 'SY';
    $period_tw = $tableData['thisTimePeriod']->period_tw;
    $month = 0;
    $version = $tableData['progress']['version'];
    $point = $tableData['progress']['progress_point'];
    $can_operation = $tableData['permission'][$point] == 'Y';
    $can_project_crud = $tableData['permission']['project_crud'] == 'Y';
    $can_create_project = $tableData['progress']['create_project'] == 'Y';
    $data_number = count($tableData['shippingYear']);
    $hasZeroLineNo = false;
    $canDoubleClick=($point==1 && $can_project_crud);
    @endphp

    <!-- 計畫頁面右上角操作 -->
    @include('layouts.project.operation_layout')
    <!-- 進度條 -->
    <div id="menus">
        <!-- 當期資訊 -->
        @include('layouts.project.info_year_layout')
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

        <!-- TAB的內容 -->
        <div class="tab-content">
            <!--  年度出荷計畫列表的內容 -->
            <div class="tab-pane fade in {{($tableData['selectTab']=='projectList')? 'active':''}}"
                id="projectList-page">
                @if($data_number==0)
                <br>
                <span style="margin-left:20px;font-size:30px">尚無計劃資料</span>
                @else
                <section style="padding-top:1.5%;">
                    <div style="float:left;">
                        <span style="color:black">上半年匯率：
                            <strong>{{($tableData['exchange']['first']==0)?'-':number_format($tableData['exchange']['first'], 4)}}</strong>
                        </span>
                        <span style='font-size:18px;color:#E1E6EB'>&ensp;|&ensp;</span>
                        <span style="color:black">下半年匯率：
                            <strong>{{($tableData['exchange']['last']==0)?'-':number_format($tableData['exchange']['last'], 4)}}</strong>
                        </span>
                    </div>
                    <!-- 刪除、編輯按鈕 -->
                    @include('layouts.project.delete_edit_button_layout')
                    <form id="formDeleteProject" style="display:none;" method="POST">
                        @csrf
                        @method('DELETE')
                        <input type="hidden" name="period_tw" value="{{$period_tw}}">
                        <input type="hidden" name="version" value="{{$version}}">
                        <input type="hidden" name="item_code" id="itemCodeForDelete">
                    </form>
                </section>

                <section class="shippingYear-list-section">
                    <!-- 列表頂端 -->
                    <div class="shippingYear-list-div-title">
                        <table class="table table-bordered" style="margin-bottom:0px;">
                            <thead style="background-color:#F5F6FB">
                                <tr>
                                    <th style="width:8%">機種</th>
                                    <th style="width:4%">結算工數</th>
                                    <th style="width:4%">賣價¥</th>
                                    @foreach($tableData['monthMaps'] as $monthMaps)
                                    <th style="width:6%">{{$monthMaps['page']}}月</th>
                                    @endforeach
                                    <th style="width:6%">合計</th>
                                    <th style="width:6%">大計畫</th>
                                </tr>
                            </thead>
                        </table>
                    </div>

                    <!-- 列表中間 -->
                    <div class="shippingYear-list-div-content">
                        <!-- 列表左側 -->
                        <table class="table shippingYear-list-table left">
                            <tbody>
                                @foreach($tableData['shippingYear'] as $itemCode => $shippingYearArray)
                                <tr>
                                    <td style="height:{{70+10*count($shippingYearArray)}}px;"
                                        class="shippingYear-list-td-left">
                                        {{$itemCode}}
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>

                        <!-- 列表中間、右側 -->
                        <table class="table shippingYear-list-table middle">
                            <tbody id="list-middle">
                                @php
                                $totalMap = array();
                                @endphp
                                @foreach($tableData['shippingYear'] as $itemCode => $shippingYearArray)
                                <tr id="shippingYear-content-{{$itemCode}}" onclick="selectTrContent('{{$itemCode}}');">
                                    @php
                                    $lotInfoTotal = array();
                                    $firstWorkHour = $shippingYearArray[0]['firstWorkHour'];
                                    $lastWorkHour = $shippingYearArray[0]['lastWorkHour'];
                                    $firstCost = $shippingYearArray[0]['firstCost'];
                                    $lastCost = $shippingYearArray[0]['lastCost'];
                                    @endphp
                                    <td style="width:4%;height:{{70+10*count($shippingYearArray)}}px;"
                                        class="shippingYear-list-td-right">
                                        {{($firstWorkHour == 0)?'-':$firstWorkHour.'H'}}
                                        <br>
                                        {{($lastWorkHour == 0)?'-':$lastWorkHour.'H'}}
                                    </td>
                                    <td style="width:4%;height:{{70+10*count($shippingYearArray)}}px;"
                                        class="shippingYear-list-td-right">
                                        {{number_format($firstCost)}}
                                        <br>
                                        {{number_format($lastCost)}}
                                    </td>
                                    @foreach($tableData['monthMaps'] as $monthMap)
                                    @php
                                    $lotInfo = '';
                                    $workHour = $shippingYearArray[0][$monthMap['yearType'].'WorkHour'];
                                    $cost = $shippingYearArray[0][$monthMap['yearType'].'Cost'];
                                    $exchange = $tableData['exchange'][$monthMap['yearType']];
                                    @endphp

                                    @foreach($shippingYearArray as $shippingYear)
                                    @php
                                    $lot_no = $shippingYear['lot_no'];
                                    $number = $shippingYear['month_'.$monthMap['page']];
                                    $sh = floatval($number) * floatval($workHour);
                                    $jpy = floatval($number) * floatval($cost) / 1000;
                                    $abbr = $tableData['transport'][$shippingYear['transport_id']]['abbreviation'];
                                    $transport_abbr = ($abbr == "")? '' : '&ensp;('.$abbr.')';

                                    if(!isset($totalMap['number']['month_'.$monthMap['page']])) {
                                    $totalMap['number']['month_'.$monthMap['page']] = floatval($number);
                                    } else {
                                    $totalMap['number']['month_'.$monthMap['page']] += floatval($number);
                                    }

                                    if(!isset($totalMap['SH']['month_'.$monthMap['page']])) {
                                    $totalMap['SH']['month_'.$monthMap['page']] = $sh;
                                    } else {
                                    $totalMap['SH']['month_'.$monthMap['page']] += $sh;
                                    }

                                    if(!isset($totalMap['JPY']['month_'.$monthMap['page']])) {
                                    $totalMap['JPY']['month_'.$monthMap['page']] = $jpy;
                                    $totalMap['NTD']['month_'.$monthMap['page']] = ($jpy * floatval($exchange));
                                    }
                                    else {
                                    $totalMap['JPY']['month_'.$monthMap['page']] += $jpy;
                                    $totalMap['NTD']['month_'.$monthMap['page']] += ($jpy * floatval($exchange));
                                    }

                                    if(floatval($number) > 0) {
                                    $lotInfo = $lotInfo.'#'.$lot_no.':'.'&emsp;'.$number.$transport_abbr.'<br>';
                                    if(!isset($lotInfoTotal['#'.$lot_no])) {
                                    $lotInfoTotal['#'.$lot_no] = floatval($number);
                                    } else {
                                    $lotInfoTotal['#'.$lot_no] += floatval($number);
                                    }
                                    }
                                    @endphp
                                    @endforeach
                                    <td style="width:6%;height:{{70+10*count($shippingYearArray)}}px;"
                                        class="shippingYear-list-td-left" id="td-{{$itemCode}}-{{$monthMap['page']}}"
                                        @if($canDoubleClick) ondblclick="selectTdContent(
                                        '{{$itemCode}}',
                                        '{{$monthMap['page']}}')" @endif>
                                        {!!$lotInfo!!}
                                    </td>
                                    @endforeach
                                    <td style="width:6%;height:{{70+10*count($shippingYearArray)}}px;"
                                        class="shippingYear-list-td-left">
                                        @foreach($lotInfoTotal as $lot => $lotTotal)
                                        {!!$lot.':'.'&emsp;&emsp;'. $lotTotal.'<br>'!!}
                                        @endforeach
                                    </td>
                                    <td style="width:6%;height:{{70+10*count($shippingYearArray)}}px;"
                                        class="shippingYear-list-td-left">
                                        @foreach($shippingYearArray as $shippingYear)
                                        @php
                                        $lot_no = $shippingYear['lot_no'];
                                        $lot_total = $shippingYear['lot_total'];
                                        $abbr = $tableData['transport'][$shippingYear['transport_id']]['abbreviation'];
                                        $transport_abbr = ($abbr == "")? '' : '&ensp;('.$abbr.')';
                                        @endphp
                                        {!!'#'.$lot_no.':'.'&emsp;'.$lot_total.$transport_abbr.'<br>'!!}
                                        @endforeach
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    <!-- 列表底端 -->
                    <div class="shippingYear-list-div-bottom">
                        <table class="table table-bordered" style="margin-bottom:0px;">
                            <thead style="background-color:#fbfbfb">
                                <tr>
                                    <td style="width:12%" colspan="2" rowspan="4">
                                        合計 ({{$tableData['thisTimePeriod']->years}}.4 ~
                                        {{$tableData['thisTimePeriod']->years + 1}}.3)
                                    </td>
                                    <td style="width:4%">台數</td>
                                    @foreach($tableData['monthMaps'] as $monthMap)
                                    <td style="width:6%" class="shippingYear-list-td-right">
                                        {{$totalMap['number']['month_'.$monthMap['page']]}}
                                    </td>
                                    @endforeach

                                    <td style="width:12%" rowspan="4"></td>
                                </tr>
                                <tr>
                                    <td style="width:4%">結算SH</td>
                                    @foreach($tableData['monthMaps'] as $monthMap)
                                    <td style="width:6%" class="shippingYear-list-td-right">
                                        {{number_format($totalMap['SH']['month_'.$monthMap['page']], 2)}}
                                    </td>
                                    @endforeach
                                </tr>
                                <tr>
                                    <td style="width:4%">¥千圓</td>
                                    @foreach($tableData['monthMaps'] as $monthMap)
                                    <td style="width:6%" class="shippingYear-list-td-right">
                                        {{number_format($totalMap['JPY']['month_'.$monthMap['page']], 2)}}
                                    </td>
                                    @endforeach
                                </tr>
                                <tr>
                                    <td style="width:4%">NT千圓</td>
                                    @foreach($tableData['monthMaps'] as $monthMap)
                                    <td style="width:6%" class="shippingYear-list-td-right">
                                        {{number_format($totalMap['NTD']['month_'.$monthMap['page']], 2)}}
                                    </td>
                                    @endforeach
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
                <section style="margin-bottom:30%;padding-top:1.5%;">
                    <form method="POST" id="project-form"
                        action="{{route('ShippingYearController.createShippingYear')}}">
                        <!-- 清除、儲存按鈕 -->
                        @include('layouts.project.clear_save_button_layout')
                        <input type="hidden" name="period_tw" value="{{$period_tw}}">
                        <input type="hidden" name="version" value="{{$version}}">
                        <!-- 計畫表單 -->
                        <div id="project-form-div">
                            <table class="table project-table" style="margin-bottom:0px;">
                                <tbody>
                                    <tr>
                                        <td colspan="4"> <span class="with-line"
                                                style="--width: 45%;">&ensp;基本資料&ensp;</span></td>
                                    </tr>
                                    <tr>
                                        <td class="right" colspan="1" style="width:30%">
                                            <span style="color:red">&#42;</span> 機種 :
                                        </td>
                                        <td colspan="3" style="width:70%">
                                            <input type="hidden" value="" id="itemCode" name="item_code">
                                            <input type="hidden" value="" id="itemName" name="item_name">
                                            <select required class="select-large" id="itemIndex">
                                                <div>
                                                    <div>
                                                        <option disabled selected value>選擇機種</option>
                                                        @foreach(range(0, count($tableData['equipment']) - 1) as $index)
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
                                    </tr>
                                </tbody>
                            </table>

                            <div id="newInputForm"></div>

                            <table class="table project-table" style="margin-bottom:0px;">
                                <tbody>
                                    <tr>
                                        <td colspan="1" style="width:30%">
                                        </td>
                                        <td colspan="3" style="width:70%;text-align:left">
                                            <button onclick="createInputForm('newInputForm');" type="button"
                                                class="addLotButton">
                                                + 新增 LOT
                                            </button>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </form>
                </section>
            </div>
            @endif
        </div>
    </div>
</section>
<div id="newLotForm"></div>
@stop