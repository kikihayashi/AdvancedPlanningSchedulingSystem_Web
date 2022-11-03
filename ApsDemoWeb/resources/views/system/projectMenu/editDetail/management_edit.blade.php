@extends('layouts.sidebar_outer')
@section('outer_content')

<script>
let tableSetting = @json($tableData['setting']);
let tableTransport = @json($tableData['transport']);
let tableManagement = @json($tableData['management']);
let realLotNumber = 0;

document.addEventListener("DOMContentLoaded", function(event) {
    //因為是編輯介面，所以初始化時先獲取實際總台數
    realLotNumber = numberListener();
    //設定出荷備註是否要隱藏
    setTransportRemark(tableManagement['is_remark_transport'] === 'Y');

    //如果運輸類型有備註，要顯示出荷備註，不然就隱藏
    $('#transport').on('change', function() {
        var id = $(this).val();
        setTransportRemark(tableTransport[id]['is_remark'] === 'Y');
        return false;
    });

    //選擇出荷預定日之後，可推出生產預計日、材料納期預定日
    $('#shipment_date').on('change', function() {
        var shipment_date = new Date($(this).val().split(' ')[0]);
        $('#product_date').val(shipment_date.addDays(-7 * tableSetting[14]['setting_value'])); //往前4週
        $('#material_date').val(shipment_date.addDays(-7 * tableSetting[15]['setting_value'])); //往前12週
        return false;
    });
});

//設置出荷備註是否隱藏
function setTransportRemark(is_remark) {
    if (is_remark) {
        $('#remark-title-transport').show();
        $('#remark-content-transport').show();
        $('#remark_transport').val(tableManagement['remark_transport']);
        $('#remark_transport').prop('required', true);
    } else {
        $('#remark-title-transport').hide();
        $('#remark-content-transport').hide();
        $('#remark_transport').prop('required', false);
    }
}

//新增大計劃維護
function updateProjectBtn() {
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

//重置大計劃維護
function resetProjectBtn() {
    document.getElementById("project-form").reset();
    document.getElementById("project-span").innerHTML = "生產台數 : " + realLotNumber + "台";
    setTransportRemark(tableManagement['is_remark_transport'] === 'Y');
}

//偵測每個月輸入的台數值，並加總顯示出來
function numberListener() {
    var lot_number = 0;
    for (i = 1; i <= 12; i++) {
        lot_number += Number(document.getElementById('month-' + i).value);
    }
    document.getElementById("project-span").innerHTML = "生產台數 : " + lot_number + "台";
    return lot_number;
}

//天數往後加
Date.prototype.addDays = function(days) {
    var date = new Date(this.valueOf());
    date.setDate(date.getDate() + days);
    return date.toISOString().substring(0, 10);
}
</script>

<!-- 新增的內容 -->
<div class="wrapper">
    <div class="content-wrapper">
        <!-- 標題、返回按鈕 -->
        @include('layouts.project.title_backup_button_layout')
        <section id="project-edit-section">
            <form id="project-form" method="POST" action="{{route('ManagementController.updateManagement',[
                    'id'=>$tableData['management']['id'],'type'=>'TOTAL'])}}">
                <!-- 重置、更新按鈕 -->
                @csrf
                @method('PUT')
                @include('layouts.project.reset_update_button_layout')
                <!-- 計畫表單 -->
                <div id="project-div">
                    <table class="table project-table">
                        <tbody>
                            <tr>
                                <td colspan="6"> <span class="with-line" style="--width: 45%;">&ensp;基本資料&ensp;</span>
                                </td>
                            </tr>

                            <tr>
                                <td class="right" style="width:28%"><span style="color:red">&#42;</span> 機種 :</td>
                                <td>
                                    <select style="background:#F5F5F5;color:#272727;" class="select-large"
                                        id="itemIndex" value="{{$tableData['management']['item_code']}}">
                                        <div>
                                            <div>
                                                <option disabled selected value>
                                                    {{$tableData['management']['item_code']}}</option>
                                            </div>
                                        </div>
                                    </select>
                                </td>

                                <td class="right" style="width:36%">永久品番 :</td>
                                <td><input type="text" name="eternal_code" id="eternalCode" class="input-not-used"
                                        readonly="readonly" value="{{$tableData['management']['eternal_code']}}">
                                </td>

                                <td class="right" style="width:36%">在庫品番 :</td>
                                <td> <input type="text" name="stock_code" id="stockCode" class="input-not-used"
                                        readonly="readonly" value="{{$tableData['management']['stock_code']}}">
                                </td>
                            </tr>

                            <tr>
                                <td class="right"><span style="color:red">&#42;</span> Lot No :</td>
                                <td><input min="1" required type="number" name="lot_no" id="lot_no"
                                        class="input-small-not-used" readonly="readonly"
                                        value="{{$tableData['management']['lot_no']}}">
                                </td>
                                <td class="right"><span style="color:red">&#42;</span> Lot總台數 :</td>
                                <td><input min="0" required type="number" name="lot_total" id="lot_total"
                                        class=" input-small" value="{{$tableData['management']['lot_total']}}">
                                </td>
                                <td class="right"><span style="color:red">&#42;</span> 整批/分批 :</td>
                                <td><select required class="select-small" name="batch" id="batch"
                                        value="{{$tableData['management']['batch']}}">
                                        @php
                                        $is_entire_batch = $tableData['management']['batch'] == 'entire_batch';
                                        @endphp
                                        <div>
                                            <div>
                                                <option value="entire_batch" @if($is_entire_batch) selected @endif>整批
                                                </option>
                                                <option value="single_batch" @if(!$is_entire_batch) selected @endif>分批
                                                </option>
                                            </div>
                                        </div>
                                    </select>
                                </td>
                            </tr>

                            <tr>
                                <td class="right"><span style="color:red">&#42;</span> 出荷方式 :</td>
                                <td>
                                    <input type="hidden" value="{{$tableData['management']['transport_id']}}"
                                        id="transportId" name="transport_id">
                                    <select required class="select-middle" id="transport">
                                        <div>
                                            <div>
                                                <option disabled selected value>選擇出荷方式</option>
                                                @foreach($tableData['transport'] as $transport)
                                                @php
                                                $is_selected=($tableData['management']['transport_id']==$transport->id);
                                                @endphp
                                                <option value="{{$transport->id}}" @if($is_selected) selected @endif>
                                                    {{$transport->name}}
                                                </option>
                                                @endforeach
                                            </div>
                                        </div>
                                    </select>
                                </td>

                                <td class="right" id="remark-title-transport" style="display:none"><span style="color:red">&#42;</span>
                                    出荷備註 :
                                </td>
                                <td id="remark-content-transport" style="display:none">
                                    <textarea class="textarea-small" name="remark_transport" id="remark_transport"
                                        placeholder="說明">{{$tableData['management']['remark_transport']}}</textarea>
                                </td>
                                <td class="right" id="remark-title-other">其他備註 :</td>
                                <td>
                                    <textarea class="textarea-small" name="remark_other" id="remark_other"
                                        placeholder="說明">{{$tableData['management']['remark_other']}}</textarea>
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
                                        onblur="(this.type='text')" onfocus="(this.type='date')" class=" input-large"
                                        placeholder="請選擇日期" value="{{$tableData['management']['arrival_date']}}">
                                </td>

                                <td class="right"><span style="color:red">&#42;</span> 出荷預定日 :</td>
                                <td><select required class="select-middle" name="shipment_date" id="shipment_date">
                                        <div>
                                            <div>
                                                <option disabled selected value>選擇出荷預定日</option>
                                                @foreach($tableData['shippingDateArray'] as $shippingDate)
                                                @php
                                                $is_selected = $tableData['management']['shipment_date'] =
                                                $shippingDate;
                                                @endphp
                                                <option value="{{$shippingDate}}" @if($is_selected) selected @endif>
                                                    {{$shippingDate}}</option>
                                                @endforeach
                                            </div>
                                        </div>
                                    </select>
                                </td>
                                <td class="right"> 實際出荷日 :</td>
                                <td><input type="text" name="actual_date" id="actual_date" onblur="(this.type='text')"
                                        onfocus="(this.type='date')" class=" input-large" placeholder="請選擇日期"
                                        value="{{$tableData['management']['actual_date']}}">
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
                                        class=" input-large-not-used"
                                        value="{{$tableData['management']['product_date']}}"></td>
                                <td class="right"><span style="color:red">&#42;</span>
                                    材料納期預定日
                                    <a style="all: unset" title="對應年度生產計劃 部品到著日">
                                        <i class="fa fa-question-circle"></i>
                                    </a> :
                                </td>
                                <td width="300"><input readonly="readonly" type="text" name="material_date"
                                        id="material_date" class=" input-large-not-used"
                                        value="{{$tableData['management']['material_date']}}"></td>
                            </tr>
                        </tbody>
                    </table>

                    <table class="table project-table">
                        <tbody>
                            <tr>
                                <td colspan="8"><span class="with-line" style="--width: 46.5%;">&ensp;詳細&ensp;</span>
                                </td>
                            </tr>
                            <tr>
                                <td colspan="8"><span id="project-span">生產台數 : 0台</span></td>
                            </tr>
                            @foreach(range(0, 2) as $i)
                            <tr>
                                @foreach(range(0, 3) as $j)
                                <td class="right">
                                    <span style="color:red">&#42;</span>
                                    {{$tableData['monthMaps'][$i*4 + $j]['page']}}月 :
                                </td>
                                <td>
                                    <input min="0" type="number" name="monthNumber[]"
                                        id="month-{{$tableData['monthMaps'][$i*4 + $j]['page']}}"
                                        value="{{$tableData['management']['month_'.$tableData['monthMaps'][$i*4 + $j]['page']]}}"
                                        class=" input-month" oninput="numberListener()" required>
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
</div>
@stop