@extends('system.system_content')
@section('inner_content')
@include('layouts.script.jq_dialog_layout')
<!-- 行事曆 -->

@php
$update_url = route('ScheduleController.changeScheduleStatus');
@endphp

<section style="margin-bottom:10%;margin-left:3%;margin-right:7%">
    <div class="title-flex">
        <div style="width:20%">
            <form class="title-div-form">
                <select class="title-select" name="year" id="year" onchange="jump()">
                    <!-- 顯示10年的西元年 -->
                    @foreach(range(((int)date("Y")-8) , ((int)date("Y")+2)) as $i)
                    <option value={{$i}}>{{$i}}年</option>
                    @endforeach
                </select>
                <select class="title-select" name="month" id="month" onchange="jump()">
                    <!-- 顯示1~12月 -->
                    @foreach(range(0, 11) as $i)
                    <option value={{$i}}>{{$i+1}}月</option>
                    @endforeach
                </select>
            </form>
        </div>

        <div>
            <!-- 代表"<" -->
            <button class="btn title-div-button" id="previous" onclick="previous()">&lt;</button>
            <!-- 代表標題(幾年幾月) -->
            <div class="title-div" id="monthAndYear"></div>
            <!-- 代表">" -->
            <button class="btn title-div-button" id="next" onclick="next()">&gt;</button>
        </div>

        <!-- 為了調整版面方便而放的 -->
        <div style="width:20%">
        </div>
    </div>

    <!-- 設定行事曆背景為灰色，這樣空格子就可以顯示灰色 -->
    <table class="table table-bordered table-responsive-sm" id="calendar" style="background-color:#DCDCDC">
        <thead>
            <tr class="title-tr">
                <th>日</th>
                <th>一</th>
                <th>二</th>
                <th>三</th>
                <th>四</th>
                <th>五</th>
                <th>六</th>
            </tr>
        </thead>
        <!-- 行事曆顯示，由js來製作 -->
        <tbody id="calendar-body">
        </tbody>
    </table>

    <!-- 點選行事曆日期的對話框 -->
    <div id="dialog-form-schedule" style="display:none">
        <p id="form-title"></p>
        <form id="update-schedule" method="POST" action="{{route('ScheduleController.changeScheduleStatus')}}">
            @csrf
            @method('POST')
            <fieldset>
                <div style="margin-top:10px">
                    <div id="div-fieldset">
                        <label for="work_type">請選擇種類 :</label>
                        <select style="width:100px;" name="status[]" id="work_type">
                            <div>
                                <div>
                                    <!-- 預設為空，令value=0，讓ScheduleController判斷使用者是否有選東西 -->
                                    <option value="0"></option>
                                    @foreach($tableData['calendar'] as $data)
                                    <!-- 回傳id到ScheduleController -->
                                    <option value="{{$data->id}}">{{$data->name}}</option>
                                    @endforeach
                                </div>
                            </div>
                        </select>
                    </div>
                    <div id="div-fieldset">
                        <!-- 傳送使用者選的日期 -->
                        <input type="hidden" name="status[]" id="now_year">
                        <input type="hidden" name="status[]" id="now_month">
                        <input type="hidden" name="status[]" id="now_date">
                        <input type="hidden" name="status[]" id="now_day">
                    </div>
                </div>
            </fieldset>
        </form>
    </div>
</section>

<!-- 以下是行事曆製作 -->
<script>
let can_basic_crud = ("{{$tableData['permission']['basic_crud']}}" == 'Y');
let update_url = <?php echo json_encode($update_url); ?>;
let tableCalendar = JSON.parse(@json($tableData['calendar'] -> toJson()));
let tableSchedule = JSON.parse(@json($tableData['schedule'] -> toJson()));

let dayArray = ['日', '一', '二', '三', '四', '五', '六', '日'];
let today = new Date();
let currentYear = today.getFullYear();
let currentMonth = today.getMonth();
let selectYear = document.getElementById("year");
let selectMonth = document.getElementById("month");
let monthAndYear = document.getElementById("monthAndYear");
showCalendar(currentMonth, currentYear);

//對話框設置
$jq_dialog(function() {
    $jq_dialog("#dialog-form-schedule").dialog({
        autoOpen: false,
        height: 260,
        width: 450,
        modal: true,
        buttons: {
            "儲存": function() {
                //儲存資料，這個是form的id
                document.getElementById('update-schedule').submit();
                //關閉對話框
                $jq_dialog(this).dialog("close");
            },
        },
        close: function() {
            //下拉式選單的預設值，0代表的是上面<option>的value="0"
            $jq_dialog("#work_type").val("0");
        }
    }).prev(".ui-dialog-titlebar").css("background", "#00C1DE").css("color", "white");
});

function showCalendar(month, year) {
    //firstDay:0(星期日)~6(星期六)
    let firstDay = (new Date(year, month)).getDay();
    //每個月的天數
    let daysInMonth = 32 - new Date(year, month, 32).getDate();
    // body of the calendar
    let tbl = document.getElementById("calendar-body");
    // clearing all previous cells
    tbl.innerHTML = "";
    // filing data about month and in the page via DOM.
    monthAndYear.innerHTML = " " + year + " 年 " + (month + 1) + "月 ";
    selectYear.value = year;
    selectMonth.value = month;

    //製作網格
    let date = 1;
    //每個月1號以前的空格子數量，方便算每個網格對應的日期
    let empty = 0;
    //此為橫向，顯示5個禮拜
    for (let i = 0; i < 6; i++) {
        // creates a table row
        let row = document.createElement("tr");
        //參考basic_menu.css
        row.classList.add("not-day");
        //此為縱向，顯示星期日~星期六
        for (let j = 0; j < 7; j++) {
            //如果當前格子對應到上個月，產生空格子
            if (i === 0 && j < firstDay) {
                let cell = document.createElement("td");
                let cellDate = document.createTextNode("");
                cell.appendChild(cellDate);
                row.appendChild(cell);
                empty++; //空格子數量+1
            }
            //如果當前格子對應到下個月
            else if (date > daysInMonth) {
                break;
            }
            //如果當前格子對應到這個月
            else {
                //格子設置
                let cell = document.createElement("td");
                let cellDate = document.createTextNode(date);
                let cellText = document.createElement("p");
                let cellButton = document.createElement("span");

                var attribute_cell = 'color:#2998FF;font-size:18px;';
                var attribute_text = 'display:inline;color:#000000;font-size:10px; line-height: 4.0em;';
                var attribute_button = 'display:inline;color:#acacac;text-align:left;font-size:14px;';

                cell.setAttribute('style', attribute_cell);
                cellText.setAttribute('style', attribute_text);
                cellButton.setAttribute('style', attribute_button);

                var dayName = ((j === 6) || (j === 0)) ? tableCalendar[1]['name'] : tableCalendar[0]['name'];
                var dayStyle = ((j === 6) || (j === 0)) ? "holiday" : "workday";

                //如果是今天
                if (date === today.getDate() && year === today.getFullYear() && month === today.getMonth()) {
                    //如果是當日顯示黃色，參考basic_menu.css
                    cell.classList.add("today");
                    //驗證資料庫是否有今天的資料
                    for (let index = 0; index < tableSchedule.length; index++) {
                        //如果資料庫有今天的資料，根據選的日曆種類顯示
                        if (date == tableSchedule[index]['date'] &&
                            year == tableSchedule[index]['year'] &&
                            (month + 1) == tableSchedule[index]['month']) {
                            dayName = tableSchedule[index]['name'];
                            break;
                        }
                    }
                }
                //如果不是今天
                else {
                    //如果是六、日，顯示藍色，平日顯示白色，參考basic_menu.css
                    cell.classList.add(dayStyle);
                    //驗證資料庫是否有今天的資料
                    for (let index = 0; index < tableSchedule.length; index++) {
                        //如果資料庫有今天的資料，根據選的日曆種類顯示
                        if (date == tableSchedule[index]['date'] &&
                            year == tableSchedule[index]['year'] &&
                            (month + 1) == tableSchedule[index]['month']) {
                            dayName = tableSchedule[index]['name'];
                            cell.classList.remove(dayStyle);
                            cell.classList.add((tableSchedule[index]['is_holiday'] === 'Y') ? "holiday" : "workday");
                            break;
                        }
                    }
                }
                //顯示日曆種類
                cellText.innerHTML = "&nbsp;&nbsp;&nbsp;&nbsp;" + dayName + "&nbsp;&nbsp;&nbsp;&nbsp;";
                cellButton.className = 'glyphicon glyphicon-pencil'; //鉛筆圖案
                cellButton.onclick = function() {
                    if (can_basic_crud) {
                        var thisTimeDate = 7 * i + (1 + j) - empty;
                        var message = year + "年 " + (month + 1) + "月 " + thisTimeDate + "日 星期" + dayArray[j];

                        document.getElementById('form-title').innerHTML = message;
                        document.getElementById('now_year').value = year;
                        document.getElementById('now_month').value = month + 1;
                        document.getElementById('now_date').value = thisTimeDate;
                        document.getElementById('now_day').value = j;

                        $jq_dialog("#dialog-form-schedule").dialog('option', 'title', "修改資料");
                        $jq_dialog("#dialog-form-schedule").dialog("open");
                    } else {
                        alert('無操作權限！');
                        return;
                    }
                }
                //填加到cell裡(顯示有順序性cellButton->cellText->cellDate)
                cell.appendChild(cellButton);
                cell.appendChild(cellText);
                cell.appendChild(cellDate);

                //將格子加到裡面
                row.appendChild(cell);
                date++;
            }
        }
        tbl.appendChild(row); // appending each row into calendar body.
    }
}

function next() {
    currentYear = (currentMonth === 11) ? currentYear + 1 : currentYear;
    currentMonth = (currentMonth + 1) % 12;
    showCalendar(currentMonth, currentYear);
}

function previous() {
    currentYear = (currentMonth === 0) ? currentYear - 1 : currentYear;
    currentMonth = (currentMonth === 0) ? 11 : currentMonth - 1;
    showCalendar(currentMonth, currentYear);
}

function jump() {
    currentYear = parseInt(selectYear.value);
    currentMonth = parseInt(selectMonth.value);
    showCalendar(currentMonth, currentYear);
}
</script>
@stop