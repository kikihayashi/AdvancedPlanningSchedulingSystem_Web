<script src="//apps.bdimg.com/libs/jquery/1.10.2/jquery.min.js"></script>
<script src='https://use.fontawesome.com/2188c74ac9.js'></script>
<script defer src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.2.1/jquery.min.js"></script>
<script defer src="https://stackpath.bootstrapcdn.com/bootstrap/4.4.1/js/bootstrap.bundle.min.js"></script>
<script defer src='https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/3.3.7/js/bootstrap.min.js'></script>
<script>
//嚴格模式
// "use strict";
//載入頁面時，自動執行
document.addEventListener("DOMContentLoaded", function(event) {
    $('#sidebarCollapse').on('click', function() {
        $('#sidebar').toggleClass('active');
        $('#content-main').toggleClass('active');
    });

    //不要讓綠色字串換行
    focusMethodForOuter('{{$selection??"logo"}}');
    focusMethodForInner('{{$visitedId??""}}');
    stretchMenu('{{$openMenu??""}}');
});

//選取最左邊外框圖示，將該圖示顏色調白 
focusMethodForOuter = function getFocus(id) {
    document.getElementById(id).style.color = 'white';
}

//選取指定頁面，將該頁面背景顏色調白 
focusMethodForInner = function getFocus(id) {
    if (id != "") {
        document.getElementById(id).style.backgroundColor = 'white';
    }
}

//觸發點擊左邊外框的選項(延遲0.1秒)
function stretchMenu(id) {
    if (id != "") {
        setTimeout(function() {
            document.getElementById(id + '-a').click();
        }, 80);
    }
}

//控制左外框選項的上下伸縮
function change(openMenu) {
    if (openMenu != '') {
        var menuId = document.getElementById(openMenu);
        if (menuId.className.includes('show-in')) {
            menuId.classList.remove("show-in");
        } else {
            menuId.classList.add("show-in");
        }
    }
}
</script>