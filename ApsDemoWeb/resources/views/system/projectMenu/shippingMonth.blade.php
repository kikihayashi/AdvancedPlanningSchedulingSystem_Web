@extends('system.system_content')
@section('inner_content')
@include('layouts.script.js_scaling_layout')
<!-- 月度出荷計畫 -->
<script>
let selectItemCode = "";
let thisTimeSelectTab = @json($tableData['selectTab']);
let tableEquipment = @json($tableData['equipment']);
let tableDateArray = @json($tableData['dateArray']);

document.addEventListener("DOMContentLoaded", function(event) {
    //選擇機種之後，帶出機種名稱
    $('#itemIndex').on('change', function() {
        var index = $(this).val();
        $('#itemCode').val(tableEquipment[index]['ItemCode']);
        $('#itemName').val(tableEquipment[index]['ItemName']);
        return false;
    });
});

//刪除日期-出荷方式
function deleteDateBtn(month, date, transport_id, abbreviation) {
    if (confirm("確定要刪除" +
            ('0' + month).slice(-2) + ' / ' + ('0' + date).slice(-2) +
            '出荷計畫(' + abbreviation + ')？')) {
        var url = "{{route('ShippingMonthController.deleteShippingMonth','DATE')}}";
        $("#dateForDelete").val(date);
        $("#transportIdForDelete").val(transport_id);
        document.getElementById('formDeleteProject').action = url;
        document.getElementById('formDeleteProject').submit();
    }
}

//刪除年度出荷計劃
function deleteProjectBtn() {
    if (confirm("確定要刪除" + selectItemCode + "?")) {
        var url = "{{route('ShippingMonthController.deleteShippingMonth','ITEM_CODE')}}";
        document.getElementById('formDeleteProject').action = url;
        document.getElementById('formDeleteProject').submit();
    }
}

//編輯年度出荷計劃
function editProjectBtn() {
    if (tableDateArray.length == 0) {
        alert('尚未新增出荷計畫，不可編輯！');
    } else {
        var url =
            "{{route('ShippingMonthController.editShippingMonthPage', ['itemCode'=>':itemCode','period_tw'=> ':period_tw','month'=> ':month','version'=>':version'])}}";
        url = url.replace(':itemCode', selectItemCode);
        url = url.replace(':period_tw', '{{$tableData["thisTimePeriod"]->period_tw}}');
        url = url.replace(':month', '{{$tableData["thisTimeMonth"]}}');
        url = url.replace(':version', '{{$tableData["progress"]["version"]}}');
        location.href = url;
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
        "{{route('ShippingMonthController.showShippingMonthPage', ['period_tw' => ':selectPeriod', 'month' => ':selectMonth', 'selectTab' => ':selectTab'])}}";
    url = url.replace(':selectPeriod', selectPeriodValue);
    url = url.replace(':selectMonth', selectMonthValue);
    url = url.replace(':selectTab', thisTimeSelectTab);
    window.location.href = url; //導向使用者點選的期別頁面
}

//偵測使用者選擇的資料列(tr)
function selectTrContent(itemCode, line) {
    selectItemCode = itemCode;
    $("#itemCodeForDelete").val(itemCode);
    resetTrContent("list-middle");
    focusTrContent("shippingMonth-content-" + itemCode);
    $('#project-btn-title').html('目前選擇項目：' + itemCode);
    $('#project-btn-title').css('color', '#000000').css("fontSize", 18);

    //顯示按鈕和按鈕標題
    $('#project-btn-div').show();

    //無線別(line = 0)，不要讓它顯示編輯刪除
    // if (line == 0 || tableDateArray.length == 0) {
    //     $('#project-btn-div').hide();
    // } else {
    //     $('#project-btn-div').show();
    // }
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
</script>

<section>
    @php
    $projectType = 'SM';
    $period_tw = $tableData['thisTimePeriod']->period_tw;
    $month = $tableData['thisTimeMonth'];
    $version = $tableData['progress']['version'];
    $point = $tableData['progress']['progress_point'];
    $can_operation = $tableData['permission'][$point] == 'Y';
    $can_project_crud = $tableData['permission']['project_crud'] == 'Y';
    $can_create_project = $tableData['progress']['create_project'] == 'Y';
    $data_number = $tableData['dataNumber'];
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

        <!-- TAB的內容 -->
        <div class="tab-content">
            <!--  月度出荷計畫列表的內容 -->
            <div class="tab-pane fade in {{($tableData['selectTab']=='projectList')? 'active':''}}"
                id="projectList-page">
                @if(count($tableData['shippingMonth'])==0)
                <br>
                <span style="margin-left:20px;font-size:30px">尚無計劃資料</span>
                @else
                <section style="padding-top:1.5%;">
                    <div style="float:left;">
                        @foreach($tableData['transport'] as $transportId => $transport)
                        <span style="color:black;font-size:13px">
                            ({{$transport['abbreviation']}})：{{$transport['name']}}
                        </span>
                        @php
                        $keyArray = array_keys($tableData['transport'])
                        @endphp
                        @if(end($keyArray) != $transportId)
                        <span style="color:#E1E6EB;font-size:18px;"> | </span>
                        @endif
                        @endforeach
                    </div>
                    <!-- 刪除、編輯按鈕 -->
                    @include('layouts.project.delete_edit_button_layout')
                    <form id="formDeleteProject" style="display:none;" method="POST">
                        @csrf
                        @method('DELETE')
                        <input type="hidden" name="period_tw" value="{{$period_tw}}">
                        <input type="hidden" name="month" value="{{$month}}">
                        <input type="hidden" name="version" value="{{$version}}">
                        <input type="hidden" name="item_code" id="itemCodeForDelete">
                        <input type="hidden" name="date" id="dateForDelete">
                        <input type="hidden" name="transport_id" id="transportIdForDelete">
                    </form>
                </section>

                <section class="shippingMonth-list-section">
                    <!-- 列表頂端 -->
                    <div class="shippingMonth-list-div-title"
                        style="width:{{420 + 200 * count($tableData['dateArray'])}}px">
                        <table class="table table-bordered" style="margin-bottom:0px;">
                            <thead style="background-color:#F5F6FB">
                                <tr>
                                    <th rowspan="2" style="width:120px">機種</th>
                                    <th rowspan="2" style="width:100px">單價</th>
                                    @foreach($tableData['dateArray'] as $dateInfo)
                                    <th colspan="2" style="width:200px">
                                        @if($can_project_crud && $point==1)
                                        <div class="removeDate" onclick="deleteDateBtn(
                                                '{{$month}}',
                                                '{{$dateInfo['date']}}',
                                                '{{$dateInfo['transport_id']}}',
                                                '{{$dateInfo['abbreviation']}}');">
                                            <a href="#">&#10005;</a>
                                        </div>
                                        @endif
                                        ITKT -
                                        {{substr($tableData['thisTimePeriod']->years,-1).str_pad($month, 2, "0", STR_PAD_LEFT)}}
                                        {{$dateInfo['letter']}}
                                        <br>
                                        {{str_pad($month, 2, "0", STR_PAD_LEFT)}} /
                                        {{str_pad($dateInfo['date'], 2, "0", STR_PAD_LEFT)}}
                                        出荷計畫({{$dateInfo['abbreviation']}})
                                    </th>
                                    @endforeach
                                    <th colspan="2" style="width:200px">ITKT - 
                                        {{substr($tableData['thisTimePeriod']->years,-1).str_pad($month, 2, "0", STR_PAD_LEFT)}}<br>出荷計畫合計
                                    </th>
                                </tr>
                                <tr>
                                    @foreach($tableData['dateArray'] as $date)
                                    <th style="width:100px">台數</th>
                                    <th style="width:100px">金額</th>
                                    @endforeach
                                    <th style="width:100px">台數</th>
                                    <th style="width:100px">金額</th>
                                </tr>
                            </thead>
                        </table>
                    </div>

                    <!-- 列表中間 -->
                    <div class="shippingMonth-list-div-content"
                        style="width:{{420 + 200 * count($tableData['dateArray'])}}px">
                        <!-- 列表左側 -->
                        <table class="table shippingMonth-list-table left" style="width:120px;">
                            <tbody>
                                @foreach($tableData['shippingMonth'] as $itemCode => $shippingMonthArray)
                                <tr>
                                    <td style="width:120px;height:{{70+10*count($shippingMonthArray)}}px;"
                                        class="shippingMonth-list-td-left">
                                        {{($shippingMonthArray[0]['line_no']==0)?'無線別':'Line '.$shippingMonthArray[0]['line_no']}}
                                        <br>
                                        {{$itemCode}}
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>

                        <!-- 列表中間、右側 -->
                        <table class="table shippingMonth-list-table middle"
                            style="width:{{300 + 200 * count($tableData['dateArray'])}}px">
                            <tbody id="list-middle">
                                @foreach($tableData['shippingMonth'] as $itemCode => $shippingMonthArray)
                                <tr id="shippingMonth-content-{{$itemCode}}" onclick="selectTrContent(
                                        '{{$itemCode}}', 
                                        '{{$shippingMonthArray[0]['line_no']}}');">
                                    <td style="width:100px;height:{{70+10*count($shippingMonthArray)}}px;"
                                        class="shippingMonth-list-td-right">
                                        ￥{{number_format($shippingMonthArray[0]['cost'])}}
                                    </td>
                                    @php
                                    $rowNumber = 0;
                                    @endphp
                                    @foreach($shippingMonthArray as $shippingMonth)
                                    @php
                                    $rowNumber += $shippingMonth['number'];
                                    @endphp
                                    @endforeach
                                    @foreach($tableData['dateArray'] as $dateInfo)
                                    @php
                                    $info = '';
                                    $number = 0;
                                    @endphp
                                    @foreach($shippingMonthArray as $shippingMonth)
                                    @if($shippingMonth['date'] == $dateInfo['date'] &&
                                    $shippingMonth['transport_id'] == $dateInfo['transport_id'] &&
                                    $shippingMonth['lot_no'] > 0)
                                    @php
                                    $number += $shippingMonth['number'];
                                    $info = $info.'#'.$shippingMonth['lot_no']
                                    .':&emsp;&emsp;'.$shippingMonth['number'].'<br>'
                                    @endphp
                                    @endif
                                    @endforeach
                                    <td style="width:100px;height:{{70+10*count($shippingMonthArray)}}px;"
                                        class="shippingMonth-list-td-left">
                                        {!!$info!!}
                                    </td>
                                    <td style="width:100px;height:{{70+10*count($shippingMonthArray)}}px;"
                                        class="shippingMonth-list-td-right">
                                        {{($info != '')?'￥'.number_format($number * $shippingMonthArray[0]['cost']) : ''}}
                                    </td>
                                    @endforeach
                                    <td style="width:100px;height:{{70+10*count($shippingMonthArray)}}px;"
                                        class="shippingMonth-list-td-right">
                                        {{$rowNumber}}
                                    </td>
                                    <td style="width:100px;height:{{70+10*count($shippingMonthArray)}}px;"
                                        class="shippingMonth-list-td-right">
                                        ￥{{number_format($rowNumber * $shippingMonthArray[0]['cost'])}}
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <!-- 列表底端 -->
                    <div class="shippingMonth-list-div-bottom"
                        style="width:{{420 + 200 * count($tableData['dateArray'])}}px">
                        <table class="table table-bordered" style="margin-bottom:0px;">
                            <thead style="background-color:#fbfbfb">
                                <tr>
                                    <td style="width:220px">
                                        日幣合計
                                    </td>
                                    @php
                                    $totalNumber = 0;
                                    $totalCost = 0;
                                    $jpyCostMap = array();
                                    @endphp
                                    @foreach($tableData['dateArray'] as $dateInfo)
                                    @php
                                    $columnNumber = 0;
                                    $columnCost = 0;
                                    @endphp
                                    @foreach($tableData['shippingMonth'] as $itemCode => $shippingMonthArray)
                                    @foreach($shippingMonthArray as $shippingMonth)
                                    @if($shippingMonth['date'] == $dateInfo['date'] &&
                                    $shippingMonth['transport_id'] == $dateInfo['transport_id'] &&
                                    $shippingMonth['lot_no'] > 0)
                                    @php
                                    $columnNumber += $shippingMonth['number'];
                                    $columnCost += $shippingMonth['number'] * $shippingMonth['cost'];
                                    @endphp
                                    @endif
                                    @endforeach
                                    @endforeach
                                    @php
                                    $totalNumber += $columnNumber;
                                    $totalCost += $columnCost;
                                    $jpyCostMap[] = $columnCost;
                                    @endphp
                                    <td style="width:100px" class="shippingMonth-list-td-right">
                                        {{$columnNumber}}
                                    </td>
                                    <td style="width:100px" class="shippingMonth-list-td-right">
                                        ￥{{number_format($columnCost)}}
                                    </td>
                                    @endforeach
                                    <td style="width:100px" class="shippingMonth-list-td-right">
                                        {{$totalNumber}}
                                    </td>
                                    <td style="width:100px" class="shippingMonth-list-td-right">
                                        ￥{{number_format($totalCost)}}
                                    </td>
                                </tr>
                                <tr>
                                    <td style="width:220px">
                                        台幣合計
                                    </td>
                                    @foreach($jpyCostMap as $jpyCost)
                                    <td style="width:100px" class="shippingMonth-list-td-right" colspan="2">
                                        NT${{number_format($jpyCost * $tableData['exchange'])}}
                                    </td>
                                    @endforeach
                                    <td style="width:100px" class="shippingMonth-list-td-right" colspan="2">
                                        NT${{number_format($totalCost * $tableData['exchange'])}}
                                    </td>
                                </tr>
                            </thead>
                        </table>
                    </div>
                </section>
                @endif
            </div>

            <!-- 新增的內容 -->
            @if($point == 1 && $can_project_crud)
            <div class="tab-pane fade in {{($tableData['selectTab']=='projectItemCode')? 'active':''}}"
                id="projectItemCode-page">
                <section style="margin-bottom:30%;padding-top:1.5%;">
                    <form method="POST" id="project-form"
                        action="{{route('ShippingMonthController.createShippingMonth', 'ITEM_CODE')}}">
                        <!-- 清除、儲存按鈕 -->
                        @include('layouts.project.clear_save_button_layout')
                        <input type="hidden" name="period_tw" value="{{$period_tw}}">
                        <input type="hidden" name="month" value="{{$month}}">
                        <input type="hidden" name="version" value="{{$version}}">
                        <!-- 計畫表單 -->
                        <div id="project-form-div">
                            <table class="table project-table" style="margin-bottom:0px;">
                                <tbody>
                                    <tr>
                                        <td colspan="2"><span class="with-line"
                                                style="--width: 45%;">&ensp;基本資料&ensp;</span></td>
                                    </tr>
                                    <tr>
                                        <td class="right" style="width:40%">
                                            <span style="color:red">&#42;</span> 機種 :
                                        </td>
                                        <td style="width:60%">
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
                        </div>
                    </form>
                </section>
            </div>

            <div class="tab-pane fade in {{($tableData['selectTab']=='projectTransport')? 'active':''}}"
                id="projectTransport-page">
                <section style="margin-bottom:30%;padding-top:1.5%;">
                    <form method="POST" id="project-form"
                        action="{{route('ShippingMonthController.createShippingMonth', 'TRANSPORT')}}">
                        <!-- 清除、儲存按鈕 -->
                        @include('layouts.project.clear_save_button_layout')
                        <input type="hidden" name="period_tw" value="{{$period_tw}}">
                        <input type="hidden" name="month" value="{{$month}}">
                        <input type="hidden" name="version" value="{{$version}}">
                        <!-- 計畫表單 -->
                        <div id="project-form-div">
                            <table class="table project-table" style="margin-bottom:0px;">
                                <tbody>
                                    <tr>
                                        <td colspan="4"><span class="with-line"
                                                style="--width: 45%;">&ensp;基本資料&ensp;</span></td>
                                    </tr>
                                    <tr>
                                        <td class="right" style="width:30%"><span
                                                style="color:red">&#42;</span>&ensp;出荷日期 :</td>
                                        <td style="width:15%">
                                            <select required class="select-small" id="date" name="date">
                                                <div>
                                                    <div>
                                                        <option disabled selected value> 選擇日期 </option>
                                                        @foreach(range(1, $tableData['thisTimeDays']) as $day)
                                                        <option value="{{$day}}">
                                                            &emsp;{{str_pad($month, 2, "0", STR_PAD_LEFT)}} /
                                                            {{str_pad($day, 2, "0", STR_PAD_LEFT)}}
                                                        </option>
                                                        @endforeach
                                                    </div>
                                                </div>
                                            </select>
                                        </td>
                                        <td class="right" style="width:10%"><span
                                                style="color:red">&#42;</span>&ensp;出荷方式 :</td>
                                        <td style="width:45%">
                                            <select required class="select-middle" id="transport" name="transport_id">
                                                <div>
                                                    <div>
                                                        <option disabled selected value> 選擇出荷方式 </option>
                                                        @foreach($tableData["transport"] as $transportId => $transport)
                                                        <option value="{{$transportId}}">
                                                            {{$transport['name']}}
                                                        </option>
                                                        @endforeach
                                                    </div>
                                                </div>
                                            </select>
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