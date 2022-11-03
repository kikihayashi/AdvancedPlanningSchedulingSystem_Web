<?php

namespace App\Http\Controllers;

use App\Http\Traits\BaseTool;
use App\Http\Traits\MesApiTool;
use App\Http\Traits\ValidatorTool;
use App\Models\Period;
use App\Models\ProductionMonth;
use App\Models\Schedule;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

class ProductionMonthController extends Controller
{
    use BaseTool, MesApiTool, ValidatorTool;
    /**
     * 月度生產計劃------------------------------------------------------------------------------
     */
    //讀取
    //$period_tw代表選擇的期別，$month代表選擇的月份，$selectTab代表選擇的分頁(列表、新增)
    public function showProductionMonthPage($period_tw = null, $month = 4, $selectTab = 'projectList')
    {
        /**
         * 優先處理，後續都會用到
         */
        //使用者切換其他期別用(排列：期別大->小)
        $periods = Period::orderBy('period_tw', 'desc')->get();
        //顯示本次期別用(若輸入無效期別，預設使用資料庫中最大的期別)
        $thisTimePeriod = $this->getProjectPeriod($period_tw, $periods->get(0)->period_tw);
        //取得目前月份，預設4月
        $month = (intval($month) > 12 || intval($month) < 1) ? 4 : intval($month);
        //取得西元年
        $year = $thisTimePeriod->years + (($month < 4) ? 1 : 0);
        //取得選擇月份的總天數
        $days = cal_days_in_month(CAL_GREGORIAN, $month, $year);
        //取得目前進度用
        $progress = $this->getProjectProgress('PM', $thisTimePeriod->period_tw, $month);
        //MES資料庫(取工數)
        $partition = $this->getPartition($progress['period']);

        //取得行事曆
        $schedules = Schedule::join('parameter_calendar', 'schedule.calendar_id', '=', DB::raw("CAST(parameter_calendar.id AS VARCHAR)"))
            ->selectRaw('schedule.* , parameter_calendar.is_holiday AS is_holiday')
            ->where('year', $year)
            ->where('month', $month)
            ->get()
            ->toArray();

        //製作是否為假日的HashMap(key:YYYYMMDD，value:是否為假日)
        foreach ($schedules as $schedule) {
            $isHolidayMap[$schedule['date']] = $schedule['is_holiday'];
        }

        foreach (range(1, $days) as $day) {
            //如果是否為假日的HashMap裡沒有該日期
            if (!isset($isHolidayMap[$day])) {
                $timestamp = strtotime($year . '-' . $month . '-' . $day);
                $date = date('w', $timestamp);
                $isHolidayMap[$day] = ($date == '0' || $date == '6') ? 'Y' : 'N';
            }
        }

        //月度計畫合併線別內藏，SQL指令：COALESCE改用ISNULL也可以，不過PostgreSQL、MySQL沒有ISNULL用法
        $sqlCommand = "SELECT production_month.*,
                        COALESCE(equipment.line,'0') AS line_no,
                        COALESCE(equipment.is_hidden,'N') AS is_hidden
                        FROM production_month LEFT JOIN equipment
                        ON production_month.item_code = equipment.item_code
                        WHERE  version = ':version' AND period = ':period' AND month = ':month'";

        //替換對應值
        $sqlCommand = str_replace(':version', $progress['version'], $sqlCommand);
        $sqlCommand = str_replace(':period', $progress['period'], $sqlCommand);
        $sqlCommand = str_replace(':month', $progress['month'], $sqlCommand);

        //月度生產計畫列表(含線別、內藏)
        $projects = DB::select($sqlCommand);

        //將object轉成array，並加入partition將工數放入projects中
        $productionMonth = array_map(function ($itemObject) use ($partition, $month) {
            //將DB查詢到的資料轉成array
            $itemArray = (array) $itemObject;
            //工數
            $itemArray['workHour'] = ($partition[$itemArray['item_code']][((3 < $month && $month < 10) ? 'first' : 'last') . 'WorkHour'] ?? 0);
            //完成工數 = 工數 * 本月計劃生產台數
            $itemArray['completeHour'] = ($itemArray['workHour'] * intval($itemArray['this_month_number']));
            //根據start_day_array、end_day_array字串，新增start、end陣列(畫面顯示用)
            $itemArray['start'] = array_map('intval', explode(',', $itemArray['start_day_array']));
            $itemArray['end'] = array_map('intval', explode(',', $itemArray['end_day_array']));
            return $itemArray;
        }, $projects);

        $hasZeroLineNo = false;
        foreach ($productionMonth as $project) {
            if ($project['line_no'] == 0) {
                $hasZeroLineNo = true;
            }
        }

        //本次期別標題
        $data['title'] = "月度生產計劃 : 台京" . $progress['period'] . "期";
        //下拉式選單(選擇其他期別)
        $data['period'] = $periods;
        //顯示進度條用
        $data['progress'] = $progress;
        //取得ISO編號
        $data['iso'] = Setting::where('memo', 'iso_production_month')->first()->setting_value;
        //取得員工人數
        $data['employee'] = Setting::where('memo', 'employee_numbers')->first()->setting_value;
        //顯示分頁用
        $data['selectTab'] = (count($productionMonth) == 0) ? 'projectList' : $selectTab;
        //本次選擇期別
        $data['thisTimePeriod'] = $thisTimePeriod;
        //本次選擇月份
        $data['thisTimeMonth'] = $month;
        //本次選擇月份總天數
        $data['thisTimeDays'] = $days;
        //顯示休息日為*用
        $data['isHolidayMap'] = $isHolidayMap;
        //顯示月度生產計畫
        $data['productionMonth'] = $productionMonth;
        //顯示月度用
        $data['monthMaps'] = $this->monthMaps;
        //檢查線別是否未設定，有線別才可以提出審核
        $data['hasZeroLineNo'] = $hasZeroLineNo;
        //操作權限畫面用
        $data['permission'] = $this->getUserPermission();
        //串API取得機種清單，下拉式選單(機種清單)
        $data['equipment'] = $this->getEquipmentList();
        //SAP的資料
        $data['sapData'] = $this->getSapData($progress, $partition);

        return view('system.projectMenu.productionMonth', [
            'selection' => 'system',
            'openMenu' => 'projectMenu',
            'visitedId' => 'productionMonth',
            'tableData' => $data]);
    }

    //取得生產區間
    private function getProductionDay($dayArray)
    {
        if ($dayArray == null) {
            return array("start" => '0', 'end' => '0');
        }
        $dayArray = array_map('intval', $dayArray);
        //取得選擇區間陣列的最後一個index
        $lastIndex = count($dayArray) - 1;
        $now = $dayArray[0];
        $start[] = $now;
        for ($index = 1; $index <= $lastIndex; $index++) {
            if ($now + 1 != $dayArray[$index]) {
                $start[] = $dayArray[$index];
                $end[] = $now;
            }
            $now = $dayArray[$index];
        }
        $end[] = $dayArray[$lastIndex];
        return array("start" => implode(',', $start), 'end' => implode(',', $end));
    }

    //取得匯入SAP所需資料
    private function getSapData($progress, $partition): array
    {
        //月度生產計畫
        $sqlCommand = "SELECT production_month.*
        FROM production_month WHERE period = ':period'
        AND month = ':month' AND version = ':version'";

        //替換對應值
        $sqlCommand = str_replace(':period', $progress['period'], $sqlCommand);
        $sqlCommand = str_replace(':month', $progress['month'], $sqlCommand);
        $sqlCommand = str_replace(':version', $progress['version'], $sqlCommand);

        //月度生產計畫列表(含線別、內藏)
        $projects = DB::select($sqlCommand);
        $period = $progress['period'];
        $month = $progress['month'];

        //將object轉成array，並加入partition將工數放入projects中
        $arrayProjects = array_map(function ($itemObject) use ($partition, $period, $month) {
            //將DB查詢到的資料轉成array
            $itemArray = (array) $itemObject;
            //成本
            $itemArray['cost'] = ($partition[$itemArray['item_code']][((3 < $month && $month < 10) ? 'first' : 'last') . 'Cost'] ?? 0);
            //出貨日期
            $day = str_pad(explode(',', $itemArray['start_day_array'])[0], 2, "0", STR_PAD_LEFT);
            $month = str_pad($month, 2, "0", STR_PAD_LEFT);
            $year = ($period + (($month > 3) ? 1969 : 1970));
            $itemArray['shippingDate'] = $year . '/' . $month . '/' . $day;
            return $itemArray;
        }, $projects);

        foreach ($arrayProjects as $project) {
            if ($project['this_month_number'] > 0) {
                $dataArray[] = array(
                    $project['lot_no'],
                    $project['item_code'],
                    $project['cost'],
                    $project['this_month_number'],
                    $project['shippingDate'],
                );
            }
        }
        return $dataArray ?? array();
    }

    //新增
    public function createProductionMonth(Request $request)
    {
        //建立驗證器
        $columnArray['id'] = $id ?? 0;
        $columnArray['name'] = 'item_code';
        $columnArray['value'] = $request->item_code;
        $columnArray['name2'] = 'lot_no';
        $columnArray['value2'] = $request->lot_no;
        $columnArray['name3'] = 'period';
        $columnArray['value3'] = $request->period_tw;
        $columnArray['name4'] = 'month';
        $columnArray['value4'] = $request->month;
        $columnArray['name5'] = 'version';
        $columnArray['value5'] = $request->version;
        $columnArray['name6'] = 'previous_month_number';
        $columnArray['value6'] = $request->previousMonthNumber;
        $columnArray['name7'] = 'this_month_number';
        $columnArray['value7'] = $request->thisMonthNumber;

        $validator = $this->checkInputValid('production_month', $columnArray);
        //如果驗證失敗
        if ($validator->fails()) {
            return redirect(route('ProductionMonthController.showProductionMonthPage',
                ['period_tw' => $request->period_tw, 'month' => $request->month, 'selectTab' => 'projectCreate']))
                ->with('errorMessage', $validator->errors()->all()[0]); //顯示錯誤訊息
        }

        $productionMonth = new ProductionMonth();
        $productionMonth->version = $request->version;
        $productionMonth->period = $request->period_tw;
        $productionMonth->month = $request->month;
        $productionMonth->item_code = $request->item_code;
        $productionMonth->item_name = $request->item_name;
        $productionMonth->lot_no = $request->lot_no;
        $productionMonth->previous_month_number = $request->previousMonthNumber;
        $productionMonth->this_month_number = $request->thisMonthNumber;
        $productionDayArray = $this->getProductionDay($request->productionDay);
        $productionMonth->start_day_array = $productionDayArray['start'];
        $productionMonth->end_day_array = $productionDayArray['end'];
        $productionMonth->save();

        return redirect(route('ProductionMonthController.showProductionMonthPage',
            ['period_tw' => $request->period_tw, 'month' => $request->month]))
            ->with('message', '成功，已新增資料！'); //顯示成功訊息
    }

    //編輯月度生產計劃頁面
    public function editProductionMonthPage($id)
    {
        //驗證操作是否合法
        $result = $this->checkOperationValid($id, 'project_crud');
        if ($result['code'] != 200) {
            return view('errors.custom_error', $result);
        }
        $productionMonth = ProductionMonth::where('id', $id)
            ->firstOrFail()
            ->toArray();

        //計算實際月份
        $month = $productionMonth['month'];
        //取得西元年
        $year = $productionMonth['period'] + (($month < 4) ? 1970 : 1969);
        //取得選擇月份的總天數
        $days = cal_days_in_month(CAL_GREGORIAN, $month, $year);

        //本次期別標題
        $data['title'] = '月度生產計劃' . $month . '月：編輯 ' . $productionMonth['item_code'] . " - Lot no : " . $productionMonth['lot_no'];
        //本次選擇月份總天數
        $data['thisTimeDays'] = $days;
        //顯示月度生產計畫
        $data['productionMonth'] = $productionMonth;

        return view('system.projectMenu.editDetail.productionMonth_edit', [
            'selection' => 'system',
            'tableData' => $data]);
    }

    //修改
    public function updateProductionMonth(Request $request, $id)
    {
        $productionMonth = ProductionMonth::findOrFail($id);
        $productionMonth->previous_month_number = $request->previousMonthNumber;
        $productionMonth->this_month_number = $request->thisMonthNumber;
        $productionDayArray = $this->getProductionDay($request->productionDay);
        $productionMonth->start_day_array = $productionDayArray['start'];
        $productionMonth->end_day_array = $productionDayArray['end'];
        $productionMonth->save();

        return redirect(route('ProductionMonthController.showProductionMonthPage',
            ['period_tw' => $productionMonth->period, 'month' => $productionMonth->month]))
            ->with('message', '成功，已修改資料！'); //顯示成功訊息
    }

    //刪除
    public function deleteProductionMonth($id)
    {
        //找到此id的資料
        $productionMonth = ProductionMonth::find($id);

        //刪除此id的資料
        $productionMonth->delete();

        return redirect(route('ProductionMonthController.showProductionMonthPage',
            ['period_tw' => $productionMonth->period, 'month' => $productionMonth->month]))
            ->with('message', '成功，已刪除資料！'); //顯示成功訊息
    }

    //上傳至SAP
    public function uploadProductionMonth($period_tw, $month)
    {
        //取得目前進度用
        $progress = $this->getProjectProgress('PM', $period_tw, $month);
        //MES資料庫(取工數)
        $partition = $this->getPartition($progress['period']);
        //SAP的資料
        $sapArray = $this->getSapData($progress, $partition);
        //製作上傳的資料
        $data = $this->createUploadData($sapArray, $period_tw, $month);
        //上傳至SAP
        $response = $this->uploadToSap('ORDR', $data);
        //上傳結果
        if ($response->successful() && $response->json()['IsSuccess']) {
            return $this->downloadSapExcel($response->json());
        } else {
            return redirect()->back()
                ->with('errorMessage', '錯誤，上傳至SAP失敗！(訊息：' . $response->json()['Msg'] . ')'); //顯示錯誤訊息
        }
    }

    //製作上傳的資料
    private function createUploadData($sapArray, $period_tw, $month)
    {
        //上傳json格式
        $lines = array();
        foreach ($sapArray as $sap) {
            $lines[] = array(
                "U_Lot" => $sap[0],
                "ItemCode" => $sap[1],
                "Price" => $sap[2],
                "Quantity" => $sap[3],
                "ShipDate" => $sap[4],
                "U_period" => $period_tw . '-' . $month,
            );
        }
        $data[] = array('ORDR' => array('Lines' => $lines));
        return $data;
    }
}
