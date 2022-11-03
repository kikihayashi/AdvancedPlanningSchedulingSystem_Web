<?php

namespace App\Http\Controllers;

use App\Http\Traits\BaseTool;
use App\Http\Traits\MesApiTool;
use App\Http\Traits\ValidatorTool;
use App\Models\Equipment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class EquipmentController extends Controller
{
    use BaseTool, MesApiTool, ValidatorTool;

    /**
     *  機種清單----------------------------------------------------------------------------------
     */
    //讀取
    public function showEquipmentPage()
    {
        $itemCodeLineMap = null;
        $equipments = Equipment::orderBy('id')->get();

        foreach ($equipments as $equipment) {
            $itemCodeLineMap[$equipment->item_code] = $equipment->line;
        }

        //串API $this->EQUIPMENT_QUERY_URL
        $responseArray = $this->getEquipmentList();
        // dd($responseArray);
        $data['title'] = "機種清單";

        //如果請求成功，有資料
        if (count($responseArray) > 0 && $responseArray[0]['ItemCode'] != '無資料') {
            foreach ($responseArray as $responseData) {
                $dataArray[] = array(
                    $responseData['ItemName'], //將ItemName放置在DataTable的"操作"欄位裡，後續修改資料要用
                    $responseData['ItemCode'],
                    $responseData['日京永久品番'],
                    $responseData['機種日文名稱'],
                    $responseData['機種英文名稱'],
                    $responseData['在庫品番'],
                    $responseData['圖番'],
                    $itemCodeLineMap[$responseData['ItemCode']] ?? $responseData['Line'],
                    $responseData['是否內藏'],
                );
            }
            $data['equipment'] = $dataArray;
            //操作權限畫面用
            $data['permission'] = $this->getUserPermission();

            return view('system.basicMenu.equipment',
                ['selection' => 'system',
                    'openMenu' => 'basicMenu',
                    'visitedId' => 'equipment',
                    'tableData' => $data]);
        }
        //如果請求失敗
        else {
            $data['equipment'] = array();
            //操作權限畫面用
            $data['permission'] = $this->getUserPermission();

            return view('system.basicMenu.equipment',
                ['selection' => 'system',
                    'openMenu' => 'basicMenu',
                    'visitedId' => 'equipment',
                    'tableData' => $data])
                ->with('errorMessage', "錯誤！HTTP Error " . $responseArray[0]['ItemCode']); //顯示錯誤訊息(這個在view時不是session，只是一般變數)
        }
    }

    //更新
    public function updateEquipment(Request $request)
    {
        $equipmentArray = $request->equipment;
        $itemCode = $equipmentArray[0];
        $line = $equipmentArray[1];
        $is_hidden = ($equipmentArray[8] == "是") ? 'Y' : 'N';

        //建立驗證器
        $columnArray['id'] = $id ?? 0;
        $columnArray['name'] = 'line';
        $columnArray['value'] = $line;
        $validator = $this->checkInputValid('equipment', $columnArray);
        //如果驗證失敗
        if ($validator->fails()) {
            return redirect()->back()
                ->with('errorMessage', $validator->errors()->all()[0]); //顯示錯誤訊息
        }

        //檢查此機種編號是否已存在Equipment資料庫
        //如果Equipment資料庫裡沒有這個機種編號，代表要新增
        //如果Equipment資料庫裡有這個機種編號，代表要修改
        $equipment = Equipment::where('item_code', $itemCode)->first() ?? new Equipment();
        $equipment->item_code = $itemCode;
        $equipment->line = $line;
        $equipment->is_hidden = $is_hidden;
        $equipment->save();

        return redirect(route('EquipmentController.showEquipmentPage'))
            ->with('message', '成功，已修改資料！'); //顯示成功訊息
    }
}
