<?php

namespace App\Http\Controllers;

use App\Http\Traits\BaseTool;
use App\Http\Traits\MesApiTool;
use App\Http\Traits\ValidatorTool;
use App\Models\Exchange;
use App\Models\Partition;
use App\Models\Period;
use App\Models\Progress;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

class PeriodController extends Controller
{
    use BaseTool, MesApiTool, ValidatorTool;

    /**
     * 期別與仕切維護------------------------------------------------------------------------------
     */
    //讀取
    public function showPeriodPage()
    {
        $data['title'] = "期別與結算";
        $data['period'] = Period::orderBy('period_tw', 'desc')->get();
        $data['monthMaps'] = $this->monthMaps;
        //操作權限畫面用
        $data['permission'] = $this->getUserPermission();

        return view('system.basicMenu.period', [
            'selection' => 'system',
            'openMenu' => 'basicMenu',
            'visitedId' => 'period',
            'tableData' => $data]);
    }

    //建立&更新
    public function writePeriod(Request $request, $id = 0)
    {
        //等同於$periodArray = $_POST['period'];
        $periodArray = $request->period;
        $period_tw = $periodArray[0];
        $period_jp = $periodArray[1];
        $start_date = $periodArray[2];
        $years = $periodArray[3];

        //建立驗證器
        $columnArray['id'] = $id;
        $columnArray['name'] = 'period_tw';
        $columnArray['value'] = $period_tw;
        $validator = $this->checkInputValid('period', $columnArray);
        //如果驗證失敗
        if ($validator->fails()) {
            return redirect()->back()
                ->with('errorMessage', $validator->errors()->all()[0]); //顯示錯誤訊息
        }

        $period = Period::where('id', $id)->first() ?? new Period();
        $period->period_tw = $period_tw;
        $period->period_jp = $period_jp;
        $period->start_date = $start_date;
        $period->years = $years;
        $period->save();

        //建立進度資料(大計劃資料維護頁面用)
        $progress = Progress::where('project_name', 'M')
            ->where('period', $period_tw)
            ->first();

        //如果無此期別資料，就新增一個進度資料
        if ($progress == null) {
            $progress = new Progress();
            $progress->project_name = 'M';
            $progress->version = 0;
            $progress->period = $period_tw;
            $progress->month = 0;
            $progress->progress_point = 0;
            $progress->create_project = 'N';
            $progress->save();
        }

        $message = ($id == 0) ? '成功，已新增資料！' : '成功，已修改資料！';

        return redirect(route('PeriodController.showPeriodPage'))
            ->with('message', $message); //顯示成功訊息
    }

    //刪除
    public function deletePeriod($id)
    {
        //找到此id的資料
        $period = Period::findOrFail($id);

        //刪除此id的資料
        $period->delete();

        return redirect(route('PeriodController.showPeriodPage'))
            ->with('message', '成功，已刪除資料！'); //顯示成功訊息
    }

    /**
     * 仕切維護分頁，方法一，取全部資料並回傳
     */
    //讀取
    public function showPartitionPage($period_tw)
    {
        //方法一，如果用方法二的話，就不需要以下程式碼(資料由ajaxFetchPartition取)
        foreach ($this->yearMaps as $yearMap) {
            $period = '';
            switch ($yearMap['page']) {
                case 'first':
                    $period = $period_tw . '-04';
                    break;
                case 'last':
                    $period = $period_tw . '-10';
                    break;
            }

            $partitions = Partition::join('MDProdPrice', 'MDProdPrice.Period', '=', 'MDProdPrice1.Period')
                ->selectRaw('MDProdPrice1.* , MDProdPrice.WorkingRate AS WorkingRate')
                ->where('MDProdPrice1.Period', $period)
                ->get();

            $dataArray = array();
            foreach ($partitions as $partition) {
                $dataArray[] = array(
                    $partition->ProductNo,
                    number_format($partition->JPMaterial),
                    number_format($partition->TWMaterial),
                    number_format($partition->TotalMaterial),
                    number_format($partition->WorkHour, 3, '.', ''),
                    number_format($partition->WorkingRate),
                    number_format($partition->WorkAmount),
                    '',//台灣工時，MES尚未給資料
                    number_format($partition->TotalMaterial + $partition->WorkAmount),
                    number_format($partition->FOBPrice),
                );
            }
            $data[$yearMap['page']] = $dataArray;
        }

        return view('system.basicMenu.periodDetail.partition',
            ['selection' => 'system',
                'visitedId' => 'partition',
                'period_tw' => $period_tw,
                'yearMaps' => $this->yearMaps,
                'monthMaps' => $this->monthMaps,
                'tableData' => $data, //用方法二就不需要
            ]);
    }

    /**
     * 方法二，用Ajax取資料，每次取一頁並回傳
     */
    public function ajaxFetchPartition(Request $request)
    {
        //上、下半年
        $type = $request->get('type');
        //第幾期
        $period_tw = $request->get('period_tw');
        $period = '';
        switch ($type) {
            case 'first':
                $period = $period_tw . '-04';
                break;
            case 'last':
                $period = $period_tw . '-10';
                break;
        }

        $draw = $request->get('draw');
        //從第幾筆資料開始
        $start = $request->get("start");
        //每頁顯示幾筆資料
        $rowPerPage = $request->get("length");

        //排序資料
        $columnIndex_arr = $request->get('order');
        $columnName_arr = $request->get('columns');
        //過濾資料
        $search_arr = $request->get('search');

        $columnSortOrder = $columnIndex_arr[0]['dir']; // asc or desc
        $columnIndex = $columnIndex_arr[0]['column']; // Column index
        $columnName = $columnName_arr[$columnIndex]['data']; // Column name
        $searchValue = $search_arr['value']; // Search value

        //總共幾筆資料
        $totalRecords = Partition::where('MDProdPrice1.Period', $period)
            ->select('count(*) as allCount')
            ->count();

        //過濾後，總共幾筆資料
        $totalRecordsWithFilter = Partition::where('MDProdPrice1.Period', $period)
            ->select('count(*) as allCount')
            ->where('ProductNo', 'like', '%' . $searchValue . '%')
            ->count();

        //提取資料
        $records = Partition::join('MDProdPrice', 'MDProdPrice.Period', '=', 'MDProdPrice1.Period')
            ->selectRaw('MDProdPrice1.* , MDProdPrice.WorkingRate AS WorkingRate')
            ->where('MDProdPrice1.Period', $period)
            ->where('MDProdPrice1.ProductNo', 'like', '%' . $searchValue . '%')
        // ->orderBy('CDate' , 'DESC')//排序資料
            ->orderBy($columnName, $columnSortOrder)
            ->skip($start)
            ->take($rowPerPage)
            ->get();

        $data_arr = array();

        foreach ($records as $record) {
            $ProductNo = $record->ProductNo;
            $JPMaterial = number_format($record->JPMaterial);
            $TWMaterial = number_format($record->TWMaterial);
            $TotalMaterial = number_format($record->TotalMaterial);
            $WorkHour = number_format($record->WorkHour, 3, '.', '');
            $WorkingRate = number_format($record->WorkingRate);
            $WorkAmount = number_format($record->WorkAmount);
            $WorkHourTw = '等待中';
            $Cost = number_format($record->TotalMaterial + $record->WorkAmount);
            $FOBPrice = number_format($record->FOBPrice);

            $data_arr[] = array(
                "ProductNo" => $ProductNo,
                "JPMaterial" => $JPMaterial,
                "TWMaterial" => $TWMaterial,
                "TotalMaterial" => $TotalMaterial,
                "WorkHour" => $WorkHour,
                "WorkingRate" => $WorkingRate,
                "WorkAmount" => $WorkAmount,
                "WorkHourTw" => $WorkHourTw,
                "Cost" => $Cost,
                "FOBPrice" => $FOBPrice,
            );
        }

        $response = array(
            "draw" => intval($draw),
            "iTotalRecords" => $totalRecords,
            "iTotalDisplayRecords" => $totalRecordsWithFilter,
            "aaData" => $data_arr,
        );
        echo json_encode($response);
        exit;
    }

    /**
     * 匯率設定分頁
     */
    public function showExchangePage($period_tw)
    {
        // dd($id);
        //方法一
        // $test = Exchange::on('sqlsrv_MES')->orderBy('Oid')->get();
        //方法二(會是Array)
        // $test = DB::connection('sqlsrv_MES')->select('select * from MDProdPrice');

        // $test = array(
        //     'first' => Exchange::on('sqlsrv_MES')->where('Period', $period_tw . '-04')->first(),
        //     'last' => Exchange::on('sqlsrv_MES')->where('Period', $period_tw . '-10')->first(),
        // );

        //只有一筆資料
        $data = array(
            'first' => Exchange::where('Period', $period_tw . '-04')
                ->select('MDProdPrice.*', DB::raw('(USDToNTDRate / USDToJPYRate) as JPYToNTDRate'))
                ->first(),
            'last' => Exchange::where('Period', $period_tw . '-10')
                ->select('MDProdPrice.*', DB::raw('(USDToNTDRate / USDToJPYRate) as JPYToNTDRate'))
                ->first(),
        );

        return view('system.basicMenu.periodDetail.exchange',
            ['selection' => 'system',
                'visitedId' => 'exchange',
                'period_tw' => $period_tw,
                'tableData' => $data,
                'yearMaps' => $this->yearMaps,
                'monthMaps' => $this->monthMaps,
            ]);
    }
}
