@extends('system.system_content')
@section('inner_content')
<!-- dataTable -->
@include('layouts.script.jq_dataTable_layout')
@include('layouts.script.js_scaling_layout')
<!-- 年度生產計畫 -->
<script>
let selectLine = 0;
let selectId = "";
let selectItemCode = "";
let selectLotNo = "";
let thisTimeSelectTab = @json($tableData['selectTab']);
let tableEquipment = @json($tableData['equipment']);

document.addEventListener("DOMContentLoaded", function(event) {
    //初始隱藏新增頁面的內藏備註
    setHiddenRemark(false);

    //選擇機種之後，帶出機種名稱，如果機種有內藏，要顯示內藏備註，不然就隱藏
    $('#itemIndex').on('change', function() {
        var index = $(this).val();
        $('#itemCode').val(tableEquipment[index]['ItemCode']);
        $('#itemName').val(tableEquipment[index]['ItemName']);
        setHiddenRemark(tableEquipment[index]['是否內藏'] === '是');
        return false;
    });
});

//設置內藏備註是否隱藏
function setHiddenRemark(is_remark) {
    if (is_remark) {
        $('#remark-title-hidden').show();
        for (month = 1; month <= 12; month++) {
            $('#remarkHidden-' + month).show();
        }
    } else {
        $('#remark-title-hidden').hide();
        for (month = 1; month <= 12; month++) {
            $('#remarkHidden-' + month).hide();
        }
    }
}

//新增年度生產計劃
function saveProjectBtn() {
    var lot_total = Number(document.getElementById('lot_total').value);
    var lot_number = 0;
    //每月數量總和
    for (i = 1; i <= 12; i++) {
        lot_number += Number(document.getElementById('month-' + i).value);
    }
    if (lot_total > lot_number) {
        return confirm("Lot總台數與每月數量總和不一致，是否新增資料？");
    } else if (lot_total < lot_number) {
        alert("Lot總台數不可以小於實際總台數！");
        return false;
    }
}

//清除年度生產計劃(當使用者點選清除時，將生產台數設為0)
function clearProjectBtn() {
    document.getElementById("project-span").innerHTML = "生產台數 : 0台";
    //隱藏內藏備註
    $('#remark-title-hidden').hide();
    for (month = 1; month <= 12; month++) {
        $('#remarkHidden-' + month).hide();
    }
}

//刪除年度生產計劃
function deleteProjectBtn() {
    if (confirm("確定要刪除" + selectItemCode + " - Lot no : " + selectLotNo + " ?")) {
        var url = "{{route('ProductionYearController.deleteProductionYear', ':id')}}";
        url = url.replace(':id', selectId);
        document.getElementById('formDeleteProject').action = url;
        document.getElementById('formDeleteProject').submit();
    }
}

//編輯年度生產計劃
function editProjectBtn() {
    if (selectLine == 0) {
        alert("尚未設定線別，不可編輯！");
    } else {
        var url = "{{route('ProductionYearController.editProductionYearPage', ':id')}}";
        url = url.replace(':id', selectId);
        location.href = url;
    }
}

//上傳年度生產計劃到SAP
function uploadProjectBtn(version) {
    if (version == 0) {
        alert('當前版本為0，不可上傳至SAP！');
        return false;
    }
    return confirm("確定上傳至SAP嗎？");
}

//選擇分頁(列表、新增)
function selectTab(selectTab) {
    thisTimeSelectTab = selectTab;
}

//選擇Line分頁時，隱藏編輯、刪除按鈕以及取消所有Tr
function selectLineTab(line) {
    $('#project-btn-div').hide();
    resetTrContent("list-middle-" + line);
}

//下拉式選單，選擇的期別
function selectPeriod() {
    var selectBox = document.getElementById("select-period");
    var selectPeriodValue = selectBox.options[selectBox.selectedIndex].value;
    var url =
        "{{route('ProductionYearController.showProductionYearPage',  ['period_tw' => ':selectPeriod', 'selectTab' => ':selectTab'])}}";
    url = url.replace(':selectPeriod', selectPeriodValue);
    url = url.replace(':selectTab', thisTimeSelectTab);
    window.location.href = url; //導向使用者點選的期別頁面
}

//偵測使用者選擇的資料列(tr)
function selectTrContent(id, itemCode, lot_no, line) {
    selectId = id;
    selectItemCode = itemCode;
    selectLotNo = lot_no;
    selectLine = line;
    resetTrContent("list-middle-" + line);
    focusTrContent("productionYear-content-" + id);
    var lineTitle = (line == 0) ? '無線別' : ('Line ' + line);
    $('#project-btn-title').html('目前選擇項目：【' + lineTitle + '】' + itemCode + " - Lot no : " + lot_no);
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

//偵測每個月輸入的台數值，並加總顯示出來
function numberListener() {
    var lot_number = 0;
    for (i = 1; i <= 12; i++) {
        var thisMonthNumber = Number(document.getElementById('month-' + i).value);
        document.getElementById('month-' + i).value = thisMonthNumber;
        lot_number += thisMonthNumber;
    }
    document.getElementById("project-span").innerHTML = "生產台數 : " + lot_number + "台";
}

//偵測生產區間輸入的值是否位於合理範圍，如果有誤，會立刻調整
function rangeListener(month, type, maxDay) {
    var startDay = Number(document.getElementById('range-start-' + month).value);
    var endDay = Number(document.getElementById('range-end-' + month).value);
    switch (type) {
        case 'start':
            if (startDay < 1) {
                startDay = 1;
            } else if (startDay > endDay) {
                startDay = endDay;
            }
            break;

        case 'end':
            if (endDay > maxDay) {
                endDay = maxDay;
            } else if (endDay < startDay) {
                endDay = startDay;
            }
            break;
    }
    document.getElementById('range-start-' + month).value = startDay;
    document.getElementById('range-end-' + month).value = endDay;
}
</script>

<section>
    @php
    $projectType = 'PY';
    $period_tw = $tableData['thisTimePeriod']->period_tw;
    $month = 0;
    $version = $tableData['progress']['version'];
    $point = $tableData['progress']['progress_point'];
    $can_operation = $tableData['permission'][$point] == 'Y';
    $can_project_crud = $tableData['permission']['project_crud'] == 'Y';
    $can_create_project = $tableData['progress']['create_project'] == 'Y';
    $data_number = count($tableData['productionYear']);
    $hasZeroLineNo = $tableData['hasZeroLineNo'];
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
            <!--  年度生產計畫列表的內容 -->
            <div class="tab-pane fade in {{($tableData['selectTab']=='projectList')? 'active':''}}"
                id="projectList-page">
                @if($data_number == 0)
                <br>
                <span style="margin-left:20px;font-size:30px">尚無計劃資料</span>
                @else
                <section style="padding-top:1.5%;display:flex;flex-direction:column">
                    <div style="margin-bottom:20px;">
                        <div style="float:left;">
                            <span style="font-size:23px;color:#81C784">&#11201;</span>
                            : 部品到著日
                            <span style='font-size:18px;color:#E1E6EB'> |</span>
                            <span style='font-size:23px;color:#FF5252'>&#9733;</span>
                            : 組立開始日
                        </div>
                        <!-- 刪除、編輯按鈕 -->
                        @include('layouts.project.delete_edit_button_layout')
                        <form id="formDeleteProject" style="display:none;" method="POST">
                            @csrf
                            @method('DELETE')
                        </form>
                    </div>

                    <!-- TAB -->
                    <div class="productionYear-div-nav-tabs">
                        <ul class="nav nav-tabs" style="margin-right:5%;">
                            @foreach($tableData['productionYear'] as $line => $productionYearArray)
                            @php
                            $is_active = ($line == array_key_first($tableData['productionYear']));
                            @endphp
                            <!-- Line的TAB -->
                            <li class="nav-item">
                                <a id="productionYear-nav-link-a" class="nav-link @if($is_active) active @endif"
                                    onclick="selectLineTab('{{$line}}');" data-toggle="tab" href="#line-{{$line}}-page">
                                    @if($line==0)
                                    無線別
                                    @else
                                    Line {{$line}}
                                    @endif
                                </a>
                            </li>
                            @endforeach
                        </ul>

                        <div class="tab-content">
                            @foreach($tableData['productionYear'] as $line => $productionYearArray)
                            @php
                            $is_active = ($line == array_key_first($tableData['productionYear']));
                            @endphp
                            <!-- Line的內容 -->
                            <div class="tab-pane fade in @if($is_active) active @endif" id="line-{{$line}}-page">
                                <section class="productionYear-list-section">
                                    <!-- 列表頂端 -->
                                    <div class="productionYear-list-div-title">
                                        <table class="table table-bordered" style="margin-bottom:0px;">
                                            <thead style="background-color:#F5F6FB">
                                                <tr>
                                                    <th rowspan="2" style="width:9%">機種</th>
                                                    <th rowspan="2" style="width:6%">ORDER NO</th>
                                                    <th rowspan="2" style="width:5%">納期</th>
                                                    <th rowspan="2" style="width:5%">製番</th>
                                                    <th rowspan="2" style="width:5%">台數</th>
                                                    <th rowspan="2" style="width:5%">部品到著</th>
                                                    <th rowspan="2" style="width:5%">組立開始</th>
                                                    <th colspan="9" style="width:45%">
                                                        {{$tableData['thisTimePeriod']->years}}年
                                                    </th>
                                                    <th colspan="3" style="width:15%">
                                                        {{$tableData['thisTimePeriod']->years + 1}}年
                                                    </th>
                                                </tr>
                                                <tr>
                                                    @foreach($tableData['monthMaps'] as $monthMaps)
                                                    <th style="width:5%">
                                                        {{$monthMaps['name']}}<br>出勤：{{$monthMaps['workDay']}}日
                                                    </th>
                                                    @endforeach
                                                </tr>
                                            </thead>
                                        </table>
                                    </div>

                                    <!-- 列表內容 -->
                                    <div class="productionYear-list-div-content">
                                        <!-- 列表左側 -->
                                        <table class="table productionYear-list-table left">
                                            <tbody>
                                                @foreach($productionYearArray as $productionYear)
                                                <tr>
                                                    <td id="productionYear-list-td-left">
                                                        @if($line==0)
                                                        無線別
                                                        @else
                                                        Line {{$line}}
                                                        @endif
                                                        @if($productionYear['is_hidden']=='Y')
                                                        <button disabled id="productionYear-isHidden-button">內藏</button>
                                                        @endif
                                                        <br>{{$productionYear['item_code']}}
                                                    </td>
                                                </tr>
                                                @endforeach
                                            </tbody>
                                        </table>

                                        <!-- 列表中間、右側 -->
                                        <table class="table productionYear-list-table middle">
                                            <tbody id="list-middle-{{$line}}">
                                                @foreach($productionYearArray as $productionYear)
                                                <tr id="productionYear-content-{{$productionYear['id']}}" onclick="selectTrContent(
                                            '{{$productionYear['id']}}',
                                            '{{$productionYear['item_code']}}',
                                            '{{$productionYear['lot_no']}}',
                                            '{{$line}}');">
                                                    <td style="width:6%" id="productionYear-list-td-left">
                                                        {!!nl2br($productionYear['order_no'])!!}
                                                    </td>
                                                    <td style="width:5%" id="productionYear-list-td-middle">
                                                        {{$productionYear['deadline']}}
                                                    </td>
                                                    <td style="width:5%" id="productionYear-list-td-middle">
                                                        #{{$productionYear['lot_no']}}
                                                    </td>
                                                    <td style="width:5%" id="productionYear-list-td-right">
                                                        {{$productionYear['lot_total']}}
                                                    </td>
                                                    <td style="width:5%" id="productionYear-list-td-middle">
                                                        {{str_replace('-','-',substr($productionYear['material_date'], 0))}}
                                                    </td>
                                                    <td style="width:5%" id="productionYear-list-td-middle">
                                                        {{str_replace('-','-',substr($productionYear['product_date'], 0))}}
                                                    </td>
                                                    @foreach($tableData['monthMaps'] as $monthMap)
                                                    @php
                                                    $yearType = $monthMap['yearType'].'WorkHour';
                                                    $workHour = $productionYear[$yearType];
                                                    $monthNumber = intval($productionYear['month_'.$monthMap['page']]);
                                                    $thisTimeYear = $tableData['thisTimePeriod']->years;

                                                    if (isset($totalMap[$line.'-'.$monthMap['page']])) {
                                                    $totalMap[$line.'-'.$monthMap['page']]['number'] += $monthNumber;
                                                    $totalMap[$line.'-'.$monthMap['page']]['workHour'] += $monthNumber *
                                                    $workHour;
                                                    }
                                                    else {
                                                    $totalMap[$line.'-'.$monthMap['page']]['number'] = $monthNumber;
                                                    $totalMap[$line.'-'.$monthMap['page']]['workHour'] = $monthNumber *
                                                    $workHour;
                                                    }

                                                    $product_date = $productionYear['product_date'];
                                                    $product_array = explode('-', $product_date);
                                                    $product_year = $product_array[0];
                                                    $product_month = $product_array[1];

                                                    $material_date = $productionYear['material_date'];
                                                    $material_array = explode('-', $material_date);
                                                    $material_year = $material_array[0];
                                                    $material_month = $material_array[1];

                                                    $min_date = $thisTimeYear.'-04-01';
                                                    $max_date = ($thisTimeYear + 1).'-03-31';
                                                    @endphp
                                                    <td style="width:5%" id="productionYear-list-td-middle">
                                                        @if($min_date <= $material_date && $material_date <=$max_date &&
                                                            (intval($material_month)==$monthMap['page'])) <a
                                                            style="all: unset" title="部品到著日：{{$material_date}}">
                                                            <span style='font-size:16px;color:#81C784'>&#11201;</span>
                                                            </a>
                                                            @endif
                                                            @if($min_date <= $product_date && $product_date<=$max_date
                                                                && (intval($product_month)==$monthMap['page'])) <a
                                                                style="all: unset" title="組立開始日：{{$product_date}}">
                                                                <span
                                                                    style='font-size:16px;color:#FF5252'>&#9733;</span>
                                                                </a>
                                                                @endif
                                                                @if($monthNumber > 0)
                                                                {{$monthNumber}}
                                                                <hr id="productionYear-hr">
                                                                {{number_format($monthNumber * $workHour , 3, '.', '')}}
                                                                @endif
                                                    </td>
                                                    @endforeach
                                                </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>

                                    <!-- 列表底端 -->
                                    <div class="productionYear-list-div-bottom">
                                        <table class="table table-bordered" style="margin-bottom:0px;">
                                            <thead style="background-color:#fbfbfb">
                                                <tr>
                                                    <td style="width:35%" colspan="6">
                                                        @if($line>0)
                                                        Line {{$line}}
                                                        @endif
                                                        小計
                                                    </td>
                                                    <td style="width:5%">台數<br>時數</td>
                                                    @foreach($tableData['monthMaps'] as $monthMap)
                                                    <td style="width:5%" id="productionYear-list-td-right">
                                                        @if($totalMap[$line.'-'.$monthMap['page']]['number'] > 0)
                                                        {{$totalMap[$line.'-'.$monthMap['page']]['number']}}
                                                        <br>
                                                        {{number_format($totalMap[$line.'-'.$monthMap['page']]['workHour'],3,'.','')}}
                                                        @endif
                                                    </td>
                                                    @endforeach
                                                </tr>
                                            </thead>
                                        </table>
                                    </div>
                                </section>
                            </div>
                            @endforeach
                        </div>
                    </div>
                </section>
                @endif
            </div>

            <!-- 新增的內容 -->
            @if($point == 1 && $can_project_crud)
            <div class="tab-pane fade in {{($tableData['selectTab']=='projectCreate')? 'active':''}}"
                id="projectCreate-page">
                <section style="margin-bottom:8%;padding-top:1.5%;">
                    <form method="POST" id="project-form"
                        action="{{route('ProductionYearController.createProductionYear')}}">
                        <!-- 清除、儲存按鈕 -->
                        @include('layouts.project.clear_save_button_layout')
                        <input type="hidden" name="period_tw" value="{{$period_tw}}">
                        <input type="hidden" name="version" value="{{$version}}">
                        <!-- 計畫表單 -->
                        <div id="project-form-div">
                            <table class="table project-table">
                                <tbody>
                                    <tr>
                                        <td colspan="4"><span class="with-line"
                                                style="--width: 45%;">&ensp;基本資料&ensp;</span></td>
                                    </tr>
                                    <tr>
                                        <td class="right" style="width:20%"><span style="color:red">&#42;</span> 機種 :
                                        </td>
                                        <td style="width:10%">
                                            <input type="hidden" value="" id="itemCode" name="item_code">
                                            <input type="hidden" value="" id="itemName" name="item_name">
                                            <select required class="select-large" id="itemIndex">
                                                <div>
                                                    <div>
                                                        <option disabled selected value>選擇機種</option>
                                                        @foreach(range(0, count($tableData['equipment'])-1) as $index)
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

                                    <tr>
                                        <td class="right"><span style="color:red">&#42;</span> Lot No :</td>
                                        <td><input min="1" required type="number" name="lot_no" id="lot_no"
                                                class="input-small"></td>
                                        <td class="right" style="width:10%"><span style="color:red">&#42;</span>
                                            組立開始 :
                                        </td>
                                        <td style="width:20%">
                                            <input required type="text" name="product_date" id="product_date"
                                                onblur="(this.type='text')" onfocus="(this.type='date')"
                                                class="input-large" placeholder="請選擇日期">
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="right"><span style="color:red">&#42;</span> Lot總台數 :</td>
                                        <td><input min="0" required type="number" name="lot_total" id="lot_total"
                                                class="input-small"></td>
                                        <td class="right"><span style="color:red">&#42;</span>
                                            部品到著 :
                                        </td>
                                        <td width="30%">
                                            <input required type="text" name="material_date" id="material_date"
                                                onblur="(this.type='text')" onfocus="(this.type='date')"
                                                class="input-large" placeholder="請選擇日期">
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="right" id="remark-title">備註 :</td>
                                        <td>
                                            <textarea class="textarea-small" name="remark" id="remark"
                                                placeholder=""></textarea>
                                        </td>
                                        <td class="right" id="deadline-title">納期 :</td>
                                        <td>
                                            <textarea class="textarea-small" name="deadline" id="deadline"
                                                placeholder=""></textarea>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>

                            <table class="table project-table">
                                <tbody>
                                    <tr>
                                        <td colspan="8"><span class="with-line"
                                                style="--width: 46.5%;">&ensp;詳細&ensp;</span></td>
                                    </tr>
                                    <tr>
                                        <td colspan="1"></td>
                                        <td class="right" colspan="2"><span id="project-span"><strong>生產台數 :
                                                    0台</strong></span></td>
                                        <td class="right" colspan="3" style="text-align:left">
                                            <span><strong>生產區間</strong></span></td>
                                        <td class="right" colspan="2" style="text-align:left"><span
                                                id="remark-title-hidden"><strong>內藏備註</strong></span></td>
                                    </tr>

                                    @foreach($tableData['monthMaps'] as $monthMap)
                                    <tr>
                                        <td class="right" style="width:25%">{{$monthMap['page']}}月 :</td>
                                        <td style="width:5%"><input min="0" type="number" name="monthNumber[]"
                                                id="month-{{$monthMap['page']}}" value="0" class="input-month"
                                                oninput="numberListener()">
                                        </td>
                                        <td style="width:10%;text-align:left">台</td>

                                        <td style="width:5%"><input min="1" max="{{$monthMap['totalDay']}}"
                                                type="number" name="rangeStart[]" id="range-start-{{$monthMap['page']}}"
                                                value="1"
                                                onchange="rangeListener('{{$monthMap['page']}}','start', '{{$monthMap['totalDay']}}')"
                                                class="input-month">
                                        </td>
                                        <td style="width:2%"> ~ </td>
                                        <td style="width:13%"><input min="1" max="{{$monthMap['totalDay']}}"
                                                type="number" name="rangeEnd[]" id="range-end-{{$monthMap['page']}}"
                                                value="{{$monthMap['totalDay']}}"
                                                onchange="rangeListener('{{$monthMap['page']}}', 'end', '{{$monthMap['totalDay']}}')"
                                                class="input-month">
                                        </td>
                                        <td style="width:40%">
                                            <textarea class="textarea-small" name="remarkHidden[]"
                                                id="remarkHidden-{{$monthMap['page']}}" placeholder=""></textarea>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </form>
                </section>
            </div>

            <!-- 轉入SAP的內容 -->
            @elseif($point == 0)
            <div class="tab-pane fade in {{($tableData['selectTab']=='projectSap')? 'active':''}}" id="projectSap-page">
                @if(count($tableData['sapData']) == 0)
                <br>
                <span style="margin-left:20px;font-size:30px">尚無資料可上傳</span>
                @else
                <form method="POST" id="sap-form"
                    action="{{route('ProductionYearController.uploadProductionYear', $period_tw)}}">
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
                            <th style="width:20%;">機種</th>
                            <th style="width:10%;">數量</th>
                            <th style="width:20%;">材料納期預定日</th>
                            <th style="width:20%;">生產預計日</th>
                            <th style="width:20%;">日期</th>
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
    "number",
    "materialDate",
    "productDate",
    "date"
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
                targets: [2, 3, 4, 5] //指定哪些欄位不排序
            },
            {
                className: 'text-center', //資料對齊中間
                targets: [0, 3, 4, 5] //指定哪些欄位對齊中間
            },
            {
                className: 'text-right', //資料對齊右邊
                targets: [2] //指定哪些欄位對齊中間
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