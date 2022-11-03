<?php
namespace App\Http\Traits;

use App\Models\Period;
use App\Models\Progress;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Validator;

trait BaseTool
{
    //年度Tab用
    protected $yearMaps = array(
        0 => array(
            'name' => "上半年",
            'page' => "first"),
        1 => array(
            'name' => "下半年",
            'page' => "last"),
    );
    //月度Tab用
    protected $monthMaps = array(
        0 => array(
            'name' => "4月",
            'page' => 4,
            "yearType" => "first"),
        1 => array(
            'name' => "5月",
            'page' => 5,
            "yearType" => "first"),
        2 => array(
            'name' => "6月",
            'page' => 6,
            "yearType" => "first"),
        3 => array(
            'name' => "7月",
            'page' => 7,
            "yearType" => "first"),
        4 => array(
            'name' => "8月",
            'page' => 8,
            "yearType" => "first"),
        5 => array(
            'name' => "9月",
            'page' => 9,
            "yearType" => "first"),
        6 => array(
            'name' => "10月",
            'page' => 10,
            "yearType" => "last"),
        7 => array(
            'name' => "11月",
            'page' => 11,
            "yearType" => "last"),
        8 => array(
            'name' => "12月",
            'page' => 12,
            "yearType" => "last"),
        9 => array(
            'name' => "1月",
            'page' => 1,
            "yearType" => "last"),
        10 => array(
            'name' => "2月",
            'page' => 2,
            "yearType" => "last"),
        11 => array(
            'name' => "3月",
            'page' => 3,
            "yearType" => "last"),
    );

    //角色設定需要
    protected $initPermissionArray = array('A', 'W', 'S', 'M', 'D', 'N');

    //權限設定需要
    protected $permissionInfoArray = array(
        0 => array(
            'name' => 'worker_operation',
            'operation' => '版本修訂<br>送出審核'),
        1 => array(
            'name' => 'supervisor_operation',
            'operation' => '課長<br>審核'),
        2 => array(
            'name' => 'manager_operation',
            'operation' => '經副理<br>審核'),
        3 => array(
            'name' => 'director_operation',
            'operation' => '執行董事<br>審核'),
        4 => array(
            'name' => 'project_crud',
            'operation' => '大計畫<br>新增刪除修改'),
        5 => array(
            'name' => 'identity_crud',
            'operation' => '身分識別<br>新增刪除修改'),
        6 => array(
            'name' => 'basic_crud',
            'operation' => '基本資料<br>新增刪除修改'),
        7 => array(
            'name' => 'maintain_crud',
            'operation' => '資料維護<br>新增刪除修改'),
        8 => array(
            'name' => 'period_delete',
            'operation' => '期別<br>刪除'),
    );

    //簽章設定需要
    protected $projectTypeArray = array(
        'PY' => array(
            'name' => '年度生產計劃',
            'title' => array('作成', '審查', '客戶承認'),
            'department' => array('', '', ''),
            'user' => array('', '', '')),
        'SY' => array(
            'name' => '年度出荷計劃',
            'title' => array('作成', '審查', '審查', '客戶承認'),
            'department' => array('', '', '', ''),
            'user' => array('', '', '', '')),
        'PM' => array(
            'name' => '月度生產計劃',
            'title' => array('作成', '審查'),
            'department' => array('', ''),
            'user' => array('', '')),
        'SM' => array(
            'name' => '月度出荷計劃',
            'title' => array('作成', '審查', '審查', '客戶承認'),
            'department' => array('', '', '', ''),
            'user' => array('', '', '', '')),
    );

    //匯出Excel報表需要
    protected function getLetterMap(): array
    {
        //產生Excel欄位A~AAA
        $letter = 'A';
        $letterAscii = ord($letter);
        while ($letter !== 'AAA') {
            $letterMap[$letterAscii++] = $letter++;
        }
        return $letterMap;
    }

    //取得目前期別(要是輸入不存在的，一律回傳最新期別)
    protected function getProjectPeriod($period_tw, $latestPeriod_tw)
    {
        if (!is_numeric($period_tw)) {
            return Period::where('period_tw', $latestPeriod_tw)->first();
        }
        return Period::where('period_tw', $period_tw)->first() ??
        Period::where('period_tw', $latestPeriod_tw)->first();
    }

    //取得目前進度用
    protected function getProjectProgress($projectType, $period_tw, $month): array
    {
        //取得大計劃管理的進度
        $progress = Progress::where('project_name', 'M')
            ->where('period', $period_tw)
            ->first();

        //如果大計劃管理無進度資料卻進入計畫頁面，要設定預設值
        $progressArray = ($progress != null) ? $progress->toArray() :
        array(
            "version" => "0",
            "period" => $period_tw,
            "month" => $month,
            "progress_point" => "0",
            "create_project" => "N",
        );  //大計畫永遠 = N

        //如果當前是大計劃管理
        if ($projectType == 'M') {
            return $progressArray;
        }
        //如果當前是年度、月度的生產、出荷計劃
        else {
            //取得大計劃管理的版本，如果大計劃管理無進度資料，設定版本=0
            $version = ($progress != null) ? $progressArray['version'] : 0;

            //取得當前計畫的進度
            $progressNow = Progress::where('project_name', $projectType)
                ->where('period', $period_tw)
                ->where('month', $month)
                ->first();

            //如果progress沒計畫資料卻進入計畫頁面，設定初始預設值
            $progressArrayNow = ($progressNow != null) ? $progressNow->toArray() :
            array(
                "version" => "0",
                "period" => $period_tw,
                "month" => $month,
                "progress_point" => "0",
                "create_project" => ($version > 0) ? "Y" : "N",
            ); //如果版本大於1，代表可以產生計畫表

            return $progressArrayNow;
        }
    }

    //取得目前帳號的權限
    protected function getUserPermission(): array
    {
        //獲取使用者目前權限
        $user = User::where('users.id', Auth::id())
            ->join('roles', 'users.role_id', '=', DB::raw("CAST(roles.id AS VARCHAR)"))
            ->join('permission', 'roles.permission_code', '=', DB::raw("CAST(permission.code AS VARCHAR)"))
            ->selectRaw('users.* , permission.*')
            ->first();

        //存入權限陣列
        $permission[0] = $user->worker_operation;
        $permission[1] = $user->worker_operation;
        $permission[2] = $user->supervisor_operation;
        $permission[3] = $user->manager_operation;
        $permission[4] = $user->director_operation;
        foreach ($this->permissionInfoArray as $permissionInfo) {
            $permission[$permissionInfo['name']] = $user->{$permissionInfo['name']};
        }
        return $permission;
    }

    //驗證操作是否合法
    protected function checkOperationValid($id, $permissionName)
    {
        //如果id非數字
        if (!is_numeric($id)) {
            return ['code' => 404, 'message' => '找不到此頁面！'];
        } else {
            if (str_contains($id, '.')) {
                return ['code' => 404, 'message' => '找不到此頁面！'];
            }
        }
        //如果使用者沒有操作權限
        if ($this->getUserPermission()[$permissionName] == 'N') {
            return ['code' => 403, 'message' => '沒有權限檢視此頁面！'];
        }
        return ['code' => 200, 'message' => '合法'];
    }
}
