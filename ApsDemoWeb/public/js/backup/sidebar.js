 
 
 
 //選取最左邊外框圖示，將該圖示顏色調白 
 focusMethodForOuter = function getFocus($id) {
    document.getElementById($id).style.color = 'white';
}

//選取指定頁面，將該頁面背景顏色調白 
focusMethodForInner = function getFocus($id) {
    document.getElementById($id).style.backgroundColor = 'white';
}

//載入頁面時，自動執行
document.addEventListener("DOMContentLoaded", function(event) {
    focusMethodForOuter('{{$selection??"logo"}}');
    focusMethodForInner('{{$visitedId??""}}'); //不要讓它換行
    if ('{{isset($openMenu)}}') {
        var menuId = document.getElementById('{{$openMenu??""}}'); //不要讓它換行
        menuId.classList.add("show");
        menuId.classList.add("in");
    }
});

function change($menuId) {
    var menuId = document.getElementById($menuId);
    if (menuId.className.includes('show in')) {
        menuId.classList.remove("show in");
    } else if ($menuId == '') {

    } else {
        menuId.classList.add("show in");
    }
}