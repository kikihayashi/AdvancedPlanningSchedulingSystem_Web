@if(count($tableData['management']) > 0)
<div style="float:left;margin-bottom:15px">
    <button type="button" class="btn btn-primary" id="toggleAllMenu-btn"
        onclick="return toggleAllMenu('{{count($tableData['management'])}}');">全部展開</button>
</div>
@endif

<table class="table table-bordered record-table">
    <thead style="background-color:#F5F6FB">
        <tr>
            <th colspan="1">操作</th>
            <th colspan="1">異動名稱</th>
            <th colspan="1">版本</th>
            <th colspan="1">機種名稱</th>
            <th colspan="1">Lot</th>
            <th colspan="1">最後更新時間</th>
        </tr>
    </thead>
    @if(count($tableData['management']) == 0)
    <tbody class="record-tbody-main">
        <tr>
            <td colspan="6">
                <h1>尚無資料</h1>
            </td>
        </tr>
    </tbody>
    @else
    @foreach(range(0, count($tableData['management']) - 1) as $index)
    @php
    $management = $tableData['management'][$index];
    @endphp
    <tbody class="record-tbody-main">
        <tr>
            <td style="text-align: center;" colspan="1">
                <a id="fa-toggle-{{$index}}" onclick="toggleMenuById('{{$index}}');" class="fa fa-angle-double-down"
                    style="margin-right:10px;margin-left:10px;font-size:24px"></a>
            </td>
            <td style="text-align: center;" colspan="1">大計畫規劃管理</td>
            <td style="text-align: center;" colspan="1">{{$management['version']}}</td>
            <td style="text-align: left;" id="test-itemCode" colspan="1">{{$management['item_code']}}</td>
            <td style="text-align: center;" colspan="1">#{{$management['lot_no']}}</td>
            <td style="text-align: right;" colspan="1">
                {{$management['updated_at_tw']}}</td>
        </tr>
    </tbody>
    <tbody id="menus-{{$index}}" class="record-tbody" style="display:none;">
        <tr>
            <td colspan="6">
                <span class="with-line" style="--width: 45%;">&ensp;基本資料&ensp;</span>
            </td>
        </tr>
        <tr>
            <td><span style='font-size:14px;color:#428BCA'>&ensp;|&ensp;</span>台京期數 :</td>
            <td class="right">{{$management['period']}}</td>
            <td><span style='font-size:14px;color:#428BCA'>&ensp;|&ensp;</span>版本 :</td>
            <td class="right">{{$management['version']}}</td>
            <td><span style='font-size:14px;color:#428BCA'>&ensp;|&ensp;</span>更新時間 :</td>
            <td class="right">{{date_format(date_create($management['updated_at']), "Y/m/d H:i:s")}}</td>
        </tr>
        <tr>
            <td><span style='font-size:14px;color:#428BCA'>&ensp;|&ensp;</span>機種 :</td>
            <td class="right">{{$management['item_code']}}</td>
            <td><span style='font-size:14px;color:#428BCA'>&ensp;|&ensp;</span>Lot No :</td>
            <td class="right">{{$management['lot_no']}}</td>
            <td><span style='font-size:14px;color:#428BCA'>&ensp;|&ensp;</span>Lot 總台數 :</td>
            <td class="right">{{$management['lot_total']}}</td>
        </tr>
        <tr>
            <td><span style='font-size:14px;color:#428BCA'>&ensp;|&ensp;</span>生產預計日 :</td>
            <td class="right">{{$management['product_date']}}</td>
            <td><span style='font-size:14px;color:#428BCA'>&ensp;|&ensp;</span>出荷預定日 :</td>
            <td class="right">{{$management['shipment_date']}}</td>
            <td><span style='font-size:14px;color:#428BCA'>&ensp;|&ensp;</span>實際出荷日 :</td>
            <td class="right">{{$management['actual_date']}}</td>
        </tr>
        <tr>
            <td><span style='font-size:14px;color:#428BCA'>&ensp;|&ensp;</span>部品到著預定日 :</td>
            <td class="right">{{$management['arrival_date']}}</td>
            <td><span style='font-size:14px;color:#428BCA'>&ensp;|&ensp;</span>材料納期預定日 :</td>
            <td class="right">{{$management['material_date']}}</td>
            <td><span style='font-size:14px;color:#428BCA'>&ensp;|&ensp;</span>其他備註 :</td>
            <td class="right">{{$management['remark_other']}}</td>
        </tr>
        <tr>
            <td><span style='font-size:14px;color:#428BCA'>&ensp;|&ensp;</span>出荷方式 :</td>
            <td class="right">{{$management['transportName']}}</td>
            <td><span style='font-size:14px;color:#428BCA'>&ensp;|&ensp;</span>整批/分批 :</td>
            <td class="right">{{($management['batch']=='entire_batch')?'整批':'分批'}}</td>
            <td><span style='font-size:14px;color:#428BCA'>&ensp;|&ensp;</span>出荷備註 :</td>
            <td class="right">{{$management['remark_transport']}}</td>
        </tr>
        <tr>
            <td colspan="6">
                <span class="with-line" style="--width: 42%;">&ensp;大計畫-詳細資料&ensp;</span>
            </td>
        </tr>
        @foreach(range(0,3) as $i)
        <tr>
            @foreach(range(0,2) as $j)
            <td colspan="2">
                <div class="record-tbody-div">
                    <div>{{$tableData['monthMaps'][$i * 3 + $j]['name']}}</div>
                    @if($management['month_'.($tableData['monthMaps'][$i * 3 + $j]['page'])] > 0)
                    <div class="right" style="color:#000000;font-weight:bolder;">
                        {{$management['month_'.($tableData['monthMaps'][$i * 3 + $j]['page'])]}}台
                    </div>
                    @else
                    <div class="right">
                        0台
                    </div>
                    @endif
                </div>
            </td>
            @endforeach
        </tr>
        @endforeach
    </tbody>
    @endforeach
    @endif
</table>