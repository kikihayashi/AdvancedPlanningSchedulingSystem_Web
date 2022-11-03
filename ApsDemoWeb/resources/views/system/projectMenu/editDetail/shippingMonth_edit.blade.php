@extends('layouts.sidebar_outer')
@section('outer_content')

<script>
let tableInputForm = @json($tableData['htmlInputForm']);

document.addEventListener("DOMContentLoaded", function(event) {
    //移除表單
    $(document).on('click', '.removeLot', function() {
        $(this).closest('.inputForm').remove();
    });
});

//新增月度出荷計劃
function saveProjectBtn() {
    //檢查是否輸入重複的製番-出荷方式
    var set = new Set();
    var lotArray = document.getElementsByClassName('checkLot');

    if (lotArray.length == 0) {
        alert('請至少輸入一筆資料！');
        return false;
    }
    for (var i = 0; i < lotArray.length; i++) {
        set.add(lotArray[i].value);
    }
    if (set.size != lotArray.length) {
        alert('錯誤，製番不可重複輸入！');
        return false;
    }
}

//清除月度出荷計劃
function clearProjectBtn() {
    $('.inputForm').remove();
}

//刪除月度出荷計劃
function deleteProjectBtn(id) {
    var canDelete = false;
    var deleteCheckBox = document.getElementsByClassName('deleteCheckBox');
    for (var i = 0; i < deleteCheckBox.length; i++) {
        if (deleteCheckBox[i].checked) {
            canDelete = true;
            break;
        }
    }
    if (canDelete) {
        if (confirm("確定要刪除以下選取項目？")) {
            $('#method-' + id).append('@method("DELETE")');
            var url = "{{route('ShippingMonthController.deleteShippingMonth','ID')}}";
            document.getElementById('formEditProject-' + id).action = url;
            document.getElementById('formEditProject-' + id).submit();
        }
    } else {
        alert('請選取刪除項目！');
    }
}

//重置月度出荷計劃
function resetProjectBtn(id) {
    document.getElementById("formEditProject-" + id).reset();
}

//更新月度出荷計劃
function updateProjectBtn(id) {
    $('#method-' + id).append('@method("PUT")');
    var url = "{{route('ShippingMonthController.updateShippingMonth')}}";
    document.getElementById('formEditProject-' + id).action = url;
    document.getElementById('formEditProject-' + id).submit();
}

//產生表單
function createInputForm(id) {
    $('#' + id).append(tableInputForm);
}
</script>
<!-- 新增的內容 -->
<div class="wrapper">
    <div class="content-wrapper">
        <div style="margin-left:-2%;top:0">
            <!--  資料操作結果的提示視窗 -->
            @include('layouts.prompt_layout')
        </div>
        <!-- 標題、返回按鈕 -->
        <br>
        <a href="{{route('ShippingMonthController.showShippingMonthPage',[
            'period_tw' => $tableData['shippingMonth'][0]['period'], 
            'month' => $tableData['shippingMonth'][0]['month']])}}" class="period-title-a btn btn-secondary">返回</a>
        <span id="title-span">{{$tableData['title']}}</span>
        <div class="line"></div>
        <section>
            <div class="div-nav-tabs">
                <!-- TAB -->
                <ul class="nav nav-tabs">
                    @foreach($tableData['dateArray'] as $dateInfo)
                    <li class="nav-item">
                        <a id="nav-link-a" data-toggle="tab"
                            class="nav-link {{($tableData['selectTab']=='date-'.$dateInfo['date'].'-'.$dateInfo['transport_id'])? 'active':''}}"
                            href="#date-{{$dateInfo['date']}}-{{$dateInfo['transport_id']}}-page">
                            {{str_pad($tableData['shippingMonth'][0]['month'], 2, "0", STR_PAD_LEFT)}}/{{str_pad($dateInfo['date'], 2, "0", STR_PAD_LEFT)}}-({{$dateInfo['abbreviation']}})
                        </a>
                    </li>
                    @endforeach
                </ul>

                <!-- TAB的內容 -->
                <div class="tab-content">
                    @foreach($tableData['dateArray'] as $dateInfo)
                    <div class="tab-pane fade in {{($tableData['selectTab']=='date-'.$dateInfo['date'].'-'.$dateInfo['transport_id'])? 'active':''}}"
                        id="date-{{$dateInfo['date']}}-{{$dateInfo['transport_id']}}-page">
                        <div style="margin-top:2%;" class="div-nav-tabs-sub">
                            <!-- 子TAB -->
                            <ul class="nav nav-tabs">
                                <li class="nav-item">
                                    <a id="nav-link-a-sub" data-toggle="tab" class="nav-link active"
                                        href="#create-{{$dateInfo['date']}}-{{$dateInfo['transport_id']}}-page">
                                        新增
                                    </a>
                                </li>
                                @php
                                $key = $dateInfo['date'].'-'.$dateInfo['transport_id'];
                                @endphp
                                @if(array_key_exists($key, $tableData['info']) && $tableData['info'][$key] > 0)
                                <li class=" nav-item">
                                    <a id="nav-link-a-sub" data-toggle="tab" class="nav-link"
                                        href="#update-{{$dateInfo['date']}}-{{$dateInfo['transport_id']}}-page">
                                        修改
                                    </a>
                                </li>
                                @endif
                            </ul>
                            <!-- 子TAB的內容 -->
                            <div class="tab-content">
                                <!-- 新增的內容 -->
                                <div class="tab-pane fade in active"
                                    id="create-{{$dateInfo['date']}}-{{$dateInfo['transport_id']}}-page">
                                    <section id="project-edit-section" style="padding-top:1.5%;">
                                        <form method="POST" id="project-form"
                                            action="{{route('ShippingMonthController.createShippingMonth','LOT')}}">
                                            <!-- 清除、儲存按鈕 -->
                                            @include('layouts.project.clear_save_button_layout')
                                            <input type="hidden" name="period_tw"
                                                value="{{$tableData['shippingMonth'][0]['period']}}">
                                            <input type="hidden" name="month"
                                                value="{{$tableData['shippingMonth'][0]['month']}}">
                                            <input type="hidden" name="version"
                                                value="{{$tableData['shippingMonth'][0]['version']}}">
                                            <input type="hidden" name="item_code"
                                                value="{{$tableData['shippingMonth'][0]['item_code']}}">
                                            <input type="hidden" name="item_name"
                                                value="{{$tableData['shippingMonth'][0]['item_name']}}">
                                            <input type="hidden" name="date" value="{{$dateInfo['date']}}">
                                            <input type="hidden" name="transport_id"
                                                value="{{$dateInfo['transport_id']}}">
                                            <!-- 計畫表單 -->
                                            <div id="project-form-div">
                                                <div id="newLotRow"></div>
                                                <table class="table project-table" style="margin-bottom:0px;">
                                                    <tbody>
                                                        <tr>
                                                            <td colspan="4"> <span class="with-line"
                                                                    style="--width: 45%;">&ensp;基本資料&ensp;</span></td>
                                                        </tr>
                                                        <tr>
                                                            <td class="right" colspan="1" style="width:30%">
                                                                <strong> 機種 :</strong>
                                                            </td>
                                                            <td colspan="3" style="width:70%;text-align:left">
                                                                <strong>{{$tableData['shippingMonth'][0]['item_code']}}</strong>
                                                            </td>

                                                        </tr>
                                                    </tbody>
                                                </table>

                                                <div id="newInputForm-{{$key}}"></div>

                                                <table class="table project-table" style="margin-bottom:0px;">
                                                    <tbody>
                                                        <tr>
                                                            <td colspan="1" style="width:30%">
                                                            </td>
                                                            <td colspan="3" style="width:70%;text-align:left">
                                                                <button
                                                                    onclick="createInputForm('newInputForm-{{$key}}');"
                                                                    type="button" class="addLotButton">
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

                                @if(array_key_exists($key, $tableData['info']) && $tableData['info'][$key] > 0)
                                <!-- 修改的內容 -->
                                <div class="tab-pane fade in"
                                    id="update-{{$dateInfo['date']}}-{{$dateInfo['transport_id']}}-page">
                                    <section id="project-edit-section" style="padding-top:1.5%;">
                                        <form method="POST"
                                            id="formEditProject-{{$dateInfo['date']}}-{{$dateInfo['transport_id']}}">
                                            <!-- 重置、更新按鈕 -->
                                            @csrf
                                            <div id="method-{{$dateInfo['date']}}-{{$dateInfo['transport_id']}}"></div>
                                            <button type="button" class="btn btn-info" id="project-btn-reset"
                                                onclick="resetProjectBtn('{{$dateInfo['date']}}-{{$dateInfo['transport_id']}}');">回復目前設定</button>
                                            <button type="button" class="btn btn-primary" id="project-btn-update"
                                                onclick="return updateProjectBtn('{{$dateInfo['date']}}-{{$dateInfo['transport_id']}}');">儲存</button>
                                            <button type="button" class="btn btn-danger" id="project-btn-delete"
                                                onclick="deleteProjectBtn('{{$dateInfo['date']}}-{{$dateInfo['transport_id']}}')">刪除</button>
                                            <input type="hidden" value="{{$tableData['shippingMonth'][0]['period']}}"
                                                id="period_tw" name="period_tw">
                                            <input type="hidden" value="{{$tableData['shippingMonth'][0]['month']}}"
                                                id="month" name="month">
                                            <input type="hidden" value="{{$tableData['shippingMonth'][0]['version']}}"
                                                id="version" name="version">
                                            <!-- 計畫表單 -->
                                            <div id="project-form-div">
                                                @foreach($tableData['shippingMonth'] as $shippingMonth)
                                                @if($shippingMonth['date']==$dateInfo['date'] &&
                                                $shippingMonth['transport_id']==$dateInfo['transport_id'] &&
                                                $shippingMonth['lot_no'] > 0)
                                                <table class="table project-table" style="margin-bottom:0px;">
                                                    <tbody>
                                                        <tr>
                                                            <td colspan="4">
                                                                <span class="with-line"
                                                                    style="--width: 45%;">&ensp;基本資料&ensp;</span>
                                                            </td>
                                                        </tr>
                                                        <tr>
                                                            <td class="right" colspan="1" style="width:30%">
                                                                <span style="color:red">&#42;</span> 機種 :
                                                            </td>
                                                            <td colspan="3" style="width:70%">
                                                                <select class="select-large-not-used" id="itemIndex"
                                                                    value="{{$tableData['shippingMonth'][0]['item_code']}}">
                                                                    <div>
                                                                        <div>
                                                                            <option disabled selected value>
                                                                                {{$tableData['shippingMonth'][0]['item_code']}}
                                                                            </option>
                                                                        </div>
                                                                    </div>
                                                                </select>
                                                            </td>
                                                        </tr>
                                                    </tbody>
                                                </table>

                                                <input type="hidden" value="{{$shippingMonth['id']}}" name="updateId[]">
                                                <table class="table project-table" style="margin-bottom:0px;">
                                                    <tbody>
                                                        <tr>
                                                            <td colspan="4">
                                                                <span class="with-line" style="--width: 35%;"></span>
                                                            </td>
                                                        </tr>
                                                        <tr>
                                                            <td style="width:30%">
                                                                <div
                                                                    style="display:flex;float:right;align-items: center;">
                                                                    <input type="checkbox" class="deleteCheckBox"
                                                                        name="deleteId[]"
                                                                        value="{{$shippingMonth['id']}}"
                                                                        style="transform: scale(2);" />
                                                                    &emsp;&emsp;
                                                                    <span style="color:red">&#42;</span>
                                                                    &ensp;Lot No :
                                                                </div>
                                                            </td>
                                                            <td style="width:10%">
                                                                <input readonly min="1" type="number" id="lot_no"
                                                                    class="text ui-widget-content ui-corner-all input-small-not-used"
                                                                    value="{{$shippingMonth['lot_no']}}" />
                                                            </td>
                                                            <td class="right" style="width:21%"><span
                                                                    style="color:red">&#42;</span>&ensp;台數 :
                                                            </td>
                                                            <td style="width:39%">
                                                                <input required min="0" type="number" name="number[]"
                                                                    id="number"
                                                                    class="text ui-widget-content ui-corner-all input-small"
                                                                    value="{{$shippingMonth['number']}}" />
                                                            </td>
                                                        </tr>
                                                        <tr>
                                                            <td class="right" colspan="1" id="remark-title">備註 : </td>
                                                            <td colspan="3">
                                                                <textarea class="textarea-large" name="remark[]"
                                                                    id="remark"
                                                                    placeholder="說明">{{$shippingMonth['remark']}}</textarea>
                                                            </td>
                                                        </tr>
                                                    </tbody>
                                                </table>
                                                @endif
                                                @endforeach
                                            </div>
                                        </form>

                                    </section>
                                </div>
                                @endif
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        </section>
    </div>
</div>
@stop