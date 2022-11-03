@extends('layouts.sidebar_outer')
@section('outer_content')
<script>
//重置年度出荷計劃
function resetProjectBtn() {
    document.getElementById("project-form").reset();
}

//更新年度出荷計劃
function updateProjectBtn() {
    $('#method').append('@method("PUT")');
    var url = "{{route('ShippingYearController.updateShippingYear', 'MULTIPLE')}}";
    document.getElementById('project-form').action = url;
}

//刪除年度出荷計劃
function deleteProjectBtn() {
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
            $('#method').append('@method("DELETE")');
            var url = "{{route('ShippingYearController.deleteShippingYear', 'MULTIPLE')}}";
            document.getElementById('project-form').action = url;
            document.getElementById('project-form').submit();
        }
    } else {
        alert('請選取刪除項目！');
    }
}
</script>
<!-- 新增的內容 -->
<div class="wrapper">
    <div class="content-wrapper">
        <!-- 標題、返回按鈕 -->
        @include('layouts.project.title_backup_button_layout')
        <section id="project-edit-section">
            <form id="project-form" method="POST">
                <!-- 重置、更新按鈕 -->
                @csrf
                <div id="method"></div>
                @include('layouts.project.reset_update_button_layout')
                <button type="button" class="btn btn-danger" id="project-btn-delete"
                    onclick="deleteProjectBtn()">刪除</button>
                <!-- 計畫表單 -->
                <div id="project-form-div">
                    <table class="table project-table" style="margin-bottom:0px;">
                        <tbody>
                            <tr>
                                <td colspan="4"> <span class="with-line" style="--width: 45%;">&ensp;基本資料&ensp;</span></td>
                            </tr>
                            <tr>
                                <td class="right" colspan="1" style="width:30%">
                                    <span style="color:red">&#42;</span> 機種 :
                                </td>
                                <td colspan="3" style="width:70%">
                                    <input type="hidden" value="{{$tableData['shippingYear'][0]['period']}}"
                                        id="period_tw" name="period_tw">
                                    <input type="hidden" value="{{$tableData['shippingYear'][0]['version']}}"
                                        id="version" name="version">
                                    <input type="hidden" value="{{$tableData['shippingYear'][0]['item_code']}}"
                                        id="itemCode" name="item_code">
                                    <input type="hidden" value="{{$tableData['shippingYear'][0]['item_name']}}"
                                        id="itemName" name="item_name">
                                    <input type="hidden" name="type" value="Multiple">
                                    <select class="select-large-not-used" id="itemIndex"
                                        value="{{$tableData['shippingYear'][0]['item_code']}}">
                                        <div>
                                            <div>
                                                <option disabled selected value>
                                                    {{$tableData['shippingYear'][0]['item_code']}}</option>
                                            </div>
                                        </div>
                                    </select>
                                </td>
                            </tr>
                        </tbody>
                    </table>

                    @foreach($tableData['shippingYear'] as $shippingYear)
                    <table class="table project-table" style="margin-bottom:0px;">
                        <tbody>
                            <tr>
                                <td colspan="4"><span class="with-line-short"></span></td>
                            </tr>
                            <tr>
                                <td style="width:30%">
                                    <div style="display:flex;float:right;align-items: center;">
                                        <input type="checkbox" class="deleteCheckBox" name="deleteId[]"
                                            value="{{$shippingYear['id']}}" style="transform: scale(2);" />
                                        &emsp;&emsp;<span style="color:red">&#42;</span>&ensp;Lot No :
                                    </div>
                                </td>
                                <td style="width:18%">
                                    <input readonly min="1" type="number" name="lot_no[]" id="lot_no"
                                        class="input-small-not-used"
                                        value="{{$shippingYear['lot_no']}}" />
                                </td>
                                <td class="right" style="width:9%"><span style="color:red">&#42;</span>&ensp;Lot總台數 :</td>
                                <td style="width:43%">
                                    <input required min="0" type="number" name="lot_total[]" id="lot_total"
                                        class="input-small"
                                        value="{{$shippingYear['lot_total']}}" />
                                </td>
                            </tr>
                            <tr>
                                <td class="right"><span style="color:red">&#42;</span>&ensp;出荷方式 :</td>
                                <td>
                                    <input type="hidden" value="{{$shippingYear['transport_id']}}" id="transportId"
                                        name="transport_id[]">
                                    <select class="select-middle-not-used" id="transport">
                                        <div>
                                            <div>
                                                <option disabled selected value>
                                                    {{$shippingYear['transport_name']}}</option>
                                            </div>
                                        </div>
                                    </select>
                                </td>
                            </tr>
                            <tr>
                                <td class="right" colspan="1" id="remark-title">備註 : </td>
                                <td colspan="3">
                                    <textarea class="textarea-large" name="remark[]" id="remark"
                                        placeholder="說明">{{$shippingYear['remark']}}</textarea>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                    @endforeach
                    <div id="newLotRow"></div>
                </div>
            </form>
        </section>
    </div>
</div>
@stop