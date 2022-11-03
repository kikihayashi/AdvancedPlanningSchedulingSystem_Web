@if(count($tableData['shippingMonth']) > 0)
<div style="float:left;margin-bottom:15px">
    <button type="button" class="btn btn-primary" id="toggleAllMenu-btn"
        onclick="return toggleAllMenu('{{count($tableData['shippingMonth'])}}');">全部展開</button>
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
    @if(count($tableData['shippingMonth']) == 0)
    <tbody class="record-tbody-main">
        <tr>
            <td colspan="6">
                <h1>尚無資料</h1>
            </td>
        </tr>
    </tbody>
    @else
    @foreach(range(0, count($tableData['shippingMonth']) - 1) as $index)
    @php
    $shippingMonth = $tableData['shippingMonth'][$index];
    @endphp
    <tbody class="record-tbody-main">
        <tr>
            <td style="text-align: center;" colspan="1">
                <a id="fa-toggle-{{$index}}" onclick="toggleMenuById('{{$index}}');" class="fa fa-angle-double-down"
                    style="margin-right:10px;margin-left:10px;font-size:24px"></a>
            </td>
            <td style="text-align: center;" colspan="1">月度出荷計畫</td>
            <td style="text-align: center;" colspan="1">{{$shippingMonth['version']}}</td>
            <td style="text-align: left;" id="test-itemCode" colspan="1">{{$shippingMonth['item_code']}}</td>
            <td style="text-align: left;" colspan="1">#{{$shippingMonth['lot_no']}} ({{$shippingMonth['transportName']}})</td>
            <td style="text-align: right;" colspan="1">{{$shippingMonth['updated_at_tw']}}</td>
        </tr>
    </tbody>
    <tbody id="menus-{{$index}}" class="record-tbody" style="display:none;">
        <tr>
            <td colspan="6">
                <span class="with-line" style="--width: 42.8%;">&ensp;出荷計畫日期&ensp;</span>
            </td>
        </tr>
        <tr>
            <td colspan="6">
                @foreach(range(0, count($tableData['dateArray'])-1) as $index)
                @php
                $dayInfo = $tableData['dateArray'][$index];
                @endphp
                <span>
                    ({{$dayInfo['abbreviation']}})：{{$dayInfo['transportName']}}
                </span>
                @if($index < count($tableData['dateArray'])-1) <span style='font-size:14px;color:#428BCA'>
                    &ensp;|&ensp;</span>
                    @endif
                    @endforeach
            </td>
        </tr>
        <tr>
            <td colspan="6">
                @foreach($tableData['dateArray'] as $dayInfo)
                <button class="btn record-td-btn">
                    @php
                    $month = $shippingMonth['month'];
                    $year = $shippingMonth['period'] + (($month > 3)? 1969 : 1970);
                    $date = $dayInfo['date'];
                    @endphp
                    {{$year}}/{{str_pad($month, 2, "0", STR_PAD_LEFT)}}/{{str_pad($date, 2, "0", STR_PAD_LEFT)}}
                    ({{$dayInfo['abbreviation']}})
                </button>
                @endforeach
            </td>
        </tr>
        <tr>
            <td colspan="6">
                <span class="with-line" style="--width: 46.2%;">&ensp;機種&ensp;</span>
            </td>
        </tr>
        <tr>
            <td colspan="6">

                @foreach($tableData['itemCodeArray'] as $itemCodeInfo)
                <button class="btn record-td-btn">
                    {{$itemCodeInfo['item_code']}}
                </button>
                @endforeach
            </td>
        </tr>
        <tr>
            <td colspan="6">
                <span class="with-line" style="--width: 39%;">&ensp;月度出荷計畫-詳細資料&ensp;</span>
            </td>
        </tr>
        <tr>
            <td colspan="6">
                <table class="record-subTable">
                    <thead>
                        <tr>
                            <th>{{$shippingMonth['item_code']}}</th>
                            <th>
                                @php
                                $month = $shippingMonth['month'];
                                $year = $shippingMonth['period'] + (($month > 3)? 1969 : 1970);
                                $date = $dayInfo['date'];
                                @endphp
                                {{$year}}/{{str_pad($month, 2, "0", STR_PAD_LEFT)}}/{{str_pad($date, 2, "0", STR_PAD_LEFT)}}
                                ({{$shippingMonth['abbreviation']}})
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>#{{$shippingMonth['lot_no']}}</td>
                            <td>{{$shippingMonth['number']}}台</td>
                        </tr>
                        <tr>
                            <td>備註：{{$shippingMonth['remark']}}</td>
                            <td>{{$shippingMonth['remark']}}</td>
                        </tr>
                    </tbody>
                </table>
            </td>
        </tr>
    </tbody>
    @endforeach
    @endif
</table>