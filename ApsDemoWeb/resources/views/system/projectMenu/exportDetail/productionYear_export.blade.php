<table>
    <thead>
        <tr>
            <th>{{$tableData['title']}}</th>
            <th></th>
            <th></th>
            <th></th>
            <th></th>
            <th></th>
            <th></th>
            @if($tableData['yearType']=='first')
            @foreach(range(0, 2) as $i)
            @foreach(range(1, $tableData['monthMaps'][$i]['totalDay']) as $j)
            <th></th>
            @endforeach
            @endforeach

            @php
            $isFirst = true;
            @endphp
            @foreach(range(1, $tableData['monthMaps'][3]['totalDay']) as $j)
            @if($isFirst && $tableData['line_no'] == 1 && $tableData['yearType']=='first')
            <th>作成</th>
            @php
            $isFirst = false;
            @endphp
            @else
            <th></th>
            @endif
            @endforeach

            @php
            $isFirst = true;
            @endphp
            @foreach(range(4, 5) as $i)
            @foreach(range(1, $tableData['monthMaps'][$i]['totalDay']) as $j)
            @if($isFirst && $tableData['line_no'] == 1 && $tableData['yearType']=='first')
            <th>審查</th>
            @php
            $isFirst = false;
            @endphp
            @else
            <th></th>
            @endif
            @endforeach
            @endforeach

            @else

            @foreach(range(6,11) as $i)
            @foreach(range(1, $tableData['monthMaps'][$i]['totalDay']) as $j)
            <th></th>
            @endforeach
            @endforeach

            @endif

            @php
            $isFirst = true;
            @endphp
            @if($isFirst && $tableData['line_no'] == 1 && $tableData['yearType']=='first')
            <th>客戶承認</th>
            @php
            $isFirst = false;
            @endphp
            @else
            <th></th>
            @endif
        </tr>
        <tr>
            @php
            $isFirst = true;
            @endphp
            <th>P.{{$tableData['page']}}/6</th>
            <th>Line {{$tableData['line_no']}}</th>
            <th>◆ 部品到著, ★ 組立開始, ─ 調整、檢查 </th>
            <th></th>
            <th></th>
            <th></th>
            <th></th>
            @if($tableData['yearType']=='first')
            @foreach(range(0,2) as $i)
            @foreach(range(1, $tableData['monthMaps'][$i]['totalDay']) as $j)
            @if($isFirst && $tableData['line_no'] == 1 && $tableData['yearType']=='first')
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
            @php
            $isFirst = false;
            @endphp
            @else
            <th></th>
            @endif
            @endforeach
            @endforeach
            @foreach(range(3,5) as $i)
            @foreach(range(1, $tableData['monthMaps'][$i]['totalDay']) as $j)
            <th></th>
            @endforeach
            @endforeach

            @else

            @foreach(range(6,11) as $i)
            @foreach(range(1, $tableData['monthMaps'][$i]['totalDay']) as $j)
            <th></th>
            @endforeach
            @endforeach

            @endif
            <th></th>
        </tr>
        <tr>
            @php
            if($tableData['yearType']=='first') {
            $start = 0;
            $end = 5;
            } else {
            $start = 6;
            $end = 11;
            }
            @endphp
            <th>ORDER</th>
            <th>納</th>
            <th>機種</th>
            <th>製</th>
            <th>台數</th>
            <th>部品</th>
            <th>組立</th>
            @foreach(range($start,$end) as $i)
            @php
            $isFirst = true;
            @endphp
            @foreach(range(1, $tableData['monthMaps'][$i]['totalDay']) as $j)
            @if($isFirst)
            <th>{{$tableData['monthMaps'][$i]['name']}}</th>
            @php
            $isFirst = false;
            @endphp
            @else
            <th></th>
            @endif
            @endforeach
            @endforeach
            <th>備註</th>
        </tr>
        <tr>
            @php
            $isFirst = true;
            if($tableData['yearType']=='first') {
            $start = 0;
            $end = 5;
            } else {
            $start = 6;
            $end = 11;
            }
            @endphp
            <th>NO.</th>
            <th>期</th>
            <th></th>
            <th>番</th>
            <th></th>
            <th>到著</th>
            <th>開始</th>
            @foreach(range($start, $end) as $i)
            @php
            $isFirst = true;
            @endphp
            @foreach(range(1, $tableData['monthMaps'][$i]['totalDay']) as $j)
            @if($isFirst)
            <th>出勤: {{$tableData['monthMaps'][$i]['workDay']}} 日</th>
            @php
            $isFirst = false;
            @endphp
            @else
            <th></th>
            @endif
            @endforeach
            @endforeach
            <th></th>
        </tr>
    </thead>
    <!-- 機種 -->
    <tbody>
        @php
        $columnMap = array();
        @endphp
        @foreach(($tableData['productionYearTotal'][$tableData['line_no']][$tableData['yearType']] ?? array()) as
        $productionYear)
        <tr>
            @php
            $isFirst = true;
            $selfYearNumber = $productionYear[$tableData['yearType'].'Number'];
            if($tableData['yearType']=='first') {
            $start = 0;
            $end = 5;
            } else {
            $start = 6;
            $end = 11;
            }
            @endphp
            <td>{!!nl2br($productionYear['order_no'])!!}</td>
            <td>{{$productionYear['deadline']}}</td>
            <td>{{$productionYear['item_code']}}</td>
            <td>{{$productionYear['lot_no']}}</td>
            <td>&ensp;{{number_format($selfYearNumber)}}</td>
            <td>{{str_replace('-', '/', $productionYear['material_date'])}}</td>
            <td>{{str_replace('-', '/', $productionYear['product_date'])}}</td>
            @foreach(range($start, $end) as $i)
            @php
            $isFirst = true;
            @endphp
            @foreach(range(1, $tableData['monthMaps'][$i]['totalDay']) as $j)
            @php
            $year = $tableData['period_tw'] + (($tableData['monthMaps'][$i] > 3) ? 1969 : 1970);
            $month = str_pad($tableData['monthMaps'][$i]['page'], 2, "0", STR_PAD_LEFT);
            $day = str_pad($j, 2, "0", STR_PAD_LEFT);
            $date = $year.'-'.$month.'-'.$day;
            @endphp
            @if($date == $productionYear['material_date'])
            <td>&#9670;</td>
            @elseif($date == $productionYear['product_date'])
            <td>&#9733;</td>
            @else
            <td></td>
            @endif
            @endforeach
            @endforeach
            <td>{{$productionYear['remark']}}</td>
        </tr>
        <tr>
            @php
            $isFirst = true;
            if($tableData['yearType']=='first') {
            $start = 0;
            $end = 5;
            } else {
            $start = 6;
            $end = 11;
            }
            @endphp
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td>
                @if($selfYearNumber != $productionYear['lot_total'])
                &ensp;{{number_format($productionYear['lot_total'])}}
                @endif
            </td>
            <td></td>
            <td></td>
            @foreach(range($start, $end) as $i)
            @php
            $isFirst = true;
            $number = $productionYear['month_'.$tableData['monthMaps'][$i]['page']];
            $sh = $number * $productionYear[$tableData['yearType'].'WorkHour'];
            if(isset($columnMap[$tableData['monthMaps'][$i]['page']]['number'])){
            $columnMap[$tableData['monthMaps'][$i]['page']]['number'] += $number;
            $columnMap[$tableData['monthMaps'][$i]['page']]['sh'] += $sh;
            } else {
            $columnMap[$tableData['monthMaps'][$i]['page']]['number'] = $number;
            $columnMap[$tableData['monthMaps'][$i]['page']]['sh'] = $sh;
            }
            @endphp
            @foreach(range(1, $tableData['monthMaps'][$i]['totalDay']) as $j)
            @if($isFirst)
            @if($number > 0)
            <td>
                {{number_format($number)}} / {{number_format($sh)}}
            </td>
            @else
            <td></td>
            @endif
            @php
            $isFirst = false;
            @endphp
            @else
            <td></td>
            @endif
            @endforeach
            @endforeach
            <td></td>
        </tr>
        @endforeach
        <tr>
            @php
            $rowNumber = 0;
            $isFirst = true;
            if($tableData['yearType']=='first') {
            $start = 0;
            $end = 5;
            } else {
            $start = 6;
            $end = 11;
            }
            @endphp
            <td>Line {{$tableData['line_no']}} 合計</td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td>台</td>
            @foreach(range($start, $end) as $i)
            @php
            $isFirst = true;
            @endphp
            @foreach(range(1, $tableData['monthMaps'][$i]['totalDay']) as $j)
            @if($isFirst)
            <td>{{number_format($columnMap[$tableData['monthMaps'][$i]['page']]['number'] ?? 0)}}</td>
            @php
            $rowNumber += ($columnMap[$tableData['monthMaps'][$i]['page']]['number'] ?? 0);
            $isFirst = false;
            @endphp
            @else
            <td></td>
            @endif
            @endforeach
            @endforeach
            <td>{{number_format($rowNumber)}}</td>
        </tr>
        <tr>
            @php
            $rowSH = 0;
            $isFirst = true;
            if($tableData['yearType']=='first') {
            $start = 0;
            $end = 5;
            } else {
            $start = 6;
            $end = 11;
            }
            @endphp
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td>H</td>
            @foreach(range($start, $end) as $i)
            @php
            $isFirst = true;
            @endphp
            @foreach(range(1, $tableData['monthMaps'][$i]['totalDay']) as $j)
            @if($isFirst)
            <td>{{number_format($columnMap[$tableData['monthMaps'][$i]['page']]['sh'] ?? 0)}}</td>
            @php
            $rowSH += ($columnMap[$tableData['monthMaps'][$i]['page']]['sh'] ?? 0);
            $isFirst = false;
            @endphp
            @else
            <td></td>
            @endif
            @endforeach
            @endforeach
            <td>{{number_format($rowSH)}}</td>
        </tr>
        @if($tableData['line_no'] == 3)
        @php
        $columnTotalMap =$tableData['columnTotalMap'];
        @endphp
        <tr>
            @php
            $rowTotalNumber = 0;
            $isFirst = true;
            if($tableData['yearType']=='first') {
            $start = 0;
            $end = 5;
            } else {
            $start = 6;
            $end = 11;
            }
            @endphp
            <td>合計</td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td>台</td>
            @foreach(range($start, $end) as $i)
            @php
            $isFirst = true;
            @endphp
            @foreach(range(1, $tableData['monthMaps'][$i]['totalDay']) as $j)
            @if($isFirst)
            <td>{{number_format($columnTotalMap[$tableData['monthMaps'][$i]['page']]['number'] ?? 0)}}</td>
            @php
            $rowTotalNumber += ($columnTotalMap[$tableData['monthMaps'][$i]['page']]['number'] ?? 0);
            $isFirst = false;
            @endphp
            @else
            <td></td>
            @endif
            @endforeach
            @endforeach
            <td>{{number_format($rowTotalNumber)}}</td>
        </tr>
        <tr>
            @php
            $rowTotalSH = 0;
            $isFirst = true;
            if($tableData['yearType']=='first') {
            $start = 0;
            $end = 5;
            } else {
            $start = 6;
            $end = 11;
            }
            @endphp
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td>H</td>
            @foreach(range($start, $end) as $i)
            @php
            $isFirst = true;
            @endphp
            @foreach(range(1, $tableData['monthMaps'][$i]['totalDay']) as $j)
            @if($isFirst)
            <td>{{number_format($columnTotalMap[$tableData['monthMaps'][$i]['page']]['sh'] ?? 0)}}</td>
            @php
            $rowTotalSH += ($columnTotalMap[$tableData['monthMaps'][$i]['page']]['sh'] ?? 0);
            $isFirst = false;
            @endphp
            @else
            <td></td>
            @endif
            @endforeach
            @endforeach
            <td>{{number_format($rowTotalSH)}}</td>
        </tr>
        @if($tableData['yearType'] == 'last')
        <tr>
            @php
            $isFirst = true;
            $isSecond = false;
            if($tableData['yearType']=='first') {
            $start = 0;
            $end = 5;
            } else {
            $start = 6;
            $end = 11;
            }
            @endphp
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            @foreach(range($start, $end - 1) as $i)
            @foreach(range(1, $tableData['monthMaps'][$i]['totalDay']) as $j)
            <td></td>
            @endforeach
            @endforeach
            @foreach(range(1, $tableData['monthMaps'][$end]['totalDay']) as $j)
            @if($j <= 20) @if($isFirst) <td>{{$tableData['period_tw']}}期總計</td>
                @php
                $isFirst = false;
                $isSecond = true;
                @endphp
                @else
                <td></td>
                @endif
                @else
                @if($isSecond)
                <td>台數</td>
                @php
                $isSecond = false;
                @endphp
                @else
                <td></td>
                @endif
                @endif
                @endforeach
                <td>{{$tableData['totalMap']['number']}}</td>
        </tr>
        <tr>
            @php
            $isFirst = true;
            if($tableData['yearType']=='first') {
            $start = 0;
            $end = 5;
            } else {
            $start = 6;
            $end = 11;
            }
            @endphp
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            @foreach(range($start, $end - 1) as $i)
            @foreach(range(1, $tableData['monthMaps'][$i]['totalDay']) as $j)
            <td></td>
            @endforeach
            @endforeach

            @foreach(range(1, $tableData['monthMaps'][$end]['totalDay']) as $j)
            @if($j <= 20) <td>
                </td>
                @else
                @if($isFirst)
                <td>工數</td>
                @php
                $isFirst = false;
                @endphp
                @else
                <td></td>
                @endif
                @endif
                @endforeach
                <td>{{$tableData['totalMap']['sh']}}</td>
        </tr>
        @endif
        @endif
        <tr>
            @php
            $isFirst = true;
            if($tableData['yearType']=='first') {
            $start = 0;
            $end = 5;
            } else {
            $start = 6;
            $end = 11;
            }
            @endphp
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            @foreach(range($start, $end) as $i)
            @foreach(range(1, $tableData['monthMaps'][$i]['totalDay']) as $j)
            <td></td>
            @endforeach
            @endforeach
            <td>{{$tableData['iso']}}</td>
        </tr>
    </tbody>
</table>