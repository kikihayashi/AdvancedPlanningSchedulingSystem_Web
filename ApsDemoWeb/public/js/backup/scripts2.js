let today = new Date();
let currentMonth = today.getMonth();
let currentYear = today.getFullYear();
let selectYear = document.getElementById("year");
let selectMonth = document.getElementById("month");
let months = ["Jan", "Feb", "Mar", "Apr", "May", "Jun", "Jul", "Aug", "Sep", "Oct", "Nov", "Dec"];
let monthAndYear = document.getElementById("monthAndYear");
showCalendar(currentMonth, currentYear);

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
    monthAndYear.innerHTML = " " + year + " 年 " + (month + 1) + "月 ";//months[month]
    selectYear.value = year;
    selectMonth.value = month;

    // creating all cells
    let date = 1;
    for (let i = 0; i < 6; i++) {
        // creates a table row
        let row = document.createElement("tr");
        row.classList.add("not-day");

        //creating individual cells, filing them up with data.
        for (let j = 0; j < 7; j++) {
            //如果當前格子對應到上個月，產生空格子
            if (i === 0 && j < firstDay) {
                let cell = document.createElement("td");
                let cellText = document.createTextNode("");
                cell.appendChild(cellText);
                row.appendChild(cell);
            }
            //如果當前格子對應到下個月
            else if (date > daysInMonth) {
                break;
            }
            //如果當前格子對應到這個月
            else {
                var attribute_cell = 'color: #2998FF;font-size:18px;';
                var attribute_button = 'display:inline;color: #acacac;text-align:left;font-size:14px;';
                var attribute_text = 'display:inline;color: #000000;font-size:10px;';
                
                let cell = document.createElement("td");
                let cellText = document.createTextNode(date);

                let form = document.createElement("form");
                let input = document.createElement("input");
                input.type = "hidden";
                input.name = "status";
                input.value = "123";
                form.appendChild(input);

                form.action = "56456";
                form.method = "post";
         
                let text = document.createElement("p");
                let button = document.createElement("span");
           
                //如果是六、日
                if ((j === 6) || (j  === 0)) {
                    text.innerHTML = '&nbsp;&nbsp;&nbsp;&nbsp;休假日&nbsp;&nbsp;&nbsp;&nbsp;';
                } 
                //如果是平日
                else {
                    text.innerHTML = '&nbsp;&nbsp;&nbsp;&nbsp;工作日&nbsp;&nbsp;&nbsp;&nbsp;';
                }

                button.className = 'glyphicon glyphicon-pencil';//鉛筆圖案
                button.id = year + "/" + (month + 1) + "/i:" + i + "/j:" + j;
                button.onclick = function() {
                    if (confirm('確定要修改：' + button.id + '?')) 
                    {
                        form.submit();
                    }
                }
                button.setAttribute('style', attribute_button); 
                text.setAttribute('style', attribute_text); 
               
                //如果是今天
                if (date === today.getDate() && year === today.getFullYear() && month === today.getMonth()) {
                    cell.classList.add("today");
                } 
                //如果不是今天
                else {
                    //如果是六、日
                    if ((j === 6) || (j  === 0)) {
                        cell.classList.add("weekend");
                    } 
                    //如果是平日
                    else {
                        cell.classList.add("day");
                    }
                }
                cell.setAttribute('style', attribute_cell); 
                cell.appendChild(button);
                cell.appendChild(text);
                cell.appendChild(cellText);
                row.appendChild(cell);
                row.appendChild(form);
                date++;
            }
        }
        tbl.appendChild(row); // appending each row into calendar body.
    }
}