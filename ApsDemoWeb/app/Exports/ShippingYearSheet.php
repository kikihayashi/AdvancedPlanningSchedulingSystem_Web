<?php

namespace App\Exports;

use App\Http\Traits\BaseTool;
use App\Http\Traits\MesApiTool;
use App\Models\Setting;
use App\Models\ShippingYear;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class ShippingYearSheet implements FromView, WithEvents, WithStyles, WithTitle
{
    use BaseTool, MesApiTool;

    protected $period_tw;
    protected $yearType;
    protected $data;

    public function __construct($period_tw, $yearType)
    {
        $this->period_tw = $period_tw;
        $this->yearType = $yearType;
    }

    public function view(): View
    {
        $period_tw = $this->period_tw;
        $version = $this->getProjectProgress('SY', $period_tw, 0)['version'];
        $yearType = $this->yearType;
        $partition = $this->getPartition($period_tw);
        $exchange = $this->getExchange($period_tw);

        //ISO編號
        $iso = Setting::where('memo', 'iso_shipping_year')->first()->setting_value;

        switch ($yearType) {
            case 'first':
                $title = ($period_tw + 1969) . '年度 4月~' . ($period_tw + 1969) . '年度 9月 出荷計劃 (' . $period_tw . '期)';
                $totalLotNumber = '(month_4+month_5+month_6+month_7+month_8+month_9)';
                break;

            case 'last':
                $title = ($period_tw + 1969) . '年度 10月~' . ($period_tw + 1970) . '年度 3月 出荷計劃 (' . $period_tw . '期)';
                $totalLotNumber = '(month_10+month_11+month_12+month_1+month_2+month_3)';
                break;
        }

        //年度出荷計劃列表
        $projects = ShippingYear::where('version', $version)
            ->where('period', $period_tw)
            ->select('shipping_year.*', DB::raw($totalLotNumber . ' AS totalLotNumber'))
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

        $data['iso'] = $iso;
        $data['title'] = $title;
        $data['period_tw'] = $period_tw;
        $data['yearType'] = $yearType;
        $data['exchange'] = $exchange;
        $data['progress'] = $this->getProjectProgress('SY', $period_tw, 0);
        $data['shippingYear'] = $shippingYear;

        $this->data = $data;

        return view('system.projectMenu.exportDetail.shippingYear_export', [
            'tableData' => $data,
        ]);
    }

    //標題設定-WithTitle
    public function title(): string
    {
        return ($this->yearType == 'first') ? 'A' : 'B';
    }

    //字體設定-WithStyles
    public function styles(Worksheet $sheet)
    {
        return [
            'A1:L2' => ['font' =>
                [
                    'size' => 16,
                ]],
            'K3:L3' => ['font' =>
                [
                    'size' => 9,
                    'italic' => true,
                    'color' => ['argb' => '0000F1'],
                ]],
            'B3:F3' => ['font' =>
                [
                    'color' => ['argb' => '0000F1'],
                ]],
        ];
    }

    //Cell大小設定-WithEvents
    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                //所需資料
                $shippingYears = $this->data['shippingYear'];
                $letterMap = $this->getLetterMap();
                $progress = $this->data['progress'];
                $yearType = $this->yearType;

                $dataSize = 0;
                foreach ($shippingYears as $itemCode => $shippingYearArray) {
                    foreach ($shippingYearArray as $shippingYear) {
                        if ($shippingYear['totalLotNumber'] > 0) {
                            $dataSize++;
                            break;
                        }
                    }
                }
                //整體寬度
                $event->sheet->getDelegate()->getColumnDimension('A')->setWidth(10);
                $event->sheet->getDelegate()->getColumnDimension('B')->setWidth(5);
                $event->sheet->getDelegate()->getColumnDimension('C')->setWidth(9);
                $event->sheet->getDelegate()->getColumnDimension('D')->setWidth(9);
                $event->sheet->getDelegate()->getColumnDimension('E')->setWidth(9);
                $event->sheet->getDelegate()->getColumnDimension('F')->setWidth(9);

                //最上層高度
                $event->sheet->getDelegate()->getRowDimension('1')->setRowHeight(25);
                $event->sheet->getDelegate()->getRowDimension('2')->setRowHeight(25);
                $event->sheet->getDelegate()->getRowDimension('3')->setRowHeight(20);

                //標題
                $event->sheet->mergeCells('A1:L2');
                $event->sheet->getDelegate()->getStyle('A1:L3')
                    ->getAlignment()->setHorizontal('center')->setVertical('center');
                //副標題
                $event->sheet->mergeCells('B3:F3');
                //狀態
                $event->sheet->mergeCells('K3:L3');
                $event->sheet->getDelegate()->getStyle('K3:L3')
                    ->getAlignment()->setHorizontal('center')->setVertical('center');
                //匯率&資料
                $event->sheet->getDelegate()->getStyle('G3')
                    ->getAlignment()->setHorizontal('center')->setVertical('center');
                $event->sheet->getDelegate()->getStyle('H3')
                    ->getAlignment()->setHorizontal('center')->setVertical('center');
                //¥ 0 / H
                $event->sheet->mergeCells('I3:J3');
                $event->sheet->getDelegate()->getStyle('I3:J3')
                    ->getAlignment()->setHorizontal('center')->setVertical('center');
                //最右邊倒數第2排
                $row = 5;
                $event->sheet->getDelegate()->getStyle('S' . $row . ':' . 'S' . ($row + $dataSize - 1 + 4))
                    ->getAlignment()->setHorizontal('right')->setVertical('center');

                //簽章--------------------------------------------------------------------------
                $letterAscii = ord('M');
                $row = 1;
                foreach (range(1, 4) as $i) {
                    //合併
                    $event->sheet->mergeCells($letterMap[$letterAscii] . $row . ':' . $letterMap[$letterAscii + 1] . $row);
                    $event->sheet->mergeCells($letterMap[$letterAscii] . ($row + 1) . ':' . $letterMap[$letterAscii + 1] . ($row + 2));
                    //置中
                    $event->sheet->getDelegate()->getStyle($letterMap[$letterAscii] . $row . ':' . $letterMap[$letterAscii + 1] . $row)
                        ->getAlignment()->setHorizontal('center')->setVertical('center');
                    $letterAscii += 2;
                }

                //機種
                $letterAscii = ord('A');
                $row = 4;
                //合併
                $event->sheet->mergeCells($letterMap[$letterAscii] . $row . ':' . $letterMap[$letterAscii + 1] . $row);
                //置中
                $event->sheet->getDelegate()->getStyle($letterMap[$letterAscii] . $row . ':' . $letterMap[$letterAscii + 1] . $row)
                    ->getAlignment()->setHorizontal('center')->setVertical('center');

                $row = 5;
                if ($dataSize > 0) {
                    foreach (range(1, $dataSize) as $i) {
                        //合併
                        $event->sheet->mergeCells($letterMap[$letterAscii] . $row . ':' . $letterMap[$letterAscii + 1] . $row);
                        //置中
                        $event->sheet->getDelegate()->getStyle($letterMap[$letterAscii] . $row . ':' . $letterMap[$letterAscii + 1] . $row)
                            ->getAlignment()->setHorizontal('left')->setVertical('center');
                        //粗體
                        $event->sheet->getDelegate()
                            ->getStyle($letterMap[$letterAscii] . $row . ':' . $letterMap[$letterAscii + 3] . $row)
                            ->getFont()->setBold(true);
                        $event->sheet->getDelegate()
                            ->getStyle($letterMap[$letterAscii + 18] . $row . ':' . $letterMap[$letterAscii + 18] . $row)
                            ->getFont()->setBold(true);
                        $row++;
                    }
                }

                //仕切工數、賣價
                $letterAscii = ord('C');
                $row = 4;
                //置中
                $event->sheet->getDelegate()->getStyle($letterMap[$letterAscii] . $row . ':' . $letterMap[$letterAscii] . $row)
                    ->getAlignment()->setHorizontal('center')->setVertical('center');
                $event->sheet->getDelegate()->getStyle($letterMap[$letterAscii + 1] . $row . ':' . $letterMap[$letterAscii + 1] . $row)
                    ->getAlignment()->setHorizontal('center')->setVertical('center');

                $row = 5;
                if ($dataSize > 0) {
                    foreach (range(1, $dataSize) as $i) {
                        //位置
                        $event->sheet->getDelegate()->getStyle($letterMap[$letterAscii] . $row . ':' . $letterMap[$letterAscii] . $row)
                            ->getAlignment()->setHorizontal('right')->setVertical('center');
                        $event->sheet->getDelegate()->getStyle($letterMap[$letterAscii + 1] . $row . ':' . $letterMap[$letterAscii + 1] . $row)
                            ->getAlignment()->setHorizontal('right')->setVertical('center');
                        //粗體
                        $event->sheet->getDelegate()->getStyle($letterMap[$letterAscii] . $row . ':' . $letterMap[$letterAscii] . $row)->getFont()->setBold(true);
                        $event->sheet->getDelegate()->getStyle($letterMap[$letterAscii + 1] . $row . ':' . $letterMap[$letterAscii + 1] . $row)->getFont()->setBold(true);
                        //顏色紅色
                        $event->sheet->getDelegate()
                            ->getStyle($letterMap[$letterAscii + 1] . $row . ':' . $letterMap[$letterAscii + 1] . $row)
                            ->getFont()->getColor()->setARGB('FF0204');
                        $row++;
                    }
                }

                //月份
                $letterAscii = ord('E');
                $row = 4;
                foreach (range(1, 8) as $i) {
                    //合併
                    $event->sheet->mergeCells($letterMap[$letterAscii] . $row . ':' . $letterMap[$letterAscii + 1] . $row);
                    //置中
                    $event->sheet->getDelegate()->getStyle($letterMap[$letterAscii] . $row . ':' . $letterMap[$letterAscii + 1] . $row)
                        ->getAlignment()->setHorizontal('center')->setVertical('center');
                    $letterAscii += 2;
                }

                //月份的資料
                $letterAscii = ord('E');
                $row = 5;
                $event->sheet->getDelegate()->getStyle($letterMap[$letterAscii] . $row . ':' . $letterMap[$letterAscii] . $row)
                    ->getAlignment()->setHorizontal('left')->setVertical('top');
                $event->sheet->getDelegate()->getStyle($letterMap[$letterAscii + 1] . $row . ':' . $letterMap[$letterAscii + 1] . $row)
                    ->getAlignment()->setHorizontal('right')->setVertical('top');

                if ($dataSize > 0) {
                    foreach (range(1, $dataSize) as $i) {
                        $letterAscii = ord('E');
                        foreach (range(1, 6) as $j) {
                            $event->sheet->getDelegate()->getStyle($letterMap[$letterAscii] . $row . ':' . $letterMap[$letterAscii] . $row)
                                ->getAlignment()->setHorizontal('left')->setVertical('top');
                            $event->sheet->getDelegate()->getStyle($letterMap[$letterAscii + 1] . $row . ':' . $letterMap[$letterAscii + 1] . $row)
                                ->getAlignment()->setHorizontal('right')->setVertical('top');
                            $letterAscii += 2;
                        }
                        $row++;
                    }
                }

                //合計、底部標題
                $letterAscii = ord('A');
                $row = 4 + $dataSize + 1;
                //合併
                $event->sheet->mergeCells($letterMap[$letterAscii] . $row . ':' . $letterMap[$letterAscii + 2] . ($row + 1));
                $event->sheet->mergeCells($letterMap[$letterAscii] . ($row + 2) . ':' . $letterMap[$letterAscii + 2] . ($row + 3));
                //置中
                $event->sheet->getDelegate()->getStyle($letterMap[$letterAscii] . $row . ':' . $letterMap[$letterAscii + 2] . ($row + 1))
                    ->getAlignment()->setHorizontal('center')->setVertical('center');
                $event->sheet->getDelegate()->getStyle($letterMap[$letterAscii] . ($row + 2) . ':' . $letterMap[$letterAscii + 2] . ($row + 3))
                    ->getAlignment()->setHorizontal('center')->setVertical('center');

                //最右邊下面單位
                $letterAscii = ord('T');
                $row = 4 + $dataSize + 1;
                foreach (range(1, 4) as $i) {
                    $event->sheet->getDelegate()->getStyle($letterMap[$letterAscii] . $row . ':' . $letterMap[$letterAscii] . $row)
                        ->getAlignment()->setHorizontal('center')->setVertical('center');
                    $row++;
                }

                //資料底下所有欄位資料
                $startColumn = 'A';
                $endColumn = 'T';
                $startRow = 4 + $dataSize + 1;
                $endRow = 4 + $dataSize + 9;
                $event->sheet->getDelegate()
                    ->getStyle($startColumn . $startRow . ':' . $endColumn . $endRow)
                    ->getFont()->setBold(true);

                //最右合計資料&單位
                $letterAscii = ord('S');
                $row = 5;
                if ($dataSize > 0) {
                    foreach (range(1, $dataSize) as $i) {
                        $event->sheet->getDelegate()->getStyle($letterMap[$letterAscii] . $row . ':' . $letterMap[$letterAscii] . $row)
                            ->getAlignment()->setHorizontal('right')->setVertical('center');
                        $event->sheet->getDelegate()->getStyle($letterMap[$letterAscii + 1] . $row . ':' . $letterMap[$letterAscii + 1] . $row)
                            ->getAlignment()->setHorizontal('left')->setVertical('center');
                        $row++;
                    }
                }

                //台數、仕切SH、千圓、千元
                $letterAscii = ord('D');
                $row = 4 + $dataSize + 1;
                foreach (range(1, 4) as $i) {
                    $event->sheet->getDelegate()->getStyle($letterMap[$letterAscii] . $row . ':' . $letterMap[$letterAscii] . $row)
                        ->getAlignment()->setHorizontal('center')->setVertical('center');
                    $row++;
                }

                //仕切SH、千元(左下的)
                $letterAscii = ord('D');
                $row = 4 + $dataSize + 2;

                $event->sheet->getDelegate()
                    ->getStyle($letterMap[$letterAscii] . $row . ':' . $letterMap[$letterAscii] . $row)
                    ->getFont()->setItalic(true);

                $event->sheet->getDelegate()
                    ->getStyle($letterMap[$letterAscii] . ($row + 2) . ':' . $letterMap[$letterAscii + 16] . ($row + 2))
                    ->getFont()->setItalic(true);

                //台數、仕切SH、千圓、千元的資料
                $letterAscii = ord('E');
                $row = 4 + $dataSize + 1;
                foreach (range(1, 7) as $i) {
                    //合併
                    $event->sheet->mergeCells($letterMap[$letterAscii] . $row . ':' . $letterMap[$letterAscii + 1] . $row);
                    $event->sheet->mergeCells($letterMap[$letterAscii] . ($row + 1) . ':' . $letterMap[$letterAscii + 1] . ($row + 1));
                    $event->sheet->mergeCells($letterMap[$letterAscii] . ($row + 2) . ':' . $letterMap[$letterAscii + 1] . ($row + 2));
                    $event->sheet->mergeCells($letterMap[$letterAscii] . ($row + 3) . ':' . $letterMap[$letterAscii + 1] . ($row + 3));
                    //靠右
                    $event->sheet->getDelegate()->getStyle($letterMap[$letterAscii] . $row . ':' . $letterMap[$letterAscii + 1] . $row)
                        ->getAlignment()->setHorizontal('right')->setVertical('center');
                    $event->sheet->getDelegate()->getStyle($letterMap[$letterAscii] . ($row + 1) . ':' . $letterMap[$letterAscii + 1] . ($row + 1))
                        ->getAlignment()->setHorizontal('right')->setVertical('center');
                    $event->sheet->getDelegate()->getStyle($letterMap[$letterAscii] . ($row + 2) . ':' . $letterMap[$letterAscii + 1] . ($row + 2))
                        ->getAlignment()->setHorizontal('right')->setVertical('center');
                    $event->sheet->getDelegate()->getStyle($letterMap[$letterAscii] . ($row + 3) . ':' . $letterMap[$letterAscii + 1] . ($row + 3))
                        ->getAlignment()->setHorizontal('right')->setVertical('center');
                    $letterAscii += 2;
                }

                //左下千圓、千元的資料
                $letterAscii = ord('D');
                $row = 4 + $dataSize + 3;
                foreach (range(1, 17) as $i) {
                    //顏色藍色
                    $event->sheet->getDelegate()
                        ->getStyle($letterMap[$letterAscii] . $row . ':' . $letterMap[$letterAscii] . ($row + 1))
                        ->getFont()->getColor()->setARGB('0000F1');
                    $letterAscii++;
                }

                //整體字型設定-------------------------------------------------------------------------------------------------------------
                $startColumn = 'A';
                $endColumn = 'U';
                $startRow = 1;
                $endRow = 4 + $dataSize + 8 + 1;
                //新細明體
                $event->sheet->getDelegate()
                    ->getStyle($startColumn . $startRow . ':' . $endColumn . $endRow)
                    ->getFont()->setName('PMingLiU');

                $startColumn = 'A';
                $endColumn = 'C';
                $startRow = 4 + $dataSize + 3;
                $endRow = 4 + $dataSize + 4;
                $event->sheet->getDelegate()
                    ->getStyle($startColumn . $startRow . ':' . $endColumn . $endRow)
                    ->getFont()->setSize(9);

                //整體邊框設定
                $startColumn = 'A';
                $endColumn = 'T';
                $row = 4;
                $event->sheet->getStyle($startColumn . $row . ':' . $endColumn . $row)
                    ->applyFromArray([
                        'borders' => [
                            'allBorders' => [
                                'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                                'color' => ['argb' => '000000'],
                            ],
                        ],
                    ]);

                $letterAscii = ord('A');
                $row = 5;
                if ($dataSize > 0) {
                    foreach (range(1, $dataSize) as $i) {
                        $event->sheet->getStyle($letterMap[$letterAscii] . $row . ':' . $letterMap[$letterAscii + 3] . $row)
                            ->applyFromArray([
                                'borders' => [
                                    'allBorders' => [
                                        'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                                        'color' => ['argb' => '000000'],
                                    ],
                                ],
                            ]);
                        $row++;
                    }
                }

                $letterAscii = ord('A');
                $row = 4 + $dataSize + 1;
                $event->sheet->getStyle($letterMap[$letterAscii] . $row . ':' . $letterMap[$letterAscii + 2] . ($row + 1))
                    ->applyFromArray([
                        'borders' => [
                            'allBorders' => [
                                'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                                'color' => ['argb' => '000000'],
                            ],
                        ],
                    ]);

                $event->sheet->getStyle($letterMap[$letterAscii] . ($row + 2) . ':' . $letterMap[$letterAscii + 2] . ($row + 3))
                    ->applyFromArray([
                        'borders' => [
                            'allBorders' => [
                                'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                                'color' => ['argb' => '000000'],
                            ],
                        ],
                    ]);

                $letterAscii = ord('D');
                $row = 4 + $dataSize + 1;
                $event->sheet->getStyle($letterMap[$letterAscii] . $row . ':' . $letterMap[$letterAscii] . ($row + 3))
                    ->applyFromArray([
                        'borders' => [
                            'allBorders' => [
                                'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                                'color' => ['argb' => '000000'],
                            ],
                        ],
                    ]);

                $letterAscii = ord('E');
                $row = 4 + $dataSize + 1;
                foreach (range(1, 4) as $i) {
                    $event->sheet->getStyle($letterMap[$letterAscii] . $row . ':' . $letterMap[$letterAscii] . $row)
                        ->applyFromArray([
                            'borders' => [
                                'allBorders' => [
                                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                                    'color' => ['argb' => '000000'],
                                ],
                            ],
                        ]);
                    $row++;
                }

                $letterAscii = ord('F');
                $row = 4 + $dataSize + 1;
                foreach (range(1, 4) as $i) {
                    $letterAscii = ord('F');
                    foreach (range(1, 7) as $j) {
                        $event->sheet->getStyle($letterMap[$letterAscii] . $row . ':' . $letterMap[$letterAscii + 1] . $row)
                            ->applyFromArray([
                                'borders' => [
                                    'allBorders' => [
                                        'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                                        'color' => ['argb' => '000000'],
                                    ],
                                ],
                            ]);
                        $letterAscii += 2;
                    }
                    $row++;
                }
                $letterAscii = ord('T');
                $row = 4 + $dataSize + 1;
                foreach (range(1, 4) as $i) {
                    $event->sheet->getStyle($letterMap[$letterAscii] . $row . ':' . $letterMap[$letterAscii] . ($row + 3))
                        ->applyFromArray([
                            'borders' => [
                                'allBorders' => [
                                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                                    'color' => ['argb' => '000000'],
                                ],
                            ],
                        ]);
                }

                $startColumn = 'E';
                $endColumn = 'T';
                $row = 5;
                if ($dataSize > 0) {
                    foreach (range(1, $dataSize) as $i) {
                        $event->sheet->getStyle($startColumn . $row . ':' . $endColumn . $row)
                            ->applyFromArray([
                                'borders' => [
                                    'bottom' => [
                                        'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                                        'color' => ['argb' => '000000'],
                                    ],
                                ]]);
                        $row++;
                    }
                }

                $letterAscii = ord('F');
                $startRow = 5;
                $endRow = 4 + $dataSize;
                foreach (range(1, 8) as $i) {
                    $event->sheet->getStyle($letterMap[$letterAscii] . $startRow . ':' . $letterMap[$letterAscii] . $endRow)
                        ->applyFromArray([
                            'borders' => [
                                'right' => [
                                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                                    'color' => ['argb' => '000000'],
                                ],
                            ]]);
                    $letterAscii += 2;
                }

                switch ($yearType) {
                    case 'first':
                        $letterAscii = ord('M');
                        $row = 1;
                        foreach (range(1, 4) as $i) {
                            $event->sheet->getStyle($letterMap[$letterAscii] . $row . ':' . $letterMap[$letterAscii + 1] . $row)
                                ->applyFromArray([
                                    'borders' => [
                                        'allBorders' => [
                                            'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                                            'color' => ['argb' => '000000'],
                                        ],
                                    ],
                                ]);
                            $event->sheet->getStyle($letterMap[$letterAscii] . ($row + 1) . ':' . $letterMap[$letterAscii + 1] . ($row + 2))
                                ->applyFromArray([
                                    'borders' => [
                                        'allBorders' => [
                                            'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                                            'color' => ['argb' => '000000'],
                                        ],
                                    ],
                                ]);
                            $letterAscii += 2;
                        }

                        //插入圖片
                        if (intval($progress['progress_point']) == 0 && intval($progress['version']) > 0) {
                            $path = public_path('/img/SY/');
                            //作成
                            if (file_exists($path . 'SY-1.png')) {
                                $letterAscii = ord('M');
                                $row = 2;
                                $drawing = new Drawing();
                                $drawing->setPath($path . 'SY-1.png');
                                $drawing->setHeight(50);
                                $drawing->setOffsetX(40);
                                $drawing->setOffsetY(5);
                                $drawing->setCoordinates($letterMap[$letterAscii] . $row);
                                $drawing->setWorksheet($event->sheet->getDelegate());
                            }
                            //審查
                            if (file_exists($path . 'SY-2.png')) {
                                $letterAscii = ord('O');
                                $row = 2;
                                $drawing = new Drawing();
                                $drawing->setPath($path . 'SY-2.png');
                                $drawing->setHeight(50);
                                $drawing->setOffsetX(40);
                                $drawing->setOffsetY(5);
                                $drawing->setCoordinates($letterMap[$letterAscii] . $row);
                                $drawing->setWorksheet($event->sheet->getDelegate());
                            }
                            //審查
                            if (file_exists($path . 'SY-3.png')) {
                                $letterAscii = ord('Q');
                                $row = 2;
                                $drawing = new Drawing();
                                $drawing->setPath($path . 'SY-3.png');
                                $drawing->setHeight(50);
                                $drawing->setOffsetX(40);
                                $drawing->setOffsetY(5);
                                $drawing->setCoordinates($letterMap[$letterAscii] . $row);
                                $drawing->setWorksheet($event->sheet->getDelegate());
                            }
                            //客戶承認
                            if (file_exists($path . 'SY-4.png')) {
                                $letterAscii = ord('S');
                                $row = 2;
                                $drawing = new Drawing();
                                $drawing->setPath($path . 'SY-4.png');
                                $drawing->setHeight(50);
                                $drawing->setOffsetX(40);
                                $drawing->setOffsetY(5);
                                $drawing->setCoordinates($letterMap[$letterAscii] . $row);
                                $drawing->setWorksheet($event->sheet->getDelegate());
                            }
                        }
                        break;

                    case 'last':
                        //總計、右下標題
                        $letterAscii = ord('O');
                        $row = 4 + $dataSize + 4 + 1;
                        //合併
                        $event->sheet->mergeCells($letterMap[$letterAscii] . $row . ':' . $letterMap[$letterAscii + 2] . ($row + 1));
                        $event->sheet->mergeCells($letterMap[$letterAscii] . ($row + 2) . ':' . $letterMap[$letterAscii + 2] . ($row + 3));
                        //置中
                        $event->sheet->getDelegate()->getStyle($letterMap[$letterAscii] . $row . ':' . $letterMap[$letterAscii + 2] . ($row + 1))
                            ->getAlignment()->setHorizontal('center')->setVertical('bottom');
                        $event->sheet->getDelegate()->getStyle($letterMap[$letterAscii] . ($row + 2) . ':' . $letterMap[$letterAscii + 2] . ($row + 3))
                            ->getAlignment()->setHorizontal('center')->setVertical('top');

                        //右下台數、仕切SH、千圓、千元 & 資料 & 單位
                        $letterAscii = ord('R');
                        $row = 4 + $dataSize + 4 + 1;
                        foreach (range(1, 4) as $i) {
                            $event->sheet->getDelegate()->getStyle($letterMap[$letterAscii] . $row . ':' . $letterMap[$letterAscii] . $row)
                                ->getAlignment()->setHorizontal('center')->setVertical('center');
                            $event->sheet->getDelegate()->getStyle($letterMap[$letterAscii + 1] . $row . ':' . $letterMap[$letterAscii + 1] . $row)
                                ->getAlignment()->setHorizontal('right')->setVertical('center');
                            $event->sheet->getDelegate()->getStyle($letterMap[$letterAscii + 2] . $row . ':' . $letterMap[$letterAscii + 2] . $row)
                                ->getAlignment()->setHorizontal('center')->setVertical('center');
                            $row++;
                        }

                        $letterAscii = ord('R');
                        $row = 4 + $dataSize + 4 + 3;
                        foreach (range(1, 3) as $i) {
                            //顏色藍色
                            $event->sheet->getDelegate()
                                ->getStyle($letterMap[$letterAscii] . $row . ':' . $letterMap[$letterAscii] . ($row + 1))
                                ->getFont()->getColor()->setARGB('0000F1');
                            $letterAscii++;
                        }

                        $letterAscii = ord('R');
                        $row = 4 + $dataSize + 4 + 2;
                        $event->sheet->getDelegate()
                            ->getStyle($letterMap[$letterAscii] . $row . ':' . $letterMap[$letterAscii] . $row)
                            ->getFont()->setItalic(true);
                        $event->sheet->getDelegate()
                            ->getStyle($letterMap[$letterAscii] . ($row + 2) . ':' . $letterMap[$letterAscii + 1] . ($row + 2))
                            ->getFont()->setItalic(true);

                        $letterAscii = ord('O');
                        $row = 4 + $dataSize + 4 + 1;
                        foreach (range(1, 2) as $i) {
                            $event->sheet->getStyle($letterMap[$letterAscii] . $row . ':' . $letterMap[$letterAscii + 2] . ($row + 1))
                                ->applyFromArray([
                                    'borders' => [
                                        'allBorders' => [
                                            'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                                            'color' => ['argb' => '000000'],
                                        ],
                                    ],
                                ]);
                            $row += 2;
                        }
                        $letterAscii = ord('R');
                        $row = 4 + $dataSize + 4 + 1;
                        $event->sheet->getStyle($letterMap[$letterAscii] . $row . ':' . $letterMap[$letterAscii + 2] . ($row + 3))
                            ->applyFromArray([
                                'borders' => [
                                    'allBorders' => [
                                        'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                                        'color' => ['argb' => '000000'],
                                    ],
                                ],
                            ]);
                        break;
                }

            },
        ];
    }
}
