@if(count($tableData['productionYear']) > 0)
<div style="float:left;margin-bottom:15px">
    <button type="button" class="btn btn-primary" id="toggleAllMenu-btn"
        onclick="return toggleAllMenu('{{count($tableData['productionYear'])}}');">全部展開</button>
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
    @if(count($tableData['productionYear']) == 0)
    <tbody class="record-tbody-main">
        <tr>
            <td colspan="6">
                <h1>尚無資料</h1>
            </td>
        </tr>
    </tbody>
    @else
    @foreach(range(0, count($tableData['productionYear']) - 1) as $index)
    @php
    $productionYear = $tableData['productionYear'][$index];
    @endphp
    <tbody class="record-tbody-main">
        <tr>
            <td style="text-align: center;" colspan="1">
                <a id="fa-toggle-{{$index}}" onclick="toggleMenuById('{{$index}}');" class="fa fa-angle-double-down"
                    style="margin-right:10px;margin-left:10px;font-size:24px"></a>
            </td>
            <td style="text-align: center;" colspan="1">年度生產計畫</td>
            <td style="text-align: center;" colspan="1">{{$productionYear['version']}}</td>
            <td style="text-align: left;" id="test-itemCode" colspan="1">{{$productionYear['item_code']}}</td>
            <td style="text-align: center;" colspan="1">#{{$productionYear['lot_no']}}</td>
            <td style="text-align: right;" colspan="1">{{$productionYear['updated_at_tw']}}</td>
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
            <td class="right">{{$productionYear['period']}}</td>
            <td><span style='font-size:14px;color:#428BCA'>&ensp;|&ensp;</span>版本 :</td>
            <td class="right">{{$productionYear['version']}}</td>
            <td><span style='font-size:14px;color:#428BCA'>&ensp;|&ensp;</span>更新時間 :</td>
            <td class="right">{{date_format(date_create($productionYear['updated_at']), "Y/m/d H:i:s")}}</td>
        </tr>
        <tr>
            <td><span style='font-size:14px;color:#428BCA'>&ensp;|&ensp;</span>機種 :</td>
            <td class="right">{{$productionYear['item_code']}}</td>
            <td><span style='font-size:14px;color:#428BCA'>&ensp;|&ensp;</span>Lot No :</td>
            <td class="right">{{$productionYear['lot_no']}}</td>
            <td><span style='font-size:14px;color:#428BCA'>&ensp;|&ensp;</span>Lot 總台數 :</td>
            <td class="right">{{$productionYear['lot_total']}}</td>
        </tr>
        <tr>
            <td><span style='font-size:14px;color:#428BCA'>&ensp;|&ensp;</span>線別 :</td>
            <td class="right">{{($productionYear['line_no']==0)?'無線別':'Line '.$productionYear['line_no']}}</td>
            <td><span style='font-size:14px;color:#428BCA'>&ensp;|&ensp;</span>組立開始日 :</td>
            <td class="right">{{$productionYear['product_date']}}</td>
            <td><span style='font-size:14px;color:#428BCA'>&ensp;|&ensp;</span>納期 :</td>
            <td class="right">{{$productionYear['deadline']}}</td>

        </tr>
        <tr>
            <td><span style='font-size:14px;color:#428BCA'>&ensp;|&ensp;</span>ORDER NO : </td>
            <td class="right">{!!nl2br($productionYear['order_no'])!!}</td>
            <td><span style='font-size:14px;color:#428BCA'>&ensp;|&ensp;</span>部品到著日 :</td>
            <td class="right">{{$productionYear['material_date']}}</td>
            <td><span style='font-size:14px;color:#428BCA'>&ensp;|&ensp;</span>備註 :</td>
            <td class="right">{{$productionYear['remark']}}</td>
        </tr>
        <tr>
            <td colspan="6">
                <span class="with-line" style="--width: 38%;">&ensp;年度生產計畫-詳細資料&ensp;</span>
            </td>
        </tr>
        @foreach(range(0,3) as $i)
        <tr>
            @foreach(range(0,2) as $j)
            <td>
                <div class="record-tbody-div">
                    <div>{{$tableData['monthMaps'][$i * 3 + $j]['name']}}</div>
                    @if($productionYear['month_'.($tableData['monthMaps'][$i * 3 + $j]['page'])] > 0)
                    <div class="right" style="color:#000000;font-weight:bolder;">
                        {{$productionYear['month_'.($tableData['monthMaps'][$i * 3 + $j]['page'])]}}台
                    </div>
                    @else
                    <div class="right">
                        0台
                    </div>
                    @endif
                </div>
            </td>
            <td>
                @if($productionYear['month_'.($tableData['monthMaps'][$i * 3 + $j]['page'])] > 0)
                <div style="vertical-align: middle;padding: 5px;">
                    <button class="btn record-td-btn">
                        @php
                        $month = $tableData['monthMaps'][$i * 3 + $j]['page'];
                        $year = $productionYear['period'] + (($month > 3)? 1969 : 1970);
                        $start = explode('-', $productionYear['range_'.$month])[0];
                        $end = explode('-', $productionYear['range_'.$month])[1];
                        @endphp
                        {{$year}}/{{str_pad($month, 2, "0", STR_PAD_LEFT)}}/{{str_pad($start, 2, "0", STR_PAD_LEFT)}} ~
                        {{str_pad($end, 2, "0", STR_PAD_LEFT)}}
                    </button>
                </div>
                @endif
            </td>
            @endforeach
        </tr>
        @endforeach
    </tbody>
    @endforeach
    @endif
</table>