<table>
    <thead>
        <tr>
        </tr>
        <tr>
            <th>
                &gt;&gt;&gt;&gt;&gt;
                信號 RELAY
                {{$tableData['period_tw'] + (($tableData['month'] > 3)? 1969 : 1970)}} 年
                {{$tableData['month']}} 月度出荷計劃表
                &lt;&lt;&lt;&lt;&lt;
            </th>
            <th></th>
            <th></th>
            <th></th>
            <th></th>
            <th>匯率： {{number_format($tableData['exchange'], 4)}}</th>
            <th></th>
            @foreach($tableData['transportMap'] as $transport)
            <th>{{$transport}}</th>
            <th></th>
            @endforeach
            <th>(Y):橫向</th>
            <th></th>
            <th>(T):縱向</th>
            <th></th>
        </tr>
        <tr>
            <th>品名</th>
            <th>單價</th>
            @foreach($tableData['dateArray'] as $dateInfo)
            <th>
                ITKT -
                {{substr($tableData['period_tw'] + 1969, -1).str_pad($tableData['month'], 2, "0", STR_PAD_LEFT)}}
                {{$dateInfo['letter']}}
            </th>
            <th></th>
            <th></th>
            @endforeach
            <th></th>
            <th></th>
            <th></th>
            <th>
                ITKT -
                {{substr($tableData['period_tw'] + 1969, -1).str_pad($tableData['month'], 2, "0", STR_PAD_LEFT)}}
            </th>
            <th></th>
            <th></th>
        </tr>
        <tr>
            <th></th>
            <th></th>
            @foreach($tableData['dateArray'] as $dateInfo)
            <th>
                {{str_pad($tableData['month'], 2, "0", STR_PAD_LEFT)}} /
                {{str_pad($dateInfo['date'], 2, "0", STR_PAD_LEFT)}}
                出荷計畫({{$dateInfo['abbreviation']}})
            </th>
            <th></th>
            <th></th>
            @endforeach
            <th></th>
            <th></th>
            <th></th>
            <th>計 劃 出 荷 合 計</th>
            <th></th>
            <th></th>
        </tr>
        <tr>
            <th></th>
            <th></th>
            @foreach($tableData['dateArray'] as $dateArray)
            <th>LOT</th>
            <th>台數</th>
            <th>金額</th>
            @endforeach
            <th></th>
            <th></th>
            <th></th>
            <th>台數</th>
            <th>金額</th>
            <th></th>
        </tr>
    </thead>
    <tbody>
        @foreach($tableData['shippingMonth'] as $shippingMonth)
        @php
        $rowNumber = 0;
        @endphp
        <tr>
            <td>{{$shippingMonth['item_code']}}</td>
            <td>￥{{number_format($shippingMonth['cost'])}}</td>
            @foreach($tableData['dateArray'] as $dateInfo)
            @if($dateInfo['date']==$shippingMonth['date'] && $dateInfo['transport_id']==$shippingMonth['transport_id'])
            @php
            $rowNumber += $shippingMonth['number'];
            @endphp
            <td>#{{$shippingMonth['lot_no']}}</td>
            <td>{{number_format($shippingMonth['number'])}}</td>
            <td>￥{{number_format($shippingMonth['number']*$shippingMonth['cost'])}}</td>
            @else
            <td></td>
            <td></td>
            <td></td>
            @endif
            @endforeach
            <td></td>
            <td></td>
            <td></td>
            <td>{{number_format($rowNumber)}}</td>
            <td>￥{{number_format($rowNumber * $shippingMonth['cost'])}}</td>
            <td></td>
        </tr>
        @endforeach

        @php
        $totalNumber = 0;
        $totalCost = 0;
        $jpyCostMap = array();
        @endphp
        <tr>
            <td>日 幣 合 計</td>
            <td></td>
            @foreach($tableData['dateArray'] as $dateInfo)
            @php
            $columnNumber = 0;
            $columnCost = 0;
            @endphp
            @foreach($tableData['shippingMonth'] as $shippingMonth)
            @if($dateInfo['date']==$shippingMonth['date'] && $dateInfo['transport_id']==$shippingMonth['transport_id'])
            @php
            $columnNumber += $shippingMonth['number'];
            $columnCost += $shippingMonth['number'] * $shippingMonth['cost'];
            @endphp
            @endif
            @endforeach
            <td></td>
            <td>{{number_format($columnNumber)}}</td>
            <td>￥{{number_format($columnCost)}}</td>
            @php
            $totalNumber += $columnNumber;
            $totalCost += $columnCost;
            $jpyCostMap[] = $columnCost;
            @endphp
            @endforeach
            <td></td>
            <td></td>
            <td></td>
            <td>{{number_format($totalNumber)}}</td>
            <td>￥{{number_format($totalCost)}}</td>
        </tr>
        <tr>
            <td>台 幣 合 計</td>
            <td></td>
            @foreach($jpyCostMap as $jpyCost)
            <td></td>
            <td></td>
            <td>NT${{number_format($jpyCost * $tableData['exchange'])}}</td>
            @endforeach
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td>NT${{number_format($totalCost * $tableData['exchange'])}}</td>
        </tr>
        <!-- 簽章 -->
        <tr>
        </tr>
        <tr>
            <td></td>
            <td></td>
            <td></td>
            <td>資料分發：</td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td>作 成</td>
            <td></td>
            <td></td>
            <td>審 查 </td>
            <td></td>
            <td></td>
            <td>審 查 </td>
            <td></td>
            <td></td>
            <td>客 戶 承 認</td>
            <td></td>
            <td></td>
        </tr>
        <tr>
            <td></td>
            <td></td>
            <td></td>
            <td>總 經 理</td>
            <td></td>
            <td>業 務 部</td>
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
        </tr>
        <tr>
            <td></td>
            <td></td>
            <td></td>
            <td>副 總經理</td>
            <td></td>
            <td>品 管 部</td>
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
        </tr>
        <tr>
            <td></td>
            <td></td>
            <td></td>
            <td>總 工程師</td>
            <td></td>
            <td>製 一 部</td>
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
        </tr>
        <tr>
            <td></td>
            <td></td>
            <td></td>
            <td>管 理 部</td>
            <td></td>
            <td>繼電器課</td>
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
        </tr>
        <tr>
            <td></td>
            <td></td>
            <td></td>
            <td>資 材 部</td>
            <td></td>
            <td>親 會 社</td>
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
    </tbody>
</table>