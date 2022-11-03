<?php

namespace App\Http\Controllers;

use App\Http\Traits\BaseTool;
use App\Http\Traits\MesApiTool;
use App\Http\Traits\ValidatorTool;
use App\Models\Period;
use App\Models\Setting;
use App\Models\ShippingMonth;
use App\Models\Transport;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

class ShippingMonthController extends Controller
{
    use BaseTool, MesApiTool, ValidatorTool;
    /**
     * 月度出荷計劃------------------------------------------------------------------------------
     */
    //讀取
    //$period_tw代表選擇的期別，$month代表選擇的月份，$selectTab代表選擇的分頁(列表、新增)
    public function showShippingMonthPage($period_tw = null, $month = 4, $selectTab = 'projectList')
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
        $progress = $this->getProjectProgress('SM', $thisTimePeriod->period_tw, $month);
        //MES資料庫(取工數)
        $partition = $this->getPartition($progress['period']);
        //MES資料庫(取匯率)
        $exchange = $this->getExchange($progress['period']);

        /**
         * 新增機種頁面
         */
        //運輸模式
        $transportArray = Transport::orderBy('id')->get()->toArray();
        $transportMap = array();
        foreach ($transportArray as $transport) {
            $transportMap[$transport['id']] = $transport;
        }

        //顯示機種用
        $itemCodeArray = ShippingMonth::selectRaw('item_code, item_name')
            ->where('version', $progress['version'])
            ->where('period', $progress['period'])
            ->where('month', $progress['month'])
            ->groupBy('item_code', 'item_name')
            ->get()
            ->toArray();

        //顯示日期用
        $dateArray = ShippingMonth::join('parameter_transport', 'shipping_month.transport_id', '=', DB::raw("CAST(parameter_transport.id AS VARCHAR)"))
            ->selectRaw('date , transport_id, parameter_transport.abbreviation AS abbreviation')
            ->where('version', $progress['version'])
            ->where('period', $progress['period'])
            ->where('date', '!=', 0) //把除了日期為0以外的日期取出來
            ->groupBy('date', 'transport_id', 'parameter_transport.abbreviation') //只需要不重複的日期和出荷方式
            ->orderBy('date')
            ->orderBy('transport_id')
            ->get()
            ->toArray();

        //日期顯示規則A~Z
        $letter = 'A';
        for ($i = 0; $i < count($dateArray); $i++) {
            $dateArray[$i]['letter'] = $letter++;
        }

        //月度計畫合併線別，SQL指令：COALESCE改用ISNULL也可以，不過PostgreSQL、MySQL沒有ISNULL用法
        $sqlCommand = "SELECT shipping_month.*,
         COALESCE(equipment.line,'0') AS line_no
         FROM shipping_month LEFT JOIN equipment
         ON shipping_month.item_code = equipment.item_code
         WHERE version = ':version' AND period = ':period' AND month = ':month'
         ORDER BY item_code";

        //替換對應值
        $sqlCommand = str_replace(':version', $progress['version'], $sqlCommand);
        $sqlCommand = str_replace(':period', $progress['period'], $sqlCommand);
        $sqlCommand = str_replace(':month', $progress['month'], $sqlCommand);

        //月度出荷計畫列表(含線別、內藏)
        $projects = DB::select($sqlCommand);

        //將object轉成array，並加入partition將成本放入projects中
        $arrayProjects = array_map(function ($itemObject) use ($partition, $exchange, $month) {
            //將DB查詢到的資料轉成array
            $itemArray = (array) $itemObject;
            //成本
            $itemArray['cost'] = ($partition[$itemArray['item_code']][((3 < $month && $month < 10) ? 'first' : 'last') . 'Cost'] ?? 0);
            return $itemArray;
        }, $projects);

        //製作年度出荷列表HashMap(key:機種，value:年度出荷列表)
        $shippingMonth = array();
        $hasZeroLineNo = false;
        foreach ($arrayProjects as $project) {
            if ($project['line_no'] == 0) {
                $hasZeroLineNo = true;
            }
            $shippingMonth[$project['item_code']][] = $project;
        }

        //檢查是否有資料，有資料才可以提出審核
        $dataNumber = ShippingMonth::where('version', $progress['version'])
            ->where('period', $progress['period'])
            ->where('month', $progress['month'])
            ->where('lot_no', '!=', 0)
            ->where('date', '!=', 0)
            ->where('transport_id', '!=', -1)
            ->get()
            ->toArray();

        //本次期別標題
        $data['title'] = "月度出荷計劃 : 台京" . $progress['period'] . "期";
        //下拉式選單(選擇其他期別)
        $data['period'] = $periods;
        //顯示進度條用
        $data['progress'] = $progress;
        //取得ISO編號
        $data['iso'] = Setting::where('memo', 'iso_shipping_month')->first()->setting_value;
        //取得員工人數
        $data['employee'] = Setting::where('memo', 'employee_numbers')->first()->setting_value;
        //顯示分頁用
        $data['selectTab'] = (count($shippingMonth) == 0) ? 'projectList' : $selectTab;
        //本次選擇期別
        $data['thisTimePeriod'] = $thisTimePeriod;
        //本次選擇月份
        $data['thisTimeMonth'] = $month;
        //本次選擇月份總天數
        $data['thisTimeDays'] = $days;
        //下拉式選單(運輸類型)
        $data['transport'] = $transportMap;
        //顯示上或下半年匯率
        $data['exchange'] = $exchange[((3 < $month && $month < 10) ? 'first' : 'last')];
        //顯示機種用
        $data['itemCodeArray'] = $itemCodeArray;
        //顯示日期用
        $data['dateArray'] = $dateArray;
        //顯示月度出荷計畫
        $data['shippingMonth'] = $shippingMonth;
        //檢查是否有資料，有資料才可以提出審核
        $data['dataNumber'] = count($dataNumber);
        //檢查線別是否未設定，有線別才可以提出審核
        $data['hasZeroLineNo'] = $hasZeroLineNo;
        //顯示月度用
        $data['monthMaps'] = $this->monthMaps;
        //操作權限畫面用
        $data['permission'] = $this->getUserPermission();
        //串API取得機種清單，下拉式選單(機種清單)
        $data['equipment'] = $this->getEquipmentList();

        return view('system.projectMenu.shippingMonth', [
            'selection' => 'system',
            'openMenu' => 'projectMenu',
            'visitedId' => 'shippingMonth',
            'tableData' => $data]);
    }

    //新增
    public function createShippingMonth(Request $request, $type)
    {
        switch ($type) {
            case 'ITEM_CODE':
                return $this->createItemCode($request);
            case 'TRANSPORT':
                return $this->createTransport($request);
            case 'LOT':
                return $this->createLot($request);
        }
    }

    //新增機種
    private function createItemCode(Request $request)
    {
        //檢查是否有此機種
        $checkData = ShippingMonth::where('version', $request->version)
            ->where('period', $request->period_tw)
            ->where('month', $request->month)
            ->where('item_code', $request->item_code)
            ->get()
            ->toArray();

        //如果沒有此機種，新增它
        if (count($checkData) == 0) {
            $shippingMonth = new ShippingMonth();
            $shippingMonth->version = $request->version;
            $shippingMonth->period = $request->period_tw;
            $shippingMonth->month = $request->month;
            $shippingMonth->date = 0;
            $shippingMonth->transport_id = -1;
            $shippingMonth->item_code = $request->item_code;
            $shippingMonth->item_name = $request->item_name;
            $shippingMonth->lot_no = 0;
            $shippingMonth->number = 0;
            $shippingMonth->save();

            return redirect(route('ShippingMonthController.showShippingMonthPage',
                ['period_tw' => $request->period_tw, 'month' => $request->month]))
                ->with('message', '成功，已新增機種！'); //顯示成功訊息
        } else {
            return redirect(route('ShippingMonthController.showShippingMonthPage',
                ['period_tw' => $request->period_tw, 'month' => $request->month, 'selectTab' => 'projectItemCode']))
                ->with('errorMessage', "錯誤，機種【" . $request->item_code . "】已存在！"); //顯示成功訊息
        }
    }

    //新增出荷計畫
    private function createTransport(Request $request)
    {
        //檢查是否有此日期-出荷方式
        $checkData = ShippingMonth::where('version', $request->version)
            ->where('period', $request->period_tw)
            ->where('month', $request->month)
            ->where('date', $request->date)
            ->where('transport_id', $request->transport_id)
            ->get()
            ->toArray();

        //如果沒有此日期-出荷方式，讓目前有的機種資料新增這個日期-出荷方式
        if (count($checkData) == 0) {
            $projects = ShippingMonth::selectRaw('item_code, item_name')
                ->where('version', $request->version)
                ->where('period', $request->period_tw)
                ->where('month', $request->month)
                ->groupBy('item_code', 'item_name')
                ->get();

            //如果都沒有機種
            if (count($projects->toArray()) == 0) {
                return redirect(route('ShippingMonthController.showShippingMonthPage',
                    ['period_tw' => $request->period_tw, 'month' => $request->month]))
                    ->with('errorMessage', "錯誤，請先新增機種！"); //顯示錯誤訊息
            }

            foreach ($projects as $project) {
                $shippingMonth = new ShippingMonth();
                $shippingMonth->version = $request->version;
                $shippingMonth->period = $request->period_tw;
                $shippingMonth->month = $request->month;
                $shippingMonth->date = $request->date;
                $shippingMonth->transport_id = $request->transport_id;
                $shippingMonth->item_code = $project->item_code;
                $shippingMonth->item_name = $project->item_name;
                $shippingMonth->lot_no = 0;
                $shippingMonth->number = 0;
                $shippingMonth->save();
            }
            return redirect(route('ShippingMonthController.showShippingMonthPage',
                ['period_tw' => $request->period_tw, 'month' => $request->month]))
                ->with('message', '成功，已新增出荷計畫！'); //顯示成功訊息
        } else {
            return redirect(route('ShippingMonthController.showShippingMonthPage',
                ['period_tw' => $request->period_tw, 'month' => $request->month, 'selectTab' => 'projectTransport']))
                ->with('errorMessage', "錯誤，出荷計畫 "
                    . str_pad($request->month, 2, "0", STR_PAD_LEFT) . "/"
                    . str_pad($request->date, 2, "0", STR_PAD_LEFT) . " ("
                    . Transport::where('id', $request->transport_id)->first()->name . ") 已存在！"); //顯示錯誤訊息
        }
    }

    //新增Lot
    private function createLot(Request $request)
    {
        for ($i = 0; $i < count($request->lot_no); $i++) {
            //建立驗證器
            $columnArray['id'] = $id ?? 0;
            $columnArray['name'] = 'item_code';
            $columnArray['value'] = $request->item_code;
            $columnArray['name2'] = 'lot_no';
            $columnArray['value2'] = $request->lot_no[$i];
            $columnArray['name3'] = 'period';
            $columnArray['value3'] = $request->period_tw;
            $columnArray['name4'] = 'month';
            $columnArray['value4'] = $request->month;
            $columnArray['name5'] = 'version';
            $columnArray['value5'] = $request->version;
            $columnArray['name6'] = 'date';
            $columnArray['value6'] = $request->date;
            $columnArray['name7'] = 'transport_id';
            $columnArray['value7'] = $request->transport_id;
            $columnArray['name8'] = 'dateAndTransportAbbreviation';
            $columnArray['value8'] = str_pad($request->month, 2, "0", STR_PAD_LEFT) . '/' . str_pad($request->date, 2, "0", STR_PAD_LEFT) . '-(' . Transport::where('id', $request->transport_id)->first()->abbreviation . ')';
            $columnArray['name9'] = 'number';
            $columnArray['value9'] = $request->number[$i];

            $validator = $this->checkInputValid('shipping_month', $columnArray);
            //如果驗證失敗
            if ($validator->fails()) {
                return redirect()->back()
                    ->with('errorMessage', $validator->errors()->all()[0]); //顯示錯誤訊息
            }
        }

        for ($i = 0; $i < count($request->lot_no); $i++) {
            $shippingMonth = new ShippingMonth();
            $shippingMonth->version = $request->version;
            $shippingMonth->period = $request->period_tw;
            $shippingMonth->item_code = $request->item_code;
            $shippingMonth->item_name = $request->item_name;
            $shippingMonth->lot_no = $request->lot_no[$i];
            $shippingMonth->month = $request->month;
            $shippingMonth->date = $request->date;
            $shippingMonth->transport_id = $request->transport_id;
            $shippingMonth->number = $request->number[$i];
            $shippingMonth->remark = $request->remark[$i];
            $shippingMonth->save();
        }

        return redirect(route('ShippingMonthController.showShippingMonthPage',
            ['period_tw' => $request->period_tw, 'month' => $request->month]))
            ->with('message', '成功，已新增Lot！'); //顯示成功訊息
    }

    //編輯月度出荷計劃頁面
    public function editShippingMonthPage($itemCode, $period_tw, $month, $version)
    {
        //驗證操作是否合法
        $result1 = $this->checkOperationValid($period_tw, 'project_crud');
        $result2 = $this->checkOperationValid($month, 'project_crud');
        $result3 = $this->checkOperationValid($version, 'project_crud');
        if ($result1['code'] != 200) {
            return view('errors.custom_error', $result1);
        }
        if ($result2['code'] != 200) {
            return view('errors.custom_error', $result2);
        }
        if ($result3['code'] != 200) {
            return view('errors.custom_error', $result3);
        }

        $sqlCommand = "SELECT shipping_month.*,
		COALESCE(equipment.line,'0') AS line_no,
		COALESCE(parameter_transport.name,'X') AS transport_name
        FROM shipping_month
		LEFT JOIN equipment ON shipping_month.item_code = equipment.item_code
		LEFT JOIN parameter_transport ON shipping_month.transport_id = parameter_transport.id
        WHERE shipping_month.item_code =':itemCode'
        AND version = ':version' AND period = ':period' AND month = ':month'
        ORDER BY lot_no";

        //替換對應值
        $sqlCommand = str_replace(':itemCode', $itemCode, $sqlCommand);
        $sqlCommand = str_replace(':version', $version, $sqlCommand);
        $sqlCommand = str_replace(':period', $period_tw, $sqlCommand);
        $sqlCommand = str_replace(':month', $month, $sqlCommand);

        //月度出荷計畫列表(含線別、內藏)
        $projects = DB::select($sqlCommand);

        if (count($projects) == 0) {
            return view('errors.custom_error', ['code' => 404, 'message' => '找不到此頁面！']);
        }

        //將object轉成array，並加入partition將工數放入projects中
        $shippingMonths = array_map(function ($itemObject) {
            //將DB查詢到的資料轉成array
            $itemArray = (array) $itemObject;
            return $itemArray;
        }, $projects);

        //用來判別是否需要顯示更新區(key:date-transport、value:lot_no)
        foreach ($shippingMonths as $shippingMonth) {
            $key = $shippingMonth['date'] . '-' . $shippingMonth['transport_id'];
            $infoMap[$key] = $shippingMonth['lot_no'];
        }

        //顯示日期用
        $dateArray = ShippingMonth::join('parameter_transport', 'shipping_month.transport_id', '=', DB::raw("CAST(parameter_transport.id AS VARCHAR)"))
            ->selectRaw('date , transport_id, parameter_transport.abbreviation AS abbreviation')
            ->where('version', $version)
            ->where('period', $period_tw)
            ->where('date', '!=', 0) //把除了日期為0以外的日期取出來
            ->groupBy('date', 'transport_id', 'parameter_transport.abbreviation') //只需要不重複的日期和出荷方式
            ->orderBy('date')
            ->orderBy('transport_id')
            ->get()
            ->toArray();

        //本次期別標題
        $data['title'] = '月度出荷計劃' . $month . '月：編輯 ' . $itemCode;
        //顯示分頁用
        $data['selectTab'] = 'date-' . $dateArray[0]['date'] . '-' . $dateArray[0]['transport_id'];
        //顯示日期區
        $data['dateArray'] = $dateArray;
        //用來判別是否需要顯示更新區
        $data['info'] = $infoMap;
        //顯示月度出荷計畫
        $data['shippingMonth'] = $shippingMonths;
        //新增表單設置
        $data['htmlInputForm'] = view('system.projectMenu.formDetail.shippingMonth_form')->render();

        return view('system.projectMenu.editDetail.shippingMonth_edit', [
            'selection' => 'system',
            'tableData' => $data]);
    }

    //修改
    public function updateShippingMonth(Request $request)
    {
        for ($i = 0; $i < count($request->updateId); $i++) {
            $shippingMonth = ShippingMonth::findOrFail($request->updateId[$i]);
            $shippingMonth->number = $request->number[$i];
            $shippingMonth->remark = $request->remark[$i];
            $shippingMonth->save();
        }

        return redirect(route('ShippingMonthController.showShippingMonthPage',
            ['period_tw' => $request->period_tw, 'month' => $request->month]))
            ->with('message', '成功，已修改資料！'); //顯示成功訊息
    }

    //刪除
    public function deleteShippingMonth(Request $request, $type)
    {
        switch ($type) {
            case 'ID':
                foreach ($request->deleteId as $id) {
                    //找到此id的資料並刪除
                    $shippingMonth = ShippingMonth::find($id)->delete();
                }
                break;
            case 'ITEM_CODE':
                //找到此itemCode的所有資料並刪除
                $shippingMonth = ShippingMonth::where('period', $request->period_tw)
                    ->where('month', $request->month)
                    ->where('version', $request->version)
                    ->where('item_code', $request->item_code)
                    ->delete();
                break;
            case 'DATE':
                //找到此日期-出荷方式的所有資料並刪除
                $shippingMonth = ShippingMonth::where('period', $request->period_tw)
                    ->where('month', $request->month)
                    ->where('version', $request->version)
                    ->where('date', $request->date)
                    ->where('transport_id', $request->transport_id)
                    ->delete();
                break;
        }

        return redirect(route('ShippingMonthController.showShippingMonthPage',
            ['period_tw' => $request->period_tw, 'month' => $request->month]))
            ->with('message', '成功，已刪除資料！'); //顯示成功訊息
    }
}
