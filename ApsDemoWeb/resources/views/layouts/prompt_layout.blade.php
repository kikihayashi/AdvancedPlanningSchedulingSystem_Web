<script>
//載入頁面時，自動執行
document.addEventListener("DOMContentLoaded", function(event) {
    $(window).mouseup(function(){
       $("#system-session").hide();
    });
});
</script>
<!--  這個是提示視窗，如果有session資訊，就顯示出來 -->
@if(session()->has('message') || isset($message))
<div id="system-session">
    <div style="margin-bottom:0px" class="alert alert-dismissible alert-success " role="alert">
        <button style="height:100%" type="button" class="close" data-dismiss="alert" aria-label="Close">
            <span aria-hidden="true">×</span></button>
        {{session('message') ?? $message}}
    </div>
</div>
@endif

@if(session()->has('errorMessage') || isset($errorMessage))
<div id="system-session">
    <div style="margin-bottom:0px" class="alert alert-dismissible alert-danger" role="alert">
        <button style="height:100%;" type="button" class="close" data-dismiss="alert" aria-label="Close">
            <span aria-hidden="true">×</span></button>
        {{session('errorMessage') ?? $errorMessage}}
    </div>
</div>
@endif