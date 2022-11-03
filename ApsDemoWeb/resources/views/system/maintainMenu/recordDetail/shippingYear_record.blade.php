@if(count($tableData['shippingYear']) > 0)
<div style="float:left;margin-bottom:15px">
    <button type="button" class="btn btn-primary" id="toggleAllMenu-btn"
        onclick="return toggleAllMenu('{{count($tableData['shippingYear'])}}');">全部展開</button>
</div>
@endif
<table class="table table-bordered record-table">
    <thead style="background-color:#F5F6FB">
        <tr>
            <th colspan="1">操作</th>
            <th colspan="1">異動名稱</th>
            <th colspan="1">版本</th>
            <th colspan="1">機種名稱</th>
            <th colspan="1">Lot & 運輸方式</th>
            <th colspan="1">最後更新時間</th>
        </tr>
    </thead>
    @if(count($tableData['shippingYear']) == 0)
    <tbody class="record-tbody-main">
        <tr>
            <td colspan="6">
                <h1>尚無資料</h1>
            </td>
        </tr>
    </tbody>
    @else
    @foreach(range(0, count($tableData['shippingYear']) - 1) as $index)
    @php
    $shippingYear = $tableData['shippingYear'][$index];
    @endphp
    <tbody class="record-tbody-main">
        <tr>
            <td style="text-align: center;" colspan="1">
                <a id="fa-toggle-{{$index}}" onclick="toggleMenuById('{{$index}}');" class="fa fa-angle-double-down"
                    style="margin-right:10px;margin-left:10px;font-size:24px"></a>
            </td>
            <td style="text-align: center;" colspan="1">年度出荷計畫</td>
            <td style="text-align: center;" colspan="1">{{$shippingYear['version']}}</td>
            <td style="text-align: left;" id="test-itemCode" colspan="1">{{$shippingYear['item_code']}}</td>
            <td style="text-align: left;" colspan="1">#{{$shippingYear['lot_no']}} ({{$shippingYear['transportName']}})</td>
            <td style="text-align: right;" colspan="1">{{$shippingYear['updated_at_tw']}}</td>
        </tr>
    </tbody>
    <tbody id="menus-{{$index}}" class="record-tbody" style="display:none;">
        <tr>
            <td colspan="6">
                <span class="with-line" style="--width: 44%;">&ensp;基本資料&ensp;</span>
            </td>
        </tr>
        <tr>
            <td><span style='font-size:14px;color:#428BCA'>&ensp;|&ensp;</span>台京期數 :</td>
            <td class="right">{{$shippingYear['period']}}</td>
            <td><span style='font-size:14px;color:#428BCA'>&ensp;|&ensp;</span>版本 :</td>
            <td class="right">{{$shippingYear['version']}}</td>
            <td><span style='font-size:14px;color:#428BCA'>&ensp;|&ensp;</span>更新時間 :</td>
            <td class="right">{{date_format(date_create($shippingYear['updated_at']), "Y/m/d H:i:s")}}</td>
        </tr>
        <tr>
            <td><span style='font-size:14px;color:#428BCA'>&ensp;|&ensp;</span>機種 :</td>
            <td class="right">{{$shippingYear['item_code']}}</td>
            <td><span style='font-size:14px;color:#428BCA'>&ensp;|&ensp;</span>Lot No :&emsp;&emsp;&emsp;</td>
            <td class="right">{{$shippingYear['lot_no']}}</td>
            <td><span style='font-size:14px;color:#428BCA'>&ensp;|&ensp;</span>Lot 總台數 :</td>
            <td class="right">{{$shippingYear['lot_total']}}</td>
        </tr>
        <tr>
            <td><span style='font-size:14px;color:#428BCA'>&ensp;|&ensp;</span>出荷方式 :&emsp;&emsp;</td>
            <td class="right">{{$shippingYear['transportName']}}</td>
            <td><span style='font-size:14px;color:#428BCA'>&ensp;|&ensp;</span>備註 :</td>
            <td class="right">{{$shippingYear['remark']}}</td>
            <td></td>
            <td></td>
        </tr>
        <tr>
            <td colspan="6">
                <span class="with-line" style="--width: 38%;">&ensp;年度出荷計畫-詳細資料&ensp;</span>
            </td>
        </tr>
        @foreach(range(0,3) as $i)
        <tr>
            @foreach(range(0,2) as $j)
            <td colspan="2">
                <div class="record-tbody-div">
                    <div>{{$tableData['monthMaps'][$i * 3 + $j]['name']}}</div>
                    @if($shippingYear['month_'.($tableData['monthMaps'][$i * 3 + $j]['page'])] > 0)
                    <div class="right" style="color:#000000;font-weight:bolder;">
                        {{$shippingYear['month_'.($tableData['monthMaps'][$i * 3 + $j]['page'])]}}台
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