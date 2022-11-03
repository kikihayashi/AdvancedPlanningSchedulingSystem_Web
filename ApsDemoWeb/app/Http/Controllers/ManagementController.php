<?php

namespace App\Http\Controllers;

use App\Http\Traits\BaseTool;
use App\Http\Traits\MesApiTool;
use App\Http\Traits\ValidatorTool;
use App\Models\Management;
use App\Models\Period;
use App\Models\Setting;
use App\Models\Transport;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

class ManagementController extends Controller
{
    use BaseTool, MesApiTool, ValidatorTool;
    /**
     * 大計劃規劃管理------------------------------------------------------------------------------
     */
    //讀取
    //$period_tw代表選擇的期別，$selectTab代表選擇的分頁(列表、新增)
    public function showManagementPage($period_tw = null, $selectTab = 'projectList')
    {
        /**
         * 優先處理，後續都會用到
         */
        //使用者切換其他期別用(排列：期別大->小)
        $periods = Period::orderBy('period_tw', 'desc')->get();
        //顯示本次期別用(若輸入無效期別，預設使用資料庫中最大的期別)
        $thisTimePeriod = $this->getProjectPeriod($period_tw, $periods->get(0)->period_tw);
        //取得目前進度用
        $progress = $this->getProjectProgress('M', $thisTimePeriod->period_tw, 0);
        //MES資料庫(取工數)
        $partition = $this->getPartition($progress['period']);

        /**
         * 計劃頁面
         */
        //大計劃維護列表
        $projects = Management::where('version', $progress['version'])
            ->where('period', $progress['period'])
            ->select('management.*', DB::raw('(month_1+month_2+month_3+month_4+month_5+month_6+month_7+month_8+month_9+month_10+month_11+month_12) AS real_lot_number'))
            ->orderBy('item_code', 'ASC')
            ->orderBy('lot_no', 'ASC')
            ->get()
            ->toArray();

        //加入partition將工數放入projects中
        $arrayProjects = array_map(function ($itemArray) use ($partition) {
            //工數
            $itemArray['firstWorkHour'] = ($partition[$itemArray['item_code']]['firstWorkHour'] ?? 0);
            $itemArray['lastWorkHour'] = ($partition[$itemArray['item_code']]['lastWorkHour'] ?? 0);
            return $itemArray;
        }, $projects);

        //製作大計劃維護HashMap(key:item_code、value:project)
        $management = array();
        foreach ($arrayProjects as $project) {
            $management[$project['item_code']][] = $project;
        }

        /**
         * 新增機種頁面
         */
        //運輸模式
        $transports = Transport::orderBy('id')->get();
        foreach ($transports as $transport) {
            $transportMap[$transport->id] = $transport;
        }
        //出荷預定日、生產預計日、材料納期預定日推算用
        $settings = Setting::orderBy('id')->get();
        //設置上、中、下旬(幾月，格式2位數，從左邊補0)
        $dayArray = array(
            str_pad($settings->get(0)->setting_value, 2, "0", STR_PAD_LEFT) . " 上旬",
            str_pad($settings->get(1)->setting_value, 2, "0", STR_PAD_LEFT) . " 中旬",
            str_pad($settings->get(2)->setting_value, 2, "0", STR_PAD_LEFT) . " 下旬");

        //出荷預定日選項(從當年4月開始 ~ 隔年3月結束)
        foreach ($this->monthMaps as $monthMap) {
            $i = $monthMap['page'];
            $month = str_pad($i, 2, "0", STR_PAD_LEFT);
            $year = (int) $thisTimePeriod->years + (($i > 3) ? 0 : 1); //本次期別年分
            foreach ($dayArray as $day) {
                $shippingDateArray[] = $year . "-" . $month . "-" . $day;
            }
        }

        //本次期別標題
        $data['title'] = "大計劃管理 : 台京" . $progress['period'] . "期";
        //下拉式選單(選擇其他期別)
        $data['period'] = $periods;
        //顯示進度條用
        $data['progress'] = $progress;
        //顯示分頁用
        $data['selectTab'] = (count($management) == 0) ? 'projectList' : $selectTab;
        //本次選擇期別
        $data['thisTimePeriod'] = $thisTimePeriod;
        //顯示大計劃維護
        $data['management'] = $management;
        //生產預計日、材料納期預定日推算用
        $data['setting'] = $settings;
        //下拉式選單(運輸類型)
        $data['transport'] = $transportMap;
        //下拉式選單(出荷預定日)
        $data['shippingDateArray'] = $shippingDateArray;
        //顯示月度用
        $data['monthMaps'] = $this->monthMaps;
        //操作權限畫面用
        $data['permission'] = $this->getUserPermission();
        //串API取得機種清單，下拉式選單(機種清單)
        $data['equipment'] = $this->getEquipmentList();

        return view('system.projectMenu.management', [
            'selection' => 'system',
            'openMenu' => 'projectMenu',
            'visitedId' => 'management',
            'tableData' => $data]);
    }

    //新增
    public function createManagement(Request $request, $id = 0)
    {
        //建立驗證器
        $columnArray['id'] = $id;
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

        $validator = $this->checkInputValid('management', $columnArray);
        //如果驗證失敗
        if ($validator->fails()) {
            return redirect(route('ManagementController.showManagementPage',
                ['period_tw' => $request->period_tw, 'selectTab' => 'projectCreate']))
                ->with('errorMessage', $validator->errors()->all()[0]); //顯示錯誤訊息
        }

        $management = new Management();
        $management->version = $request->version;
        $management->period = $request->period_tw;
        $management->item_code = $request->item_code;
        $management->item_name = $request->item_name;
        $management->eternal_code = $request->eternal_code;
        $management->stock_code = $request->stock_code;
        $management->lot_no = $request->lot_no;
        $management->lot_total = $request->lot_total;
        $management->order_no = $this->getOrderNo([$request->item_code, $request->lot_no]);
        $management->batch = $request->batch;
        $management->transport_id = $request->transport_id;
        $management->remark_transport = $request->remark_transport;
        $management->remark_other = $request->remark_other;
        $management->arrival_date = $request->arrival_date;
        $management->shipment_date = $request->shipment_date;
        $management->actual_date = $request->actual_date;
        $management->product_date = $request->product_date;
        $management->material_date = $request->material_date;

        $monthNumberArray = $request->monthNumber;

        for ($index = 0; $index < 12; $index++) {
            $month = $this->monthMaps[$index]['page']; //4、5、6...3月
            $management->{'month_' . $month} = $monthNumberArray[$index];
        }
        $management->save();

        return redirect(route('ManagementController.showManagementPage', $request->period_tw))
            ->with('message', '成功，已新增資料！'); //顯示成功訊息
    }

    //編輯大計劃維護頁面
    public function editManagementPage($id)
    {
        //驗證操作是否合法
        $result = $this->checkOperationValid($id, 'project_crud');
        if ($result['code'] != 200) {
            return view('errors.custom_error', $result);
        }
        $management = Management::join('parameter_transport', 'management.transport_id', '=', DB::raw("CAST(parameter_transport.id AS VARCHAR)"))
            ->selectRaw('management.* , parameter_transport.is_remark AS is_remark_transport')
            ->where('management.id', $id)
            ->firstOrFail()
            ->toArray();

        //運輸模式
        $transports = Transport::orderBy('id')->get();
        foreach ($transports as $transport) {
            $transportMap[$transport->id] = $transport;
        }
        //出荷預定日、生產預計日、材料納期預定日推算用
        $settings = Setting::orderBy('id')->get();
        //設置上、中、下旬(幾月，格式2位數，從左邊補0)
        $dayArray = array(
            str_pad($settings->get(0)->setting_value, 2, "0", STR_PAD_LEFT) . " 上旬",
            str_pad($settings->get(1)->setting_value, 2, "0", STR_PAD_LEFT) . " 中旬",
            str_pad($settings->get(2)->setting_value, 2, "0", STR_PAD_LEFT) . " 下旬");

        //出荷預定日選項(從當年4月開始 ~ 隔年3月結束)
        foreach ($this->monthMaps as $monthMap) {
            $month = str_pad($monthMap['page'], 2, "0", STR_PAD_LEFT);
            $year = (int) $management['period'] + (($monthMap['page'] > 3) ? 1969 : 1970); //本次期別年分
            foreach ($dayArray as $day) {
                $shippingDateArray[] = $year . "-" . $month . "-" . $day;
            }
        }

        $data['title'] = $management['period'] . '期大計劃規劃管理：編輯 ' . $management['item_code'] . " - Lot no : " . $management['lot_no'];
        //下拉式選單(運輸類型)
        $data['transport'] = $transportMap;
        //生產預計日、材料納期預定日推算用
        $data['setting'] = $settings;
        //下拉式選單(出荷預定日)
        $data['shippingDateArray'] = $shippingDateArray;
        //顯示月度用
        $data['monthMaps'] = $this->monthMaps;
        //大計劃維護列表
        $data['management'] = $management;

        return view('system.projectMenu.editDetail.management_edit', [
            'selection' => 'system',
            'tableData' => $data]);
    }

    //修改
    public function updateManagement(Request $request, $id, $type)
    {
        $management = Management::findOrFail($id);
        $monthNumberArray = $request->monthNumber;
        switch ($type) {
            case 'SINGLE':
                $real_total_number = 0;
                for ($index = 0; $index < 12; $index++) {
                    $month = $this->monthMaps[$index]['page']; //4、5、6...3月
                    if ($month == $request->month) {
                        $real_total_number += $monthNumberArray[0];
                    } else {
                        $real_total_number += $management->{'month_' . $month};
                    }
                }
                if ($monthNumberArray[0] == null) {
                    return redirect()->back()
                        ->with('errorMessage', "錯誤，數量不可為空！"); //顯示錯誤訊息
                } else if ($monthNumberArray[0] < 0) {
                    return redirect()->back()
                        ->with('errorMessage', "錯誤，數量不可小於0！"); //顯示錯誤訊息
                } else if ($real_total_number > $management->lot_total) {
                    return redirect()->back()
                        ->with('errorMessage', "錯誤，實際總台數不可以大於Lot總台數！"); //顯示錯誤訊息
                }
                $management->{'month_' . $request->month} = $monthNumberArray[0];
                break;

            case 'TOTAL':
                $management->lot_total = $request->lot_total;
                $management->batch = $request->batch;
                $management->transport_id = $request->transport_id;
                $management->remark_transport = $request->remark_transport;
                $management->remark_other = $request->remark_other;
                $management->arrival_date = $request->arrival_date;
                $management->shipment_date = $request->shipment_date;
                $management->actual_date = $request->actual_date;
                $management->product_date = $request->product_date;
                $management->material_date = $request->material_date;
                for ($index = 0; $index < 12; $index++) {
                    $month = $this->monthMaps[$index]['page']; //4、5、6...3月
                    $management->{'month_' . $month} = $monthNumberArray[$index];
                }
                break;
        }
        $management->save();

        return redirect(route('ManagementController.showManagementPage', $management->period))
            ->with('message', '成功，已修改資料！'); //顯示成功訊息
    }

    //刪除
    public function deleteManagement($id)
    {
        //找到此id的資料
        $management = Management::find($id);

        //刪除此id的資料
        $management->delete();

        return redirect(route('ManagementController.showManagementPage', $management->period))
            ->with('message', '成功，已刪除資料！'); //顯示成功訊息
    }
}
