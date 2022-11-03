@extends('system.basicMenu.periodDetail.period_content')
@section('inner_content')
<script>
document.addEventListener("DOMContentLoaded", function(event) {
    //初始顯示半年度資料 
    $('#section-half-a-year').show();
    $('#section-months').hide();
});
$(function() {
    $('#dynamic-select-exchange').on('change', function() {
        var selectType = $(this).val();
        if (selectType == "half-a-year") {
            $('#section-half-a-year').show();
            $('#section-months').hide();
        } else {
            $('#section-half-a-year').hide();
            $('#section-months').show();
        }
        return false;
    });
});
</script>

<!-- 此處用來判斷當前是點選哪個TAB，顯示對應的內容，預設是上半年、4月 -->
@php
$selectPageYear = session('selectPageYear') ?? 'first';
$selectPageMonth = session('selectPageMonth') ?? '4';
@endphp

<!-- 上、下半年的資料 -->
<section id="section-half-a-year">
    <div class="div-nav-tabs">
        <ul class="nav nav-tabs">
            <!-- 上、下半年的TAB -->
            @foreach($yearMaps as $yearMap)
            <li class="nav-item">
                <a id="nav-link-a" class="nav-link {{($selectPageYear==$yearMap['page'])? 'active':''}}" data-toggle="tab"
                    href="#{{$yearMap['page']}}-half-page">{{$yearMap['name']}}</a>
            </li>
            @endforeach
        </ul>

        <div class="tab-content" id="exchange-tab-content">
            @foreach($yearMaps as $yearMap)
            @php
            $hasData = ($tableData[$yearMap['page']] == null);
            $JPYToNTDRate = ($hasData)? '' : number_format($tableData[$yearMap['page']]->JPYToNTDRate, 4, '.', '');
            $USDToNTDRate = ($hasData)? '' : number_format($tableData[$yearMap['page']]->USDToNTDRate, 6, '.', '');
            $USDToJPYRate = ($hasData)? '' : number_format($tableData[$yearMap['page']]->USDToJPYRate, 6, '.', '');
            $MaterialRate = ($hasData)? '' : number_format($tableData[$yearMap['page']]->MateialRate, 6, '.', '');
            $WorkingRate = ($hasData)? '' : number_format($tableData[$yearMap['page']]->WorkingRate, 6, '.', '');
            @endphp

            <!-- 上、下半年的內容 -->
            <div style="margin:10px;margin-left:50px;"
                class="tab-pane fade in {{($selectPageYear==$yearMap['page'])? 'active':''}}"
                id="{{$yearMap['page']}}-half-page">
                <br>
                <div style="display:flex;">
                    <label for="jp_tw_rate">&emsp;&emsp;<span style="color:red">&#42;</span>日幣對台幣匯率：</label>
                    <input readonly="readonly" type="number" name="jp_tw_rate" id="jp_tw_rate-{{$yearMap['page']}}" value="{{$JPYToNTDRate}}" required>
                    <br>
                </div>
                <br>

                <div style="display:flex;">
                    <label for="us_tw_rate">&emsp;&emsp;<span style="color:red">&#42;</span>美金對台幣匯率：</label>
                    <input readonly="readonly" type="number" name="us_tw_rate" id="us_tw_rate-{{$yearMap['page']}}" value="{{$USDToNTDRate}}" required>
                    <br>
                </div>
                <br>

                <div style="display:flex;">
                    <label for="us_jp_rate">&emsp;&emsp;<span style="color:red">&#42;</span>美金對日幣匯率：</label>
                    <input readonly="readonly" type="number" name="us_jp_rate" id="us_jp_rate-{{$yearMap['page']}}" value="{{$USDToJPYRate}}" required>
                    <br>
                </div>
                <br>

                <div style="display:flex;">
                    <label for="material_rate">&emsp;&emsp;<span style="color:red">&#42;</span>台構材料換算率：</label>
                    <input readonly="readonly" type="number" name="material_rate" id="material_rate-{{$yearMap['page']}}" value="{{$MaterialRate}}" required>
                    <br>
                </div>
                <br>

                <div style="display:flex;">
                    <label for="processing_rate"><span style="color:red">&#42;</span>加工費率 / 日幣時薪：</label>
                    <input readonly="readonly" type="number" name="processing_rate" id="processing_rate-{{$yearMap['page']}}" value="{{$WorkingRate}}" required>
                    <br>
                </div>
                <br>
            </div>
            @endforeach
        </div>
    </div>
</section>

<!-- 月度的資料 -->
<section id="section-months">
    <div class="div-nav-tabs">
        <ul class="nav nav-tabs">
            <!-- 月度的TAB -->
            @foreach($monthMaps as $monthMap)
            <li class="nav-item">
                <a id="nav-link-a" class="nav-link {{($selectPageMonth==$monthMap['page'])? 'active':''}}" data-toggle="tab"
                    href="#{{$monthMap['page']}}-page">{{$monthMap['name']}}</a>
            </li>
            @endforeach
        </ul>

        <div class="tab-content" id="exchange-tab-content">
            @foreach($monthMaps as $monthMap)
            @php
            $hasData = ($tableData[$monthMap['yearType']] != null);
            $JPYToNTDRate = ($hasData)? number_format($tableData[$monthMap['yearType']]->JPYToNTDRate, 4, '.', '') : '';
            $USDToNTDRate = ($hasData)? number_format($tableData[$monthMap['yearType']]->USDToNTDRate, 6, '.', '') : '';
            $USDToJPYRate = ($hasData)? number_format($tableData[$monthMap['yearType']]->USDToJPYRate, 6, '.', '') : '';
            $MaterialRate = ($hasData)? number_format($tableData[$monthMap['yearType']]->MateialRate, 6, '.', '') : '';
            $WorkingRate  = ($hasData)? number_format($tableData[$monthMap['yearType']]->WorkingRate, 6, '.', '') : '';
            @endphp
            <!-- 月度的內容 -->
            <div style="margin:10px;margin-left:50px;" class="tab-pane fade in {{($selectPageMonth==$monthMap['page'])? 'active':''}}"
                id="{{$monthMap['page']}}-page">
                <br>
                <div style="display:flex;">
                    <label for="jp_tw_rate"><span style="color:red">&#42;</span>日幣對台幣匯率：</label>
                    <input readonly="readonly" type="number" name="jp_tw_rate" id="jp_tw_rate-{{$monthMap['page']}}" value="{{$JPYToNTDRate}}" required>
                    <br>
                </div>
                <br>

                <div style="display:flex;">
                    <label for="us_tw_rate"><span style="color:red">&#42;</span>美金對台幣匯率：</label>
                    <input readonly="readonly" type="number" name="us_tw_rate" id="us_tw_rate-{{$monthMap['page']}}" value="{{$USDToNTDRate}}" required>
                    <br>
                </div>
                <br>

                <div style="display:flex;">
                    <label for="us_jp_rate"><span style="color:red">&#42;</span>美金對日幣匯率：</label>
                    <input readonly="readonly" type="number" name="us_jp_rate" id="us_jp_rate-{{$monthMap['page']}}" value="{{$USDToJPYRate}}" required>
                    <br>
                </div>
                <br>

                <div style="display:flex;">
                    <label for="material_rate"><span style="color:red">&#42;</span>台構材料換算率：</label>
                    <input readonly="readonly" type="number" name="material_rate" id="material_rate-{{$monthMap['page']}}" value="{{$MaterialRate}}" required>
                    <br>
                </div>
                <br>

                <div style="display:flex;">
                    <label for="processing_rate">&emsp;&emsp;&emsp;<span style="color:red">&#42;</span>加工費率：</label>
                    <input readonly="readonly" type="number" name="processing_rate" id="processing_rate-{{$monthMap['page']}}" value="{{$WorkingRate}}"
                        required>
                    <br>
                </div>
                <br>
            </div>
            @endforeach
        </div>
    </div>
</section>
@stop