@extends('system.basicMenu.periodDetail.period_content')
@section('inner_content')
@include('layouts.script.jq_dataTable_layout')
<!-- <span id="title-span">台京第{{$period_tw}}期</span>
<div class="line" style="margin:9px 0;"></div> -->

<!-- 此處用來判斷當前是點選哪個TAB，顯示對應的內容，預設是上半年 -->
@php
$selectPageYear = session('selectPageYear') ?? 'first';
@endphp

<!-- 上、下半年的資料 -->
<section id="section-half-a-year">
    <div class="div-nav-tabs">
        <ul class="nav nav-tabs">
            <!-- 上、下半年的TAB -->
            @foreach($yearMaps as $yearMap)
            <li class="nav-item">
                <a id="nav-link-a" class="nav-link {{($selectPageYear==$yearMap['page'])? 'active':''}}"
                    data-toggle="tab" href="#{{$yearMap['page']}}-half-page">{{$yearMap['name']}}</a>
            </li>
            @endforeach
        </ul>
        <div class="tab-content" id="partition-tab-content">
            @foreach($yearMaps as $yearMap)
            <!-- 上、下半年的內容 -->
            <div class="tab-pane fade in {{($selectPageYear==$yearMap['page'])? 'active':''}}"
                id="{{$yearMap['page']}}-half-page">
                <table id="{{$yearMap['page']}}-year-table" class="table table-bordered">
                    <thead style="background-color:#F5F6FB">
                        <tr>
                            <th id="partition-th" colspan="10">台京{{$period_tw}}期-{{$yearMap['name']}}</th>
                        </tr>
                        <tr>
                            <th id="partition-th" style="width:14%" rowspan="2">機種名稱</th>
                            <th id="partition-th" colspan="3">材料費</th>
                            <th id="partition-th" colspan="3">加工費</th>
                            <th id="partition-th" style="width:10%" rowspan="2">台灣工時</th>
                            <th id="partition-th" style="width:7%">成本</th>
                            <th id="partition-th" style="width:12%" rowspan="2">F.O.B PRICE</th>
                        </tr>
                        <tr>
                            <th id="partition-th">&lt;A&gt;-日 </th>
                            <th id="partition-th">&lt;B&gt;-台</th>
                            <th id="partition-th">&lt;C&gt;=A+B</th>
                            <th id="partition-th">&lt;D&gt; 工數</th>
                            <th id="partition-th">加工費率</th>
                            <th id="partition-th">E = 金額</th>
                            <th id="partition-th">C+E</th>
                        </tr>
                    </thead>
                </table>
            </div>
            @endforeach
        </div>
    </div>
</section>

<!-- DataTable設置 -->
<script type="text/javascript">
$jq_dataTable(document).ready(function() {
    @foreach($yearMaps as $yearMap)
    $jq_dataTable('#{{$yearMap["page"]}}-year-table').DataTable({
        //方法一(取全部資料)
        data: @json($tableData[$yearMap['page']]),//資料
        // 方法二(用Ajax取資料，每次取一頁並回傳)
        // ajax: {
        //     type: "POST",
        //     url: "{{route('PeriodController.ajaxFetchPartition')}}",
        //     headers: {
        //         'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        //     },
        //     data: {
        //         type: "{{$yearMap['page']}}",
        //         period_tw: "{{$period_tw}}"
        //     },
        // },
        info: false, //關閉顯示搜尋結果
        ordering: true, //是否要做排序，不要做排序的話，controller裡面的排序資料要註解
        pageLength: 10, //初始每頁顯示幾筆資料
        pagingType: "simple_numbers", //'Next' and 'Last' buttons, page numbers
        bLengthChange: false, //關閉顯示幾項圖示
        processing: false, //顯示處理中，使用方法二時可開啟
        serverSide: false, //啟用ServerSide模式，使用方法二時要開啟
        bAutoWidth: false, //是否啟用自動適應列寬，針對有Tab的頁面，這樣<th>的設定，在轉換Tab時不會不一樣
        lengthChange: true, //是否啟動改變每頁顯示幾筆資料的功能
        lengthMenu: [ //改變每頁顯示幾筆資料設置
            [10, 25, 50, -1],
            [10, 25, 50, "All"]
        ],
        columnDefs: [{ //欄位設置
            className: 'text-right',//資料對齊右邊
            orderable: false,//不排序
            targets: [1, 2, 3, 4, 5, 6, 7, 8, 9]//指定哪些欄位不排序、對齊右邊
        }],
        //客製化出按鈕、語系可參考：https://ithelp.ithome.com.tw/articles/10272813
        language: {
            "lengthMenu": "顯示 _MENU_ 筆資料",
            "search": "", //不顯示搜尋兩個字
            "searchPlaceholder": "查詢機種名稱",
            "sProcessing": "<div class='loader'></div>", //客製處理中動畫
            "sZeroRecords": "没有資料",
            "oPaginate": {
                "sFirst": "<<",
                "sPrevious": "<",
                "sNext": ">",
                "sLast": ">>"
            },
        },
        //使用方法二才需要加
        // columns: [
        //     {
        //         data: 'ProductNo'
        //     },
        //     {
        //         data: 'JPMaterial'
        //     },
        //     {
        //         data: 'TWMaterial'
        //     },
        //     {
        //         data: 'TotalMaterial',
        //     },
        //     {
        //         data: 'WorkHour',
        //     },
        //     {
        //         data: 'WorkingRate',
        //     },
        //     {
        //         data: 'WorkAmount',
        //     },
        //     {
        //         data: 'WorkHourTw',
        //     },
        //     {
        //         data: 'Cost',
        //     },
        //     {
        //         data: 'FOBPrice',
        //     },
        // ],
    });
    @endforeach
});
</script>
@stop