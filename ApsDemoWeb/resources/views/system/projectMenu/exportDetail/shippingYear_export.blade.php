<table>
    <thead>
        <tr>
            <th>
                {{$tableData['title']}}
            </th>
            <th></th>
            <th></th>
            <th></th>
            <th></th>
            <th></th>
            <th></th>
            <th></th>
            <th></th>
            <th></th>
            <th></th>
            <th></th>
            <!-- 簽章 -->
            <th>{{($tableData['yearType']=='first')?'作 成':''}}</th>
            <th></th>
            <th>{{($tableData['yearType']=='first')?'審 查':''}}</th>
            <th></th>
            <th>{{($tableData['yearType']=='first')?'審 查':''}}</th>
            <th></th>
            <th>{{($tableData['yearType']=='first')?'客 戶 承 認':''}}</th>
            <th></th>
        </tr>
        <tr>
            <th></th>
            <th></th>
            <th></th>
            <th></th>
            <th></th>
            <th></th>
            <th></th>
            <th></th>
            <th></th>
            <th></th>
            <th></th>
            <th></th>
            <th></th>
            <th></th>
            <th></th>
            <th></th>
            <th></th>
            <th></th>
            <th></th>
            <th></th>
        </tr>
        <tr>
            <th>{{($tableData['yearType']=='first')?'P1/2':'P2/2'}}</th>
            <th>{{$tableData['period_tw'] + 1969}}. {{($tableData['yearType']=='first')?'4':'10'}}. 1 賣價、レ‐ト、貸率修正 (
                1:27.78:114 )</th>
            <th></th>
            <th></th>
            <th></th>
            <th></th>
            <th>匯率:</th>
            <th>{{number_format($tableData['exchange'][$tableData['yearType']], 4)}}</th>
            <th>¥ 0 / H</th>
            <th></th>
            @php
            $status = '';
            switch($tableData['progress']['progress_point']) {
            case 0:
            $status='正常';
            break;
            case 1:
            $status='未審核';
            break;
            case 2:
            $status='審核中';
            break;
            case 3:
            $status='審核中';
            break;
            case 4:
            $status='審核中';
            break;
            }
            @endphp
            <th>狀態：{{$status}} 版次:{{$tableData['progress']['version']}}</th>
            <th></th>
            <th></th>
            <th></th>
            <th></th>
            <th></th>
            <th></th>
            <th></th>
            <th></th>
            <th></th>
        </tr>
        <tr>
            <th>機&emsp;&emsp;種</th>
            <th></th>
            <th>結算工數</th>
            <th>賣價￥</th>
            @if($tableData['yearType'] == 'first')
            <th>4月</th>
            <th></th>
            <th>5月</th>
            <th></th>
            <th>6月</th>
            <th></th>
            <th>7月</th>
            <th></th>
            <th>8月</th>
            <th></th>
            <th>9月</th>
            <th></th>
            @else
            <th>10月</th>
            <th></th>
            <th>11月</th>
            <th></th>
            <th>12月</th>
            <th></th>
            <th>1月</th>
            <th></th>
            <th>2月</th>
            <th></th>
            <th>3月</th>
            <th></th>
            @endif
            <th></th>
            <th></th>
            <th>合&emsp;&emsp;計</th>
            <th></th>
        </tr>
        @php
        $totalMap = array();
        @endphp
        @foreach($tableData['shippingYear'] as $itemCode => $shippingYearArray)
        @php
        $totalLotNumber = 0;
        $totalRowNumber = 0;
        $infoMap = array();
        $firstWorkHour = $shippingYearArray[0]['firstWorkHour'];
        $lastWorkHour = $shippingYearArray[0]['lastWorkHour'];
        $firstCost = $shippingYearArray[0]['firstCost'];
        $lastCost = $shippingYearArray[0]['lastCost'];
        @endphp

        @foreach($shippingYearArray as $shippingYear)
        @php
        $totalLotNumber += $shippingYear['totalLotNumber'];
        @endphp

        @foreach(range(1, 12) as $month)
        @php

        if ($tableData['yearType'] == 'first') {
        if(4 <= $month && $month <=9) { $totalRowNumber +=$shippingYear['month_'.$month]; } } else { if(10 <=$month ||
            $month <=3) { $totalRowNumber +=$shippingYear['month_'.$month]; } } if(isset($infoMap[$month]['number'])) {
            if($shippingYear['month_'.$month]> 0) {

            $infoMap[$month]['number'] =
            $infoMap[$month]['number'].(($infoMap[$month]['number']=='')?'':'<br>').$shippingYear['month_'.$month];
            $infoMap[$month]['lot_no'] =
            $infoMap[$month]['lot_no'].(($infoMap[$month]['lot_no']=='')?'':'<br>').'#'.$shippingYear['lot_no'];
            }
            } else {
            if($shippingYear['month_'.$month] > 0) {
            $infoMap[$month]['number'] = $shippingYear['month_'.$month];
            $infoMap[$month]['lot_no'] = '#'.$shippingYear['lot_no'];
            } else {
            $infoMap[$month]['number'] = '';
            $infoMap[$month]['lot_no'] = '';
            }
            }

            if (isset($totalMap[$month]['number'])) {
            $totalMap[$month]['number'] += $shippingYear['month_'.$month];

            $totalMap[$month]['sh'] += $shippingYear['month_'.$month] *
            (($tableData['yearType'] == 'first')? $firstWorkHour : $lastWorkHour);

            $totalMap[$month]['jpy'] += $shippingYear['month_'.$month] *
            (($tableData['yearType'] == 'first')? $firstCost : $lastCost);
            } else {
            $totalMap[$month]['number'] = $shippingYear['month_'.$month];

            $totalMap[$month]['sh'] = $shippingYear['month_'.$month] *
            (($tableData['yearType'] == 'first')? $firstWorkHour : $lastWorkHour);

            $totalMap[$month]['jpy'] = $shippingYear['month_'.$month] *
            (($tableData['yearType'] == 'first')? $firstCost : $lastCost);
            }
            @endphp
            @endforeach

            @endforeach

            @if($totalLotNumber > 0)
            <tr>
                <td>{{$itemCode}}</td>
                <td></td>
                <td>{{number_format($firstWorkHour, 3)}}H<br>{{number_format($lastWorkHour, 3)}}H</td>
                <td>{{number_format($firstCost)}}<br>{{number_format($lastCost)}}</td>
                @if($tableData['yearType'] == 'first')
                <td>{!!$infoMap[4]['lot_no']!!}</td>
                <td>{!!$infoMap[4]['number']!!}</td>
                <td>{!!$infoMap[5]['lot_no']!!}</td>
                <td>{!!$infoMap[5]['number']!!}</td>
                <td>{!!$infoMap[6]['lot_no']!!}</td>
                <td>{!!$infoMap[6]['number']!!}</td>
                <td>{!!$infoMap[7]['lot_no']!!}</td>
                <td>{!!$infoMap[7]['number']!!}</td>
                <td>{!!$infoMap[8]['lot_no']!!}</td>
                <td>{!!$infoMap[8]['number']!!}</td>
                <td>{!!$infoMap[9]['lot_no']!!}</td>
                <td>{!!$infoMap[9]['number']!!}</td>
                @else
                <td>{!!$infoMap[10]['lot_no']!!}</td>
                <td>{!!$infoMap[10]['number']!!}</td>
                <td>{!!$infoMap[11]['lot_no']!!}</td>
                <td>{!!$infoMap[11]['number']!!}</td>
                <td>{!!$infoMap[12]['lot_no']!!}</td>
                <td>{!!$infoMap[12]['number']!!}</td>
                <td>{!!$infoMap[1]['lot_no']!!}</td>
                <td>{!!$infoMap[1]['number']!!}</td>
                <td>{!!$infoMap[2]['lot_no']!!}</td>
                <td>{!!$infoMap[2]['number']!!}</td>
                <td>{!!$infoMap[3]['lot_no']!!}</td>
                <td>{!!$infoMap[3]['number']!!}</td>
                @endif
                <td></td>
                <td></td>
                <td>{{$totalRowNumber}}</td>
                <td>台</td>
            </tr>
            @endif
            @endforeach

            @php
            $totalNumberFirst = 0;
            $totalSHFirst = 0;
            $totalJPYFirst = 0;
            $totalNTDFirst = 0;
            $totalNumberLast = 0;
            $totalSHLast = 0;
            $totalJPYLast = 0;
            $totalNTDLast = 0;
            @endphp

            @if(count($totalMap)>0)
            @foreach(range(4, 9) as $i)
            @php
            $totalNumberFirst += $totalMap[$i]['number'];
            $totalSHFirst += $totalMap[$i]['sh'];
            $totalJPYFirst += $totalMap[$i]['jpy'];
            $totalNTDFirst += $totalMap[$i]['jpy'] * $tableData['exchange']['first'];
            @endphp
            @endforeach

            @foreach(range(1, 3) as $i)
            @php
            $totalNumberLast += $totalMap[$i]['number'];
            $totalSHLast += $totalMap[$i]['sh'];
            $totalJPYLast += $totalMap[$i]['jpy'];
            $totalNTDLast += $totalMap[$i]['jpy'] * $tableData['exchange']['last'];
            @endphp
            @endforeach

            @foreach(range(10, 12) as $i)
            @php
            $totalNumberLast += $totalMap[$i]['number'];
            $totalSHLast += $totalMap[$i]['sh'];
            $totalJPYLast += $totalMap[$i]['jpy'];
            $totalNTDLast += $totalMap[$i]['jpy'] * $tableData['exchange']['last'];
            @endphp
            @endforeach
            @endif
            <tr>
                <td>合&emsp;&emsp;計</td>
                <td></td>
                <td></td>
                <td>台&emsp;&emsp;數</td>
                @if($tableData['yearType'] == 'first')
                <td>{{$totalMap[4]['number'] ?? 0}}</td>
                <td></td>
                <td>{{$totalMap[5]['number'] ?? 0}}</td>
                <td></td>
                <td>{{$totalMap[6]['number'] ?? 0}}</td>
                <td></td>
                <td>{{$totalMap[7]['number'] ?? 0}}</td>
                <td></td>
                <td>{{$totalMap[8]['number'] ?? 0}}</td>
                <td></td>
                <td>{{$totalMap[9]['number'] ?? 0}}</td>
                <td></td>
                @else
                <td>{{$totalMap[10]['number'] ?? 0}}</td>
                <td></td>
                <td>{{$totalMap[11]['number'] ?? 0}}</td>
                <td></td>
                <td>{{$totalMap[12]['number'] ?? 0}}</td>
                <td></td>
                <td>{{$totalMap[1]['number'] ?? 0}}</td>
                <td></td>
                <td>{{$totalMap[2]['number'] ?? 0}}</td>
                <td></td>
                <td>{{$totalMap[3]['number'] ?? 0}}</td>
                <td></td>
                @endif
                <td></td>
                <td></td>
                <td>{{number_format(($tableData['yearType'] == 'first')? $totalNumberFirst : $totalNumberLast, 2)}}</td>
                <td>台</td>
            </tr>
            <tr>
                <td></td>
                <td></td>
                <td></td>
                <td>結算SH</td>
                @if($tableData['yearType'] == 'first')
                <td>{{number_format($totalMap[4]['sh'] ?? 0, 2)}}</td>
                <td></td>
                <td>{{number_format($totalMap[5]['sh'] ?? 0, 2)}}</td>
                <td></td>
                <td>{{number_format($totalMap[6]['sh'] ?? 0, 2)}}</td>
                <td></td>
                <td>{{number_format($totalMap[7]['sh'] ?? 0, 2)}}</td>
                <td></td>
                <td>{{number_format($totalMap[8]['sh'] ?? 0, 2)}}</td>
                <td></td>
                <td>{{number_format($totalMap[9]['sh'] ?? 0, 2)}}</td>
                <td></td>
                @else
                <td>{{number_format($totalMap[10]['sh'] ?? 0, 2)}}</td>
                <td></td>
                <td>{{number_format($totalMap[11]['sh'] ?? 0, 2)}}</td>
                <td></td>
                <td>{{number_format($totalMap[12]['sh'] ?? 0, 2)}}</td>
                <td></td>
                <td>{{number_format($totalMap[1]['sh'] ?? 0, 2)}}</td>
                <td></td>
                <td>{{number_format($totalMap[2]['sh'] ?? 0, 2)}}</td>
                <td></td>
                <td>{{number_format($totalMap[3]['sh'] ?? 0, 2)}}</td>
                <td></td>
                @endif
                <td></td>
                <td></td>
                <td>{{number_format(($tableData['yearType'] == 'first')? $totalSHFirst : $totalSHLast, 2)}}</td>
                <td>H</td>
            </tr>
            <tr>
                <td>
                    ({{$tableData['period_tw'] + 1969}}年度
                    {{($tableData['yearType']=='first')?'4':'10'}}月～
                    {{$tableData['period_tw'] + (($tableData['yearType']=='first')? 1969 : 1970)}}年度
                    {{($tableData['yearType']=='first')?'9':'3'}}月)
                </td>
                <td></td>
                <td></td>
                <td>￥ 千圓</td>
                @if($tableData['yearType'] == 'first')
                <td>{{number_format(($totalMap[4]['jpy'] ?? 0)/1000, 2)}}</td>
                <td></td>
                <td>{{number_format(($totalMap[5]['jpy'] ?? 0)/1000, 2)}}</td>
                <td></td>
                <td>{{number_format(($totalMap[6]['jpy'] ?? 0)/1000, 2)}}</td>
                <td></td>
                <td>{{number_format(($totalMap[7]['jpy'] ?? 0)/1000, 2)}}</td>
                <td></td>
                <td>{{number_format(($totalMap[8]['jpy'] ?? 0)/1000, 2)}}</td>
                <td></td>
                <td>{{number_format(($totalMap[9]['jpy'] ?? 0)/1000, 2)}}</td>
                <td></td>
                @else
                <td>{{number_format(($totalMap[10]['jpy'] ?? 0)/1000, 2)}}</td>
                <td></td>
                <td>{{number_format(($totalMap[11]['jpy'] ?? 0)/1000, 2)}}</td>
                <td></td>
                <td>{{number_format(($totalMap[12]['jpy'] ?? 0)/1000, 2)}}</td>
                <td></td>
                <td>{{number_format(($totalMap[1]['jpy'] ?? 0)/1000, 2)}}</td>
                <td></td>
                <td>{{number_format(($totalMap[2]['jpy'] ?? 0)/1000, 2)}}</td>
                <td></td>
                <td>{{number_format(($totalMap[3]['jpy'] ?? 0)/1000, 2)}}</td>
                <td></td>
                @endif
                <td></td>
                <td></td>
                <td>{{number_format(($tableData['yearType'] == 'first')? $totalJPYFirst / 1000: $totalJPYLast / 1000, 2)}}
                </td>
                <td>千圓</td>
            </tr>
            <tr>
                <td></td>
                <td></td>
                <td></td>
                <td>NT千元</td>
                @if($tableData['yearType'] == 'first')
                <td>{{number_format(($totalMap[4]['jpy'] ?? 0) * $tableData['exchange'][$tableData['yearType']] / 1000, 2)}}
                </td>
                <td></td>
                <td>{{number_format(($totalMap[5]['jpy'] ?? 0) * $tableData['exchange'][$tableData['yearType']] / 1000, 2)}}
                </td>
                <td></td>
                <td>{{number_format(($totalMap[6]['jpy'] ?? 0) * $tableData['exchange'][$tableData['yearType']] / 1000, 2)}}
                </td>
                <td></td>
                <td>{{number_format(($totalMap[7]['jpy'] ?? 0) * $tableData['exchange'][$tableData['yearType']] / 1000, 2)}}
                </td>
                <td></td>
                <td>{{number_format(($totalMap[8]['jpy'] ?? 0) * $tableData['exchange'][$tableData['yearType']] / 1000, 2)}}
                </td>
                <td></td>
                <td>{{number_format(($totalMap[9]['jpy'] ?? 0) * $tableData['exchange'][$tableData['yearType']] / 1000, 2)}}
                </td>
                <td></td>
                @else
                <td>{{number_format(($totalMap[10]['jpy'] ?? 0) * $tableData['exchange'][$tableData['yearType']] / 1000, 2)}}
                </td>
                <td></td>
                <td>{{number_format(($totalMap[11]['jpy'] ?? 0) * $tableData['exchange'][$tableData['yearType']] / 1000, 2)}}
                </td>
                <td></td>
                <td>{{number_format(($totalMap[12]['jpy'] ?? 0) * $tableData['exchange'][$tableData['yearType']] / 1000, 2)}}
                </td>
                <td></td>
                <td>{{number_format(($totalMap[1]['jpy'] ?? 0) * $tableData['exchange'][$tableData['yearType']] / 1000, 2)}}
                </td>
                <td></td>
                <td>{{number_format(($totalMap[2]['jpy'] ?? 0) * $tableData['exchange'][$tableData['yearType']] / 1000, 2)}}
                </td>
                <td></td>
                <td>{{number_format(($totalMap[3]['jpy'] ?? 0) * $tableData['exchange'][$tableData['yearType']] / 1000, 2)}}
                </td>
                <td></td>
                @endif
                <td></td>
                <td></td>
                <td>{{number_format(($tableData['yearType'] == 'first')? $totalNTDFirst / 1000: $totalNTDLast / 1000, 2)}}
                </td>
                <td>千元</td>
            </tr>
            @if($tableData['yearType'] == 'first')
            <tr>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td>{{$tableData['iso']}}</td>
            </tr>
            @else
            <tr>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td>{{$tableData['period_tw']}}期總計</td>
                <td></td>
                <td></td>
                <td>台數</td>
                <td>{{number_format($totalNumberFirst + $totalNumberLast, 2)}}</td>
                <td>台</td>
            </tr>
            <tr>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td>結算SH</td>
                <td>{{number_format($totalSHFirst + $totalSHLast, 2)}}</td>
                <td>H</td>
            </tr>
            <tr>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td>({{$tableData['period_tw'] + 1969}}.4～{{$tableData['period_tw'] + 1970}}.3)</td>
                <td></td>
                <td></td>
                <td>￥ 千圓</td>
                <td>{{number_format(($totalJPYFirst + $totalJPYLast) / 1000, 2)}}</td>
                <td>千圓</td>
            </tr>
            <tr>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td>NT千元</td>
                <td>{{number_format(($totalNTDFirst + $totalNTDLast) / 1000, 2)}}</td>
                <td>千元</td>
            </tr>
            <tr>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td>{{$tableData['iso']}}</td>
            </tr>
            @endif
    </thead>
</table>