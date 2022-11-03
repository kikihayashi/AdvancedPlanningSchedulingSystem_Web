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

class ProductionYearSheet implements FromView, WithEvents, WithStyles, WithTitle
{
    use BaseTool, MesApiTool;

    protected $period_tw;
    protected $yearType;
    protected $line_no;
    protected $data;

    public function __construct($period_tw, $yearType, $line_no)
    {
        $this->period_tw = $period_tw;
        $this->yearType = $yearType;
        $this->line_no = $line_no;
    }

    public function view(): View
    {
        $period_tw = $this->period_tw;
        $version = $this->getProjectProgress('PY', $period_tw, 0)['version'];
        $yearType = $this->yearType;
        $line_no = $this->line_no;
        $partition = $this->getPartition($period_tw);
        $exchange = $this->getExchange($period_tw);

        //ISO編號
        $iso = Setting::where('memo', 'iso_production_year')->first()->setting_value;

        //取得行事曆
        $schedules = Schedule::join('parameter_calendar', 'schedule.calendar_id', '=', DB::raw("CAST(parameter_calendar.id AS VARCHAR)"))
            ->selectRaw('schedule.* , parameter_calendar.is_holiday AS is_holiday')
            ->get()
            ->toArray();

        //製作是否為假日的HashMap(key:YYYYMMDD，value:是否為假日)
        foreach ($schedules as $schedule) {
            $isHolidayMap[$schedule['year'] . $schedule['month'] . $schedule['date']] = $schedule['is_holiday'];
        }

        //將出勤日加到monthMaps裡面
        for ($index = 0; $index < count($this->monthMaps); $index++) {
            $monthMap = $this->monthMaps[$index];
            $month = $monthMap['page'];
            $year = (int) $period_tw + (($month > 3) ? 1969 : 1970);
            $workDay = $this->countWorkDay($year, $month, $isHolidayMap ?? array());
            $this->monthMaps[$index]['workDay'] = $workDay;
            $this->monthMaps[$index]['totalDay'] = cal_days_in_month(CAL_GREGORIAN, $month, $year);
        }

        //年度計畫合併線別內藏，SQL指令：COALESCE改用ISNULL也可以，不過PostgreSQL、MySQL沒有ISNULL用法
        $sqlCommand = "SELECT production_year.*,
                        COALESCE(equipment.line,'0') AS line_no,
                        (month_4 + month_5 + month_6 + month_7 + month_8 + month_9) AS firstNumber,
                        (month_10 + month_11 + month_12 + month_1 + month_2 + month_3) AS lastNumber
                        FROM production_year LEFT JOIN equipment
                        ON production_year.item_code = equipment.item_code
                        WHERE period = ':period' AND version = ':version'";

        //替換對應值
        $sqlCommand = str_replace(':period', $period_tw, $sqlCommand);
        $sqlCommand = str_replace(':version', $version, $sqlCommand);

        //年度生產計畫列表(含線別)
        $projects = DB::select($sqlCommand);

        //將object轉成array，並加入partition將工數放入projects中
        $arrayProjects = array_map(function ($itemObject) use ($partition) {
            //將DB查詢到的資料轉成array
            $itemArray = (array) $itemObject;
            //工數
            $itemArray['firstWorkHour'] = ($partition[$itemArray['item_code']]['firstWorkHour'] ?? 0);
            $itemArray['lastWorkHour'] = ($partition[$itemArray['item_code']]['lastWorkHour'] ?? 0);
            return $itemArray;
        }, $projects);

        //製作年度生產計劃HashMap(key:line_no、value:project)
        $productionYear = array();
        foreach ($arrayProjects as $project) {
            if ($project['line_no'] > 0) {
                $productionYear[$project['line_no']]['total'][] = $project;
                if ($project[$yearType . 'Number'] > 0) {
                    $productionYear[$project['line_no']][$yearType][] = $project;
                }
            }
        }

        $totalNumber = 0;
        $totalSH = 0;
        $columnTotalMap = array();
        foreach ($productionYear as $productionYearMap) {
            foreach ($productionYearMap['total'] as $productionYearElement) {
                foreach ($this->monthMaps as $monthMaps) {
                    if (isset($columnTotalMap[$monthMaps['page']]['number'])) {
                        $columnTotalMap[$monthMaps['page']]['number'] += $productionYearElement['month_' . $monthMaps['page']];
                        $columnTotalMap[$monthMaps['page']]['sh'] += $productionYearElement['month_' . $monthMaps['page']] *
                            $productionYearElement[$monthMaps['yearType'] . 'WorkHour'];
                    } else {
                        $columnTotalMap[$monthMaps['page']]['number'] = $productionYearElement['month_' . $monthMaps['page']];
                        $columnTotalMap[$monthMaps['page']]['sh'] = $productionYearElement['month_' . $monthMaps['page']] *
                            $productionYearElement[$monthMaps['yearType'] . 'WorkHour'];
                    }
                    $totalNumber += $productionYearElement['month_' . $monthMaps['page']];
                    $totalSH += $productionYearElement['month_' . $monthMaps['page']] *
                        $productionYearElement[$monthMaps['yearType'] . 'WorkHour'];
                }
            }
        }

        $titleFirst = ($period_tw + 1969) . '年度 4月～ ' . ($period_tw + 1969) . '年度 9月 生產計劃表(' . $period_tw . '期)';
        $titleLast = ($period_tw + 1969) . '年度 10月～ ' . ($period_tw + 1970) . '年度 3月 生產計劃表(' . $period_tw . '期)';

        $data['iso'] = $iso;
        $data['period_tw'] = $period_tw;
        $data['line_no'] = $line_no;
        $data['yearType'] = $yearType;
        $data['title'] = ($yearType == 'first') ? $titleFirst : $titleLast;
        $data['page'] = $this->getSheetPage();
        $data['progress'] = $this->getProjectProgress('PY', $period_tw, 0);
        $data['monthMaps'] = $this->monthMaps;
        $data['productionYearTotal'] = $productionYear;
        $data['columnTotalMap'] = $columnTotalMap;
        $data['totalMap'] = array('number' => $totalNumber, 'sh' => $totalSH);

        $this->data = $data;

        return view('system.projectMenu.exportDetail.productionYear_export', [
            'tableData' => $data,
        ]);
    }

    //計算工作日
    private function countWorkDay($year, $month, $isHolidayMap)
    {
        //工作日
        $workDay = 0;
        //當年該月總天數
        $days = cal_days_in_month(CAL_GREGORIAN, $month, $year);
        //從1號到該月最後一號
        for ($date = 1; $date <= $days; $date++) {
            //取得該日期是星期幾(英文表示)
            $dayName = date("l", mktime(0, 0, 0, $month, $date, $year));
            //本次日期
            $day = $year . $month . $date;
            //如果有在$isHolidayMap裡，且為工作日
            if (isset($isHolidayMap[$day])) {
                if ($isHolidayMap[$day] == 'N') {
                    $workDay++;
                }
            } else {
                //如果不在$isHolidayMap裡，且不是假日
                if ($dayName != 'Sunday' && $dayName != 'Saturday') {
                    $workDay++;
                }
            }
        }
        return $workDay;
    }

    private function getSheetPage(): string
    {
        switch ($this->line_no) {
            case 1:
                return (($this->yearType == 'first') ? '1' : '4');
            case 2:
                return (($this->yearType == 'first') ? '2' : '5');
            case 3:
                return (($this->yearType == 'first') ? '3' : '6');
        }
    }

    //標題設定-WithTitle
    public function title(): string
    {
        return $this->getSheetPage();
    }

    //字體設定-WithStyles
    public function styles(Worksheet $sheet)
    {
        return [
            // '1' => ['font' =>
            //     [
            //         'size' => 13,
            //     ]],
            // '2' => ['font' =>
            //     [
            //         'size' => 12,
            //     ]],
            // 'K3:L3' => ['font' =>
            //     [
            //         'size' => 9,
            //         'italic' => true,
            //         'color' => ['argb' => '0000F1'],
            //     ]],
            // 'B3:F3' => ['font' =>
            //     [
            //         'color' => ['argb' => '0000F1'],
            //     ]],
        ];
    }

    //Cell大小設定-WithEvents
    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                //所需資料
                $monthMaps = $this->monthMaps;
                $yearType = $this->yearType;
                $progress = $this->data['progress'];
                $line_no = $this->line_no;
                $letterMap = $this->getLetterMap();
                $productionYear = $this->data['productionYearTotal'][$line_no][$yearType] ?? array();
                if ($yearType == 'first') {
                    $start = 0;
                    $end = 5;
                } else {
                    $start = 6;
                    $end = 11;
                }

                //高度設置
                $event->sheet->getDelegate()->getRowDimension('1')->setRowHeight(20);
                $event->sheet->getDelegate()->getRowDimension('2')->setRowHeight(50);
                $event->sheet->getDelegate()->getRowDimension('3')->setRowHeight(15);
                $event->sheet->getDelegate()->getRowDimension('4')->setRowHeight(15);

                //寬度設置
                $event->sheet->getDelegate()->getColumnDimension('A')->setWidth(11); //Order No
                $event->sheet->getDelegate()->getColumnDimension('B')->setWidth(9); //納期
                $event->sheet->getDelegate()->getColumnDimension('C')->setWidth(15); //機種
                $event->sheet->getDelegate()->getColumnDimension('D')->setWidth(5); //製番
                $event->sheet->getDelegate()->getColumnDimension('E')->setWidth(8); //台數
                $event->sheet->getDelegate()->getColumnDimension('F')->setWidth(10); //部品到著
                $event->sheet->getDelegate()->getColumnDimension('G')->setWidth(10); //組立開始

                //月份設置
                $letterAscii = ord('H');
                $row = 1;
                foreach (range($start, $end) as $i) {
                    foreach (range(1, $monthMaps[$i]['totalDay']) as $j) {
                        //寬度
                        $event->sheet->getDelegate()->getColumnDimension($letterMap[$letterAscii])->setWidth(1.2);
                        //大小
                        $event->sheet->getDelegate()->getStyle($letterMap[$letterAscii])->getFont()->setSize(5);
                        //置中
                        $event->sheet->getDelegate()->getStyle($letterMap[$letterAscii])->getAlignment()
                            ->setHorizontal('center')->setVertical('center');
                        $letterAscii++;
                    }
                }

                //月份資料設置
                $row = 5;
                foreach ($productionYear as $productionYearElement) {
                    $letterAscii = ord('G');
                    foreach (range($start, $end) as $i) {
                        if ($productionYearElement['range_' . $monthMaps[$i]['page']] != null) {
                            $rangeStart = explode('-', $productionYearElement['range_' . $monthMaps[$i]['page']])[0];
                            $rangeEnd = explode('-', $productionYearElement['range_' . $monthMaps[$i]['page']])[1];
                            //這是每個機種的生產日期
                            $event->sheet->getStyle($letterMap[$letterAscii + $rangeStart] . $row . ':' . $letterMap[$letterAscii + $rangeEnd] . $row)
                                ->applyFromArray([
                                    'borders' => [
                                        'bottom' => [
                                            'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THICK,
                                            'color' => ['argb' => '0E7EED'],
                                        ],
                                    ],
                                ]);
                        }
                        $letterAscii += $monthMaps[$i]['totalDay'];
                    }
                    $row += 2;
                }

                $row = 6;
                foreach ($productionYear as $productionYearElement) {
                    $letterAscii = ord('G');
                    foreach (range($start, $end) as $i) {
                        //合併
                        $event->sheet->mergeCells($letterMap[$letterAscii + 1] . $row . ':' . $letterMap[$letterAscii + $monthMaps[$i]['totalDay']] . $row);
                        //大小
                        $event->sheet->getDelegate()->getStyle($letterMap[$letterAscii + 1] . $row . ':' . $letterMap[$letterAscii + $monthMaps[$i]['totalDay']] . $row)->getFont()->setSize(12);
                        //置中
                        $event->sheet->getDelegate()->getStyle($letterMap[$letterAscii + 1] . $row . ':' . $letterMap[$letterAscii + $monthMaps[$i]['totalDay']] . $row)->getAlignment()
                            ->setHorizontal('center')->setVertical('center');
                        $letterAscii += $monthMaps[$i]['totalDay'];
                    }
                    $row += 2;
                }

                //大小
                $event->sheet->getDelegate()->getStyle('1')->getFont()->setSize(13);
                $event->sheet->getDelegate()->getStyle('2')->getFont()->setSize(12);
                $event->sheet->getDelegate()->getStyle('3')->getFont()->setSize(12);
                $event->sheet->getDelegate()->getStyle('4')->getFont()->setSize(12);
                //底部合計大小
                $row = 4 + 2 * count($productionYear) + 1;
                foreach (range(1, 6) as $i) {
                    $event->sheet->getDelegate()->getStyle($row)->getFont()->setSize(12);
                    $row++;
                }
                //標題----------------------------------------------------------------------------------------------------------------------
                $letterAscii = ord('A');
                $row = 1;
                $threeMonthDays = 0;
                foreach (range($start, $end - 3) as $i) {
                    $threeMonthDays += $monthMaps[$i]['totalDay'];
                }
                //合併
                $event->sheet->mergeCells($letterMap[$letterAscii] . $row . ':' . $letterMap[$letterAscii + 6 + $threeMonthDays] . $row);
                //置中
                $event->sheet->getDelegate()
                    ->getStyle($letterMap[$letterAscii] . $row . ':' . $letterMap[$letterAscii + 6 + $threeMonthDays] . $row)
                    ->getAlignment()->setHorizontal('center')->setVertical('center');

                //◆ 部品到著,  ★ 組立開始, ─ 調整、檢查 & 狀態----------------------------------------------------------------------------------------
                //合併
                $event->sheet->mergeCells('C2:G2');
                $letterAscii = ord('G');
                $row = 2;
                $event->sheet->mergeCells($letterMap[$letterAscii + 1] . $row . ':' . $letterMap[$letterAscii + $threeMonthDays] . $row);
                //顏色紅色
                $event->sheet->getDelegate()->getStyle($letterMap[$letterAscii + 1] . $row . ':' . $letterMap[$letterAscii + $threeMonthDays] . $row)
                    ->getFont()->getColor()->setARGB('FF0204');
                //斜體
                $event->sheet->getDelegate()->getStyle($letterMap[$letterAscii + 1] . $row . ':' . $letterMap[$letterAscii + $threeMonthDays] . $row)
                    ->getFont()->setItalic(true);
                //粗體
                $event->sheet->getDelegate()->getStyle($letterMap[$letterAscii + 1] . $row . ':' . $letterMap[$letterAscii + $threeMonthDays] . $row)
                    ->getFont()->setBold(true);

                //置中靠右
                $event->sheet->getDelegate()
                    ->getStyle($letterMap[$letterAscii] . $row . ':' . $letterMap[$letterAscii + 6 + $threeMonthDays] . $row)
                    ->getAlignment()->setHorizontal('right')->setVertical('center');

                //作成 & 簽章區----------------------------------------------------------------------------------------------------------------------
                $letterAscii = ord('G');
                $row = 1;
                $fourMonthDays = $threeMonthDays;
                $fourMonthDays += $monthMaps[$start + 3]['totalDay'];
                $event->sheet->mergeCells($letterMap[$letterAscii + $threeMonthDays + 1] . $row . ':' . $letterMap[$letterAscii + $fourMonthDays] . $row);
                $event->sheet->mergeCells($letterMap[$letterAscii + $threeMonthDays + 1] . ($row + 1) . ':' . $letterMap[$letterAscii + $fourMonthDays] . ($row + 1));
                //置中
                $event->sheet->getDelegate()
                    ->getStyle($letterMap[$letterAscii + $threeMonthDays + 1] . $row . ':' . $letterMap[$letterAscii + $fourMonthDays] . $row)
                    ->getAlignment()->setHorizontal('center')->setVertical('center');

                //審查 & 簽章區----------------------------------------------------------------------------------------------------------------------
                $letterAscii = ord('G');
                $row = 1;
                $sixMonthDays = $fourMonthDays;
                foreach (range($start + 4, $end) as $i) {
                    $sixMonthDays += $monthMaps[$i]['totalDay'];
                }
                $event->sheet->mergeCells($letterMap[$letterAscii + $fourMonthDays + 1] . $row . ':' . $letterMap[$letterAscii + $sixMonthDays] . $row);
                $event->sheet->mergeCells($letterMap[$letterAscii + $fourMonthDays + 1] . ($row + 1) . ':' . $letterMap[$letterAscii + $sixMonthDays] . ($row + 1));
                //置中
                $event->sheet->getDelegate()
                    ->getStyle($letterMap[$letterAscii + $fourMonthDays + 1] . $row . ':' . $letterMap[$letterAscii + $sixMonthDays] . $row)
                    ->getAlignment()->setHorizontal('center')->setVertical('center');

                //客戶承認 & 簽章區----------------------------------------------------------------------------------------------------------------------
                $letterAscii = ord('G');
                $event->sheet->getDelegate()->getColumnDimension($letterMap[$letterAscii + $sixMonthDays + 1])->setWidth(30);
                //置中
                $event->sheet->getDelegate()
                    ->getStyle($letterMap[$letterAscii + $sixMonthDays + 1])
                    ->getAlignment()->setHorizontal('center')->setVertical('center');

                //備註
                $letterAscii = ord('G');
                $row = 3;
                //合併
                $event->sheet->mergeCells($letterMap[$letterAscii + $sixMonthDays + 1] . $row . ':' . $letterMap[$letterAscii + $sixMonthDays + 1] . ($row + 1));
                //置中
                $event->sheet->getDelegate()->getStyle($letterMap[$letterAscii + $sixMonthDays + 1] . $row . ':' . $letterMap[$letterAscii + $sixMonthDays + 1] . ($row + 1))
                    ->getAlignment()->setHorizontal('center')->setVertical('center');

                //備註資料
                $row = 5;
                foreach ($productionYear as $productionYearElement) {
                    //合併
                    $event->sheet->mergeCells($letterMap[$letterAscii + $sixMonthDays + 1] . $row . ':' . $letterMap[$letterAscii + $sixMonthDays + 1] . ($row + 1));
                    //置中
                    $event->sheet->getDelegate()->getStyle($letterMap[$letterAscii + $sixMonthDays + 1] . $row . ':' . $letterMap[$letterAscii + $sixMonthDays + 1] . ($row + 1))
                        ->getAlignment()->setHorizontal('center')->setVertical('center');

                    $event->sheet->getDelegate()->getRowDimension($row)->setRowHeight(20);
                    $event->sheet->getDelegate()->getRowDimension($row + 1)->setRowHeight(20);
                    $row += 2;
                }

                //ORDER NO、納期、機種、製番、台數、部品到著、組立開始 合併----------------------------------------------------------------------------------------------------------------------
                $letterAscii = ord('A');
                $row = 3;
                $event->sheet->mergeCells($letterMap[$letterAscii + 2] . $row . ':' . $letterMap[$letterAscii + 2] . ($row + 1));
                $event->sheet->mergeCells($letterMap[$letterAscii + 4] . $row . ':' . $letterMap[$letterAscii + 4] . ($row + 1));

                //置中
                $event->sheet->getDelegate()
                    ->getStyle($letterMap[$letterAscii] . $row . ':' . $letterMap[$letterAscii + 6 + $sixMonthDays + 1] . $row)
                    ->getAlignment()->setHorizontal('center')->setVertical('center');

                $event->sheet->getDelegate()
                    ->getStyle($letterMap[$letterAscii] . ($row + 1) . ':' . $letterMap[$letterAscii + 6 + $sixMonthDays + 1] . ($row + 1))
                    ->getAlignment()->setHorizontal('center')->setVertical('center');

                //月份、出勤標題合併----------------------------------------------------------------------------------------------------------------------
                $letterAscii = ord('G');
                $row = 3;
                foreach (range($start, $end) as $i) {
                    $event->sheet->mergeCells($letterMap[$letterAscii + 1] . $row . ':' . $letterMap[$letterAscii + $monthMaps[$i]['totalDay']] . $row);
                    $event->sheet->mergeCells($letterMap[$letterAscii + 1] . ($row + 1) . ':' . $letterMap[$letterAscii + $monthMaps[$i]['totalDay']] . ($row + 1));
                    $letterAscii += $monthMaps[$i]['totalDay'];
                }
                //月份合計資料合併----------------------------------------------------------------------------------------------------------------------
                $letterAscii = ord('G');
                $row = 4 + 2 * count($productionYear) + 1;
                foreach (range($start, $end) as $i) {
                    //合併
                    $event->sheet->mergeCells($letterMap[$letterAscii + 1] . $row . ':' . $letterMap[$letterAscii + $monthMaps[$i]['totalDay']] . $row);
                    $event->sheet->mergeCells($letterMap[$letterAscii + 1] . ($row + 1) . ':' . $letterMap[$letterAscii + $monthMaps[$i]['totalDay']] . ($row + 1));
                    //置中靠右
                    $event->sheet->getDelegate()->getStyle($letterMap[$letterAscii + 1] . $row . ':' . $letterMap[$letterAscii + $monthMaps[$i]['totalDay']] . $row)
                        ->getAlignment()->setHorizontal('right')->setVertical('center');
                    $event->sheet->getDelegate()->getStyle($letterMap[$letterAscii + 1] . ($row + 1) . ':' . $letterMap[$letterAscii + $monthMaps[$i]['totalDay']] . ($row + 1))
                        ->getAlignment()->setHorizontal('right')->setVertical('center');
                    $letterAscii += $monthMaps[$i]['totalDay'];
                }

                //資料區----------------------------------------------------------------------------------------------------------------------
                $letterAscii = ord('A');
                $row = 5;
                foreach ($productionYear as $productionYearElement) {
                    $event->sheet->mergeCells($letterMap[$letterAscii] . $row . ':' . $letterMap[$letterAscii] . ($row + 1));
                    $event->sheet->mergeCells($letterMap[$letterAscii + 1] . $row . ':' . $letterMap[$letterAscii + 1] . ($row + 1));
                    $event->sheet->mergeCells($letterMap[$letterAscii + 2] . $row . ':' . $letterMap[$letterAscii + 2] . ($row + 1));
                    $event->sheet->mergeCells($letterMap[$letterAscii + 3] . $row . ':' . $letterMap[$letterAscii + 3] . ($row + 1));
                    //半年數量等於總數量就合併
                    if ($productionYearElement[$this->yearType . 'Number'] == $productionYearElement['lot_total']) {
                        $event->sheet->mergeCells($letterMap[$letterAscii + 4] . $row . ':' . $letterMap[$letterAscii + 4] . ($row + 1));
                    }
                    $event->sheet->mergeCells($letterMap[$letterAscii + 5] . $row . ':' . $letterMap[$letterAscii + 5] . ($row + 1));
                    $event->sheet->mergeCells($letterMap[$letterAscii + 6] . $row . ':' . $letterMap[$letterAscii + 6] . ($row + 1));
                    //位置設置
                    $event->sheet->getDelegate()->getStyle($letterMap[$letterAscii] . $row . ':' . $letterMap[$letterAscii] . ($row + 1))
                        ->getAlignment()->setHorizontal('center')->setVertical('center');
                    $event->sheet->getDelegate()->getStyle($letterMap[$letterAscii + 1] . $row . ':' . $letterMap[$letterAscii + 1] . ($row + 1))
                        ->getAlignment()->setHorizontal('center')->setVertical('center');
                    $event->sheet->getDelegate()->getStyle($letterMap[$letterAscii + 2] . $row . ':' . $letterMap[$letterAscii + 2] . ($row + 1))
                        ->getAlignment()->setHorizontal('left')->setVertical('center');
                    $event->sheet->getDelegate()->getStyle($letterMap[$letterAscii + 3] . $row . ':' . $letterMap[$letterAscii + 3] . ($row + 1))
                        ->getAlignment()->setHorizontal('center')->setVertical('center');
                    $event->sheet->getDelegate()->getStyle($letterMap[$letterAscii + 4] . $row . ':' . $letterMap[$letterAscii + 4] . ($row + 1))
                        ->getAlignment()->setHorizontal('right')->setVertical('center');
                    $event->sheet->getDelegate()->getStyle($letterMap[$letterAscii + 5] . $row . ':' . $letterMap[$letterAscii + 5] . ($row + 1))
                        ->getAlignment()->setHorizontal('center')->setVertical('center');
                    $event->sheet->getDelegate()->getStyle($letterMap[$letterAscii + 6] . $row . ':' . $letterMap[$letterAscii + 6] . ($row + 1))
                        ->getAlignment()->setHorizontal('center')->setVertical('center');
                    $row += 2;
                }

                //Line合計
                $letterAscii = ord('A');
                $row = 4 + 2 * count($productionYear) + 1;
                //合併
                $event->sheet->mergeCells($letterMap[$letterAscii] . $row . ':' . $letterMap[$letterAscii + 5] . ($row + 1));
                //置中
                $event->sheet->getDelegate()->getStyle($letterMap[$letterAscii] . $row . ':' . $letterMap[$letterAscii + 6] . ($row + 1))
                    ->getAlignment()->setHorizontal('center')->setVertical('center');

                //最右下設置
                $letterAscii = ord('G');
                $row = 4 + 2 * count($productionYear) + 1;
                //靠右
                $event->sheet->getDelegate()->getStyle($letterMap[$letterAscii + $sixMonthDays + 1] . $row . ':' . $letterMap[$letterAscii + $sixMonthDays + 1] . ($row + 5))
                    ->getAlignment()->setHorizontal('right')->setVertical('center');

                //iso
                $event->sheet->getDelegate()->getStyle($letterMap[$letterAscii + $sixMonthDays + 1] . ($row + 2) . ':' . $letterMap[$letterAscii + $sixMonthDays + 1] . ($row + 2))
                    ->getAlignment()->setHorizontal('left')->setVertical('center');

                //邊框設定------------------------------------------------------------------------------------------------------------------------------
                $letterAscii = ord('A');
                $row = 1;
                //副標題
                $event->sheet->getStyle($letterMap[$letterAscii] . ($row + 1) . ':' . $letterMap[$letterAscii + 6 + $sixMonthDays + 1] . ($row + 1))
                    ->applyFromArray([
                        'borders' => [
                            'bottom' => [
                                'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                                'color' => ['argb' => '000000'],
                            ],
                        ],
                    ]);

                //資料區邊框設定
                $letterAscii = ord('A');
                $row = 4;
                foreach (range(1, count($productionYear) + 1) as $i) {
                    $event->sheet->getStyle($letterMap[$letterAscii] . $row . ':' . $letterMap[$letterAscii + 6 + $sixMonthDays + 1] . $row)
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

                //Line 合計
                $letterAscii = ord('A');
                $row = 4 + 2 * count($productionYear) + 1;
                $event->sheet->getStyle($letterMap[$letterAscii] . $row . ':' . $letterMap[$letterAscii + 6 + $sixMonthDays + 1] . ($row + 1))
                    ->applyFromArray([
                        'borders' => [
                            'allBorders' => [
                                'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                                'color' => ['argb' => '000000'],
                            ],
                        ],
                    ]);
                //斜體
                $letterAscii = ord('G');
                $row = 4 + 2 * count($productionYear) + 2;
                $event->sheet->getDelegate()->getStyle($letterMap[$letterAscii] . $row . ':' . $letterMap[$letterAscii] . $row)
                    ->getFont()->setItalic(true);

                //縱向邊框
                $letterAscii = ord('A');
                $startRow = 3;
                $endRow = 4 + 2 * count($productionYear);
                foreach (range(1, 7) as $i) {
                    $event->sheet->getStyle($letterMap[$letterAscii] . $startRow . ':' . $letterMap[$letterAscii] . $endRow)
                        ->applyFromArray([
                            'borders' => [
                                'right' => [
                                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                                    'color' => ['argb' => '000000'],
                                ],
                            ],
                        ]);
                    $letterAscii++;
                }

                $letterAscii = ord('G');
                $startRow = 3;
                $endRow = 4 + 2 * count($productionYear);
                foreach (range($start, $end) as $i) {
                    $event->sheet->getStyle($letterMap[$letterAscii] . $startRow . ':' . $letterMap[$letterAscii] . $endRow)
                        ->applyFromArray([
                            'borders' => [
                                'right' => [
                                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                                    'color' => ['argb' => '000000'],
                                ],
                            ],
                        ]);
                    $letterAscii += $monthMaps[$i]['totalDay'];
                }

                foreach (range(1, 2) as $i) {
                    $event->sheet->getStyle($letterMap[$letterAscii] . $startRow . ':' . $letterMap[$letterAscii] . $endRow)
                        ->applyFromArray([
                            'borders' => [
                                'right' => [
                                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                                    'color' => ['argb' => '000000'],
                                ],
                            ],
                        ]);
                    $letterAscii++;
                }

                //第1頁
                if ($yearType == 'first' && $line_no == 1) {
                    $letterAscii = ord('G');
                    $row = 1;
                    //簽章區
                    $event->sheet->getStyle($letterMap[$letterAscii + $threeMonthDays + 1] . $row . ':' . $letterMap[$letterAscii + $sixMonthDays + 1] . ($row + 1))
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
                        $letterAscii = ord('G');
                        $row = 2;
                        $path = public_path('/img/PY/');
                        //作成
                        if (file_exists($path . 'PY-1.png')) {
                            $drawing = new Drawing();
                            $drawing->setPath($path . 'PY-1.png');
                            $drawing->setHeight(50);
                            $drawing->setOffsetX(0);
                            $drawing->setOffsetY(10);
                            $drawing->setCoordinates($letterMap[$letterAscii + $threeMonthDays + 14] . $row);
                            $drawing->setWorksheet($event->sheet->getDelegate());
                        }
                        //審查
                        if (file_exists($path . 'PY-2.png')) {
                            $drawing = new Drawing();
                            $drawing->setPath($path . 'PY-2.png');
                            $drawing->setHeight(50);
                            $drawing->setOffsetX(0);
                            $drawing->setOffsetY(10);
                            $drawing->setCoordinates($letterMap[$letterAscii + $sixMonthDays - $monthMaps[$end]['totalDay'] - 2] . $row);
                            $drawing->setWorksheet($event->sheet->getDelegate());
                        }
                        //客戶承認
                        if (file_exists($path . 'PY-3.png')) {
                            $drawing = new Drawing();
                            $drawing->setPath($path . 'PY-3.png');
                            $drawing->setHeight(50);
                            $drawing->setOffsetX(85);
                            $drawing->setOffsetY(10);
                            $drawing->setCoordinates($letterMap[$letterAscii + $sixMonthDays + 1] . $row);
                            $drawing->setWorksheet($event->sheet->getDelegate());
                        }
                    }
                }
                //第3、6頁
                else if ($line_no == 3) {
                    //總合計
                    $letterAscii = ord('A');
                    $row = 4 + 2 * count($productionYear) + 3;
                    //合併
                    $event->sheet->mergeCells($letterMap[$letterAscii] . $row . ':' . $letterMap[$letterAscii + 5] . ($row + 1));
                    //置中
                    $event->sheet->getDelegate()->getStyle($letterMap[$letterAscii] . $row . ':' . $letterMap[$letterAscii + 6] . ($row + 1))
                        ->getAlignment()->setHorizontal('center')->setVertical('center');
                    //斜體
                    $letterAscii = ord('G');
                    $row = 4 + 2 * count($productionYear) + 4;
                    $event->sheet->getDelegate()->getStyle($letterMap[$letterAscii] . $row . ':' . $letterMap[$letterAscii] . $row)
                        ->getFont()->setItalic(true);

                    //月份總合計資料合併----------------------------------------------------------------------------------------------------------------------
                    $letterAscii = ord('G');
                    $row = 4 + 2 * count($productionYear) + 3;
                    foreach (range($start, $end) as $i) {
                        //合併
                        $event->sheet->mergeCells($letterMap[$letterAscii + 1] . $row . ':' . $letterMap[$letterAscii + $monthMaps[$i]['totalDay']] . $row);
                        $event->sheet->mergeCells($letterMap[$letterAscii + 1] . ($row + 1) . ':' . $letterMap[$letterAscii + $monthMaps[$i]['totalDay']] . ($row + 1));
                        //置中靠右
                        $event->sheet->getDelegate()->getStyle($letterMap[$letterAscii + 1] . $row . ':' . $letterMap[$letterAscii + $monthMaps[$i]['totalDay']] . $row)
                            ->getAlignment()->setHorizontal('right')->setVertical('center');
                        $event->sheet->getDelegate()->getStyle($letterMap[$letterAscii + 1] . ($row + 1) . ':' . $letterMap[$letterAscii + $monthMaps[$i]['totalDay']] . ($row + 1))
                            ->getAlignment()->setHorizontal('right')->setVertical('center');
                        //斜體
                        $event->sheet->getDelegate()->getStyle($letterMap[$letterAscii + 1] . $row . ':' . $letterMap[$letterAscii + $monthMaps[$i]['totalDay']] . $row)
                            ->getFont()->setItalic(true);
                        $event->sheet->getDelegate()->getStyle($letterMap[$letterAscii + 1] . ($row + 1) . ':' . $letterMap[$letterAscii + $monthMaps[$i]['totalDay']] . ($row + 1))
                            ->getFont()->setItalic(true);
                        $letterAscii += $monthMaps[$i]['totalDay'];
                    }
                    //邊框
                    $letterAscii = ord('A');
                    $event->sheet->getStyle($letterMap[$letterAscii] . $row . ':' . $letterMap[$letterAscii + 6 + $sixMonthDays + 1] . ($row + 1))
                        ->applyFromArray([
                            'borders' => [
                                'allBorders' => [
                                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                                    'color' => ['argb' => '000000'],
                                ],
                            ],
                        ]);

                    //第6頁
                    if ($yearType == 'last') {
                        $letterAscii = ord('G');
                        $row = 4 + 2 * count($productionYear) + 5;
                        $fiveMonthDays = $sixMonthDays - $monthMaps[$end]['totalDay'];
                        //期別總計合併
                        $event->sheet->mergeCells($letterMap[$letterAscii + $fiveMonthDays + 1] . $row . ':' . $letterMap[$letterAscii + $fiveMonthDays + 20] . ($row + 1));
                        //總計台數、工數合併
                        $event->sheet->mergeCells($letterMap[$letterAscii + $fiveMonthDays + 21] . $row . ':' . $letterMap[$letterAscii + $sixMonthDays] . $row);
                        $event->sheet->mergeCells($letterMap[$letterAscii + $fiveMonthDays + 21] . ($row + 1) . ':' . $letterMap[$letterAscii + $sixMonthDays] . ($row + 1));
                        //斜體
                        $event->sheet->getDelegate()->getStyle($letterMap[$letterAscii + $fiveMonthDays + 21] . $row . ':' . $letterMap[$letterAscii + $sixMonthDays] . ($row + 1))
                            ->getFont()->setItalic(true);

                        //置中
                        $event->sheet->getDelegate()->getStyle($letterMap[$letterAscii + $fiveMonthDays + 1] . $row . ':' . $letterMap[$letterAscii + $sixMonthDays] . ($row + 1))
                            ->getAlignment()->setHorizontal('center')->setVertical('center');
                        //邊框
                        $event->sheet->getStyle($letterMap[$letterAscii + $fiveMonthDays + 1] . $row . ':' . $letterMap[$letterAscii + $sixMonthDays + 1] . ($row + 1))
                            ->applyFromArray([
                                'borders' => [
                                    'allBorders' => [
                                        'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                                        'color' => ['argb' => '000000'],
                                    ],
                                ],
                            ]);

                        //總計台數、工數資料位置
                        $letterAscii = ord('G');
                        $row = 4 + 2 * count($productionYear) + 1;
                        $event->sheet->getDelegate()->getStyle($letterMap[$letterAscii + $sixMonthDays + 1] . $row . ':' . $letterMap[$letterAscii + $sixMonthDays + 1] . ($row + 5))
                            ->getAlignment()->setHorizontal('right')->setVertical('center');

                        //iso
                        $letterAscii = ord('G');
                        $row = 4 + 2 * count($productionYear) + 7;
                        $event->sheet->getDelegate()->getStyle($letterMap[$letterAscii + $sixMonthDays + 1] . $row . ':' . $letterMap[$letterAscii + $sixMonthDays + 1] . $row)
                            ->getAlignment()->setHorizontal('left')->setVertical('center');
                    }
                }

                //整體字型設定-------------------------------------------------------------------------------------------------------------
                $letterAscii = ord('A');
                $startRow = 1;
                $endRow = 4 + 2 * count($productionYear) + 7;
                //新細明體
                $event->sheet->getDelegate()
                    ->getStyle($letterMap[$letterAscii] . $startRow . ':' . $letterMap[$letterAscii + 6 + $sixMonthDays + 1] . $endRow)
                    ->getFont()->setName('PMingLiU');

                //凍結窗格-------------------------------------------------------------------------------------------------------------------
                $event->sheet->getDelegate()->freezePane('H5');
            },
        ];
    }
}
