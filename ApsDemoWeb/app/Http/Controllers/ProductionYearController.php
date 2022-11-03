<?php

namespace App\Http\Controllers;

use App\Http\Traits\BaseTool;
use App\Http\Traits\MesApiTool;
use App\Http\Traits\ValidatorTool;
use App\Models\Period;
use App\Models\ProductionYear;
use App\Models\Schedule;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

class ProductionYearController extends Controller
{
    use BaseTool, MesApiTool, ValidatorTool;
    /**
     * 年度生產計劃------------------------------------------------------------------------------
     */
    //讀取
    //$period_tw代表選擇的期別，$selectTab代表選擇的分頁(列表、新增)
    public function showProductionYearPage($period_tw = null, $selectTab = 'projectList')
    {
        /**
         * 優先處理，後續都會用到
         */
        //使用者切換其他期別用(排列：期別大->小)
        $periods = Period::orderBy('period_tw', 'desc')->get();
        //顯示本次期別用(若輸入無效期別，預設使用資料庫中最大的期別)
        $thisTimePeriod = $this->getProjectPeriod($period_tw, $periods->get(0)->period_tw);
        //取得目前進度用
        $progress = $this->getProjectProgress('PY', $thisTimePeriod->period_tw, 0);
        //MES資料庫(取工數)
        $partition = $this->getPartition($progress['period']);

        /**
         * 計劃頁面
         */
        //取得行事曆
        $schedules = Schedule::join('parameter_calendar', 'schedule.calendar_id', '=', DB::raw("CAST(parameter_calendar.id AS VARCHAR)"))
            ->selectRaw('schedule.* , parameter_calendar.is_holiday AS is_holiday')
            ->get()
            ->toArray();

        //製作是否為假日的HashMap(key:YYYYMMDD，value:是否為假日)
        foreach ($schedules as $schedule) {
            $isHolidayMap[$schedule['year'] . $schedule['month'] . $schedule['date']] = $schedule['is_holiday'];
        }

        //將出勤日加到monthMaps裡面
        for ($index = 0; $index < count($this->monthMaps); $index++) {
            $monthMap = $this->monthMaps[$index];
            $month = $monthMap['page'];
            $year = intval($progress['period']) + (($month > 3) ? 1969 : 1970);
            $workDay = $this->countWorkDay($year, $month, $isHolidayMap ?? array());
            $this->monthMaps[$index]['workDay'] = $workDay;
            $this->monthMaps[$index]['totalDay'] = cal_days_in_month(CAL_GREGORIAN, $month, $year);
        }

        //年度計畫合併線別內藏，SQL指令：COALESCE改用ISNULL也可以，不過PostgreSQL、MySQL沒有ISNULL用法
        $sqlCommand = "SELECT production_year.*,
                        COALESCE(equipment.line,'0') AS line_no,
                        COALESCE(equipment.is_hidden,'N') AS is_hidden
                        FROM production_year LEFT JOIN equipment
                        ON production_year.item_code = equipment.item_code
                        WHERE  version = ':version' AND period = ':period'";

        //替換對應值
        $sqlCommand = str_replace(':version', $progress['version'], $sqlCommand);
        $sqlCommand = str_replace(':period', $progress['period'], $sqlCommand);

        //年度生產計畫列表(含線別、內藏)
        $projects = DB::select($sqlCommand);
        //將object轉成array，並加入partition將工數放入projects中
        $arrayProjects = array_map(function ($itemObject) use ($partition) {
            //將DB查詢到的資料轉成array
            $itemArray = (array) $itemObject;
            //工數
            $itemArray['firstWorkHour'] = ($partition[$itemArray['item_code']]['firstWorkHour'] ?? 0);
            $itemArray['lastWorkHour'] = ($partition[$itemArray['item_code']]['lastWorkHour'] ?? 0);
            return $itemArray;
        }, $projects);

        //製作年度生產計劃HashMap(key:line_no、value:project)
        $productionYear = array();
        $hasZeroLineNo = false;
        foreach ($arrayProjects as $project) {
            if ($project['line_no'] == 0) {
                $hasZeroLineNo = true;
            }
            $productionYear[$project['line_no']][] = $project;
        }

        //Line從小到大排列
        ksort($productionYear);

        //本次期別標題
        $data['title'] = "年度生產計劃 : 台京" . $progress['period'] . "期";
        //下拉式選單(選擇其他期別)
        $data['period'] = $periods;
        //顯示進度條用
        $data['progress'] = $progress;
        //取得ISO編號
        $data['iso'] = Setting::where('memo', 'iso_production_year')->first()->setting_value;
        //顯示分頁用
        $data['selectTab'] = (count($productionYear) == 0) ? 'projectList' : $selectTab;
        //本次選擇期別
        $data['thisTimePeriod'] = $thisTimePeriod;
        //顯示年度生產計畫
        $data['productionYear'] = $productionYear;
        //顯示年度用
        $data['monthMaps'] = $this->monthMaps;
        //檢查線別是否未設定，有線別才可以提出審核
        $data['hasZeroLineNo'] = $hasZeroLineNo;
        //操作權限畫面用
        $data['permission'] = $this->getUserPermission();
        //串API取得機種清單，下拉式選單(機種清單)
        $data['equipment'] = $this->getEquipmentList();
        //SAP的資料
        $data['sapData'] = $this->getSapData($progress)['show'];

        return view('system.projectMenu.productionYear', [
            'selection' => 'system',
            'openMenu' => 'projectMenu',
            'visitedId' => 'productionYear',
            'tableData' => $data]);
    }

    //計算工作日
    private function countWorkDay($year, $month, $isHolidayMap)
    {
        //工作日
        $workDay = 0;
        //當年該月總天數
        $days = cal_days_in_month(CAL_GREGORIAN, $month, $year);
        //從1號到該月最後一號
        for ($date = 1; $date <= $days; $date++) {
            //取得該日期是星期幾(英文表示)
            $dayName = date("l", mktime(0, 0, 0, $month, $date, $year));
            //本次日期
            $day = $year . $month . $date;
            //如果有在$isHolidayMap裡，且為工作日
            if (isset($isHolidayMap[$day])) {
                if ($isHolidayMap[$day] == 'N') {
                    $workDay++;
                }
            } else {
                //如果不在$isHolidayMap裡，且不是假日
                if ($dayName != 'Sunday' && $dayName != 'Saturday') {
                    $workDay++;
                }
            }
        }
        return $workDay;
    }

    //取得匯入SAP所需資料
    //取得匯入SAP所需資料
    private function getSapData($progress): array
    {
        //年度計畫合併線別，SQL指令：COALESCE改用ISNULL也可以，不過PostgreSQL、MySQL沒有ISNULL用法
        $sqlCommand = "SELECT production_year.*,
           COALESCE(equipment.line,'0') AS line_no
           FROM production_year LEFT JOIN equipment
           ON production_year.item_code = equipment.item_code
           WHERE period = ':period' AND version = ':version'
           ORDER BY production_year.item_code ASC, production_year.lot_no ASC";

        //替換對應值
        $sqlCommand = str_replace(':period', $progress['period'], $sqlCommand);
        $sqlCommand = str_replace(':version', $progress['version'], $sqlCommand);

        //年度生產計畫列表(含線別、內藏)
        $projects = DB::select($sqlCommand);

        //將DB查詢到的資料轉成array
        $arrayProjects = array_map(function ($itemObject) {
            return (array) $itemObject;
        }, $projects);

        $showDataArray = array();
        $uploadDataArray = array();

        foreach ($arrayProjects as $project) {
            if ($project['line_no'] > 0) {
                if ($project['lot_total'] > 0) {
                    $showDataArray[] = array(
                        $project['lot_no'],
                        $project['item_code'],
                        $project['lot_total'],
                        $project['material_date'],
                        $project['product_date'],
                        $project['material_date'],
                    );

                    $material_month = explode('-', $project['material_date'])[1];
                    $uploadDataArray[$progress['period'] . '-' . $material_month][] = array(
                        $project['lot_no'],
                        $project['item_code'],
                        $project['lot_total'],
                        $project['material_date'],
                        $project['product_date'],
                        $project['material_date'],
                    );
                }
            }
        }
        return array('show' => $showDataArray, 'upload' => $uploadDataArray);
    }

    //新增
    public function createProductionYear(Request $request)
    {
        //建立驗證器
        $columnArray['id'] = $id ?? 0;
        $columnArray['name'] = 'item_code';
        $columnArray['value'] = $request->item_code;
        $columnArray['name2'] = 'lot_no';
        $columnArray['value2'] = $request->lot_no;
        $columnArray['name3'] = 'period';
        $columnArray['value3'] = $request->period_tw;
        $columnArray['name4'] = 'version';
        $columnArray['value4'] = $request->version;
        $columnArray['name5'] = 'lot_total';
        $columnArray['value5'] = $request->lot_total;

        $validator = $this->checkInputValid('production_year', $columnArray);
        //如果驗證失敗
        if ($validator->fails()) {
            return redirect(route('ProductionYearController.showProductionYearPage',
                ['period_tw' => $request->period_tw, 'selectTab' => 'projectCreate']))
                ->with('errorMessage', $validator->errors()->all()[0]); //顯示錯誤訊息
        }

        $productionYear = new ProductionYear();
        $productionYear->version = $request->version;
        $productionYear->period = $request->period_tw;
        $productionYear->item_code = $request->item_code;
        $productionYear->item_name = $request->item_name;
        $productionYear->lot_no = $request->lot_no;
        $productionYear->lot_total = $request->lot_total;
        $productionYear->remark = $request->remark;
        $productionYear->deadline = $request->deadline;
        $productionYear->order_no = $this->getOrderNo([$request->item_code, $request->lot_no]);
        $productionYear->product_date = $request->product_date;
        $productionYear->material_date = $request->material_date;

        $monthNumberArray = $request->monthNumber;
        $rangeStartArray = $request->rangeStart;
        $rangeEndArray = $request->rangeEnd;
        $remarkHiddenArray = $request->remarkHidden;

        //0~11
        for ($index = 0; $index < 12; $index++) {
            $month = $this->monthMaps[$index]['page']; //4、5、6...3月
            $productionYear->{'month_' . $month} = $monthNumberArray[$index];
            //如果台數 > 0，要存生產區間資料
            if ($monthNumberArray[$index] > 0) {
                $productionYear->{'range_' . $month} = $rangeStartArray[$index] . '-' . $rangeEndArray[$index];
            }
            //如果內藏備註有東西，要存入資料
            if ($remarkHiddenArray[$index] != null) {
                $productionYear->{'remark_hidden_' . $month} = $remarkHiddenArray[$index];
            }
        }
        $productionYear->save();

        return redirect(route('ProductionYearController.showProductionYearPage', $request->period_tw))
            ->with('message', '成功，已新增資料！'); //顯示成功訊息
    }

    //編輯年度生產計劃頁面
    public function editProductionYearPage($id)
    {
        //驗證操作是否合法
        $result = $this->checkOperationValid($id, 'project_crud');
        if ($result['code'] != 200) {
            return view('errors.custom_error', $result);
        }
        $productionYear = ProductionYear::join('equipment', 'production_year.item_code', '=', 'equipment.item_code')
            ->selectRaw('production_year.* , equipment.is_hidden AS is_hidden')
            ->where('production_year.id', $id)
            ->firstOrFail()
            ->toArray();

        //0~11
        for ($index = 0; $index < count($this->monthMaps); $index++) {
            $month = $this->monthMaps[$index]['page']; //4、5、6...3月
            $year = intval($productionYear['period']) + (($month > 3) ? 1969 : 1970);
            $this->monthMaps[$index]['totalDay'] = cal_days_in_month(CAL_GREGORIAN, $month, $year);
            //如果生產區間無資料，要設定預設值
            if ($productionYear['range_' . $month] == null) {
                $productionYear['rangeStart'][$month] = '1';
                $productionYear['rangeEnd'][$month] = strval($this->monthMaps[$index]['totalDay']);
            } else {
                $productionYear['rangeStart'][$month] = explode('-', $productionYear['range_' . $month])[0];
                $productionYear['rangeEnd'][$month] = explode('-', $productionYear['range_' . $month])[1];
            }
            //如果內藏備註無資料，要設定預設值
            if ($productionYear['remark_hidden_' . $month] == null) {
                $productionYear['remarkHidden'][$month] = "";
            } else {
                $productionYear['remarkHidden'][$month] = $productionYear['remark_hidden_' . $month];
            }
        }

        $data['title'] = $productionYear['period'] . '期年度生產計劃：編輯 ' . $productionYear['item_code'] . " - Lot no : " . $productionYear['lot_no'];
        //顯示年度用
        $data['monthMaps'] = $this->monthMaps;
        //年度生產計劃列表
        $data['productionYear'] = $productionYear;

        return view('system.projectMenu.editDetail.productionYear_edit', [
            'selection' => 'system',
            'tableData' => $data]);
    }

    //修改
    public function updateProductionYear(Request $request, $id)
    {
        $productionYear = ProductionYear::findOrFail($id);

        $productionYear->lot_no = $request->lot_no;
        $productionYear->lot_total = $request->lot_total;
        $productionYear->remark = $request->remark;
        $productionYear->deadline = $request->deadline;
        $productionYear->product_date = $request->product_date;
        $productionYear->material_date = $request->material_date;
        $productionYear->order_no = $this->getOrderNo([$productionYear->item_code, $request->lot_no]);
        $monthNumberArray = $request->monthNumber;
        $rangeStartArray = $request->rangeStart;
        $rangeEndArray = $request->rangeEnd;
        $remarkHiddenArray = $request->remarkHidden;
        for ($index = 0; $index < 12; $index++) {
            $month = $this->monthMaps[$index]['page']; //4、5、6...3月
            $productionYear->{'month_' . $month} = $monthNumberArray[$index];
            //如果台數 > 0，要存生產區間資料
            if ($monthNumberArray[$index] > 0) {
                $productionYear->{'range_' . $month} = $rangeStartArray[$index] . '-' . $rangeEndArray[$index];
            }
            //如果內藏備註有東西，要存入資料
            if ($remarkHiddenArray[$index] != null) {
                $productionYear->{'remark_hidden_' . $month} = $remarkHiddenArray[$index];
            }
        }
        $productionYear->save();

        return redirect(route('ProductionYearController.showProductionYearPage', $productionYear->period))
            ->with('message', '成功，已修改資料！'); //顯示成功訊息
    }

    //刪除
    public function deleteProductionYear($id)
    {
        //找到此id的資料
        $productionYear = ProductionYear::find($id);

        //刪除此id的資料
        $productionYear->delete();

        return redirect(route('ProductionYearController.showProductionYearPage', $productionYear->period))
            ->with('message', '成功，已刪除資料！'); //顯示成功訊息
    }

    //上傳至SAP
    public function uploadProductionYear($period_tw)
    {
        //取得目前進度用
        $progress = $this->getProjectProgress('PY', $period_tw, 0);
        //MES資料庫(取工數)
        $partition = $this->getPartition($progress['period']);
        //SAP的資料
        $sapArray = $this->getSapData($progress, $partition)['upload'];
        //製作上傳的資料
        $data = $this->createUploadData($sapArray);
        //上傳API
        $response = $this->uploadToSap('OFCT', $data);
        //上傳結果
        if ($response->successful() && $response->json()['IsSuccess']) {
            return $this->downloadSapExcel($response->json());
        } else {
            return redirect()->back()
                ->with('errorMessage', '錯誤，上傳至SAP失敗！(訊息：' . $response->json()['Msg'] . ')'); //顯示錯誤訊息
        }
    }

    //製作上傳的資料
    private function createUploadData($sapArray)
    {
        $ofct = array();
        foreach ($sapArray as $periodMonth => $sapSubArray) {
            $lines = array();
            foreach ($sapSubArray as $sap) {
                $lines[] = array(
                    "U_Lot" => $sap[0],
                    "ItemCode" => $sap[1],
                    "Quantity" => $sap[2],
                    "U_MATA_DATE" => $sap[3],
                    "U_PP_DATE" => $sap[4],
                    "Date" => $sap[5],
                );
            }

            $period = intval(explode('-', $periodMonth)[0]);
            $month = intval(explode('-', $periodMonth)[1]);
            $year = $period + (($month > 3) ? 1969 : 1970);
            $ofct = array(
                "Code" => $year . "-" . str_pad($month, 2, "0", STR_PAD_LEFT),
                "Name" => $periodMonth,
                "StartDate" => $year . "-" . str_pad($month, 2, "0", STR_PAD_LEFT) . "-01",
                "EndDate" => $year . "-" . str_pad($month, 2, "0", STR_PAD_LEFT) . "-" . cal_days_in_month(CAL_GREGORIAN, $month, $year),
                'Lines' => $lines,
            );
            $data[] = array('OFCT' => $ofct);
        }
        return $data;
    }
}
