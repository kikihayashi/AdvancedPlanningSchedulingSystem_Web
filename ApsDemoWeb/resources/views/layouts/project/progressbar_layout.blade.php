<script>
let tableProgress = @json($tableData['progress']);

document.addEventListener("DOMContentLoaded", function(event) {
    //如果進度點為0，隱藏進度條
    if (tableProgress['progress_point'] == 0) {
        $('#section-progress').hide();
    } else {
        $('#section-progress').show();
    }
    //進度條、點設置
    var dotClass = '';
    var lineClass = '';
    var titleClass = '';
    var contentClass = '';
    for (point = 1; point <= 4; point++) {
        if (tableProgress['progress_point'] < point) {
            dotClass = "yet";
            lineClass = "yet";
            titleClass = "yet";
            contentClass = "yet";
        } else if (tableProgress['progress_point'] > point) {
            dotClass = "finish";
            lineClass = "finish";
            titleClass = "finish";
            contentClass = "finish";
        } else {
            dotClass = "now";
            lineClass = "finish";
            titleClass = "now";
            contentClass = "now";
        }
        $('#progress-dot-' + point).toggleClass(dotClass);
        if (point > 1) {
            $('#progress-line-' + (point - 1)).toggleClass(lineClass);
        }
        $('#progress-title-' + point).toggleClass(titleClass);
        $('#progress-content-' + point).toggleClass(contentClass);
    }
});
</script>

<section id="section-progress" style="margin-bottom:15px;display:none;">
    <table class="table progress-table">
        <tbody>
            <tr>
                <td style="width:15%"></td>
                <td style="width:1%;padding:0">
                    <span id="progress-dot-1" class="progress-dot"></span>
                </td>
                <td style="width:22%;padding:2px">
                    <span id="progress-line-1" class="progress-line"></span>
                </td>
                <td style="width:1%;padding:0">
                    <span id="progress-dot-2" class="progress-dot"></span>
                </td>
                <td style="width:22%;padding:2px">
                    <span id="progress-line-2" class="progress-line"></span>
                </td>
                <td style="width:1%;padding:0">
                    <span id="progress-dot-3" class="progress-dot"></span>
                </td>
                <td style="width:22%;padding:2px">
                    <span id="progress-line-3" class="progress-line"></span>
                </td>
                <td style="width:1%;padding:0">
                    <span id="progress-dot-4" class="progress-dot"></span>
                </td>
                <td style="width:15%"></td>
            </tr>
        </tbody>
    </table>

    <table class="table progress-table">
        <tbody>
            <tr>
                <td style="width:5%">
                </td>
                <td style="width:19%">
                    <span id="progress-title-1" class="progress-title">
                        提出版本修訂
                    </span>
                </td>
                <td style="width:5%">
                </td>
                <td style="width:18%">
                    <span id="progress-title-2" class="progress-title">
                        課長確認
                    </span>
                </td>
                <td style="width:5%">
                </td>
                <td style="width:20%">
                    <span id="progress-title-3" class="progress-title">
                        經副理審核
                    </span>
                </td>
                <td style="width:5%">
                </td>
                <td style="width:18%">
                    <span id="progress-title-4" class="progress-title">
                        執行董事承認
                    </span>
                </td>
                <td style="width:5%">
                </td>
            </tr>
            @if($tableData['progress']['progress_point'] > 1)
            <tr>
                <td style="width:5%">
                </td>
                <td>
                    <span id="progress-content-1" class="progress-content">
                        @if($tableData['progress']['progress_point'] > 1)
                        送出審核
                        @endif
                    </span>
                </td>
                <td style="width:5%">
                </td>
                <td>
                    <span id="progress-content-2" class="progress-content">
                        @if($tableData['progress']['progress_point'] > 2)
                        核可
                        @elseif($tableData['progress']['progress_point'] = 2)
                        審核中
                        @endif
                    </span>
                </td>
                <td style="width:5%">
                </td>
                <td>
                    <span id="progress-content-3" class="progress-content">
                        @if($tableData['progress']['progress_point'] > 3)
                        變更結案(日京)
                        @elseif($tableData['progress']['progress_point'] = 3)
                        審核中
                        @endif
                    </span>
                </td>
                <td style="width:5%">
                </td>
                <td>
                    <span id="progress-content-4" class="progress-content">
                        @if($tableData['progress']['progress_point'] = 4)
                        審核中
                        @endif
                    </span>
                </td>
                <td style="width:5%">
                </td>
            </tr>
            @endif
        </tbody>
    </table>
</section>