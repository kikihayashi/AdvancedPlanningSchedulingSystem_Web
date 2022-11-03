<?php

namespace App\Exports;

use App\Http\Traits\BaseTool;
use App\Http\Traits\MesApiTool;
use App\Models\Setting;
use App\Models\ShippingMonth;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class ShippingMonthExport implements FromView, WithEvents, WithStyles, WithTitle
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
        $version = $this->getProjectProgress('SM', $period_tw, $month)['version'];
        $partition = $this->getPartition($period_tw);
        $exchange = $this->getExchange($period_tw);

        //ISO編號
        $iso = Setting::where('memo', 'iso_shipping_month')->first()->setting_value;

        //顯示日期用
        $dateArray = ShippingMonth::join('parameter_transport', 'shipping_month.transport_id', '=', DB::raw("CAST(parameter_transport.id AS VARCHAR)"))
            ->selectRaw('date , transport_id, parameter_transport.abbreviation AS abbreviation, parameter_transport.name AS name')
            ->where('version', $version)
            ->where('period', $period_tw)
            ->where('date', '!=', 0) //把除了日期為0以外的日期取出來
            ->where('number', '!=', 0) //把除了數量為0以外的日期取出來
            ->groupBy('date', 'transport_id', 'parameter_transport.abbreviation', 'parameter_transport.name') //只需要不重複的日期和出荷方式
            ->orderBy('date')
            ->orderBy('transport_id')
            ->get()
            ->toArray();

        //蒐集當前資料擁有的出荷方式
        $transportMap = array();
        foreach ($dateArray as $dateInfo) {
            if (!in_array('(' . $dateInfo['abbreviation'] . ')-' . $dateInfo['name'], $transportMap)) {
                $transportMap[] = '(' . $dateInfo['abbreviation'] . ')-' . $dateInfo['name'];
            }
        }

        //日期顯示規則A~Z
        $letter = 'A';
        for ($i = 0; $i < count($dateArray); $i++) {
            $dateArray[$i]['letter'] = $letter++;
        }

        //月度計畫合併線別，SQL指令：COALESCE改用ISNULL也可以，不過PostgreSQL、MySQL沒有ISNULL用法
        $sqlCommand = "SELECT shipping_month.*,
        COALESCE(equipment.line,'0') AS line_no
        FROM shipping_month LEFT JOIN equipment
        ON shipping_month.item_code = equipment.item_code
        WHERE period = ':period' AND month = ':month' AND version = ':version'
        AND lot_no <> 0 AND transport_id <> -1 ORDER BY item_code";

        //替換對應值
        $sqlCommand = str_replace(':period', $period_tw, $sqlCommand);
        $sqlCommand = str_replace(':month', $month, $sqlCommand);
        $sqlCommand = str_replace(':version', $version, $sqlCommand);

        //月度出荷計畫列表(含線別、內藏)
        $projects = DB::select($sqlCommand);

        //將object轉成array，並加入partition將工數放入projects中
        $shippingMonth = array_map(function ($itemObject) use ($partition, $exchange, $month) {
            //將DB查詢到的資料轉成array
            $itemArray = (array) $itemObject;
            //工數
            $itemArray['cost'] = ($partition[$itemArray['item_code']][((4 <= $month && $month <= 9) ? 'first' : 'last') . 'Cost'] ?? 0);
            return $itemArray;
        }, $projects);

        $data['iso'] = $iso;
        $data['period_tw'] = $period_tw;
        $data['month'] = $month;
        $data['exchange'] = $exchange[((4 <= $month && $month <= 9) ? 'first' : 'last')];
        $data['dateArray'] = $dateArray;
        $data['transportMap'] = $transportMap;
        $data['progress'] = $this->getProjectProgress('SM', $period_tw, $month);
        $data['shippingMonth'] = $shippingMonth;

        $this->data = $data;

        return view('system.projectMenu.exportDetail.shippingMonth_export', [
            'tableData' => $data,
        ]);
    }

    //標題設定-WithTitle
    public function title(): string
    {
        return '月度出荷計劃';
    }

    //字體設定-WithStyles
    public function styles(Worksheet $sheet)
    {
        return [
            // Style the first row as bold text.
            3 => ['font' => ['bold' => true]],
            4 => ['font' => ['bold' => true]],
            5 => ['font' => ['bold' => true]],

            // // Styling a specific cell by coordinate.
            // 'A1:CC200' => ['font' =>
            //     [
            //         'italic' => true,
            //         'family' => 'PMingLiU',
            //     ]],

            // // Styling an entire column.
            // 'C'  => ['font' => ['size' => 16]],
        ];
    }

    //Cell大小設定-WithEvents
    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                //所需資料
                $dateArray = $this->data['dateArray'];
                $transportMap = $this->data['transportMap'];
                $shippingMonth = $this->data['shippingMonth'];
                $progress = $this->data['progress'];
                $letterMap = $this->getLetterMap();

                //最上層高度
                $event->sheet->getDelegate()->getRowDimension('1')->setRowHeight(15);
                //標題
                $event->sheet->mergeCells('A2:E2');
                //匯率
                $event->sheet->mergeCells('F2:G2');

                //運輸方式--------------------------------------------------------------------------------------------------------------------
                $letterAscii = ord('H');
                foreach (range(1, count($transportMap) + 2) as $transportNumber) {
                    //合併
                    $event->sheet->mergeCells($letterMap[$letterAscii] . '2:' . $letterMap[$letterAscii + 1] . '2');
                    $letterAscii += 2;
                }

                //品名、單價、計畫出荷合計置中--------------------------------------------------------------------------------------------------
                $letterAscii = ord('A');
                $startColumn = $letterMap[$letterAscii];
                $endColumn = $letterMap[$letterAscii + 1 + 3 * (count($dateArray) + 2)];
                //品名合併
                $event->sheet->mergeCells('A3:A5');
                //品名寬度
                $event->sheet->getDelegate()->getColumnDimension('A')->setWidth(18);
                //品名、單價、計畫出荷合計的標題置中
                $event->sheet->getDelegate()->getStyle($startColumn . '3:' . $endColumn . '5')
                    ->getAlignment()->setHorizontal('center')->setVertical('center');

                //單價--------------------------------------------------------------------------------------------------------------------
                $letterAscii = ord('B');
                $startRow = 6;
                $endRow = $startRow + (count($shippingMonth) - 1);
                //單價合併
                $event->sheet->mergeCells('B3:B5');
                //單價寬度
                $event->sheet->getDelegate()->getColumnDimension('B')->setWidth(12);
                //單價偏右
                $event->sheet->getDelegate()->getStyle($letterMap[$letterAscii] . $startRow . ':' . $letterMap[$letterAscii] . $endRow)
                    ->getAlignment()->setHorizontal('right');

                //ITKT、出荷計畫、Lot、台數、金額、計畫出荷合計-------------------------------------------------------------------------------------------
                $letterAscii = ord('C');
                foreach ($dateArray as $dateInfo) {
                    //合併
                    $event->sheet->mergeCells($letterMap[$letterAscii] . '3:' . ($letterMap[$letterAscii + 2]) . '3'); //ITKT
                    $event->sheet->mergeCells($letterMap[$letterAscii] . '4:' . ($letterMap[$letterAscii + 2]) . '4'); //出荷計畫
                    //寬度
                    $event->sheet->getDelegate()->getColumnDimension($letterMap[$letterAscii])->setWidth(5); //Lot
                    $event->sheet->getDelegate()->getColumnDimension($letterMap[$letterAscii + 1])->setWidth(10); //台數
                    $event->sheet->getDelegate()->getColumnDimension($letterMap[$letterAscii + 2])->setWidth(15); //金額
                    $letterAscii += 3;
                }
                //合併(空格)
                $event->sheet->mergeCells($letterMap[$letterAscii] . '3:' . ($letterMap[$letterAscii + 2]) . '3');
                $event->sheet->mergeCells($letterMap[$letterAscii] . '4:' . ($letterMap[$letterAscii + 2]) . '4');
                //寬度(空格)
                $event->sheet->getDelegate()->getColumnDimension($letterMap[$letterAscii])->setWidth(5);
                $event->sheet->getDelegate()->getColumnDimension($letterMap[$letterAscii + 1])->setWidth(10);
                $event->sheet->getDelegate()->getColumnDimension($letterMap[$letterAscii + 2])->setWidth(15);
                $letterAscii += 3;
                //合併(合計)
                $event->sheet->mergeCells($letterMap[$letterAscii] . '3:' . $letterMap[$letterAscii + 2] . '3');
                $event->sheet->mergeCells($letterMap[$letterAscii] . '4:' . $letterMap[$letterAscii + 2] . '4');
                $event->sheet->mergeCells($letterMap[$letterAscii + 1] . '5:' . $letterMap[$letterAscii + 2] . '5'); //金額
                //寬度(合計)
                $event->sheet->getDelegate()->getColumnDimension($letterMap[$letterAscii])->setWidth(10);
                $event->sheet->getDelegate()->getColumnDimension($letterMap[$letterAscii + 1])->setWidth(8);

                //日幣、台幣金額合計---------------------------------------------------------------------------------------------------------------------
                //合併(日幣)
                $event->sheet->mergeCells('A' . (5 + count($shippingMonth) + 1) . ':' . 'B' . (5 + count($shippingMonth) + 1));
                //合併(台幣)
                $event->sheet->mergeCells('A' . (5 + count($shippingMonth) + 2) . ':' . 'B' . (5 + count($shippingMonth) + 2));
                //置中(日幣)
                $event->sheet->getDelegate()->getStyle('A' . (5 + count($shippingMonth) + 1) . ':' . 'B' . (5 + count($shippingMonth) + 1))
                    ->getAlignment()->setHorizontal('center')->setVertical('center');
                //置中(台幣)
                $event->sheet->getDelegate()->getStyle('A' . (5 + count($shippingMonth) + 2) . ':' . 'B' . (5 + count($shippingMonth) + 2))
                    ->getAlignment()->setHorizontal('center')->setVertical('center');
                //粗體
                $event->sheet->getDelegate()->getStyle(5 + count($shippingMonth) + 1)->getFont()->setBold(true);
                $event->sheet->getDelegate()->getStyle(5 + count($shippingMonth) + 2)->getFont()->setBold(true);

                //品名、計劃出荷合計的資料-------------------------------------------------------------------------------------------------------------------------
                $letterAscii = ord('A');
                $row = 6;
                foreach ($shippingMonth as $shipping) {
                    //高度
                    $event->sheet->getDelegate()->getRowDimension(strval($row))->setRowHeight(25);
                    //位置
                    $event->sheet->getDelegate()
                        ->getStyle($letterMap[$letterAscii] . $row . ':' . $letterMap[$letterAscii + 1 + 3 * (count($dateArray) + 2)] . $row)
                        ->getAlignment()->setVertical('center');
                    //計劃出荷合計金額合併
                    $event->sheet->mergeCells($letterMap[$letterAscii + 1 + 3 * (count($dateArray) + 1) + 2] . $row . ':' . $letterMap[$letterAscii + 1 + 3 * (count($dateArray) + 2)] . $row);
                    $row++;
                }
                //日幣合計、計劃出荷合計、金額合併
                $event->sheet->mergeCells($letterMap[$letterAscii + 1 + 3 * (count($dateArray) + 1) + 2] . $row . ':' . $letterMap[$letterAscii + 1 + 3 * (count($dateArray) + 2)] . $row);
                //台幣合計、計劃出荷合計、金額合併
                $event->sheet->mergeCells($letterMap[$letterAscii + 1 + 3 * (count($dateArray) + 1) + 2] . ($row + 1) . ':' . $letterMap[$letterAscii + 1 + 3 * (count($dateArray) + 2)] . ($row + 1));

                //Lot、台數、金額的資料位置-----------------------------------------------------------------------------------------------------------
                $letterAscii = ord('C');
                $startRow = 6;
                $endRow = $startRow + (count($shippingMonth) - 1) + 2; //startRow + (機種總數量-1) + 2(日幣、台幣合計)
                foreach ($dateArray as $dateInfo) {
                    //Lot置中
                    $event->sheet->getDelegate()->getStyle($letterMap[$letterAscii] . $startRow . ':' . $letterMap[$letterAscii] . $endRow)
                        ->getAlignment()->setHorizontal('center');
                    //台數偏右
                    $event->sheet->getDelegate()->getStyle($letterMap[$letterAscii + 1] . $startRow . ':' . $letterMap[$letterAscii + 1] . $endRow)
                        ->getAlignment()->setHorizontal('right');
                    //金額偏右
                    $event->sheet->getDelegate()->getStyle($letterMap[$letterAscii + 2] . $startRow . ':' . $letterMap[$letterAscii + 2] . $endRow)
                        ->getAlignment()->setHorizontal('right');
                    $letterAscii += 3;
                }
                //合計台數偏右
                $event->sheet->getDelegate()->getStyle($letterMap[$letterAscii + 3] . $startRow . ':' . $letterMap[$letterAscii + 3] . $endRow)
                    ->getAlignment()->setHorizontal('right');
                //合計金額偏右
                $event->sheet->getDelegate()->getStyle($letterMap[$letterAscii + 4] . $startRow . ':' . $letterMap[$letterAscii + 5] . $endRow)
                    ->getAlignment()->setHorizontal('right');

                //整體邊框設定-------------------------------------------------------------------------------------------------------------
                $letterAscii = ord('A');
                $row = 3;
                $event->sheet->getStyle($letterMap[$letterAscii] . $row . ':' . $letterMap[$letterAscii + 1 + (3 * (count($dateArray) + 2))] . ($row + 2 + count($shippingMonth) + 2))
                    ->applyFromArray([
                        'borders' => [
                            'allBorders' => [
                                'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                                'color' => ['argb' => '000000'],
                            ],
                        ],
                    ]);

                //顏色設定-------------------------------------------------------------------------------------------------------------
                $letterAscii = ord('C');
                $row = 3;
                for ($i = 0; $i < count($dateArray); $i++) {
                    //顏色藍色
                    $event->sheet->getDelegate()
                        ->getStyle($letterMap[$letterAscii] . $row . ':' . ($letterMap[$letterAscii + 2]) . ($row + 2 + count($shippingMonth) + 2))
                        ->getFont()->getColor()->setARGB('0000FF');
                    $letterAscii += 6;
                }

                //簽章設定----------------------------------------------------------------------------------------------------------------
                //合併
                $letterAscii = ord('D');
                $row = 9 + count($shippingMonth);
                for ($i = 0; $i < 6; $i++) {
                    $event->sheet->mergeCells($letterMap[$letterAscii] . $row . ':' . $letterMap[$letterAscii + 1] . $row);
                    $event->sheet->mergeCells($letterMap[$letterAscii + 2] . $row . ':' . $letterMap[$letterAscii + 3] . $row);
                    $row++;
                }
                $letterAscii = ord('I');
                $row = 9 + count($shippingMonth);
                for ($i = 0; $i < 4; $i++) {
                    $event->sheet->mergeCells($letterMap[$letterAscii] . $row . ':' . $letterMap[$letterAscii + 2] . $row);
                    $event->sheet->mergeCells($letterMap[$letterAscii] . ($row + 1) . ':' . $letterMap[$letterAscii + 2] . ($row + 5));
                    $letterAscii += 3;
                }
                //高度設定
                $row = 9 + count($shippingMonth);
                $event->sheet->getDelegate()->getRowDimension(strval($row))->setRowHeight(20);

                //置中
                $letterAscii = ord('D');
                $row = 9 + count($shippingMonth);
                $event->sheet->getDelegate()
                    ->getStyle($letterMap[$letterAscii] . $row . ':' . $letterMap[$letterAscii + 16] . ($row + 5))
                    ->getAlignment()
                    ->setHorizontal('center')
                    ->setVertical('center');

                //邊框設定
                $letterAscii = ord('I');
                $row = 9 + count($shippingMonth);
                $event->sheet->getStyle($letterMap[$letterAscii] . $row . ':' . $letterMap[$letterAscii + 11] . ($row + 5))
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
                    $path = public_path('/img/SM/');
                    //作成
                    if (file_exists($path . 'SM-1.png')) {
                        $letterAscii = ord('J');
                        $row = 10 + count($shippingMonth);
                        $drawing = new Drawing();
                        $drawing->setPath($path . 'SM-1.png');
                        $drawing->setHeight(75);
                        $drawing->setOffsetX(35);
                        $drawing->setOffsetY(10);
                        $drawing->setCoordinates($letterMap[$letterAscii] . $row);
                        $drawing->setWorksheet($event->sheet->getDelegate());
                    }
                    //審查
                    if (file_exists($path . 'SM-2.png')) {
                        $letterAscii = ord('M');
                        $row = 10 + count($shippingMonth);
                        $drawing = new Drawing();
                        $drawing->setPath($path . 'SM-2.png');
                        $drawing->setHeight(75);
                        $drawing->setOffsetX(35);
                        $drawing->setOffsetY(10);
                        $drawing->setCoordinates($letterMap[$letterAscii] . $row);
                        $drawing->setWorksheet($event->sheet->getDelegate());
                    }
                    //審查
                    if (file_exists($path . 'SM-3.png')) {
                        $letterAscii = ord('P');
                        $row = 10 + count($shippingMonth);
                        $drawing = new Drawing();
                        $drawing->setPath($path . 'SM-3.png');
                        $drawing->setHeight(75);
                        $drawing->setOffsetX(30);
                        $drawing->setOffsetY(10);
                        $drawing->setCoordinates($letterMap[$letterAscii] . $row);
                        $drawing->setWorksheet($event->sheet->getDelegate());
                    }
                    //客戶承認
                    if (file_exists($path . 'SM-4.png')) {
                        $letterAscii = ord('R');
                        $row = 10 + count($shippingMonth);
                        $drawing = new Drawing();
                        $drawing->setPath($path . 'SM-4.png');
                        $drawing->setHeight(75);
                        $drawing->setOffsetX(60);
                        $drawing->setOffsetY(10);
                        $drawing->setCoordinates($letterMap[$letterAscii] . $row);
                        $drawing->setWorksheet($event->sheet->getDelegate());
                    }
                }

                //字型設定-------------------------------------------------------------------------------------------------------------
                $letterAscii = ord('A');
                $row = 2;
                //新細明體
                $event->sheet->getDelegate()
                    ->getStyle($letterMap[$letterAscii] . $row . ':' . $letterMap[$letterAscii + 1 + 3 * (count($dateArray) + 2 + 3)] . ($row + 5 + count($shippingMonth) + 7))
                    ->getFont()->setName('PMingLiU');

                //凍結窗格-------------------------------------------------------------------------------------------------------------------
                $event->sheet->freezePane('B6');
            },
        ];
    }

    //圖片匯入，需要 implements WithDrawings
    // public function drawings()
    // {
    //     $drawing = new Drawing();
    //     $drawing->setPath(public_path('/img/sign.png'));
    //     $drawing->setHeight(75);
    //     $drawing->setCoordinates('J18');

    //     $drawing2 = new Drawing();
    //     $drawing2->setPath(public_path('/img/sign.png'));
    //     $drawing2->setHeight(70);
    //     $drawing2->setCoordinates('M18');

    //     return array();
    //     return [$drawing, $drawing2];
    // }
}
