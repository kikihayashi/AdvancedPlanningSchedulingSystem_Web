@extends('layouts.sidebar_outer')
@section('outer_content')

<script>
let realLotNumber = 0;
let tableProductionYear = @json($tableData['productionYear']);

document.addEventListener("DOMContentLoaded", function(event) {
    //初始設定內藏備註的顯示、隱藏
    setHiddenRemark(tableProductionYear['is_hidden'] === 'Y');
    //因為是編輯介面，所以初始化時先獲取實際總台數
    realLotNumber = numberListener();
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
function updateProjectBtn() {
    var lot_total = Number(document.getElementById('lot_total').value);
    var lot_number = 0;
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

//重置年度生產計劃
function resetProjectBtn() {
    document.getElementById("project-form").reset();
    document.getElementById("project-span").innerHTML = "生產台數 : " + realLotNumber + "台";
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

<!-- 新增的內容 -->
<div class="wrapper">
    <div class="content-wrapper">
        <!-- 標題、返回按鈕 -->
        @include('layouts.project.title_backup_button_layout')
        <section id="project-edit-section">
            <form method="POST" id="project-form"
                action="{{route('ProductionYearController.updateProductionYear', $tableData['productionYear']['id'])}}">
                <!-- 重置、更新按鈕 -->
                @csrf
                @method('PUT')
                @include('layouts.project.reset_update_button_layout')
                <!-- 計畫表單 -->
                <div id="project-div">
                    <table class="table project-table">
                        <tbody>
                            <tr>
                                <td colspan="4"><span class="with-line" style="--width: 45%;">&ensp;基本資料&ensp;</span>
                                </td>
                            </tr>
                            <tr>
                                <td class="right" style="width:20%"><span style="color:red">&#42;</span> 機種 :</td>
                                <td style="width:10%">
                                    <select class="select-large-not-used" id="itemIndex"
                                        value="{{$tableData['productionYear']['item_code']}}">
                                        <div>
                                            <div>
                                                <option disabled selected value>
                                                    {{$tableData['productionYear']['item_code']}}</option>
                                            </div>
                                        </div>
                                    </select>
                                </td>
                            </tr>
                            <tr>
                                <td class="right" ><span style="color:red">&#42;</span> Lot No :</td>
                                <td><input min="1" required type="number" name="lot_no" id="lot_no"
                                        class="input-small-not-used"
                                        readonly="readonly" value="{{$tableData['productionYear']['lot_no']}}"></td>
                                <td class="right" style="width:10%"><span style="color:red">&#42;</span>
                                    組立開始 :
                                </td>
                                <td style="width:20%">
                                    <input required type="text" name="product_date" id="product_date"
                                        onblur="(this.type='text')" onfocus="(this.type='date')"
                                        class="input-large" placeholder="請選擇日期"
                                        value="{{$tableData['productionYear']['product_date']}}">
                                </td>
                            </tr>
                            <tr>
                                <td class="right" ><span style="color:red">&#42;</span> Lot總台數 :</td>
                                <td><input min="0" required type="number" name="lot_total" id="lot_total"
                                        class="input-small"
                                        value="{{$tableData['productionYear']['lot_total']}}"></td>
                                <td class="right" ><span style="color:red">&#42;</span>
                                    部品到著 :
                                </td>
                                <td width="30%">
                                    <input required type="text" name="material_date" id="material_date"
                                        onblur="(this.type='text')" onfocus="(this.type='date')"
                                        class="input-large" placeholder="請選擇日期"
                                        value="{{$tableData['productionYear']['material_date']}}">
                                </td>
                            </tr>
                            <tr>
                                <td class="right" id="remark-title-other">備註 :</td>
                                <td>
                                    <textarea class="textarea-small" name="remark" id="remark"
                                        placeholder="">{{$tableData['productionYear']['remark']}}</textarea>
                                </td>
                                <td class="right" id="remark-title-other">納期 :</td>
                                <td>
                                    <textarea class="textarea-small" name="deadline" id="deadline"
                                        placeholder="">{{$tableData['productionYear']['deadline']}}</textarea>
                                </td>
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
                                <td colspan="1"></td>
                                <td colspan="2"><span id="project-span"><strong>生產台數 : 0台</strong></span></td>
                                <td colspan="3" style="text-align:left"><span><strong>生產區間</strong></span></td>
                                <td colspan="2" style="text-align:left"><span
                                        id="remark-title-hidden" style="display:none"><strong>內藏備註</strong></span></td>
                            </tr>

                            @foreach($tableData['monthMaps'] as $monthMap)
                            <tr>
                                <td class="right" style="width:25%">{{$monthMap['page']}}月 :</td>
                                <td style="width:5%"><input min="0" type="number" name="monthNumber[]"
                                        id="month-{{$monthMap['page']}}"
                                        class="input-month"
                                        oninput="numberListener()"
                                        value="{{$tableData['productionYear']['month_'.$monthMap['page']]}}">
                                </td>
                                <td style="width:10%;text-align:left">台</td>

                                <td style="width:5%"><input min="1" max="{{$monthMap['totalDay']}}" type="number"
                                        name="rangeStart[]" id="range-start-{{$monthMap['page']}}"
                                        onchange="rangeListener('{{$monthMap['page']}}','start', '{{$monthMap['totalDay']}}')"
                                        class="input-month"
                                        value="{{$tableData['productionYear']['rangeStart'][$monthMap['page']]}}">
                                </td>
                                <td style="width:2%"> ~ </td>
                                <td style="width:13%"><input min="1" max="{{$monthMap['totalDay']}}" type="number"
                                        name="rangeEnd[]" id="range-end-{{$monthMap['page']}}"
                                        onchange="rangeListener('{{$monthMap['page']}}', 'end', '{{$monthMap['totalDay']}}')"
                                        class="input-month"
                                        value="{{$tableData['productionYear']['rangeEnd'][$monthMap['page']]}}">
                                </td>
                                <td style="width:40%">
                                    <textarea class="textarea-small" name="remarkHidden[]"
                                        id="remarkHidden-{{$monthMap['page']}}" style="display:none"
                                        placeholder="">{{$tableData['productionYear']['remarkHidden'][$monthMap['page']]}}</textarea>
                                </td>
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