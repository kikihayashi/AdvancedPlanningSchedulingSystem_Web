@extends('system.system_content')
@section('inner_content')
<!-- 對話框設置 -->
@include('layouts.script.jq_dialog_layout')
@include('layouts.script.js_scaling_layout')
<!-- 大計劃規劃管理 -->
<script>
let selectId = "";
let selectItemCode = "";
let selectLotNo = "";
let thisTimeSelectTab = @json($tableData['selectTab']);
let tableEquipment = @json($tableData['equipment']);
let tableSetting = @json($tableData['setting']);
let tableTransport = @json($tableData['transport']);

document.addEventListener("DOMContentLoaded", function(event) {
    //初始隱藏新增頁面的出荷備註
    setTransportRemark(false);

    //選擇機種之後，帶出對應的永久品番、在庫品番
    $('#itemIndex').on('change', function() {
        var index = $(this).val();
        $('#itemCode').val(tableEquipment[index]['ItemCode']);
        $('#itemName').val(tableEquipment[index]['ItemName']);
        $('#eternalCode').val((tableEquipment[index]['日京永久品番'].length == 0) ?
            "-" : tableEquipment[index]['日京永久品番']);
        $('#stockCode').val((tableEquipment[index]['在庫品番'].length == 0) ?
            "-" : tableEquipment[index]['在庫品番']);
        return false;
    });

    //如果運輸類型有備註，要顯示出荷備註，不然就隱藏
    $('#transport').on('change', function() {
        var id = $(this).val();
        setTransportRemark(tableTransport[id]['is_remark'] === 'Y');
        return false;
    });

    //選擇出荷預定日之後，可推出生產預計日、材料納期預定日
    $('#shipment_date').on('change', function() {
        var shipment_date = new Date($(this).val().split(' ')[0]);
        $('#product_date').val(shipment_date.addDays(-7 * tableSetting[14][
            'setting_value'
        ])); //往前4週
        $('#material_date').val(shipment_date.addDays(-7 * tableSetting[15][
            'setting_value'
        ])); //往前12週
        return false;
    });
});

//設置出荷備註是否隱藏
function setTransportRemark(is_remark) {
    if (is_remark) {
        $('#remark-title-transport').show();
        $('#remark-content-transport').show();
        $('#remark_transport').val("");
        $('#remark_transport').prop('required', true);
    } else {
        $('#remark-title-transport').hide();
        $('#remark-content-transport').hide();
        $('#remark_transport').prop('required', false);
    }
}

//新增大計劃維護
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

//清除大計劃維護(當使用者點選清除時，將生產台數設為0)
function clearProjectBtn() {
    document.getElementById("project-span").innerHTML = "生產台數 : 0台";
    //方法二，在DOMContentLoaded裡加入以下
    // $('#project-form').on('reset', function() {
    //     document.getElementById("project-span").innerHTML = "生產台數 : 0台";
    // });
}

//刪除大計劃維護
function deleteProjectBtn() {
    if (confirm("確定要刪除" + selectItemCode + " - Lot no : " + selectLotNo + " ?")) {
        var url = "{{route('ManagementController.deleteManagement', ':id')}}";
        url = url.replace(':id', selectId);
        document.getElementById('formDeleteProject').action = url;
        document.getElementById('formDeleteProject').submit();
    }
}

//修改大計劃維護
function editProjectBtn() {
    var url = "{{route('ManagementController.editManagementPage', ':id')}}";
    url = url.replace(':id', selectId);
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
        "{{route('ManagementController.showManagementPage', ['period_tw' => ':selectPeriod', 'selectTab' => ':selectTab'])}}";
    url = url.replace(':selectPeriod', selectPeriodValue);
    url = url.replace(':selectTab', thisTimeSelectTab);
    window.location.href = url; //導向使用者點選的期別頁面
}

//偵測使用者選擇的資料列(tr)
function selectTrContent(id, itemCode, lot_no) {
    selectId = id;
    selectItemCode = itemCode;
    selectLotNo = lot_no;
    resetTrContent("list-middle");
    focusTrContent("management-content-" + id);
    $('#project-btn-title').html('目前選擇項目：' + itemCode + " - Lot no : " + lot_no);
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
    //這是讓最後一排td的顏色、邊框保持不變而設置的
    var lastTdArray = document.getElementsByClassName('management-list-last-td');
    Array.from(lastTdArray).forEach((lastTd) => {
        lastTd.style.background = '#FFFFFF';
        lastTd.style.setProperty("border", "0.114em solid #BFBFBF", "important");
    });
}

//偵測使用者選擇的資料項(td)
function selectTdContent(id, itemCode, lot_no, month, number) {
    for (i = 1; i <= 12; i++) {
        var elementId = 'td-' + itemCode + '-' + lot_no + '-' + i;
        if (i == month) {
            //加上邊框
            document.getElementById(elementId).classList.add("focus");
        } else {
            //去除邊框
            document.getElementById(elementId).classList.remove("focus");
        }
    }
    openDialog(id, itemCode, lot_no, month, number);
}

//開啟對話框
function openDialog(id, itemCode, lot_no, month, number) {
    //對話框設置
    var title = itemCode + "  #" + lot_no + "：" + month + "月";
    var url = "{{route('ManagementController.updateManagement',['id'=>':id', 'type'=>'SINGLE'])}}";
    url = url.replace(':id', id);
    $jq_dialog("#month-form").attr('action', url); //設置Action
    $jq_dialog("#month-form-name").val(month);
    $jq_dialog("#month-form-monthNumber").val(number);
    $jq_dialog("#month-dialog-div").dialog({
        title: title,
        autoOpen: false,
        width: 350,
        height: 235,
        modal: true,
        buttons: {
            "儲存": function() {
                //送出資料，這個是底下form的id
                document.getElementById("month-form").submit();
                //關閉對話框
                $jq_dialog(this).dialog("close");
            },
        },
        close: function() {
            //重置預設值
        }
    }).prev(".ui-dialog-titlebar").css("background", "#00C1DE").css("color", "white");
    $jq_dialog("#month-dialog-div").dialog("open");
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

//天數往後加
Date.prototype.addDays = function(days) {
    var date = new Date(this.valueOf());
    date.setDate(date.getDate() + days);
    return date.toISOString().substring(0, 10);
}
</script>

<section>
    @php
    $projectType = 'M';
    $period_tw = $tableData['thisTimePeriod']->period_tw;
    $month = 0;
    $version = $tableData['progress']['version'];
    $point = $tableData['progress']['progress_point'];
    $can_operation = $tableData['permission'][$point] == 'Y';
    $can_project_crud = $tableData['permission']['project_crud'] == 'Y';
    $can_create_project = $tableData['progress']['create_project'] == 'Y';
    $data_number = count($tableData['management']);
    $hasZeroLineNo = false;
    $canDoubleClick = ($point==1 && $can_project_crud);
    @endphp

    <!-- 計畫頁面右上角操作 -->
    @include('layouts.project.operation_layout')
    <!-- 進度條 -->
    <div id="menus">
        <!-- 當期資訊 -->
        @include('layouts.project.info_management_layout')
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
            <!-- 大計劃列表的內容 -->
            <div class="tab-pane fade in {{($tableData['selectTab']=='projectList')? 'active':''}}"
                id="projectList-page">
                @if($data_number == 0)
                <br>
                <span style="margin-left:20px;font-size:30px">尚無計劃資料</span>
                @else
                <section style="padding-top:1.5%;">
                    <div style="float:left;">
                        <span style='font-size:23px;color:#000000'>&#9733;</span>
                        <span>: 部品到著預定日</span>
                    </div>
                    <!-- 刪除、編輯按鈕 -->
                    @include('layouts.project.delete_edit_button_layout')
                    <form id="formDeleteProject" style="display:none;" method="POST">
                        @csrf
                        @method('DELETE')
                    </form>
                </section>

                <section class="management-list-section">
                    <!-- 列表頂端 -->
                    <div class="management-list-div-title">
                        <table class="table table-bordered" style="margin-bottom:0px;">
                            <thead style="background-color:#F5F6FB">
                                <tr>
                                    <th style="width:10%">品名</th>
                                    <th style="width:6%">製番</th>
                                    <th style="width:6%">台數</th>
                                    <th style="width:8%">台灣AH</th>
                                    @foreach($tableData['monthMaps'] as $monthMaps)
                                    <th style="width:5%">{{$monthMaps['page']}}月</th>
                                    @endforeach
                                    <th style="width:10%">本期生產總台數</th>
                                </tr>
                            </thead>
                        </table>
                    </div>

                    <!-- 列表內容 -->
                    <div class="management-list-div-content">
                        <!-- 列表左側 -->
                        <table class="table management-list-table left">
                            <tbody>
                                @foreach($tableData['management'] as $itemCode => $managementArray)
                                @php
                                $i = 0;
                                @endphp
                                @foreach($managementArray as $management)
                                <tr>
                                    @if($i == 0)
                                    @php
                                    $i++;
                                    @endphp
                                    <td style="width:10%;height:{{80*count($managementArray)}}px;"
                                        class="management-list-td-left" rowspan="{{count($managementArray)}}">
                                        {{$itemCode}}
                                    </td>
                                    @endif
                                </tr>
                                @endforeach
                                @endforeach
                            </tbody>
                        </table>

                        <!-- 列表中間、右側 -->
                        <table class="table management-list-table middle">
                            <tbody id="list-middle">
                                @foreach($tableData['management'] as $itemCode => $managementArray)
                                @php
                                $i = 0;
                                @endphp
                                @foreach($managementArray as $management)
                                <tr id="management-content-{{$management['id']}}" onclick="selectTrContent(
                                            '{{$management['id']}}',
                                            '{{$itemCode}}',
                                            '{{$management['lot_no']}}');">
                                    <td style="height:80px;width:6%" class="management-list-td-middle">
                                        #{{$management['lot_no']}}
                                    </td>
                                    <td style="height:80px;width:6%" class="management-list-td-middle">
                                        {{number_format($management['lot_total'])}}
                                    </td>
                                    <td style="height:80px;width:8%" class="management-list-td-middle">
                                        {{($management['firstWorkHour'] == 0)? '-' : $management['firstWorkHour'].'H'}}
                                        <br>
                                        {{($management['lastWorkHour'] == 0)? '-' : $management['lastWorkHour'].'H'}}
                                    </td>
                                    @foreach($tableData['monthMaps'] as $monthMap)
                                    @php
                                    $thisTimeYear = $tableData['thisTimePeriod']->years;
                                    $arrivalDate = $management['arrival_date'];
                                    $arrivalArray = explode('-', $arrivalDate);
                                    $arrivalYear = $arrivalArray[0];
                                    $arrivalMonth = $arrivalArray[1];
                                    $minDate = $thisTimeYear.'-04-01';
                                    $maxDate = ($thisTimeYear + 1).'-03-31';
                                    $withinRange = ($minDate <= $arrivalDate && $arrivalDate <=$maxDate); @endphp <td
                                        style="height:80px;width:5%;" class="management-list-td-right"
                                        id="td-{{$itemCode}}-{{$management['lot_no']}}-{{$monthMap['page']}}"
                                        @if($canDoubleClick) ondblclick="selectTdContent(
                                            '{{$management['id']}}',
                                            '{{$itemCode}}',
                                            '{{$management['lot_no']}}',
                                            '{{$monthMap['page']}}',
                                            '{{$management['month_'.$monthMap['page']]}}');" @endif>
                                        {{(intval($management['month_'.$monthMap['page']])==0)? '' : $management['month_'.$monthMap['page']]}}
                                        {!!($withinRange && (intval($arrivalMonth)==$monthMap['page']))?'&#9733;':''!!}
                                        </td>
                                        @endforeach

                                        @if($i == 0)
                                        @php
                                        $i++;
                                        $totalNumber = 0;
                                        foreach($managementArray as $management) {
                                        $totalNumber += intval($management['real_lot_number']);
                                        }
                                        @endphp
                                        <td style="width:10%;height:{{80*count($managementArray)}}px;"
                                            rowspan="{{count($managementArray)}}" class="management-list-last-td">
                                            {{$totalNumber}}台
                                        </td>
                                        @endif
                                </tr>
                                @endforeach
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </section>
                @endif
            </div>

            <!-- 新增的內容 -->
            @if($point == 1 && $can_project_crud)
            <div class="tab-pane fade in {{($tableData['selectTab']=='projectCreate')? 'active':''}}"
                id="projectCreate-page">
                <section style="margin-bottom:8%;padding-top:1.5%;">
                    <form method="POST" id="project-form" action="{{route('ManagementController.createManagement')}}">
                        <!-- 清除、儲存按鈕 -->
                        @include('layouts.project.clear_save_button_layout')
                        <input type="hidden" name="period_tw" value="{{$period_tw}}">
                        <input type="hidden" name="version" value="{{$version}}">
                        <!-- 計畫表單 -->
                        <div id="project-form-div">
                            <table class="table project-table">
                                <tbody>
                                    <tr>
                                        <td colspan="6"> <span class="with-line"
                                                style="--width: 45%;">&ensp;基本資料&ensp;</span></td>
                                    </tr>
                                    <tr>
                                        <td class="right" style="width:28%"><span style="color:red">&#42;</span> 機種 :
                                        </td>
                                        <td>
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
                                        <td class="right" style="width:36%">永久品番 :</td>
                                        <td><input type="text" name="eternal_code" id="eternalCode"
                                                class="input-not-used" readonly="readonly" value="-">
                                        </td>
                                        <td class="right" style="width:36%">在庫品番 :</td>
                                        <td> <input type="text" name="stock_code" id="stockCode" class="input-not-used"
                                                readonly="readonly" value="-">
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="right"><span style="color:red">&#42;</span> Lot No :</td>
                                        <td><input min="1" required type="number" name="lot_no" id="lot_no"
                                                class="input-small" /></td>
                                        <td class="right"><span style="color:red">&#42;</span> Lot總台數 :</td>
                                        <td><input min="0" required type="number" name="lot_total" id="lot_total"
                                                class="input-small" /></td>
                                        <td class="right"><span style="color:red">&#42;</span> 整批/分批 :</td>
                                        <td><select required class="select-small" name="batch" id="batch"
                                                value="entire_batch">
                                                <div>
                                                    <div>
                                                        <option value="entire_batch">整批</option>
                                                        <option value="single_batch">分批</option>
                                                    </div>
                                                </div>
                                            </select></td>
                                    </tr>
                                    <tr>
                                        <td class="right"><span style="color:red">&#42;</span> 出荷方式 :</td>
                                        <td>
                                            <select required class="select-middle" id="transport" name="transport_id">
                                                <div>
                                                    <div>
                                                        <option disabled selected value>選擇出荷方式</option>
                                                        @foreach($tableData['transport'] as $transport)
                                                        <option value="{{$transport->id}}">
                                                            {{$transport->name}}
                                                        </option>
                                                        @endforeach
                                                    </div>
                                                </div>
                                            </select>
                                        </td>
                                        <td class="right" id="remark-title-transport"><span
                                                style="color:red">&#42;</span>
                                            出荷備註 :
                                        </td>
                                        <td id="remark-content-transport">
                                            <textarea class="textarea-small" name="remark_transport"
                                                id="remark_transport" placeholder="說明"></textarea>
                                        </td>
                                        <td class="right" id="remark-title-other">其他備註 :</td>
                                        <td>
                                            <textarea class="textarea-small" name="remark_other" id="remark_other"
                                                placeholder="說明"></textarea>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td colspan="6"><span class="with-line"
                                                style="--width: 44.2%;margin-top: 2%;">&ensp;生產、出荷&ensp;</span>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="right"><span style="color:red">&#42;</span> 部品到著預定日 :</td>
                                        <td><input required type="text" name="arrival_date" id="arrival_date"
                                                onblur="(this.type='text')" onfocus="(this.type='date')"
                                                class="input-large" placeholder="請選擇日期" /></td>
                                        <td class="right"><span style="color:red">&#42;</span> 出荷預定日 :</td>
                                        <td><select required class="select-middle" name="shipment_date"
                                                id="shipment_date">
                                                <div>
                                                    <div>
                                                        <option disabled selected value>選擇出荷預定日</option>
                                                        @foreach($tableData['shippingDateArray'] as
                                                        $shippingDate)
                                                        <option value="{{$shippingDate}}">{{$shippingDate}}
                                                        </option>
                                                        @endforeach
                                                    </div>
                                                </div>
                                            </select>
                                        </td>
                                        <td class="right"> 實際出荷日 :</td>
                                        <td><input type="text" name="actual_date" id="actual_date"
                                                onblur="(this.type='text')" onfocus="(this.type='date')"
                                                class="input-large" placeholder="請選擇日期">
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="right"><span style="color:red">&#42;</span>
                                            生產預計日
                                            <a style="all: unset" title="對應年度生產計劃 組立開始日">
                                                <i class="fa fa-question-circle"></i>
                                            </a> :
                                        </td>
                                        <td><input readonly="readonly" type="text" name="product_date" id="product_date"
                                                class="input-large-not-used">
                                        </td>
                                        <td class="right"><span style="color:red">&#42;</span>
                                            材料納期預定日
                                            <a style="all: unset" title="對應年度生產計劃 部品到著日">
                                                <i class="fa fa-question-circle"></i>
                                            </a> :
                                        </td>
                                        <td width="300"><input readonly="readonly" type="text" name="material_date"
                                                id="material_date" class="input-large-not-used">
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
                                        <td colspan="8"><span id="project-span">生產台數 : 0台</span></td>
                                    </tr>

                                    @foreach(range(0,2) as $i)
                                    <tr>
                                        @foreach(range(0,3) as $j)
                                        <td class="right">
                                            <span style="color:red">&#42;</span>
                                            {{$tableData['monthMaps'][$i * 4 + $j]['page']}}月 :
                                        </td>
                                        <td>
                                            <input required min="0" type="number" name="monthNumber[]"
                                                id="month-{{$tableData['monthMaps'][$i*4 + $j]['page']}}" value="0"
                                                class="input-month" oninput="numberListener()" />
                                        </td>
                                        @endforeach
                                    </tr>
                                    @endforeach
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

<!-- 點選單月的對話框(初始隱藏) -->
<div class="management-form" id="month-dialog-div" style="display:none">
    <form id="month-form" method="POST">
        @csrf
        @method("PUT")
        <fieldset>
            <div style="margin-top:30px">
                <div id="div-fieldset">
                    <label style="margin-top:2%">數量：</label>
                    <input id="month-form-monthNumber" type="number" name="monthNumber[]" value="" min="0"
                        class="input-small" placeholder="請輸入數量">
                    <input id="month-form-name" type="hidden" name="month" value="">
                </div>
            </div>
        </fieldset>
    </form>
</div>
@stop