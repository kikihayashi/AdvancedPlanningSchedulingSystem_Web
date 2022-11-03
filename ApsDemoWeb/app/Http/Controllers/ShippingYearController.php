<?php

namespace App\Http\Controllers;

use App\Http\Traits\BaseTool;
use App\Http\Traits\MesApiTool;
use App\Http\Traits\ValidatorTool;
use App\Models\Period;
use App\Models\Setting;
use App\Models\ShippingYear;
use App\Models\Transport;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

class ShippingYearController extends Controller
{
    use BaseTool, MesApiTool, ValidatorTool;
    /**
     * 年度出荷計劃------------------------------------------------------------------------------
     */
    //讀取
    //$period_tw代表選擇的期別，$selectTab代表選擇的分頁(列表、新增)
    public function showShippingYearPage($period_tw = null, $selectTab = 'projectList')
    {
        /**
         * 優先處理，後續都會用到
         */
        //使用者切換其他期別用(排列：期別大->小)
        $periods = Period::orderBy('period_tw', 'desc')->get();
        //顯示本次期別用(若輸入無效期別，預設使用資料庫中最大的期別)
        $thisTimePeriod = $this->getProjectPeriod($period_tw, $periods->get(0)->period_tw);
        //取得目前進度用
        $progress = $this->getProjectProgress('SY', $thisTimePeriod->period_tw, 0);
        //MES資料庫(取工數、成本)
        $partition = $this->getPartition($progress['period']);
        //MES資料庫(取匯率)
        $exchange = $this->getExchange($progress['period']);

        /**
         * 年度出荷計劃頁面
         */
        //年度出荷計劃列表
        $projects = ShippingYear::where('version', $progress['version'])
            ->where('period', $progress['period'])
            ->orderBy('item_code', 'ASC')
            ->orderBy('lot_no', 'ASC')
            ->get()
            ->toArray();

        //將object轉成array，並加入partition將工數放入projects中
        $arrayProjects = array_map(function ($itemArray) use ($partition) {
            //工數
            $itemArray['firstWorkHour'] = ($partition[$itemArray['item_code']]['firstWorkHour'] ?? 0);
            $itemArray['lastWorkHour'] = ($partition[$itemArray['item_code']]['lastWorkHour'] ?? 0);
            //成本
            $itemArray['firstCost'] = ($partition[$itemArray['item_code']]['firstCost'] ?? 0);
            $itemArray['lastCost'] = ($partition[$itemArray['item_code']]['lastCost'] ?? 0);
            return $itemArray;
        }, $projects);

        //製作年度出荷列表HashMap(key:機種，value:年度出荷列表)
        $shippingYear = array();
        foreach ($arrayProjects as $project) {
            $shippingYear[$project['item_code']][] = $project;
        }

        /**
         * 新增機種頁面
         */
        //運輸模式
        $transportArray = Transport::orderBy('id')->get()->toArray();
        $transportMap = array();
        foreach ($transportArray as $transport) {
            $transportMap[$transport['id']] = $transport;
        }

        //本次期別標題
        $data['title'] = "年度出荷計劃 : 台京" . $progress['period'] . "期";
        //下拉式選單(選擇其他期別)
        $data['period'] = $periods;
        //顯示進度條用
        $data['progress'] = $progress;
        //取得ISO編號
        $data['iso'] = Setting::where('memo', 'iso_shipping_year')->first()->setting_value;
        //顯示分頁用
        $data['selectTab'] = (count($shippingYear) == 0) ? 'projectList' : $selectTab;
        //本次選擇期別
        $data['thisTimePeriod'] = $thisTimePeriod;
        //下拉式選單(運輸類型)
        $data['transport'] = $transportMap;
        //顯示上下半年匯率
        $data['exchange'] = $exchange;
        //顯示年度出荷計畫
        $data['shippingYear'] = $shippingYear;
        //顯示月度用
        $data['monthMaps'] = $this->monthMaps;
        //操作權限畫面用
        $data['permission'] = $this->getUserPermission();
        //串API取得機種清單，下拉式選單(機種清單)
        $data['equipment'] = $this->getEquipmentList();
        //新增表單設置
        $data['htmlInputForm'] = view('system.projectMenu.formDetail.shippingYear_form')
            ->with(compact('transportMap'))->render();

        return view('system.projectMenu.shippingYear', [
            'selection' => 'system',
            'openMenu' => 'projectMenu',
            'visitedId' => 'shippingYear',
            'tableData' => $data]);
    }

    //新增
    public function createShippingYear(Request $request)
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
            $columnArray['name4'] = 'version';
            $columnArray['value4'] = $request->version;
            $columnArray['name5'] = 'transport_id';
            $columnArray['value5'] = $request->transport_id[$i];
            $columnArray['name6'] = 'transport_name';
            $columnArray['value6'] = Transport::where('id', $request->transport_id[$i])->first()->name;
            $columnArray['name7'] = 'lot_total';
            $columnArray['value7'] = $request->lot_total[$i];

            $validator = $this->checkInputValid('shipping_year', $columnArray);
            //如果驗證失敗
            if ($validator->fails()) {
                return redirect(route('ShippingYearController.showShippingYearPage',
                    ['period_tw' => $request->period_tw, 'selectTab' => 'projectCreate']))
                    ->with('errorMessage', $validator->errors()->all()[0]); //顯示錯誤訊息
            }
        }

        for ($i = 0; $i < count($request->lot_no); $i++) {
            $shippingYear = new ShippingYear();
            $shippingYear->version = $request->version;
            $shippingYear->period = $request->period_tw;
            $shippingYear->item_code = $request->item_code;
            $shippingYear->item_name = $request->item_name;
            $shippingYear->lot_no = $request->lot_no[$i];
            $shippingYear->lot_total = $request->lot_total[$i];
            $shippingYear->transport_id = $request->transport_id[$i];
            $shippingYear->remark = $request->remark[$i];
            for ($index = 0; $index < 12; $index++) {
                $month = $this->monthMaps[$index]['page']; //4、5、6...3月
                $shippingYear->{'month_' . $month} = 0;
            }
            $shippingYear->save();
        }

        return redirect(route('ShippingYearController.showShippingYearPage', $request->period_tw))
            ->with('message', '成功，已新增資料！'); //顯示成功訊息
    }

    //編輯年度出荷計劃頁面
    public function editShippingYearPage($itemCode, $period_tw, $version)
    {
        //驗證操作是否合法
        $result = $this->checkOperationValid(0, 'project_crud');
        if ($result['code'] != 200) {
            return view('errors.custom_error', $result);
        }
        $shippingYear = ShippingYear::join('parameter_transport', 'shipping_year.transport_id', '=', DB::raw("CAST(parameter_transport.id AS VARCHAR)"))
            ->selectRaw('shipping_year.* , parameter_transport.name AS transport_name')
            ->where('period', $period_tw)
            ->where('version', $version)
            ->where('item_code', $itemCode)
            ->orderBy('lot_no')
            ->get()
            ->toArray();

        if (count($shippingYear) == 0) {
            return view('errors.custom_error', ['code' => 404, 'message' => '找不到此頁面！']);
        }

        $data['title'] = $period_tw . '期年度出荷計劃：編輯 ' . $itemCode;
        //年度生產計劃列表
        $data['shippingYear'] = $shippingYear;

        return view('system.projectMenu.editDetail.shippingYear_edit', [
            'selection' => 'system',
            'tableData' => $data]);
    }

    //修改
    public function updateShippingYear(Request $request, $type)
    {
        switch ($type) {
            //更新單一月份數量
            case 'SINGLE':
                //確認輸入的數量是否為空值或小於0
                for ($i = 0; $i < count($request->lotNumber); $i++) {
                    if ($request->lotNumber[$i] == null || $request->lotNumber[$i] < 0) {
                        return redirect()->back()
                            ->with('errorMessage', "錯誤，數量不可為空，且不可小於0！"); //顯示錯誤訊息
                    }
                }
                for ($i = 0; $i < count($request->lot_no); $i++) {
                    $shippingYear = ShippingYear::where('period', $request->period_tw)
                        ->where('version', $request->version)
                        ->where('item_code', $request->item_code)
                        ->where('lot_no', $request->lot_no[$i])
                        ->where('transport_id', $request->transport_id[$i])
                        ->first();
                    $shippingYear->{'month_' . $request->month} = $request->lotNumber[$i];
                    $shippingYear->save();
                }
                break;

            //更新總數量(多個)
            case 'MULTIPLE':
                for ($i = 0; $i < count($request->lot_no); $i++) {
                    $shippingYear = ShippingYear::where('period', $request->period_tw)
                        ->where('version', $request->version)
                        ->where('item_code', $request->item_code)
                        ->where('lot_no', $request->lot_no[$i])
                        ->where('transport_id', $request->transport_id[$i])
                        ->first();
                    $shippingYear->lot_total = $request->lot_total[$i];
                    $shippingYear->remark = $request->remark[$i];
                    $shippingYear->save();
                }
                break;
        }
        return redirect(route('ShippingYearController.showShippingYearPage', $request->period_tw))
            ->with('message', '成功，已修改資料！'); //顯示成功訊息
    }

    //刪除
    public function deleteShippingYear(Request $request, $type)
    {
        switch ($type) {
            //刪除單項機種
            case 'SINGLE':
                $shippingYear = ShippingYear::where('period', $request->period_tw)
                    ->where('version', $request->version)
                    ->where('item_code', $request->item_code)
                    ->delete();
                break;
            //刪除總數量(多個)
            case 'MUlTIPLE':
                foreach ($request->deleteId as $id) {
                    $shippingYear = ShippingYear::where('id', $id)
                        ->delete();
                }
                break;
        }
        return redirect(route('ShippingYearController.showShippingYearPage', $request->period_tw))
            ->with('message', '成功，已刪除資料！'); //顯示成功訊息
    }
}
