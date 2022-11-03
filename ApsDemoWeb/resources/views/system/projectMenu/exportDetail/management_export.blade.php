<table>
    <thead>
        <tr>
            <th>計畫期數</th>
            <th>計畫期(起)</th>
            <th>計畫期(迄)</th>
            <th>版本</th>
            <th>Lot No</th>
            <th>Lot 總台數</th>
            <th>品番</th>
            <th>品名</th>
            <th>出荷台數</th>
            <th>結算工數(上半年)</th>
            <th>結算工數(下半年)</th>
            <th>台京生產工數(上半年)</th>
            <th>台京生產工數(下半年)</th>
            <th>生產方式</th>
            <th>生產預定日</th>
            <th>納期預定日</th>
            <th>出荷方式</th>
            <th>出荷備註</th>
            <th>其它備註</th>
        </tr>
    </thead>
    <tbody>
        @foreach($tableData['management'] as $management)
        <tr>
            <td>{{$management['period']}}</td>
            <td>{{$management['period'] + 1969}}/04/01</td>
            <td>{{$management['period'] + 1970}}/03/31</td>
            <td>&ensp;{{$management['version']}}</td>
            <td>{{$management['lot_no']}}</td>
            <td>&ensp;{{number_format($management['lot_total'])}}</td>
            <td>{{$management['item_code']}}</td>
            <td>{{$management['item_name']}}</td>
            <td>&ensp;{{number_format($management['real_lot_number'])}}</td>
            <td>&ensp;{{$management['firstWorkHour']}}</td>
            <td>&ensp;{{$management['lastWorkHour']}}</td>
            <td></td>
            <td></td>
            <td>{{($management['batch']=='entire_batch')?'整批':'分批'}}</td>
            <td>{{str_replace('-', '/', $management['product_date'])}}</td>
            <td>{{str_replace('-', '/', $management['material_date'])}}</td>
            <td>{{$management['transportName']}}</td>
            <td>{{$management['remark_transport']}}</td>
            <td>{{$management['remark_other']}}</td>
        </tr>
        @endforeach
    </tbody>
</table>