<table>
    <thead>
        <tr>
            <th>Lot</th>
            <th>機種</th>
            <th>數量</th>
            <th>材料納期預定日</th>
            <th>生產預計日</th>
            <th>生產日期</th>
        </tr>
    </thead>
    <tbody>
        @foreach($tableData['ofct'] as $ofct)
        <tr>
            <td>{{$ofct['lot_no']}}</td>
            <td>{{$ofct['item_code']}}</td>
            <td>{{$ofct['lot_total']}}</td>
            <td>{{$ofct['material_date']}}</td>
            <td>{{$ofct['product_date']}}</td>
            <td>{{$ofct['date']}}</td>
        </tr>
        @endforeach
    </tbody>
</table>