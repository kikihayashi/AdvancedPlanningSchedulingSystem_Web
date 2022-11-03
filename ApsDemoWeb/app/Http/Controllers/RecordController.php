<?php

namespace App\Http\Controllers;

use App\Http\Traits\BaseTool;
use App\Http\Traits\MesApiTool;
use App\Models\Period;
use App\Models\ShippingMonth;
use App\Models\ShippingYear;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

class RecordController extends Controller
{
    use BaseTool, MesApiTool;

    /**
     * 變更紀錄管理------------------------------------------------------------------------------
     */
    //讀取
    public function showRecordPage()
    {
        //使用者切換其他期別用(排列：期別大->小)
        $periods = Period::orderBy('period_tw', 'desc')->get()->toArray();

        //本次期別標題
        $data['title'] = "變更紀錄管理";
        //下拉式選單(選擇其他期別)
        $data['period'] = $periods;
        //顯示月度用
        $data['monthMaps'] = $this->monthMaps;

        return view('system.maintainMenu.record', [
            'selection' => 'system',
            'openMenu' => 'maintainMenu',
            'visitedId' => 'record',
            'tableData' => $data]);
    }

    //取得篩選條件的所有版本
    public function ajaxFetchVersion(Request $request)
    {
        $projectType = $request->projectType;
        $period = $request->period;
        $month = $request->month;
        $tableName = '';

        switch ($projectType) {
            case 'M':
                $tableName = 'management';
                $month = 0;
                break;
            case 'PY':
                $tableName = 'production_year';
                $month = 0;
                break;
            case 'SY':
                $tableName = 'shipping_year';
                $month = 0;
                break;
            case 'PM':
                $tableName = 'production_month';
                break;
            case 'SM':
                $tableName = 'shipping_month';
                break;
        }

        $projectArray = array();
        if ($projectType == 'M' || $projectType == 'PY' || $projectType == 'SY') {
            $projectArray = DB::table($tableName)
                ->select('version', 'created_at')
                ->where('period', $period)
                ->groupBy('version', 'created_at')
                ->get();
        } else {
            $projectArray = DB::table($tableName)
                ->select('version', 'created_at')
                ->where('period', $period)
                ->where('month', $month)
                ->groupBy('version', 'created_at')
                ->get();
        }

        $responseArray = array();
        foreach ($projectArray as $project) {
            if (!array_key_exists($project->version, $responseArray)) {
                //date轉換為Unix時間戳
                $timestamp = strtotime(date_format(date_create($project->created_at), "Y/m/d H:i:s"));
                //轉成台灣時區
                $responseArray[$project->version]['date'] = date("Y-m-d H:i:s", ($timestamp + 8 * 3600));
                $responseArray[$project->version]['status'] = "";
                $responseArray[$project->version]['info'] = "";
            }
        }

        if (count($responseArray) > 0) {
            $status = '';
            $info = '';
            $progress = $this->getProjectProgress($projectType, $period, $month);
            switch ($progress['progress_point']) {
                case 0:
                    if ($progress['version'] == 0) {
                        $status = '正常';
                        $info = 0;
                    } else {
                        $status = '結案';
                        $info = ($progress['version'] - 1) . ' -> ' . $progress['version'];
                    }
                    break;
                case 1:
                    $status = '未審核';
                    $info = '版本修訂中';
                    break;
                case 2:
                    $status = '課長審核中';
                    $info = '版本修訂中';
                    break;
                case 3:
                    $status = '經副理審核中';
                    $info = '版本修訂中';
                    break;
                case 4:
                    $status = '執行董事審核中';
                    $info = '版本修訂中';
                    break;
            }
            $responseArray[$progress['version']]['status'] = $status;
            $responseArray[$progress['version']]['info'] = $info;
        }
        echo json_encode($responseArray);
        exit;
    }

    //取得版本紀錄資料
    public function ajaxFetchRecord(Request $request)
    {
        $projectType = $request->projectType;
        $period = $request->period;
        $month = $request->month;
        $version = $request->version;

        $responseArray = array();
        switch ($projectType) {
            case 'M':
                $responseArray = $this->getManagementRecord($version, $period);
                break;
            case 'PY':
                $responseArray = $this->getProductionYearRecord($version, $period);
                break;
            case 'SY':
                $responseArray = $this->getShippingYearRecord($version, $period);
                break;
            case 'PM':
                $responseArray = $this->getProductionMonthRecord($version, $period, $month);
                break;
            case 'SM':
                $responseArray = $this->getShippingMonthRecord($version, $period, $month);
                break;
        }
        echo json_encode($responseArray);
        exit;
    }

    //取得大計畫紀錄
    private function getManagementRecord($version, $period): array
    {
        //取得特定版本、期別的大計畫資料
        $sqlCommand = "SELECT management.*, parameter_transport.name AS transportName
         FROM management LEFT JOIN parameter_transport
         ON management.transport_id = parameter_transport.id
         WHERE version = ':version' AND period = ':period'";

        //替換對應值
        $sqlCommand = str_replace(':version', $version, $sqlCommand);
        $sqlCommand = str_replace(':period', $period, $sqlCommand);
        $projects = DB::select($sqlCommand);

        //將DB查詢到的資料轉成array
        $managements = array_map(function ($itemObject) {
            $itemArray = (array) $itemObject;
            //date轉換為Unix時間戳
            $timestamp = strtotime(date_format(date_create($itemArray['updated_at']), "Y/m/d H:i:s"));
            //轉成台灣時區
            $itemArray['updated_at_tw'] = date("Y-m-d H:i:s", ($timestamp + 8 * 3600));
            return $itemArray;
        }, $projects);

        $tableData['management'] = $managements;
        $tableData['monthMaps'] = $this->monthMaps;

        $html = view('system.maintainMenu.recordDetail.management_record')
            ->with(compact('tableData'))
            ->render();

        return array('html' => $html);
    }

    //取得年度生產計畫紀錄
    private function getProductionYearRecord($version, $period): array
    {
        //年度生產計畫
        $sqlCommand = "SELECT production_year.*,
                        COALESCE(equipment.line,'0') AS line_no,
                        COALESCE(equipment.is_hidden,'N') AS is_hidden
                        FROM production_year LEFT JOIN equipment
                        ON production_year.item_code = equipment.item_code
                        WHERE  version = ':version' AND period = ':period'";

        //替換對應值
        $sqlCommand = str_replace(':version', $version, $sqlCommand);
        $sqlCommand = str_replace(':period', $period, $sqlCommand);

        //年度生產計畫列表
        $projects = DB::select($sqlCommand);
        //將DB查詢到的資料轉成array
        $productionYears = array_map(function ($itemObject) {
            $itemArray = (array) $itemObject;
            //date轉換為Unix時間戳
            $timestamp = strtotime(date_format(date_create($itemArray['updated_at']), "Y/m/d H:i:s"));
            //轉成台灣時區
            $itemArray['updated_at_tw'] = date("Y-m-d H:i:s", ($timestamp + 8 * 3600));
            return $itemArray;
        }, $projects);

        $tableData['productionYear'] = $productionYears;
        $tableData['monthMaps'] = $this->monthMaps;

        $html = view('system.maintainMenu.recordDetail.productionYear_record')
            ->with(compact('tableData'))
            ->render();

        return array('html' => $html);
    }

    //取得年度出荷計畫紀錄
    private function getShippingYearRecord($version, $period): array
    {
        //年度出荷計畫
        $sqlCommand = "SELECT shipping_year.*,
                        parameter_transport.name AS transportName, 
                        parameter_transport.abbreviation AS abbreviation  
                        FROM shipping_year LEFT JOIN parameter_transport 
                        ON shipping_year.transport_id = parameter_transport.id 
                        WHERE  version = ':version' AND period = ':period'
                        ORDER BY item_code ASC, lot_no ASC";

        //替換對應值
        $sqlCommand = str_replace(':version', $version, $sqlCommand);
        $sqlCommand = str_replace(':period', $period, $sqlCommand);

        //年度出荷計畫列表
        $projects = DB::select($sqlCommand);

        //將DB查詢到的資料轉成array
        $shippingYears = array_map(function ($itemObject) {
            $itemArray = (array) $itemObject;
            //date轉換為Unix時間戳
            $timestamp = strtotime(date_format(date_create($itemArray['updated_at']), "Y/m/d H:i:s"));
            //轉成台灣時區
            $itemArray['updated_at_tw'] = date("Y-m-d H:i:s", ($timestamp + 8 * 3600));
            return $itemArray;
        }, $projects);

        $tableData['shippingYear'] = $shippingYears;
        $tableData['monthMaps'] = $this->monthMaps;

        $html = view('system.maintainMenu.recordDetail.shippingYear_record')
            ->with(compact('tableData'))
            ->render();

        return array('html' => $html);
    }

    //取得月度生產計畫紀錄
    private function getProductionMonthRecord($version, $period, $month): array
    {
        //取得特定版本、期別的大計畫資料
        $sqlCommand = "SELECT production_month.*,
        COALESCE(equipment.line,'0') AS line_no
        FROM production_month LEFT JOIN equipment
        ON production_month.item_code = equipment.item_code
        WHERE version = ':version' AND period = ':period' AND month = ':month'";

        //替換對應值
        $sqlCommand = str_replace(':version', $version, $sqlCommand);
        $sqlCommand = str_replace(':period', $period, $sqlCommand);
        $sqlCommand = str_replace(':month', $month, $sqlCommand);
        $projects = DB::select($sqlCommand);

        //將DB查詢到的資料轉成array
        $productionMonths = array_map(function ($itemObject) {
            //將DB查詢到的資料轉成array
            $itemArray = (array) $itemObject;
            //根據start_day_array、end_day_array字串，新增start、end陣列(畫面顯示用)
            $itemArray['start'] = array_map('intval', explode(',', $itemArray['start_day_array']));
            $itemArray['end'] = array_map('intval', explode(',', $itemArray['end_day_array']));
            //date轉換為Unix時間戳
            $timestamp = strtotime(date_format(date_create($itemArray['updated_at']), "Y/m/d H:i:s"));
            //轉成台灣時區
            $itemArray['updated_at_tw'] = date("Y-m-d H:i:s", ($timestamp + 8 * 3600));
            return $itemArray;
        }, $projects);

        $tableData['productionMonth'] = $productionMonths;

        $html = view('system.maintainMenu.recordDetail.productionMonth_record')
            ->with(compact('tableData'))
            ->render();

        return array('html' => $html);
    }

    //取得月度出荷計畫紀錄
    private function getShippingMonthRecord($version, $period, $month): array
    {
        //顯示日期用
        $dateArray = ShippingMonth::join('parameter_transport', 'shipping_month.transport_id', '=', DB::raw("CAST(parameter_transport.id AS VARCHAR)"))
            ->selectRaw('date , transport_id, parameter_transport.name AS transportName, parameter_transport.abbreviation AS abbreviation')
            ->where('version', $version)
            ->where('period', $period)
            ->where('date', '!=', 0) //把除了日期為0以外的日期取出來
            ->groupBy('date', 'transport_id', 'parameter_transport.name', 'parameter_transport.abbreviation') //只需要不重複的日期和出荷方式
            ->orderBy('date')
            ->orderBy('transport_id')
            ->get()
            ->toArray();

        //顯示機種用
        $itemCodeArray = ShippingMonth::selectRaw('item_code')
            ->where('version', $version)
            ->where('period', $period)
            ->where('month', $month)
            ->where('number', '!=', 0)
            ->groupBy('item_code')
            ->get()
            ->toArray();

        //月度計畫
        $sqlCommand = "SELECT shipping_month.*,
        COALESCE(equipment.line,'0') AS line_no,
        parameter_transport.abbreviation AS abbreviation,
        parameter_transport.name AS transportName
        FROM shipping_month LEFT JOIN equipment
        ON shipping_month.item_code = equipment.item_code
        LEFT JOIN parameter_transport ON shipping_month.transport_id = parameter_transport.id
        WHERE version = ':version' AND period = ':period' AND month = ':month'
        AND date <> 0 AND number <> 0 ORDER BY item_code";

        //替換對應值
        $sqlCommand = str_replace(':version', $version, $sqlCommand);
        $sqlCommand = str_replace(':period', $period, $sqlCommand);
        $sqlCommand = str_replace(':month', $month, $sqlCommand);

        //月度出荷計畫列表(含線別、內藏)
        $projects = DB::select($sqlCommand);

        //將DB查詢到的資料轉成array
        $shippingMonths = array_map(function ($itemObject) {
            $itemArray = (array) $itemObject;
            //date轉換為Unix時間戳
            $timestamp = strtotime(date_format(date_create($itemArray['updated_at']), "Y/m/d H:i:s"));
            //轉成台灣時區
            $itemArray['updated_at_tw'] = date("Y-m-d H:i:s", ($timestamp + 8 * 3600));
            return $itemArray;
        }, $projects);

        $tableData['dateArray'] = $dateArray;
        $tableData['itemCodeArray'] = $itemCodeArray;
        $tableData['shippingMonth'] = $shippingMonths;

        $html = view('system.maintainMenu.recordDetail.shippingMonth_record')
            ->with(compact('tableData'))
            ->render();

        return array('html' => $html);
    }
}
