@php
$status = '';
$role='';
switch($tableData['progress']['progress_point']) {
case 0:
$status='正常';
break;
case 1:
$status='未審核';
break;
case 2:
$status='審核中';
$role='課長';
break;
case 3:
$status='審核中';
$role='經副理';
break;
case 4:
$status='審核中';
$role='執行董事';
break;
}
@endphp
<section>
    <div>
        <table class="table project-title-table">
            <tbody>
                <tr>
                    <td class="right" style="width:17%">台京期數 :</td>
                    <td class="left" style="width:17%">
                        <select id="select-period" onchange="selectPeriod();">
                            @foreach($tableData['period'] as $period)
                            @php
                            $is_selected = ($tableData['thisTimePeriod'] -> period_tw == $period->period_tw);
                            @endphp
                            <option value="{{$period->period_tw}}" @if($is_selected) selected @endif>
                                {{$period->period_tw}}
                            </option>
                            @endforeach
                        </select>
                    </td>
                    <td class="right"style="width:15%">年份 :</td>
                    <td class="left"style="width:15%"><input type="years" id="years" readonly="readonly"
                            value="{{$tableData['thisTimePeriod']->years}}">
                    </td>
                    <td class="right"style="width:15%">狀態 :</td>
                    <td class="left">
                        <div style=" display: flex;">
                            @if($tableData['progress']['progress_point'] > 1)
                            <button disabled id="progress-role-button">&ensp;{{$role}}&ensp;</button>
                            @endif
                            <input type="text" id="status" readonly="readonly" value="{{$status}}">
                        </div>
                    </td>
                    </td>
                </tr>
                <tr>
                    <td class="right">日京期數 :</td>
                    <td class="left"><input type="text" id="period_jp" readonly="readonly"
                            value="{{$tableData['thisTimePeriod']->period_jp}}">
                    <td class="right">ISO文件編號 :</td>
                    <td class="left"><input type="years" id="years" readonly="readonly" value="{{$tableData['iso']}}">
                    </td>
                    <td class="right">版本 :</td>
                    <td class="left"><input type="text" id="version" readonly="readonly"
                            value="{{$tableData['progress']['version']}}">
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</section>