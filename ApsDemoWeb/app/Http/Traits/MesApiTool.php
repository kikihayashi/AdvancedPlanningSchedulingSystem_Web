<?php
namespace App\Http\Traits;

use App\Models\Exchange;
use App\Models\Partition;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Validator;

trait MesApiTool
{
    //Order No Key
    protected $ORDER_NO_KEY = [":ITEM_CODE", ":LOT"];
    //SAP獲取Order No URL
    protected $ORDER_NO_QUERY_URL = 'http://127.0.0.1:8088/api/items/orderNo?itemcode=:ITEM_CODE&lot=:LOT';
    //SAP獲取機種清單URL
    protected $EQUIPMENT_QUERY_URL = 'http://127.0.0.1:8088/api/items/list';
    //OFCT上傳至SAP URL
    protected $OFCT_UPLOAD_URL = 'http://127.0.0.1:8088/api/excel/OFCT';
    //ORDR上傳至SAP URL
    protected $ORDR_UPLOAD_URL = 'http://127.0.0.1:8088/api/excel/ORDR';

    //取得仕切資料(工數、成本)
    protected function getPartition($period_tw)
    {
        //MES資料庫(取工數、成本)
        $partitions = Partition::where('Period', $period_tw . '-04')
            ->orWhere('Period', $period_tw . '-10')
            ->orderBy('Period', 'ASC') //強制排序是上半年先排，如果沒上半年，才換下半年
            ->get();

        //製作工數成本的HashMap(key:機種，value:上下半年工數、成本)
        foreach ($partitions as $partition) {
            //第一次設定
            if (!isset($partitionMap[$partition->ProductNo])) {
                //如果第一次設定是上半年，那可能還有下半年
                if ($partition->Period == $period_tw . '-04') {
                    //上半年工數、成本
                    $partitionMap[$partition->ProductNo]['firstWorkHour'] = (double) $partition->WorkHour;
                    $partitionMap[$partition->ProductNo]['firstCost'] = (double) ($partition->TotalMaterial + $partition->WorkAmount);
                    //先假設下半年工數、成本為0，如果後續有會換掉
                    $partitionMap[$partition->ProductNo]['lastWorkHour'] = 0;
                    $partitionMap[$partition->ProductNo]['lastCost'] = 0;
                }
                //如果第一次設定是下半年，那一定沒有上半年
                else {
                    //上半年工數、成本一定為0
                    $partitionMap[$partition->ProductNo]['firstWorkHour'] = 0;
                    $partitionMap[$partition->ProductNo]['firstCost'] = 0;
                    //下半年工數、成本
                    $partitionMap[$partition->ProductNo]['lastWorkHour'] = (double) $partition->WorkHour;
                    $partitionMap[$partition->ProductNo]['lastCost'] = (double) ($partition->TotalMaterial + $partition->WorkAmount);
                }
            }
            //以下情況是已經有上半年，也有下半年要設定
            else {
                //下半年工數、成本
                $partitionMap[$partition->ProductNo]['lastWorkHour'] = (double) $partition->WorkHour;
                $partitionMap[$partition->ProductNo]['lastCost'] = (double) ($partition->TotalMaterial + $partition->WorkAmount);
            }
        }
        return $partitionMap ?? array();
    }

    //取得仕切資料(匯率)
    protected function getExchange($period_tw)
    {
        $firstExchange = Exchange::where('Period', $period_tw . '-04')
            ->select(DB::raw('(USDToNTDRate / USDToJPYRate) as JPYToNTDRate'))
            ->first();
        $lastExchange = Exchange::where('Period', $period_tw . '-10')
            ->select(DB::raw('(USDToNTDRate / USDToJPYRate) as JPYToNTDRate'))
            ->first();

        return array(
            'first' => ($firstExchange == null) ? 0 : $firstExchange->JPYToNTDRate,
            'last' => ($lastExchange == null) ? 0 : $lastExchange->JPYToNTDRate,
        );
    }

    //串API $this->ORDER_NO_QUERY_URL，取得Order No，在產生年度生產計劃時會用到
    protected function getOrderNo($value)
    {
        $response = Http::post(str_replace($this->ORDER_NO_KEY, $value, $this->ORDER_NO_QUERY_URL));
        $order_no = "";
        if ($response->successful() && count($response->json()) > 0) {
            foreach ($response->json() as $order) {
                $order_no = $order_no . $order . "\n";
            }
            $order_no = rtrim($order_no, "\n"); //刪掉最後一個換行
        }
        return $order_no;
    }

    //串API $this->EQUIPMENT_QUERY_URL，取得機種清單
    protected function getEquipmentList()
    {
        $response = Http::post($this->EQUIPMENT_QUERY_URL);
        //如果請求成功，生成json字串；失敗，生成空矩陣(通常是API連線逾時)
        return ($response->successful()) ? $response->json() :
        array(array('ItemCode' => '無資料', 'errorMessage' => $response->status()));
    }

    //串API，上傳至SAP
    protected function uploadToSap($type, $data)
    {
        $url = '';
        switch ($type) {
            case 'OFCT':
                $url = $this->OFCT_UPLOAD_URL;
                break;
            case 'ORDR':
                $url = $this->ORDR_UPLOAD_URL;
                break;
        }
        return Http::withBody(json_encode($data), 'application/json')->post($url);
    }

    //下載SAP報表(APS用)
    protected function downloadSapExcel($responseArray)
    {
        //設定儲存檔案的路徑
        $destinationPath = public_path() . '/file/';
        //如果沒有此資料夾
        if (!file_exists($destinationPath)) {
            //產生資料夾
            mkdir($destinationPath, 0777, true);
        }
        $date = date('YmdHis');
        $fileName = $date . '_SAP.xlsx';
        $filePath = $destinationPath . $fileName;
        $file = base64_decode($responseArray['base64']);
        $headers = array('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        $success = file_put_contents($destinationPath . $fileName, $file);

        if ($success) {
            return response()->download($filePath, $fileName, $headers)->deleteFileAfterSend(true);
        } else {
            return redirect()->back()
                ->with('errorMessage', '錯誤，下載失敗！(訊息：' . $responseArray['Msg'] . ')'); //顯示錯誤訊息
        }
    }

}
