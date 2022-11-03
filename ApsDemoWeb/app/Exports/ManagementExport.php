<?php
namespace App\Exports;

use App\Http\Traits\BaseTool;
use App\Http\Traits\MesApiTool;
use App\Models\Management;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithStrictNullComparison;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class ManagementExport implements FromView, WithEvents, WithStyles, WithTitle, WithStrictNullComparison
{
    use BaseTool, MesApiTool;

    protected $period_tw;
    protected $data;

    public function __construct($period_tw)
    {
        $this->period_tw = $period_tw;
    }

    public function view(): View
    {
        $period_tw = $this->period_tw;
        $version = $this->getProjectProgress('M', $period_tw, 0)['version'];
        $partition = $this->getPartition($period_tw);

        //大計劃維護列表
        $projects = Management::join('parameter_transport', 'management.transport_id', '=', DB::raw("CAST(parameter_transport.id AS VARCHAR)"))
            ->where('version', $version)
            ->where('period', $period_tw)
            ->select('management.*', 'parameter_transport.name AS transportName', DB::raw('(month_1+month_2+month_3+month_4+month_5+month_6+month_7+month_8+month_9+month_10+month_11+month_12) AS real_lot_number'))
            ->orderBy('item_code', 'ASC')
            ->orderBy('lot_no', 'ASC')
            ->get()
            ->toArray();

        //加入partition將工數放入projects中
        $management = array_map(function ($itemArray) use ($partition) {
            //工數
            $itemArray['firstWorkHour'] = ($partition[$itemArray['item_code']]['firstWorkHour'] ?? 0);
            $itemArray['lastWorkHour'] = ($partition[$itemArray['item_code']]['lastWorkHour'] ?? 0);
            return $itemArray;
        }, $projects);

        $data['management'] = $management;

        $this->data = $data;

        return view('system.projectMenu.exportDetail.management_export', [
            'tableData' => $data,
        ]);
    }

    //標題設定-WithTitle
    public function title(): string
    {
        return '大計劃';
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

                $management = $this->data['management'];
                $letterMap = $this->getLetterMap();

                $event->sheet->getDelegate()->getRowDimension('1')->setRowHeight(30);

                $event->sheet->getDelegate()->getColumnDimension('A')->setWidth(10);
                $event->sheet->getDelegate()->getColumnDimension('B')->setWidth(12);
                $event->sheet->getDelegate()->getColumnDimension('C')->setWidth(12);
                $event->sheet->getDelegate()->getColumnDimension('D')->setWidth(8);
                $event->sheet->getDelegate()->getColumnDimension('E')->setWidth(8);
                $event->sheet->getDelegate()->getColumnDimension('F')->setWidth(12);
                $event->sheet->getDelegate()->getColumnDimension('G')->setWidth(18);
                $event->sheet->getDelegate()->getColumnDimension('H')->setWidth(18);
                $event->sheet->getDelegate()->getColumnDimension('I')->setWidth(10);
                $event->sheet->getDelegate()->getColumnDimension('J')->setWidth(20);
                $event->sheet->getDelegate()->getColumnDimension('K')->setWidth(20);
                $event->sheet->getDelegate()->getColumnDimension('L')->setWidth(22);
                $event->sheet->getDelegate()->getColumnDimension('M')->setWidth(22);
                $event->sheet->getDelegate()->getColumnDimension('N')->setWidth(10);
                $event->sheet->getDelegate()->getColumnDimension('O')->setWidth(12);
                $event->sheet->getDelegate()->getColumnDimension('P')->setWidth(12);
                $event->sheet->getDelegate()->getColumnDimension('Q')->setWidth(10);
                $event->sheet->getDelegate()->getColumnDimension('R')->setWidth(10);
                $event->sheet->getDelegate()->getColumnDimension('S')->setWidth(10);

                //標題置中設定
                $event->sheet->getDelegate()->getStyle('A1:S1')
                    ->getAlignment()->setHorizontal('center')->setVertical('center');

                //靠右設定
                $startRow = 2;
                $endRow = 1 + count($management);
                $letterAscii = ord('A');
                $event->sheet->getDelegate()->getStyle($letterMap[$letterAscii] . $startRow . ':' . $letterMap[$letterAscii] . $endRow)
                    ->getAlignment()->setHorizontal('right')->setVertical('center');
                $letterAscii = ord('D');
                $event->sheet->getDelegate()->getStyle($letterMap[$letterAscii] . $startRow . ':' . $letterMap[$letterAscii] . $endRow)
                    ->getAlignment()->setHorizontal('right')->setVertical('center');
                $letterAscii = ord('E');
                $event->sheet->getDelegate()->getStyle($letterMap[$letterAscii] . $startRow . ':' . $letterMap[$letterAscii] . $endRow)
                    ->getAlignment()->setHorizontal('right')->setVertical('center');
                $letterAscii = ord('F');
                $event->sheet->getDelegate()->getStyle($letterMap[$letterAscii] . $startRow . ':' . $letterMap[$letterAscii] . $endRow)
                    ->getAlignment()->setHorizontal('right')->setVertical('center');
                $letterAscii = ord('I');
                $event->sheet->getDelegate()->getStyle($letterMap[$letterAscii] . $startRow . ':' . $letterMap[$letterAscii] . $endRow)
                    ->getAlignment()->setHorizontal('right')->setVertical('center');
                $letterAscii = ord('J');
                $event->sheet->getDelegate()->getStyle($letterMap[$letterAscii] . $startRow . ':' . $letterMap[$letterAscii] . $endRow)
                    ->getAlignment()->setHorizontal('right')->setVertical('center');
                $letterAscii = ord('K');
                $event->sheet->getDelegate()->getStyle($letterMap[$letterAscii] . $startRow . ':' . $letterMap[$letterAscii] . $endRow)
                    ->getAlignment()->setHorizontal('right')->setVertical('center');
                $letterAscii = ord('L');
                $event->sheet->getDelegate()->getStyle($letterMap[$letterAscii] . $startRow . ':' . $letterMap[$letterAscii] . $endRow)
                    ->getAlignment()->setHorizontal('right')->setVertical('center');
                $letterAscii = ord('M');
                $event->sheet->getDelegate()->getStyle($letterMap[$letterAscii] . $startRow . ':' . $letterMap[$letterAscii] . $endRow)
                    ->getAlignment()->setHorizontal('right')->setVertical('center');

                //邊框設定
                $event->sheet->getStyle('A1:S1')->applyFromArray([
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                            'color' => ['argb' => '000000'],
                        ],
                    ],
                ]);

                //凍結窗格-------------------------------------------------------------------------------------------------------------------
                $event->sheet->getDelegate()->freezePane('A2');

                $startColumn = 'A';
                $endColumn = 'S';
                $startRow = 1;
                $endRow = 1 + count($management);

                //新細明體
                $event->sheet->getDelegate()
                    ->getStyle($startColumn . $startRow . ':' . $endColumn . $endRow)
                    ->getFont()->setName('PMingLiU');
            },
        ];
    }
}
