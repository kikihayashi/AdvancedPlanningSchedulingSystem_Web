<script>
let projectType = '{{$projectType}}';
let hasZeroLineNo = '{{$hasZeroLineNo}}';
let progressPoint = '{{$point}}';
let dataNumber = '{{$data_number}}';
let can_operation = '{{$can_operation}}';
let can_project_crud = '{{$can_project_crud}}';

//產生計劃表
function createProjectBtn() {
    if (can_project_crud) {
        if (progressPoint > 1) {
            alert('計畫審核中，不可產生計畫表！');
            return false;
        }

        var message = "";
        switch (projectType) {
            case 'PY':
                message = '年度生產';
                break;
            case 'SY':
                message = '年度出荷';
                break;
            case 'PM':
                message = '月度生產';
                break;
            case 'SM':
                message = '月度出荷';
                break;
        }

        if (confirm("確定產生【" + message + "計畫】表嗎？")) {
            var data_number = "{{$data_number}}";
            if (data_number > 0) {
                if (confirm("【警告】計畫目前已存在資料\n產生計畫表將覆蓋當前全部資料，確定執行？")) {
                    execute();
                } else {
                    return false;
                }
            } else {
                execute();
            }
        } else {
            return false;
        }
    } else {
        alert('無操作權限');
        return false;
    }
}

//執行產生計畫表
function execute() {
    var period = "{{$period_tw}}";
    var month = "{{$month}}";
    var version = "{{$version}}";
    var url =
        "{{route('ProjectController.createProject',['projectType'=>':projectType', 'period_tw' => ':period', 'month' => ':month', 'version' => ':version'])}}";
    url = url.replace(':projectType', projectType);
    url = url.replace(':period', period);
    url = url.replace(':month', month);
    url = url.replace(':version', version);
    document.getElementById('formProject').action = url;
    document.getElementById('formProject').submit();
}

//匯出Excel
function exportProjectBtn(projectType) {
    switch (projectType) {
        case 'M':
            message = '大計畫';
            break;
        case 'PY':
            message = '年度生產計畫';
            break;
        case 'SY':
            message = '年度出荷計畫';
            break;
        case 'PM':
            message = '月度生產計畫';
            break;
        case 'SM':
            message = '月度出荷計畫';
            break;
    }
    if (confirm("確定匯出" + message + "嗎？")) {
        var period = "{{$period_tw}}";
        var month = "{{$month}}";
        var url =
            "{{route('ProjectController.exportProject',['projectType'=>':projectType', 'period_tw' => ':period', 'month' => ':month'])}}";
        url = url.replace(':projectType', projectType);
        url = url.replace(':period', period);
        url = url.replace(':month', month);
        document.getElementById('formProject').action = url;
        document.getElementById('formProject').submit();
    }
}

//核准大計劃維護
function reviewProjectBtn(operation) {
    if (can_operation) {
        var message = "";
        switch (operation) {
            case 'send':
                message = '提出版本修訂';
                break;
            case 'submit':
                message = '送出審核';
                break;
            case 'cancel':
                message = '放棄變更';
                break;
            case 'approve':
                message = '核准';
                break;
            case 'reject':
                message = '駁回';
                break;
        }
        if (confirm("確定" + message + "嗎？")) {
            if (operation == 'submit' && dataNumber == 0) {
                alert('尚無計劃資料，無法送出審核！');
                return false;
            }
            if (operation == 'submit' && hasZeroLineNo) {
                alert('有機種尚未設定線別，無法送出審核！');
                return false;
            }
            var period = "{{$period_tw}}";
            var month = "{{$month}}";
            var url =
                "{{route('ProjectController.reviewProject',['projectType'=>':projectType', 'period_tw' => ':period', 'month' => ':month', 'operation' => ':operation'])}}";
            url = url.replace(':projectType', projectType);
            url = url.replace(':period', period);
            url = url.replace(':month', month);
            url = url.replace(':operation', operation);
            document.getElementById('formProject').action = url;
            document.getElementById('formProject').submit();
        } else {
            return false;
        }
    } else {
        alert('無操作權限');
        return false;
    }
}
</script>

<div style="float:right;margin-top:-75px;margin-right:70px;">
    <table class="table project-table">
        <tbody>
            <tr>
                <form id="formProject" style="display:none;" method="POST">
                    @csrf
                    @method('POST')
                </form>
                @if($point < 1 && $can_operation) <td>
                    <button type="button" class="btn btn-primary" id="project-btn-submit"
                        onclick="reviewProjectBtn('send');">提出版本修訂</button>
                    </td>
                    @endif

                    @if($point > 0 && $can_operation && $can_create_project) <td>
                        <button type="button" class="btn btn-primary" id="project-btn-create"
                            onclick="createProjectBtn();">產生計畫表</button>
                    </td>
                    @endif

                    @if($point > 0 && $can_operation)
                    <td class="dropdown">
                        <button type="button" class="btn btn-info dropdown-toggle" aria-haspopup="true"
                            aria-expanded="false">審核&ensp;<i class="fa fa-caret-down"></i></button>
                        <ul class="dropdown-menu">

                            @if($point == 1)
                            <li><a class="dropdown-item" href="#" onclick="reviewProjectBtn('submit');">送出審核</a>
                            </li>
                            <li><a class="dropdown-item" href="#" onclick="reviewProjectBtn('cancel');"
                                    style="color:red">放棄變更</a>
                            </li>
                            @else
                            <li><a class="dropdown-item" href="#" onclick="reviewProjectBtn('approve');">核准</a>
                            </li>
                            <li><a class="dropdown-item" href="#" onclick="reviewProjectBtn('reject');"
                                    style="color:red">駁回</a>
                            </li>
                            @endif
                        </ul>
                    </td>
                    @endif

                    <td class="dropdown">
                        <button type="button" class="btn btn-success dropdown-toggle" id="project-btn-excel"
                            aria-haspopup="true" aria-expanded="false">Excel下載&ensp;
                            <i class="fa fa-caret-down"></i></button>
                        <ul class="dropdown-menu">
                            @if($projectType =='M' || $projectType=='PY' || $projectType=='SY')
                            <li><a class="dropdown-item" href="#" onclick="exportProjectBtn('M')">大計劃</a></li>
                            <li><a class="dropdown-item" href="#" onclick="exportProjectBtn('PY')">年度生產計畫</a></li>
                            <li><a class="dropdown-item" href="#" onclick="exportProjectBtn('SY')">年度出荷計畫</a></li>
                            @else
                            <li><a class="dropdown-item" href="#" onclick="exportProjectBtn('PM')">月度生產計畫</a></li>
                            <li><a class="dropdown-item" href="#" onclick="exportProjectBtn('SM')">月度出荷計畫</a></li>
                            @endif
                        </ul>
                    </td>
            </tr>
        </tbody>
    </table>
</div>