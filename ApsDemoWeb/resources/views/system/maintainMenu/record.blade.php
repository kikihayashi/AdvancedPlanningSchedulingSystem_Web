@extends('system.system_content')
@section('inner_content')
@include('layouts.script.js_scaling_layout')
<script>
let tablePeriod = @json($tableData['period']);
let nowProjectType = 'M';
let nowPeriod = (tablePeriod.length > 0) ? tablePeriod[0]['period_tw'] : 0;
let nowMonth = 4;
let nowVersion = 0;
let isMenuClose = true;

document.addEventListener("DOMContentLoaded", function(event) {
    filterToFindVersion(nowProjectType, nowPeriod, nowMonth);
});

//偵測選擇的下拉式選單
function selectListener(type) {
    var selectBox = document.getElementById("select-" + type);
    var selectValue = selectBox.options[selectBox.selectedIndex].value;
    switch (type) {
        case 'dateType':
            changeProjectOption(selectValue);
            break;
        case 'projectType':
            nowProjectType = selectValue;
            break;
        case 'period':
            nowPeriod = selectValue;
            break;
        case 'month':
            nowMonth = selectValue;
            break;
        case 'version':
            nowVersion = selectValue;
            break;
    }
    if (type != 'version') {
        filterToFindVersion(nowProjectType, nowPeriod, nowMonth);
    }
}

//根據選擇的類型(年度、月度)，來顯示、隱藏選項
function changeProjectOption(selectValue) {
    switch (selectValue) {
        case 'year':
            nowProjectType = 'M';
            $("#select-projectType").val(nowProjectType);
            $("#select-projectType option[value= M]").show();
            $("#select-projectType option[value= PY]").show();
            $("#select-projectType option[value= SY]").show();
            $("#select-projectType option[value= PM]").hide();
            $("#select-projectType option[value= SM]").hide();
            $("#month-div").hide();
            break;
        case 'month':
            nowProjectType = 'PM';
            $("#select-projectType").val(nowProjectType);
            $("#select-projectType option[value= M]").hide();
            $("#select-projectType option[value= PY]").hide();
            $("#select-projectType option[value= SY]").hide();
            $("#select-projectType option[value= PM]").show();
            $("#select-projectType option[value= SM]").show();
            $("#month-div").show();
            break;
    }
}

//Ajax根據option篩選出符合條件的所有版本
function filterToFindVersion(projectType, period, month) {
    nowProjectType = projectType;
    nowPeriod = period;
    nowMonth = month;
    changeSearchButtonStatus('filter');
    //Ajax取得符合條件的所有版本
    $.ajax({
        url: "{{route('RecordController.ajaxFetchVersion')}}",
        type: "POST",
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        data: {
            projectType: nowProjectType,
            period: nowPeriod,
            month: nowMonth
        },
        success: function(resultJson) {
            //將JSON字串轉成Array，Object.values(JSON.parse(resultJson))//看整個json字串
            var resultArray = JSON.parse(resultJson);
            changeVersionOptionStatus(resultArray);
        },
    });
}

//更改查詢按鈕狀態
function changeSearchButtonStatus(status) {
    //先移除所有CSS的Class
    $('#record-btn-search').toggleClass("filter", false);
    $('#record-btn-search').toggleClass("finish", false);
    $('#record-btn-search').toggleClass("none", false);
    $('#record-btn-search').toggleClass("search", false);
    //讓按鈕不可按
    $('#record-btn-search').attr('disabled', true);
    switch (status) {
        case 'filter'://篩選(不可按)
            $('#record-btn-search').toggleClass("filter");
            $('#record-btn-search').html('篩選中...');
            break;
        case 'finish'://能查詢(可按)
            $('#record-btn-search').toggleClass("finish");
            $('#record-btn-search').html('查詢');
            $('#record-btn-search').attr('disabled', false);
            break;
        case 'none'://無資料(不可按)
            $('#record-btn-search').toggleClass("none");
            $('#record-btn-search').html('無資料');
            break;
        case 'search'://查詢中(不可按)
            $('#record-btn-search').toggleClass("search");
            $('#record-btn-search').html('查詢中...');
            break;
    }
}

//更改版次下拉式選單狀態
function changeVersionOptionStatus(resultArray) {
    $('#project-title').show();
    $('#project-table').hide();
    if (resultArray.length == 0) {
        changeSearchButtonStatus('none');
        $('#version-option-temp').show();
        $('#version-option').hide();
        $('#version-info').hide();
    } else {
        changeSearchButtonStatus('finish');
        $('#version-option-temp').hide();
        $('#version-option').show();
        $('#version-info').show();
        createVersionOption(resultArray);
        createVersionInfo(resultArray);
    }
}

//產生版次下拉式選單
function createVersionOption(resultArray) {
    var html = '';
    html += '<a>版次 :</a>';
    html += '<br>';
    html += '<select style="width:210px;" id="select-version" onchange="selectListener(`version`);">';
    for (var version in resultArray) {
        html += '<option value="' + version + '">' + resultArray[version]['date'] + ' 版本' + version + '</option>';
        nowVersion = version;
    }
    html += '</select>';
    $('#version-option').html('');
    $('#version-option').html(html);
    $('#select-version').val(nowVersion);
}

//產生版本資訊
function createVersionInfo(resultArray) {
    var html = '';
    html += '<div>第 ' + nowVersion + ' 次提出修訂</div>';
    html += '<div>提出修訂時間︰' + resultArray[nowVersion]['date'] + '</div>';
    html += '<div>計畫狀態︰' + resultArray[nowVersion]['status'] + '</div>';
    html += '<div>版本變更︰' + resultArray[nowVersion]['info'] + '</div>';
    $('#version-info').html('');
    $('#version-info').html(html);
}

//Ajax查詢版本紀錄
function clickSearchBtn() {
    changeSearchButtonStatus('search')
    $('#project-title').show();
    $('#project-table').hide();
    //Ajax該版本資料
    $.ajax({
        url: "{{route('RecordController.ajaxFetchRecord')}}",
        type: "POST",
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        data: {
            projectType: nowProjectType,
            period: nowPeriod,
            month: nowMonth,
            version: nowVersion,
        },
        success: function(resultJson) {
            //將JSON字串轉成Array
            var resultArray = JSON.parse(resultJson);
            showRecordTable(resultArray);
        },
    });
}

//顯示版本紀錄
function showRecordTable(resultArray) {
    isMenuClose = true;//剛顯示出來的紀錄，所有列表都是收合狀態
    changeSearchButtonStatus('finish');
    $('#project-title').hide();
    $('#project-table').html('');
    $('#project-table').html(resultArray['html']);
    $('#project-table').show();
}
</script>

<section style="margin-top:3%;margin-left:3%;margin-right:8%;padding-bottom:8%">
    <div class="record-div">
        <div>
            <a>類型 :</a>
            <br>
            <select id="select-dateType" onchange="selectListener('dateType');">
                <option value="year">
                    年度
                </option>
                <option value="month">
                    月度
                </option>
            </select>
        </div>
        <div>
            <a>資料表 :</a>
            <br>
            <select id="select-projectType" onchange="selectListener('projectType');">
                <option value="M">
                    大計畫
                </option>
                <option value="PY">
                    年度生產計畫
                </option>
                <option value="SY">
                    年度出荷計畫
                </option>
                <option value="PM" style="display:none">
                    月度生產計畫
                </option>
                <option value="SM" style="display:none">
                    月度出荷計畫
                </option>
            </select>
        </div>
        <div>
            <a>期數 :</a>
            <br>
            <select id="select-period" onchange="selectListener('period');">
                @foreach($tableData['period'] as $period)
                <option value="{{$period['period_tw']}}">
                    {{$period['period_tw']}}
                </option>
                @endforeach
            </select>
        </div>
        <div id="month-div" style="display:none">
            <a>月份 :</a>
            <br>
            <select id="select-month" onchange="selectListener('month');">
                @foreach($tableData['monthMaps'] as $monthMap)
                <option value="{{$monthMap['page']}}">
                    {{$monthMap['page']}}月&ensp;
                </option>
                @endforeach
            </select>
        </div>
        <div id="version-option">
        </div>
        <div id="version-option-temp">
            <a>版次 :</a>
            <br>
            <select style="width:210px;">
                <option>
                </option>
            </select>
        </div>
        <div>
            <br>
            <button id="record-btn-search" class="btn btn-info" onclick="clickSearchBtn();">
                查詢
            </button>
        </div>
    </div>
    <div>
        <div id="version-info" style="margin-top:5px;color:black;">
        </div>
        <h1 id="project-title" style="margin-top:15px;color:black;">點擊查詢以查看資料</h1>
        <div id="project-table" style="margin-top:15px;display:none"></div>
    </div>
</section>
@stop