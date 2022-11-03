@if(count($tableData['productionMonth']) > 0)
<div style="float:left;margin-bottom:15px">
    <button type="button" class="btn btn-primary" id="toggleAllMenu-btn"
        onclick="return toggleAllMenu('{{count($tableData['productionMonth'])}}');">全部展開</button>
</div>
@endif

<table class="table table-bordered record-productionMonth-table">
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
    @if(count($tableData['productionMonth']) == 0)
    <tbody class="record-tbody-main">
        <tr>
            <td colspan="6">
                <h1>尚無資料</h1>
            </td>
        </tr>
    </tbody>
    @else
    @foreach(range(0, count($tableData['productionMonth']) - 1) as $index)
    @php
    $productionMonth = $tableData['productionMonth'][$index];
    @endphp
    <tbody class="record-tbody-main">
        <tr>
            <td style="text-align: center;" colspan="1">
                <a id="fa-toggle-{{$index}}" onclick="toggleMenuById('{{$index}}');" class="fa fa-angle-double-down"
                    style="margin-right:10px;margin-left:10px;font-size:24px"></a>
            </td>
            <td style="text-align: center;" colspan="1">月度生產計畫</td>
            <td style="text-align: center;" colspan="1">{{$productionMonth['version']}}</td>
            <td style="text-align: left;" id="test-itemCode" colspan="1">{{$productionMonth['item_code']}}</td>
            <td style="text-align: center;" colspan="1">#{{$productionMonth['lot_no']}}</td>
            <td style="text-align: right;" colspan="1">{{$productionMonth['updated_at_tw']}}</td>
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
            <td class="right">{{$productionMonth['period']}}</td>
            <td><span style='font-size:14px;color:#428BCA'>&ensp;|&ensp;</span>月份 :</td>
            <td class="right">{{$productionMonth['month']}}月</td>
            <td><span style='font-size:14px;color:#428BCA'>&ensp;|&ensp;</span>更新時間 :</td>
            <td class="right">{{$productionMonth['updated_at']}}</td>
        </tr>
        <tr>
            <td><span style='font-size:14px;color:#428BCA'>&ensp;|&ensp;</span>版本 :</td>
            <td class="right">{{$productionMonth['version']}}</td>
            <td><span style='font-size:14px;color:#428BCA'>&ensp;|&ensp;</span>機種 :</td>
            <td class="right">{{$productionMonth['item_code']}}</td>
            <td><span style='font-size:14px;color:#428BCA'>&ensp;|&ensp;</span>Lot No :</td>
            <td class="right">{{$productionMonth['lot_no']}}</td>
        </tr>
        <tr>
            <td><span style='font-size:14px;color:#428BCA'>&ensp;|&ensp;</span>線別 :</td>
            <td class="right">{{($productionMonth['line_no']==0)?'無線別':'Line '.$productionMonth['line_no']}}</td>
            <td><span style='font-size:14px;color:#428BCA'>&ensp;|&ensp;</span>前月迄完成數 :</td>
            <td class="right">{{$productionMonth['previous_month_number']}}台</td>
            <td><span style='font-size:14px;color:#428BCA'>&ensp;|&ensp;</span>本月計畫生產台數 :</td>
            <td class="right">{{$productionMonth['this_month_number']}}台</td>
        </tr>
        <tr>
            <td colspan="6">
                <span class="with-line" style="--width: 39%;">&ensp;月度生產計畫-詳細資料&ensp;</span>
            </td>
        </tr>
        <tr>
            <td colspan="6">
                生產日期 ：
                @foreach(range(0, count($productionMonth['start'])-1) as $i)
                @php
                $month = $productionMonth['month'];
                $year = $productionMonth['period'] + (($month > 3)? 1969 : 1970);
                $start = $productionMonth['start'][$i];
                $end = $productionMonth['end'][$i];
                @endphp
                <button class="btn record-td-btn">
                    @if($start == $end)
                    {{$year}}/{{str_pad($month, 2, "0", STR_PAD_LEFT)}}/{{str_pad($start, 2, "0", STR_PAD_LEFT)}}
                    @else
                    {{$year}}/{{str_pad($month, 2, "0", STR_PAD_LEFT)}}/{{str_pad($start, 2, "0", STR_PAD_LEFT)}} ~
                    {{str_pad($end, 2, "0", STR_PAD_LEFT)}}
                    @endif
                </button>
                @endforeach
            </td>
        </tr>
    </tbody>
    @endforeach
    @endif
</table>