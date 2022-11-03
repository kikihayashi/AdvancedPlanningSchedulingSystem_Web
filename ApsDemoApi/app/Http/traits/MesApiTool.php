<?php
namespace App\Http\Traits;

use Illuminate\Support\Facades\Http;
use Validator;

trait MesApiTool
{
    //SAP獲取機種清單URL
    protected $EQUIPMENT_QUERY_URL = 'http://192.168.0.236:8066/Index/getOITM';

    //串API $this->EQUIPMENT_QUERY_URL，取得機種清單
    protected function getEquipmentList()
    {
        $response = Http::post($this->EQUIPMENT_QUERY_URL);
        //如果請求成功，生成json字串；失敗，生成空矩陣(通常是API連線逾時)
        return ($response->successful()) ? $response->json() :
        array(array('ItemCode' => '無資料', 'errorMessage' => $response->status()));
        //VPN跑太慢時，不看機種的話，可暫時先用空矩陣替代
        // return array(array('ItemCode' => '無資料'));
    }
}
