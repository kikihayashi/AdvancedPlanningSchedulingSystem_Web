<!-- 建立DataTable需要引用的來源，有多個分頁會用到 --->
<!-- CSS -->
<!-- <link rel="stylesheet" type="text/css" href="{{asset('DataTables/datatables.min.css')}}"> -->
<!-- Datatables CSS CDN -->
<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.10.21/css/jquery.dataTables.min.css">
<!-- Script -->
<!-- <script src="{{asset('jquery-3.4.1.min.js')}}" type="text/javascript"></script> -->
<!-- <script src="{{asset('DataTables/datatables.min.js')}}" type="text/javascript"></script> -->
<!-- jQuery CDN -->
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
<!-- Datatables JS CDN -->
<script type="text/javascript" src="https://cdn.datatables.net/1.10.21/js/jquery.dataTables.min.js"></script>
<!-- 避免衝突用 -->
<script>
var $jq_dataTable = jQuery.noConflict(true);
</script>