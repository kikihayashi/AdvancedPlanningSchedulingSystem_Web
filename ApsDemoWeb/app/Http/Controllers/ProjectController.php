<?php

namespace App\Http\Controllers;

use App\Exports\ManagementExport;
use App\Exports\ProductionMonthExport;
use App\Exports\ProductionYearExport;
use App\Exports\ShippingMonthExport;
use App\Exports\ShippingYearExport;
use App\Models\Management;
use App\Models\ProductionMonth;
use App\Models\ProductionYear;
use App\Models\Progress;
use App\Models\ShippingMonth;
use App\Models\ShippingYear;
use App\Http\Traits\BaseTool;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;

class ProjectController extends Controller
{
    use BaseTool;

    private $EXCEL_NAME_M = 'B-1-大計劃.xlsx';
    private $EXCEL_NAME_PY = 'B-2-年度生產計劃表.xlsx';
    private $EXCEL_NAME_SY = 'B-3-年度出荷計劃.xlsx';
    private $EXCEL_NAME_PM = 'B-4-月度生產計劃.xlsx';
    private $EXCEL_NAME_SM = 'B-5-月度出荷計畫.xlsx';

    //審核大計劃，變更進度點
    public function reviewProject($projectType, $period_tw, $month, $operation)
    {
        $progress = Progress::where('project_name', $projectType)
            ->where('period', $period_tw)
            ->where('month', $month)
            ->first();

        //如果此期別，未有資料，建立初始值(此為針對父計畫版本為0，當前計畫提出版本修訂的情況)
        if ($progress == null) {
            $progress = new Progress();
            $progress->project_name = $projectType;
            $progress->version = 0;
            $progress->period = $period_tw;
            $progress->month = $month;
            $progress->progress_point = 0;
            $progress->create_project = 'N';
        }

        switch ($operation) {
            case 'send':
                $message = '提出版本修訂'; //直接到進度點1
                $progress->progress_point = 1;
                break;
            case 'submit':
                $message = '送出審核'; //直接到進度點2
                $progress->progress_point = 2;
                break;
            case 'cancel':
                $message = '放棄變更'; //直接到進度點0
                $progress->progress_point = 0;
                break;
            case 'approve':
                $message = '核准'; //進度點加1
                $progress->progress_point += 1;
                break;
            case 'reject':
                $message = '駁回'; //直接到進度點1
                $progress->progress_point = 1;
                break;
        }

        //如果進度點大於4(代表執行董事同意，版本必須更新，結束後子計畫可以選產生計畫表)
        if ($progress->progress_point > 4) {
            $progress->progress_point = 0; //直接到進度點0
            $progress->version += 1; //版本加1

            //複製當前計畫，一份保留當前版本、一份版本+1
            $this->copyProject($progress);

            //允許產生計畫表
            switch ($projectType) {
                case 'M': //大計畫
                    $this->allowCreateProject('PY', $progress->period, 0);
                    $this->allowCreateProject('SY', $progress->period, 0);
                    break;
                case 'PY': //年度生產計畫
                    for ($month = 1; $month <= 12; $month++) {
                        $this->allowCreateProject('PM', $progress->period, $month);
                    }
                    break;
                case 'SY': //年度出荷計畫
                    for ($month = 1; $month <= 12; $month++) {
                        $this->allowCreateProject('SM', $progress->period, $month);
                    }
                    break;
            }
        }
        $progress->save();

        switch ($projectType) {
            case 'M':
                return redirect(route('ManagementController.showManagementPage', $period_tw))
                    ->with('message', '成功，已' . $message . '！'); //顯示成功訊息
            case 'PY':
                return redirect(route('ProductionYearController.showProductionYearPage', $period_tw))
                    ->with('message', '成功，已' . $message . '！'); //顯示成功訊息
            case 'SY':
                return redirect(route('ShippingYearController.showShippingYearPage', $period_tw))
                    ->with('message', '成功，已' . $message . '！'); //顯示成功訊息
            case 'PM':
                return redirect(route('ProductionMonthController.showProductionMonthPage', ['period_tw' => $period_tw, 'month' => $month]))
                    ->with('message', '成功，已' . $message . '！'); //顯示成功訊息
            case 'SM':
                return redirect(route('ShippingMonthController.showShippingMonthPage', ['period_tw' => $period_tw, 'month' => $month]))
                    ->with('message', '成功，已' . $message . '！'); //顯示成功訊息
        }
    }

    //將目前版本複製一份存到資料庫(存完之後會有資料一樣的新舊版本)
    private function copyProject($progress)
    {
        //先找出舊版本
        switch ($progress->project_name) {
            case 'M':
                $projects = Management::where('period', $progress->period)
                    ->where('version', $progress->version - 1)
                    ->get();
                break;
            case 'PY':
                $projects = ProductionYear::where('period', $progress->period)
                    ->where('version', $progress->version - 1)
                    ->get();
                break;
            case 'SY':
                $projects = ShippingYear::where('period', $progress->period)
                    ->where('version', $progress->version - 1)
                    ->get();
                break;
            case 'PM':
                $projects = ProductionMonth::where('period', $progress->period)
                    ->where('month', $progress->month)
                    ->where('version', $progress->version - 1)
                    ->get();
                break;
            case 'SM':
                $projects = ShippingMonth::where('period', $progress->period)
                    ->where('month', $progress->month)
                    ->where('version', $progress->version - 1)
                    ->get();
                break;
        }

        //將舊版本存一份到資料庫，並變更成新版本
        foreach ($projects as $project) {
            $projectRecord = $project->replicate();
            $projectRecord->version = $progress->version;
            $projectRecord->save(); //這樣才會產生id
        }
    }

    //允許計畫可以點選"產生計畫表"
    private function allowCreateProject($projectType, $period_tw, $month)
    {
        //找出此期別的projectType計畫進度
        $progress = Progress::where('project_name', $projectType)
            ->where('period', $period_tw)
            ->where('month', $month)
            ->first();

        //如果此期別，未有資料，建立一個初始資料
        if ($progress == null) {
            $progress = new Progress();
            $progress->project_name = $projectType;
            $progress->version = 0;
            $progress->period = $period_tw;
            $progress->month = $month;
            $progress->progress_point = 0;
        }
        //這是代表可在頁面按"產生計畫表"，產生計畫資料
        $progress->create_project = 'Y';
        $progress->save();
    }

    //產生計畫表
    public function createProject($projectType, $period_tw, $month, $version)
    {
        switch ($projectType) {
            case 'PY':
                return $this->createProductionYear($period_tw, $version);
            case 'SY':
                return $this->createShippingYear($period_tw, $version);
            case 'PM':
                return $this->createProductionMonth($period_tw, $month, $version);
            case 'SM':
                return $this->createShippingMonth($period_tw, $month, $version);
        }
    }

    //產生年度生產計畫
    private function createProductionYear($period_tw, $version)
    {
        //找出大計劃最新版本
        $versionM = Progress::where('project_name', 'M')
            ->where('period', $period_tw)
            ->first()
            ->version;

        //先找出最新版本的前一版大計劃(因為最新版本有可能在修改中)
        $managements = Management::where('period', $period_tw)
            ->where('version', intval($versionM) - 1)
            ->get();

        //找出此期別的年度生產計畫進度
        $progress = Progress::where('project_name', 'PY')
            ->where('period', $period_tw)
            ->first();

        $version = 0; //年度生產計畫目前版本，預設為0

        //如果此期別，已有年度生產資料
        if ($progress != null) {
            //先記錄當下版本
            $version = $progress->version;
            //將當下版本的資料刪除
            ProductionYear::where('period', $period_tw)
                ->where('version', $version)
                ->delete();
        }

        //將大計劃內容存給年度生產計畫
        foreach ($managements as $management) {
            $productionYear = new ProductionYear();
            $productionYear->version = $version;
            $productionYear->period = $period_tw;
            $productionYear->item_code = $management->item_code;
            $productionYear->item_name = $management->item_name;
            $productionYear->lot_no = $management->lot_no;
            $productionYear->lot_total = $management->lot_total;
            $productionYear->remark = $management->remark_other;
            $productionYear->deadline = "";
            $productionYear->order_no = $management->order_no;
            $productionYear->product_date = $management->product_date;
            $productionYear->material_date = $management->material_date;

            foreach ($this->monthMaps as $monthMap) {
                //計算實際月份
                $month = $monthMap['page'];
                //取得西元年
                $year = intval($period_tw) + (($month < 4) ? 1970 : 1969);
                $productionYear->{'month_' . $month} = $management->{'month_' . $month};
                if (intval($productionYear->{'month_' . $month}) > 0) {
                    $productionYear->{'range_' . $month} = '1-' . cal_days_in_month(CAL_GREGORIAN, $month, $year);
                }
            }
            $productionYear->save();
        }
        return redirect(route('ProductionYearController.showProductionYearPage', $period_tw))
            ->with('message', '成功，已建立年度生產計劃！'); //顯示成功訊息
    }

    //產生年度出荷計畫
    private function createShippingYear($period_tw, $version)
    {
        //找出大計劃最新版本
        $versionM = Progress::where('project_name', 'M')
            ->where('period', $period_tw)
            ->first()
            ->version;

        //先找出最新版本的前一版大計劃(因為最新版本有可能在修改中)
        $managements = Management::where('period', $period_tw)
            ->where('version', intval($versionM) - 1)
            ->get();

        //找出此期別的年度出荷計畫進度
        $progress = Progress::where('project_name', 'SY')
            ->where('period', $period_tw)
            ->first();

        $version = 0; //年度生產計畫目前版本，預設為0

        //如果此期別，已有年度生產資料
        if ($progress != null) {
            //先記錄當下版本
            $version = $progress->version;
            //將當下版本的資料刪除
            ShippingYear::where('period', $period_tw)
                ->where('version', $version)
                ->delete();
        }

        //將大計劃內容存給年度出荷計畫
        foreach ($managements as $management) {
            $productionYear = new ShippingYear();
            $productionYear->version = $version;
            $productionYear->period = $period_tw;
            $productionYear->item_code = $management->item_code;
            $productionYear->item_name = $management->item_name;
            $productionYear->lot_no = $management->lot_no;
            $productionYear->lot_total = $management->lot_total;
            $productionYear->transport_id = $management->transport_id;
            $productionYear->remark = $management->remark_transport;
            foreach ($this->monthMaps as $monthMap) {
                //計算實際月份
                $month = $monthMap['page'];
                $productionYear->{'month_' . $month} = $management->{'month_' . $month};
            }
            $productionYear->save();
        }
        return redirect(route('ShippingYearController.showShippingYearPage', $period_tw))
            ->with('message', '成功，已建立年度出荷計劃！'); //顯示成功訊息
    }

    //產生月度生產計畫
    private function createProductionMonth($period_tw, $month, $version)
    {
        //找出年度生產計畫最新版本
        $versionPY = Progress::where('project_name', 'PY')
            ->where('period', $period_tw)
            ->first()
            ->version;

        //找出最新版本的前一版年度生產計畫(因為最新版本有可能在修改中)
        $productionYears = ProductionYear::where('period', $period_tw)
            ->where('version', intval($versionPY) - 1)
            ->get();

        //找出此期別的月度生產計畫進度
        $progress = Progress::where('project_name', 'PM')
            ->where('period', $period_tw)
            ->where('month', $month)
            ->first();

        $version = 0; //月度生產計畫目前版本，預設為0

        //如果此期別，已有月度生產資料
        if ($progress != null) {
            //先記錄當下版本
            $version = $progress->version;
            //將當下版本的資料全部刪除
            ProductionMonth::where('period', $period_tw)
                ->where('month', $month)
                ->where('version', $version)
                ->delete();
        }

        //將年度生產計畫內容存給月度生產計畫
        foreach ($productionYears as $productionYear) {
            if (intval($productionYear->{'month_' . $month}) > 0) {
                $productionMonth = new ProductionMonth();
                $productionMonth->version = $version;
                $productionMonth->period = $period_tw;
                $productionMonth->item_code = $productionYear->item_code;
                $productionMonth->item_name = $productionYear->item_name;
                $productionMonth->lot_no = $productionYear->lot_no;
                $productionMonth->month = $month;
                $productionMonth->previous_month_number = 0;
                $productionMonth->this_month_number = $productionYear->{'month_' . $month};

                $rangeArray = $productionYear->{'range_' . $month};
                if ($rangeArray != null) {
                    $productionMonth->start_day_array = explode("-", $rangeArray)[0];
                    $productionMonth->end_day_array = explode("-", $rangeArray)[1];
                }
                $productionMonth->save();
            }
        }
        return redirect(route('ProductionMonthController.showProductionMonthPage', ['period_tw' => $period_tw, 'month' => $month]))
            ->with('message', '成功，已建立月度生產計劃！'); //顯示成功訊息
    }

    //產生月度出荷計畫
    private function createShippingMonth($period_tw, $month, $version)
    {
        //找出年度出荷計畫最新版本
        $versionSY = Progress::where('project_name', 'SY')
            ->where('period', $period_tw)
            ->first()
            ->version;

        //找出最新版本的前一版年度出荷計畫(因為最新版本有可能在修改中)
        $shippingYears = DB::table('shipping_year')
            ->select('item_code', 'item_name', DB::raw('SUM (month_' . $month . ') AS month_' . $month))
            ->where('period', $period_tw)
            ->where('version', intval($versionSY) - 1)
            ->groupBy('item_code', 'item_name') //只需要不重複的機種和名稱
            ->get();

        //找出此期別的月度出荷計畫進度
        $progress = Progress::where('project_name', 'SM')
            ->where('period', $period_tw)
            ->where('month', $month)
            ->first();

        $version = 0; //月度出荷計畫目前版本，預設為0

        //如果此期別，已有月度出荷資料
        if ($progress != null) {
            //先記錄當下版本
            $version = $progress->version;
            //將當下版本的資料全部刪除
            ShippingMonth::where('period', $period_tw)
                ->where('month', $month)
                ->where('version', $version)
                ->delete();
        }

        //將年度出荷計畫內容存給月度出荷計畫
        foreach ($shippingYears as $shippingYear) {
            if (intval($shippingYear->{'month_' . $month}) > 0) {
                $shippingMonth = new ShippingMonth();
                $shippingMonth->version = $version;
                $shippingMonth->period = $period_tw;
                $shippingMonth->item_code = $shippingYear->item_code;
                $shippingMonth->item_name = $shippingYear->item_name;
                $shippingMonth->lot_no = 0;
                $shippingMonth->month = $month;
                $shippingMonth->date = 0;
                $shippingMonth->transport_id = -1;
                $shippingMonth->number = 0;
                $shippingMonth->save();
            }
        }
        return redirect(route('ShippingMonthController.showShippingMonthPage', ['period_tw' => $period_tw, 'month' => $month]))
            ->with('message', '成功，已建立月度出荷計劃！'); //顯示成功訊息
    }

    //匯出Excel報表
    public function exportProject($projectType, $period_tw, $month)
    {
        switch ($projectType) {
            case 'M':
                return Excel::download(
                    new ManagementExport($period_tw), $this->EXCEL_NAME_M);
            case 'PY':
                return Excel::download(
                    new ProductionYearExport($period_tw), $this->EXCEL_NAME_PY);
            case 'SY':
                return Excel::download(
                    new ShippingYearExport($period_tw), $this->EXCEL_NAME_SY);
            case 'PM':
                return Excel::download(
                    new ProductionMonthExport($period_tw, $month), $this->EXCEL_NAME_PM);
            case 'SM':
                return Excel::download(
                    new ShippingMonthExport($period_tw, $month), $this->EXCEL_NAME_SM);
        }
    }
}
