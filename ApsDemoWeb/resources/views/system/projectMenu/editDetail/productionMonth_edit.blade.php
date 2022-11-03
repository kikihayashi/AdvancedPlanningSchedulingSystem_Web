@extends('layouts.sidebar_outer')
@section('outer_content')

<script>
let tableProductionMonth = @json($tableData['productionMonth']);
let thisTimeDays = @json($tableData['thisTimeDays']);

document.addEventListener("DOMContentLoaded", function(event) {
    //初始化使用者當初選擇的生產日期
    initSelectDay();
});

//重置月度生產計畫
function resetProjectBtn() {
    document.getElementById("project-form").reset();
    initSelectDay();
}

//初始化使用者當初選擇的生產日期
function initSelectDay() {
    var start = tableProductionMonth['start_day_array'].split(',').map(str => Number(str)); //2, 10, 18, 25, 28
    var end = tableProductionMonth['end_day_array'].split(',').map(str => Number(str)); //2, 15, 21, 25, 28
    var index = 0;
    var lastIndex = start.length - 1;
    //執行一整個月，選擇區間就顯示藍色，非選擇區間就顯示白色
    for (day = 1; day <= thisTimeDays; day++) {
        if (index <= lastIndex) {
            if (day == start[index]) {
                for (now = start[index]; now <= end[index]; now++) {
                    $('#grid-' + now).css('background-color', '#B3E5FC');
                    $('#checkbox-' + now).prop('checked', true);
                }
                day = end[index];
                index++;
            } else {
                $('#grid-' + day).css('background-color', '#FFFFFF');
                $('#checkbox-' + day).prop('checked', false);
            }
        } else {
            $('#grid-' + day).css('background-color', '#FFFFFF');
            $('#checkbox-' + day).prop('checked', false);
        }
    }
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
</script>
@php
$days = $tableData['thisTimeDays'];
$month = $tableData['productionMonth']['month'];
$year = $tableData['productionMonth']['period'] + (($month < 4) ? 1970 : 1969); @endphp <!-- 新增的內容 -->
    <div class="wrapper">
        <div class="content-wrapper">
            <!-- 標題、返回按鈕 -->
            @include('layouts.project.title_backup_button_layout')
            <section id="project-edit-section">
                <form method="POST" id="project-form"
                    action="{{route('ProductionMonthController.updateProductionMonth', $tableData['productionMonth']['id'])}}">
                    <!-- 重置、更新按鈕 -->
                    @csrf
                    @method('PUT')
                    @include('layouts.project.reset_update_button_layout')
                    <!-- 計畫表單 -->
                    <div id="project-div">
                        <table class="table project-table">
                            <tbody>
                                <tr>
                                    <td colspan="3"><span class="with-line" style="--width: 45%;">&ensp;基本資料&ensp;</span></td>
                                </tr>
                                <tr>
                                    <td class="right" style="width:25%"><span style="color:red">&#42;</span> 機種 :</td>
                                    <td style="width:30%">
                                        <select style="background:#F5F5F5;color:#272727;" class="select-large"
                                            id="itemIndex" value="{{$tableData['productionMonth']['item_code']}}">
                                            <div>
                                                <div>
                                                    <option disabled selected value>
                                                        {{$tableData['productionMonth']['item_code']}}</option>
                                                </div>
                                            </div>
                                        </select>
                                    </td>
                                    <td class="right" style="width:45%;text-align:left;font-size:20px">
                                        生產日期：{{$year}}年{{$month}}月
                                    </td>
                                </tr>
                                <tr>
                                    <td class="right"><span style="color:red">&#42;</span> Lot No :</td>
                                    <td><input min="1" required type="number" name="lot_no" id="lot_no"
                                            class="text ui-widget-content ui-corner-all input-small-not-used"
                                            readonly="readonly" value="{{$tableData['productionMonth']['lot_no']}}">
                                    </td>
                                    <td rowspan="3">
                                        <div class="grid-container">
                                            @foreach(range(1, $days) as $day)
                                            <input type="checkbox" style="display:none" id="checkbox-{{$day}}"
                                                value="{{$day}}" name="productionDay[]">
                                            <div class="grid-item" id="grid-{{$day}}" onclick="selectDay('{{$day}}');">
                                                {{$day}}
                                            </div>
                                            @endforeach
                                        </div>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="right" ><span style="color:red">&#42;</span> 前月迄完成數 :</td>
                                    <td><input min="0" required type="number" name="previousMonthNumber"
                                            id="previousMonthNumber"
                                            value="{{$tableData['productionMonth']['previous_month_number']}}"
                                            class="text ui-widget-content ui-corner-all input-small">
                                    </td>
                                </tr>
                                <tr>
                                    <td class="right" ><span style="color:red">&#42;</span> 本月計劃生產台數:</td>
                                    <td><input min="0" required type="number" name="thisMonthNumber"
                                            id="thisMonthNumber"
                                            value="{{$tableData['productionMonth']['this_month_number']}}"
                                            class="text ui-widget-content ui-corner-all input-small">
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </form>
            </section>
        </div>
    </div>
    @stop