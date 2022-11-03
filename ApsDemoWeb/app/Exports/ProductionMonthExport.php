<?php

namespace App\Exports;

use App\Http\Traits\BaseTool;
use App\Http\Traits\MesApiTool;
use App\Models\Schedule;
use App\Models\Setting;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class ProductionMonthExport implements FromView, WithEvents, WithStyles, WithTitle
{
    use BaseTool, MesApiTool;

    protected $period_tw;
    protected $month;
    protected $data;

    public function __construct($period_tw, $month)
    {
        $this->period_tw = $period_tw;
        $this->month = $month;
    }

    public function view(): View
    {
        $period_tw = $this->period_tw;
        $month = $this->month;
        $year = $period_tw + (($month > 3) ? 1969 : 1970);
        $version = $this->getProjectProgress('PM', $period_tw, $month)['version'];
        $partition = $this->getPartition($period_tw);

        //取得選擇月份的總天數
        $days = cal_days_in_month(CAL_GREGORIAN, $month, $year);

        //ISO編號
        $iso = Setting::where('memo', 'iso_production_month')->first()->setting_value;

        //員工人數
        $employee = Setting::where('memo', 'employee_numbers')->first()->setting_value;

        //取得行事曆
        $schedules = Schedule::join('parameter_calendar', 'schedule.calendar_id', '=', DB::raw("CAST(parameter_calendar.id AS VARCHAR)"))
            ->selectRaw('schedule.* , parameter_calendar.is_holiday AS is_holiday')
            ->where('year', $year)
            ->where('month', $month)
            ->get()
            ->toArray();

        //工作日天數
        $workdays = 0;

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
            $workdays += ($isHolidayMap[$day] == 'Y') ? 0 : 1;
        }

        //月度計畫合併線別內藏，SQL指令：COALESCE改用ISNULL也可以，不過PostgreSQL、MySQL沒有ISNULL用法
        $sqlCommand = "SELECT production_month.*,
        COALESCE(equipment.line,'0') AS line_no,
        COALESCE(equipment.is_hidden,'N') AS is_hidden
        FROM production_month LEFT JOIN equipment
        ON production_month.item_code = equipment.item_code
        WHERE period = ':period' AND month = ':month' AND version = ':version'";

        //替換對應值
        $sqlCommand = str_replace(':period', $period_tw, $sqlCommand);
        $sqlCommand = str_replace(':month', $month, $sqlCommand);
        $sqlCommand = str_replace(':version', $version, $sqlCommand);

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

        $data['iso'] = $iso;
        $data['employee'] = $employee;
        $data['workdays'] = $workdays;
        $data['period_tw'] = $period_tw;
        $data['month'] = $month;
        $data['isHolidayMap'] = $isHolidayMap;
        $data['thisTimeDays'] = $days;
        $data['progress'] = $this->getProjectProgress('PM', $period_tw, $month);
        $data['productionMonth'] = $productionMonth;

        $this->data = $data;

        return view('system.projectMenu.exportDetail.productionMonth_export', [
            'tableData' => $data,
        ]);
    }

    //標題設定-WithTitle
    public function title(): string
    {
        return '月度生產計劃';
    }

    //字體設定-WithStyles
    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }

    //Cell大小設定-WithEvents
    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                //所需資料
                $days = $this->data['thisTimeDays'];
                $productionMonths = $this->data['productionMonth'];
                $progress = $this->data['progress'];
                $letterMap = $this->getLetterMap();

                //標題--------------------------------------------------------------------------------------------------------------------
                $letterAscii = ord('A');
                $startColumn = $letterMap[$letterAscii];
                $endColumn = $letterMap[$letterAscii + 5 + $days + 1];
                //高度
                $event->sheet->getDelegate()->getRowDimension('1')->setRowHeight(20);
                //合併
                $event->sheet->mergeCells($startColumn . '1' . ':' . $endColumn . '1');
                //置中
                $event->sheet->getDelegate()->getStyle($startColumn . '1' . ':' . $endColumn . '1')
                    ->getAlignment()->setHorizontal('center')->setVertical('center');

                //休日、橫向、縱向、二極体付、改修品--------------------------------------------------------------------------------------------------------------------
                //合併
                $event->sheet->mergeCells($startColumn . '2' . ':' . $endColumn . '2');
                //垂直置中靠左
                $event->sheet->getDelegate()->getStyle($startColumn . '2' . ':' . $endColumn . '2')
                    ->getAlignment()->setHorizontal('left')->setVertical('center');

                //機種名、仕切、製番、前月、本月、完成工數--------------------------------------------------------------------------------------------------------------------
                //機種名寬度
                $event->sheet->getDelegate()->getColumnDimension('A')->setWidth(20);
                //本月合併
                $event->sheet->mergeCells('E3:F3');
                $event->sheet->getDelegate()->getColumnDimension('E')->setWidth(3);
                //第3行置中
                $event->sheet->getDelegate()->getStyle($startColumn . '3' . ':' . $endColumn . '3')
                    ->getAlignment()->setHorizontal('center')->setVertical('center');

                //機種名、仕切、製番、前月、本月、合計、完成工數的資料--------------------------------------------------------------------------------------------------
                $letterAscii = ord('A');
                $row = 4;
                foreach ($productionMonths as $productionMonth) {
                    //機種名合併
                    $event->sheet->mergeCells($letterMap[$letterAscii] . $row . ':' . $letterMap[$letterAscii] . ($row + 1));
                    //仕切合併
                    $event->sheet->mergeCells($letterMap[$letterAscii + 1] . $row . ':' . $letterMap[$letterAscii + 1] . ($row + 1));
                    //製番合併
                    $event->sheet->mergeCells($letterMap[$letterAscii + 2] . $row . ':' . $letterMap[$letterAscii + 2] . ($row + 1));
                    //前月合併
                    $event->sheet->mergeCells($letterMap[$letterAscii + 3] . $row . ':' . $letterMap[$letterAscii + 3] . ($row + 1));
                    //本月合併
                    $event->sheet->mergeCells($letterMap[$letterAscii + 4] . $row . ':' . $letterMap[$letterAscii + 5] . ($row + 1));
                    //完成工數合併
                    $event->sheet->mergeCells($letterMap[$letterAscii + 5 + $days + 1] . $row . ':' . $letterMap[$letterAscii + 5 + $days + 1] . ($row + 1));
                    $row += 2;
                }
                //合計合併
                $event->sheet->mergeCells($letterMap[$letterAscii] . $row . ':' . $letterMap[$letterAscii + 3] . $row);
                //本月合計合併
                $event->sheet->mergeCells($letterMap[$letterAscii + 4] . $row . ':' . $letterMap[$letterAscii + 5] . $row);
                //位置
                $letterAscii = ord('A');
                $startRow = 4;
                $endRow = $startRow + 2 * (count($productionMonths) - 1) + 1;

                //機種名垂直置中靠左
                $event->sheet->getDelegate()->getStyle($letterMap[$letterAscii] . $startRow . ':' . $letterMap[$letterAscii] . $endRow)
                    ->getAlignment()->setHorizontal('left')->setVertical('center');
                //仕切置中
                $event->sheet->getDelegate()->getStyle($letterMap[$letterAscii + 1] . $startRow . ':' . $letterMap[$letterAscii + 1] . $endRow)
                    ->getAlignment()->setHorizontal('center')->setVertical('center');
                //製番置中
                $event->sheet->getDelegate()->getStyle($letterMap[$letterAscii + 2] . $startRow . ':' . $letterMap[$letterAscii + 2] . $endRow)
                    ->getAlignment()->setHorizontal('center')->setVertical('center');
                //前月垂直置中靠右
                $event->sheet->getDelegate()->getStyle($letterMap[$letterAscii + 3] . $startRow . ':' . $letterMap[$letterAscii + 3] . $endRow)
                    ->getAlignment()->setHorizontal('right')->setVertical('center');
                //本月垂直置中靠右(包含合計的)
                $event->sheet->getDelegate()->getStyle($letterMap[$letterAscii + 4] . $startRow . ':' . $letterMap[$letterAscii + 5] . ($endRow + 1))
                    ->getAlignment()->setHorizontal('right')->setVertical('center');
                //完成工數置中
                $event->sheet->getDelegate()->getStyle($letterMap[$letterAscii + 5 + $days + 1] . $startRow . ':' . $letterMap[$letterAscii + 5 + $days + 1] . ($endRow + 1))
                    ->getAlignment()->setHorizontal('center')->setVertical('center');
                //合計置中
                $event->sheet->getDelegate()->getStyle($letterMap[$letterAscii] . ($endRow + 1) . ':' . $letterMap[$letterAscii + 3] . ($endRow + 1))
                    ->getAlignment()->setHorizontal('center')->setVertical('center');

                //邊框設定 機種名、仕切、製番、前月、本月
                $letterAscii = ord('A');
                $startColumn = $letterMap[$letterAscii];
                $endColumn = $letterMap[$letterAscii + 5];
                $startRow = 3;
                $endRow = $startRow + 2 * count($productionMonths) + 1;
                $event->sheet->getStyle($startColumn . $startRow . ':' . $endColumn . $endRow)
                    ->applyFromArray([
                        'borders' => [
                            'allBorders' => [
                                'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                                'color' => ['argb' => '000000'],
                            ],
                        ],
                    ]);

                //邊框設定 日期、完成工數
                $letterAscii = ord('G');
                $startColumn = $letterMap[$letterAscii];
                $endColumn = $letterMap[$letterAscii + $days];
                $row = 3;
                $event->sheet->getStyle($startColumn . $row . ':' . $endColumn . $row)
                    ->applyFromArray([
                        'borders' => [
                            'allBorders' => [
                                'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                                'color' => ['argb' => '000000'],
                            ],
                        ],
                    ]);

                //邊框設定 完成工數
                $letterAscii = ord('G');
                $column = $letterMap[$letterAscii + $days];
                $startRow = 4;
                $endRow = $startRow + 2 * count($productionMonths);
                $event->sheet->getStyle($column . $startRow . ':' . $column . $endRow)
                    ->applyFromArray([
                        'borders' => [
                            'allBorders' => [
                                'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                                'color' => ['argb' => '000000'],
                            ],
                        ],
                    ]);

                //邊框設定 日期(最底部)
                $letterAscii = ord('G');
                $startColumn = $letterMap[$letterAscii];
                $endColumn = $letterMap[$letterAscii + $days - 1];
                $row = 4 + 2 * count($productionMonths);
                $event->sheet->getStyle($startColumn . $row . ':' . $endColumn . $row)
                    ->applyFromArray([
                        'borders' => [
                            'top' => [
                                'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                                'color' => ['argb' => '000000'],
                            ],
                            'bottom' => [
                                'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                                'color' => ['argb' => '000000'],
                            ],
                        ],
                    ]);

                //邊框設定 日期(中間)
                $letterAscii = ord('F'); //本月計畫生產台數欄位
                $startColumn = $letterMap[$letterAscii + 1];
                $endColumn = $letterMap[$letterAscii + $days];
                $row = 4;
                foreach ($productionMonths as $productionMonth) {
                    $startArray = $productionMonth['start'];
                    $endArray = $productionMonth['end'];
                    for ($index = 0; $index < count($startArray); $index++) {
                        for ($nowDay = $startArray[$index]; $nowDay <= $endArray[$index]; $nowDay++) {
                            //這是每個機種的生產日期
                            $event->sheet->getStyle($letterMap[$letterAscii + $nowDay] . $row . ':' . $letterMap[$letterAscii + $nowDay] . $row)
                                ->applyFromArray([
                                    'borders' => [
                                        'bottom' => [
                                            'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THICK,
                                            'color' => ['argb' => '0E7EED'],
                                        ],
                                    ],
                                ]);
                        }
                    }
                    //這是每個機種日期的間隔
                    $event->sheet->getStyle($startColumn . ($row + 1) . ':' . $endColumn . ($row + 1))
                        ->applyFromArray([
                            'borders' => [
                                'bottom' => [
                                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                                    'color' => ['argb' => '000000'],
                                ],
                            ],
                        ]);
                    $row += 2;
                }

                //日期寬度-----------------------------------------------------------------------------------------------------------------------------
                $letterAscii = ord('G');
                for ($i = 0; $i < $days; $i++) {
                    $event->sheet->getDelegate()->getColumnDimension($letterMap[$letterAscii + $i])->setWidth(3);
                }

                //簽章設定----------------------------------------------------------------------------------------------------------------
                $letterAscii = ord('A');
                $row = 3 + 2 * count($productionMonths) + 4;
                //日靠右
                $event->sheet->getDelegate()->getStyle($letterMap[$letterAscii + 3] . $row)
                    ->getAlignment()->setHorizontal('right')->setVertical('center');
                //預定投入工數、預定完成工數、預定生產效率靠右
                $event->sheet->getDelegate()->getStyle($letterMap[$letterAscii] . ($row + 1) . ':' . $letterMap[$letterAscii] . ($row + 3))
                    ->getAlignment()->setHorizontal('right')->setVertical('center');
                $event->sheet->getDelegate()->getStyle($letterMap[$letterAscii + 5] . ($row + 1) . ':' . $letterMap[$letterAscii + 5] . ($row + 3))
                    ->getAlignment()->setHorizontal('right')->setVertical('center');
                //X靠右
                $event->sheet->getDelegate()->getStyle($letterMap[$letterAscii + 2] . ($row + 1))
                    ->getAlignment()->setHorizontal('right')->setVertical('center');
                //合併
                $letterAscii = ord('F');
                //作成
                $event->sheet->mergeCells($letterMap[$letterAscii + $days - 9] . $row . ':' . $letterMap[$letterAscii + $days - 9 + 4] . $row);
                //作成簽章區
                $event->sheet->mergeCells($letterMap[$letterAscii + $days - 9] . ($row + 1) . ':' . $letterMap[$letterAscii + $days - 9 + 4] . ($row + 4));
                //審查
                $event->sheet->mergeCells($letterMap[$letterAscii + $days - 4] . $row . ':' . $letterMap[$letterAscii + $days] . $row);
                //審查簽章區
                $event->sheet->mergeCells($letterMap[$letterAscii + $days - 4] . ($row + 1) . ':' . $letterMap[$letterAscii + $days] . ($row + 4));
                //資料分發
                $event->sheet->mergeCells($letterMap[$letterAscii + $days - 20] . $row . ':' . $letterMap[$letterAscii + $days - 20 + 10] . $row);

                //總經理
                for ($i = 1; $i < 5; $i++) {
                    $event->sheet->mergeCells($letterMap[$letterAscii + $days - 20] . ($row + $i) . ':' . $letterMap[$letterAscii + $days - 20 + 3] . ($row + $i));
                    $event->sheet->mergeCells($letterMap[$letterAscii + $days - 20 + 4] . ($row + $i) . ':' . $letterMap[$letterAscii + $days - 20 + 10] . ($row + $i));
                }

                //作成置中
                $event->sheet->getDelegate()
                    ->getStyle($letterMap[$letterAscii + $days - 9] . $row . ':' . $letterMap[$letterAscii + $days - 9 + 4] . $row)
                    ->getAlignment()->setHorizontal('center')->setVertical('center');

                //審查置中
                $event->sheet->getDelegate()
                    ->getStyle($letterMap[$letterAscii + $days - 4] . $row . ':' . $letterMap[$letterAscii + $days] . $row)
                    ->getAlignment()->setHorizontal('center')->setVertical('center');

                //作成邊框
                $event->sheet->getStyle($letterMap[$letterAscii + $days - 9] . $row . ':' . $letterMap[$letterAscii + $days - 9 + 4] . $row)
                    ->applyFromArray([
                        'borders' => [
                            'allBorders' => [
                                'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                                'color' => ['argb' => '000000'],
                            ],
                        ],
                    ]);

                //作成簽章邊框
                $event->sheet->getStyle($letterMap[$letterAscii + $days - 9] . ($row + 1) . ':' . $letterMap[$letterAscii + $days - 9 + 4] . ($row + 4))
                    ->applyFromArray([
                        'borders' => [
                            'allBorders' => [
                                'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                                'color' => ['argb' => '000000'],
                            ],
                        ],
                    ]);

                //審查邊框
                $event->sheet->getStyle($letterMap[$letterAscii + $days - 4] . $row . ':' . $letterMap[$letterAscii + $days] . $row)
                    ->applyFromArray([
                        'borders' => [
                            'allBorders' => [
                                'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                                'color' => ['argb' => '000000'],
                            ],
                        ],
                    ]);

                //審查簽章邊框
                $event->sheet->getStyle($letterMap[$letterAscii + $days - 4] . ($row + 1) . ':' . $letterMap[$letterAscii + $days] . ($row + 4))
                    ->applyFromArray([
                        'borders' => [
                            'allBorders' => [
                                'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                                'color' => ['argb' => '000000'],
                            ],
                        ],
                    ]);

                //插入圖片
                if (intval($progress['progress_point']) == 0 && intval($progress['version']) > 0) {
                    $letterAscii = ord('F');
                    $row = 3 + 2 * count($productionMonths) + 4;
                    $path = public_path('/img/PM/');
                    //作成
                    if (file_exists($path . 'PM-1.png')) {
                        $drawing = new Drawing();
                        $drawing->setPath($path . 'PM-1.png');
                        $drawing->setHeight(65);
                        $drawing->setOffsetY(5);
                        $drawing->setCoordinates($letterMap[$letterAscii + $days - 9 + 1] . ($row + 1));
                        $drawing->setWorksheet($event->sheet->getDelegate());
                    }
                    //審查
                    if (file_exists($path . 'PM-2.png')) {
                        $drawing = new Drawing();
                        $drawing->setPath($path . 'PM-2.png');
                        $drawing->setHeight(65);
                        $drawing->setOffsetY(5);
                        $drawing->setCoordinates($letterMap[$letterAscii + $days - 4 + 1] . ($row + 1));
                        $drawing->setWorksheet($event->sheet->getDelegate());
                    }
                }

                //整體字型設定-------------------------------------------------------------------------------------------------------------
                $letterAscii = ord('A');
                $startColumn = $letterMap[$letterAscii];
                $endColumn = $letterMap[$letterAscii + 4 + $days + 1];
                $startRow = 1;
                $endRow = $startRow + 2 + 2 * count($productionMonths) + 8;
                //新細明體
                $event->sheet->getDelegate()
                    ->getStyle($startColumn . $startRow . ':' . $endColumn . $endRow)
                    ->getFont()->setName('PMingLiU');

                //凍結窗格-------------------------------------------------------------------------------------------------------------------
                $event->sheet->freezePane('G4');
            },
        ];
    }
}
