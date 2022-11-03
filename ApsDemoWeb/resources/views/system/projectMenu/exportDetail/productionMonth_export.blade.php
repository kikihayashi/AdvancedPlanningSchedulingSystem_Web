@php
$days = $tableData['thisTimeDays'];
@endphp

<table>
    <thead>
        <tr>
            <th>
                &gt;&gt;&gt;&gt;&gt;
                信號 RELAY
                {{$tableData['period_tw'] + (($tableData['month'] > 3)? 1969 : 1970)}} 年
                {{$tableData['month']}} 月度生產計劃表
                &lt;&lt;&lt;&lt;&lt;
            </th>
        </tr>
        <tr>
            <th>(＊): 休日 , (Y):橫向 , (T): 縱向 , (D):二極体付 , (S):改修品</th>
        </tr>
        <tr>
            <th>機 種 名</th>
            <th>仕 切<br>SH</th>
            <th>製番</th>
            <th>前月迄<br>完成數</th>
            <th>本月計畫<br>生產台數</th>
            <th></th>
            @foreach(range(1, $days) as $day)
            <th>
                {{($tableData['isHolidayMap'][$day]=='Y')? '*' : str_pad($day, 2, "0", STR_PAD_LEFT)}}
            </th>
            @endforeach
            <th>完&emsp;成<br>工(H)數</th>
        </tr>
    </thead>

    <tbody>
        @php
        $totalNumber = 0;
        $totalHour = 0;
        @endphp
        @foreach($tableData['productionMonth'] as $productionMonth)
        @php
        $index = 0;
        $start = $productionMonth['start'];
        $end = $productionMonth['end'];
        $totalNumber += floatval($productionMonth['this_month_number']);
        $totalHour += floatval($productionMonth['completeHour']);
        @endphp
        <tr>
            <td>{{$productionMonth['item_code']}}</td>
            <td>{{$productionMonth['workHour']}}</td>
            <td>#{{$productionMonth['lot_no']}}</td>
            <td>{{number_format($productionMonth['previous_month_number'])}}</td>
            <td>{{number_format($productionMonth['this_month_number'])}}</td>
            <td></td>
            @foreach(range(1, $days) as $day)
            <td>
            </td>
            @endforeach
            <td>{{$productionMonth['completeHour']}}</td>
        </tr>
        <tr>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            @foreach(range(1, $days) as $day)
            <td>
            </td>
            @endforeach
            <td></td>
        </tr>
        @endforeach
        <tr>
            <td>合&emsp;計</td>
            <td></td>
            <td></td>
            <td></td>
            <td>{{number_format($totalNumber)}}</td>
            <td></td>
            @foreach(range(1, $days) as $day)
            <td>
            </td>
            @endforeach
            <td>{{number_format($totalHour)}}</td>
        </tr>

        <!-- 簽章 -->
        <tr></tr>
        <tr></tr>
        <tr>
            <td></td>
            <td></td>
            <td>名</td>
            <td>日</td>
            <td></td>
            <td></td>
            <td></td>
            @foreach(range(1, $days-22) as $day)
            <td>
            </td>
            @endforeach
            <td>資料分發：</td>
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
            <td>作 成</td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td>審 查</td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
        </tr>
        <tr>
            <td>預定投入工數:</td>
            <td>{{$tableData['employee']}}</td>
            <td>&#x2715;</td>
            <td>{{$tableData['workdays']}}</td>
            <td>=</td>
            @php
            $totalWorkHour = $tableData['employee'] * $tableData['workdays'] * 8;
            @endphp
            <td>{{number_format($totalWorkHour)}}</td>
            <td>H</td>
            @foreach(range(1, $days-22) as $day)
            <td>
            </td>
            @endforeach
            <td>總 經 理</td>
            <td></td>
            <td></td>
            <td></td>
            <td>資 材 部</td>
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
        <tr>
            <td>預定完成工數:</td>
            <td></td>
            <td></td>
            <td></td>
            <td>=</td>
            <td>{{number_format($totalHour)}}</td>
            <td>H</td>
            @foreach(range(1, $days-22) as $day)
            <td>
            </td>
            @endforeach
            <td>副 總經理</td>
            <td></td>
            <td></td>
            <td></td>
            <td>品 管 部</td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
        </tr>
        <tr>
            <td>預定生產效率:</td>
            <td></td>
            <td></td>
            <td></td>
            <td>=</td>
            @php
            $totalWorkHour = $totalHour / $totalWorkHour * 100;
            @endphp
            <td>{{number_format($totalWorkHour, 2)}}</td>
            <td>%</td>
            @foreach(range(1, $days-22) as $day)
            <td>
            </td>
            @endforeach
            <td>總 工程師</td>
            <td></td>
            <td></td>
            <td></td>
            <td>製 一 部</td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
        </tr>
        <tr>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            @foreach(range(1, $days-22) as $day)
            <td>
            </td>
            @endforeach
            <td>管 理 部</td>
            <td></td>
            <td></td>
            <td></td>
            <td>親 會 社</td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
        </tr>
    </tbody>
</table>
