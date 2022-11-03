<script>
//上下展開功能
function toggleMenu() {
    var o = document.getElementById("menus");
    o.style.display = (o.style.display == 'none') ? '' : 'none';

    var fa_o = document.getElementById("fa-toggle");
    fa_o.className = (o.style.display == 'none') ? "fa fa-angle-double-down" : "fa fa-angle-double-up";
}

//上下展開功能
function toggleMenuById(i) {
    var o = document.getElementById("menus-" + i);
    o.style.display = (o.style.display == 'none') ? '' : 'none';

    var fa_o = document.getElementById("fa-toggle-" + i);
    fa_o.className = (o.style.display == 'none') ? "fa fa-angle-double-down" : "fa fa-angle-double-up";
}

//全部展開、收合
function toggleAllMenu(dataSize) {
    if (isMenuClose) {
        for (var i = 0; i < dataSize; i++) {
            document.getElementById("toggleAllMenu-btn").innerHTML = '全部收合';
            document.getElementById("menus-" + i).style.display = '';
            document.getElementById("fa-toggle-" + i).className = "fa fa-angle-double-up";
        }
        isMenuClose = false;
    } else {
        for (var i = 0; i < dataSize; i++) {
            document.getElementById("toggleAllMenu-btn").innerHTML = '全部展開';
            document.getElementById("menus-" + i).style.display = 'none';
            document.getElementById("fa-toggle-" + i).className = "fa fa-angle-double-down";
        }
        isMenuClose = true;
    }
}
</script>